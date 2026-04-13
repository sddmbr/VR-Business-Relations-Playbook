<?php

class Monica_Notes {

    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'add_notes_meta_box' ] );
    }

    public function add_notes_meta_box() {
        add_meta_box(
            'monica_notes',
            __( 'Notes', 'monica-integration' ),
            [ $this, 'render_notes_meta_box' ],
            'monica_contact',
            'normal',
            'default'
        );
    }

    public function render_notes_meta_box( $post ) {
        $api = new Monica_API();
        $monica_contact_id = get_post_meta( $post->ID, '_monica_contact_id', true );

        if ( ! $monica_contact_id ) {
            echo '<p>' . __( 'Save the contact to view notes.', 'monica-integration' ) . '</p>';
            return;
        }

        $notes = $api->get( "contacts/{$monica_contact_id}/notes" );

        if ( is_wp_error( $notes ) ) {
            echo '<p>' . $notes->get_error_message() . '</p>';
            return;
        }

        if ( empty( $notes['data'] ) ) {
            echo '<p>' . __( 'No notes found.', 'monica-integration' ) . '</p>';
        } else {
            echo '<ul>';
            foreach ( $notes['data'] as $note ) {
                echo '<li>';
                echo wpautop( esc_html( $note['body'] ) );
                echo '</li>';
            }
            echo '</ul>';
        }
        ?>
        <h4><?php _e( 'Add New Note', 'monica-integration' ); ?></h4>
        <form action="" method="post">
            <p>
                <textarea id="monica_note_body" name="monica_note_body" rows="5" style="width: 100%;"></textarea>
            </p>
            <input type="hidden" name="monica_contact_id" value="<?php echo esc_attr( $monica_contact_id ); ?>" />
            <?php wp_nonce_field( 'monica_add_note', 'monica_add_note_nonce' ); ?>
            <input type="submit" name="monica_add_note" class="button" value="<?php _e( 'Add Note', 'monica-integration' ); ?>" />
        </form>
        <?php
    }
}
