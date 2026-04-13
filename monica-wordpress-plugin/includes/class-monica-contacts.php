<?php

class Monica_Contacts {

    public function __construct() {
        add_action( 'init', [ $this, 'register_contact_post_type' ] );
        add_action( 'add_meta_boxes', [ $this, 'add_contact_meta_boxes' ] );
        add_action( 'save_post_monica_contact', [ $this, 'save_contact_meta_data' ] );
    }

    public function register_contact_post_type() {
        $labels = [
            'name'               => _x( 'Contacts', 'post type general name', 'monica-integration' ),
            'singular_name'      => _x( 'Contact', 'post type singular name', 'monica-integration' ),
            'menu_name'          => _x( 'Monica Contacts', 'admin menu', 'monica-integration' ),
            'name_admin_bar'     => _x( 'Contact', 'add new on admin bar', 'monica-integration' ),
            'add_new'            => _x( 'Add New', 'contact', 'monica-integration' ),
            'add_new_item'       => __( 'Add New Contact', 'monica-integration' ),
            'new_item'           => __( 'New Contact', 'monica-integration' ),
            'edit_item'          => __( 'Edit Contact', 'monica-integration' ),
            'view_item'          => __( 'View Contact', 'monica-integration' ),
            'all_items'          => __( 'All Contacts', 'monica-integration' ),
            'search_items'       => __( 'Search Contacts', 'monica-integration' ),
            'parent_item_colon'  => __( 'Parent Contacts:', 'monica-integration' ),
            'not_found'          => __( 'No contacts found.', 'monica-integration' ),
            'not_found_in_trash' => __( 'No contacts found in Trash.', 'monica-integration' ),
        ];

        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => [ 'slug' => 'monica-contact' ],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => [ 'title', 'editor', 'author' ],
            'menu_icon'          => 'dashicons-groups',
        ];

        register_post_type( 'monica_contact', $args );
    }

    public function add_contact_meta_boxes() {
        add_meta_box(
            'monica_contact_details',
            __( 'Contact Details', 'monica-integration' ),
            [ $this, 'render_contact_details_meta_box' ],
            'monica_contact',
            'normal',
            'high'
        );
    }

    public function render_contact_details_meta_box( $post ) {
        wp_nonce_field( 'monica_contact_details', 'monica_contact_details_nonce' );

        $first_name = get_post_meta( $post->ID, '_monica_first_name', true );
        $last_name  = get_post_meta( $post->ID, '_monica_last_name', true );
        $email      = get_post_meta( $post->ID, '_monica_email', true );
        ?>
        <p>
            <label for="monica_first_name"><?php _e( 'First Name', 'monica-integration' ); ?></label>
            <input type="text" id="monica_first_name" name="monica_first_name" value="<?php echo esc_attr( $first_name ); ?>" />
        </p>
        <p>
            <label for="monica_last_name"><?php _e( 'Last Name', 'monica-integration' ); ?></label>
            <input type="text" id="monica_last_name" name="monica_last_name" value="<?php echo esc_attr( $last_name ); ?>" />
        </p>
        <p>
            <label for="monica_email"><?php _e( 'Email', 'monica-integration' ); ?></label>
            <input type="email" id="monica_email" name="monica_email" value="<?php echo esc_attr( $email ); ?>" />
        </p>
        <?php
    }

    public function save_contact_meta_data( $post_id ) {
        if ( ! isset( $_POST['monica_contact_details_nonce'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['monica_contact_details_nonce'], 'monica_contact_details' ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $first_name = sanitize_text_field( $_POST['monica_first_name'] );
        $last_name  = sanitize_text_field( $_POST['monica_last_name'] );
        $email      = sanitize_email( $_POST['monica_email'] );

        update_post_meta( $post_id, '_monica_first_name', $first_name );
        update_post_meta( $post_id, '_monica_last_name', $last_name );
        update_post_meta( $post_id, '_monica_email', $email );

        $api = new Monica_API();
        $monica_contact_id = get_post_meta( $post_id, '_monica_contact_id', true );

        $contact_data = [
            'first_name' => $first_name,
            'last_name'  => $last_name,
            'email'      => $email,
        ];

        if ( $monica_contact_id ) {
            $api->put( "contacts/{$monica_contact_id}", [
                'body' => json_encode( $contact_data ),
            ] );
        } else {
            $response = $api->post( 'contacts', [
                'body' => json_encode( $contact_data ),
            ] );

            if ( ! is_wp_error( $response ) && isset( $response['data']['id'] ) ) {
                update_post_meta( $post_id, '_monica_contact_id', $response['data']['id'] );
            }
        }
    }
}
