<?php

class Monica_Relationships {

    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'add_relationships_meta_box' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    public function enqueue_scripts( $hook ) {
        global $post;
        if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
            return;
        }
        if ( ! $post || $post->post_type !== 'monica_contact' ) {
            return;
        }

        wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 'monica-relationships-js', plugin_dir_url( __FILE__ ) . '../assets/js/monica-relationships.js', [ 'jquery', 'jquery-ui-autocomplete' ], '1.0.0', true );
        wp_localize_script( 'monica-relationships-js', 'monicaRelationshipsVars', [
            'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'monica_search_contacts' ),
            'postId'   => $post->ID,
        ] );

        // Add inline style to ensure UI autocomplete shows above WordPress UI
        wp_add_inline_style( 'wp-admin', '.ui-autocomplete { z-index: 10001 !important; }' );
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
                <label for="monica_related_contact_search"><?php _e( 'Contact', 'monica-integration' ); ?></label>
                <input type="text" id="monica_related_contact_search" name="monica_related_contact_search" value="" placeholder="<?php esc_attr_e( 'Search contacts...', 'monica-integration' ); ?>" autocomplete="off" />
                <input type="hidden" id="monica_related_contact_id" name="monica_related_contact_id" value="" />
            </p>
            <input type="hidden" name="monica_contact_id" value="<?php echo esc_attr( $monica_contact_id ); ?>" />
            <?php wp_nonce_field( 'monica_add_relationship', 'monica_add_relationship_nonce' ); ?>
            <input type="submit" name="monica_add_relationship" class="button" value="<?php _e( 'Add Relationship', 'monica-integration' ); ?>" />
        </form>
        <?php
    }
}
