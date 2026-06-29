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
                    $relationship_types = get_transient( 'monica_relationship_types' );
                    if ( false === $relationship_types ) {
                        $relationship_types = $api->get( 'relationshiptypes' );
                        if ( ! is_wp_error( $relationship_types ) ) {
                            set_transient( 'monica_relationship_types', $relationship_types, 86400 );
                        }
                    }
                    if ( ! is_wp_error( $relationship_types ) && ! empty( $relationship_types['data'] ) ) {
                        foreach ( $relationship_types['data'] as $relationship_type ) {
                            echo '<option value="' . esc_attr( $relationship_type['id'] ) . '">' . esc_html( $relationship_type['name'] ) . '</option>';
                        }
                    }
                    ?>
                </select>
            </p>
            <p>
                <label for="monica_related_contact_id"><?php _e( 'Contact', 'monica-integration' ); ?></label>
                <select id="monica_related_contact_id" name="monica_related_contact_id">
                    <?php
                    $contacts = get_posts( [
                        'post_type'      => 'monica_contact',
                        'posts_per_page' => -1,
                        'post__not_in'   => [ $post->ID ],
                    ] );
                    if ( ! empty( $contacts ) ) {
                        foreach ( $contacts as $contact ) {
                            $monica_id = get_post_meta( $contact->ID, '_monica_contact_id', true );
                            if ( $monica_id ) {
                                echo '<option value="' . esc_attr( $monica_id ) . '">' . esc_html( $contact->post_title ) . '</option>';
                            }
                        }
                    }
                    ?>
                </select>
            </p>
            <input type="hidden" name="monica_contact_id" value="<?php echo esc_attr( $monica_contact_id ); ?>" />
            <input type="hidden" name="monica_post_id" value="<?php echo esc_attr( $post->ID ); ?>" />
            <?php wp_nonce_field( 'monica_add_relationship', 'monica_add_relationship_nonce' ); ?>
            <input type="submit" name="monica_add_relationship" class="button" value="<?php _e( 'Add Relationship', 'monica-integration' ); ?>" />
        </form>
        <?php
    }
}
