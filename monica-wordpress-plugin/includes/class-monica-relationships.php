<?php

class Monica_Relationships {

    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'add_relationships_meta_box' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'wp_ajax_monica_search_contacts', [ $this, 'ajax_search_contacts' ] );
    }

    public function add_relationships_meta_box() {
        add_meta_box(
            'monica_relationships',
            __( 'Relationships', 'monica-integration' ),
            [ $this, 'render_relationships_meta_box' ],
            'monica_contact',
            'side',
            'default'
        );
    }

    public function enqueue_scripts( $hook ) {
        if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
            return;
        }

        $screen = get_current_screen();
        if ( ! $screen || 'monica_contact' !== $screen->post_type ) {
            return;
        }

        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script(
            'monica-relationships',
            plugin_dir_url( __FILE__ ) . '../assets/js/monica-relationships.js',
            [ 'jquery', 'jquery-ui-autocomplete' ],
            '1.0.0',
            true
        );

        wp_localize_script( 'monica-relationships', 'monicaRelationships', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'monica_search_contacts' ),
            'post_id'  => get_the_ID(),
        ] );

        // Enqueue jQuery UI styles for autocomplete
        wp_enqueue_style( 'jquery-ui-autocomplete', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css' );

        // Basic styling for the autocomplete dropdown to ensure it's usable if theme doesn't support it well
        $custom_css = "
            .ui-autocomplete {
                z-index: 10001 !important;
                max-height: 200px;
                overflow-y: auto;
                overflow-x: hidden;
            }
        ";
        wp_add_inline_style( 'jquery-ui-autocomplete', $custom_css );
    }

    public function ajax_search_contacts() {
        check_ajax_referer( 'monica_search_contacts', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( 'Forbidden' );
        }

        $term    = isset( $_GET['term'] ) ? sanitize_text_field( $_GET['term'] ) : '';
        $exclude = isset( $_GET['exclude'] ) ? absint( $_GET['exclude'] ) : 0;

        $args = [
            'post_type'      => 'monica_contact',
            'posts_per_page' => 20,
            's'              => $term,
        ];

        if ( $exclude ) {
            $args['post__not_in'] = [ $exclude ];
        }

        $contacts = get_posts( $args );
        $results  = [];

        if ( ! empty( $contacts ) ) {
            foreach ( $contacts as $contact ) {
                $monica_id = get_post_meta( $contact->ID, '_monica_contact_id', true );
                if ( $monica_id ) {
                    $results[] = [
                        'id'    => $monica_id,
                        'label' => $contact->post_title,
                        'value' => $contact->post_title,
                    ];
                }
            }
        }

        wp_send_json( $results );
    }

    public function render_relationships_meta_box( $post ) {
        $api = new Monica_API();
        $monica_contact_id = get_post_meta( $post->ID, '_monica_contact_id', true );

        if ( ! $monica_contact_id ) {
            echo '<p>' . __( 'Save the contact to view relationships.', 'monica-integration' ) . '</p>';
            return;
        }

        $relationships = $api->get( "contacts/{$monica_contact_id}/relationships" );

        if ( is_wp_error( $relationships ) ) {
            echo '<p>' . $relationships->get_error_message() . '</p>';
            return;
        }

        if ( empty( $relationships['data'] ) ) {
            echo '<p>' . __( 'No relationships found.', 'monica-integration' ) . '</p>';
        } else {
            echo '<ul>';
            foreach ( $relationships['data'] as $relationship ) {
                echo '<li>';
                echo esc_html( $relationship['relationship_type']['name'] );
                echo ': ';
                echo esc_html( $relationship['contact']['first_name'] . ' ' . $relationship['contact']['last_name'] );
                echo '</li>';
            }
            echo '</ul>';
        }
        ?>
        <h4><?php _e( 'Add New Relationship', 'monica-integration' ); ?></h4>
        <form action="" method="post">
            <p>
                <label for="monica_relationship_type_id"><?php _e( 'Relationship Type', 'monica-integration' ); ?></label>
                <select id="monica_relationship_type_id" name="monica_relationship_type_id">
                    <?php
                    $relationship_types = $api->get( 'relationshiptypes' );
                    if ( ! is_wp_error( $relationship_types ) && ! empty( $relationship_types['data'] ) ) {
                        foreach ( $relationship_types['data'] as $relationship_type ) {
                            echo '<option value="' . esc_attr( $relationship_type['id'] ) . '">' . esc_html( $relationship_type['name'] ) . '</option>';
                        }
                    }
                    ?>
                </select>
            </p>
            <p>
                <label for="monica_contact_search"><?php _e( 'Contact', 'monica-integration' ); ?></label>
                <input type="text" id="monica_contact_search" class="widefat" placeholder="<?php esc_attr_e( 'Search contacts...', 'monica-integration' ); ?>" />
                <input type="hidden" id="monica_related_contact_id" name="monica_related_contact_id" value="" />
            </p>
            <input type="hidden" name="monica_contact_id" value="<?php echo esc_attr( $monica_contact_id ); ?>" />
            <?php wp_nonce_field( 'monica_add_relationship', 'monica_add_relationship_nonce' ); ?>
            <input type="submit" name="monica_add_relationship" class="button" value="<?php _e( 'Add Relationship', 'monica-integration' ); ?>" />
        </form>
        <?php
    }
}
