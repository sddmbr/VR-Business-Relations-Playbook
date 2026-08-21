<?php

class Monica_Relationships {

    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'add_relationships_meta_box' ] );
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

        $relationship_types = get_transient( 'monica_relationship_types' );
        if ( false === $relationship_types ) {
            $relationship_types = $api->get( 'relationshiptypes' );
            if ( ! is_wp_error( $relationship_types ) ) {
                set_transient( 'monica_relationship_types', $relationship_types, 86400 );
            }
        }

        $contacts = get_posts( [
            'post_type'      => 'monica_contact',
            'posts_per_page' => -1,
            'post__not_in'   => [ $post->ID ],
        ] );

        include dirname( __DIR__ ) . '/templates/meta-box-relationships.php';
    }
}
