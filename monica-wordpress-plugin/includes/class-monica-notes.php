<?php

class Monica_Notes {

    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'add_notes_meta_box' ] );
        add_action( 'admin_init', [ $this, 'handle_add_note' ] );
    }

    public function handle_add_note() {
        if ( isset( $_POST['monica_add_note'] ) && isset( $_POST['monica_add_note_nonce'] ) ) {
            if ( ! wp_verify_nonce( $_POST['monica_add_note_nonce'], 'monica_add_note' ) ) {
                return;
            }

            if ( ! current_user_can( 'edit_post', $_POST['monica_post_id'] ?? 0 ) ) {
                return;
            }

            $contact_id = absint( $_POST['monica_contact_id'] ?? 0 );
            $body       = wp_kses_post( $_POST['monica_note_body'] ?? '' );

            if ( ! $contact_id || ! $body ) {
                return;
            }

            $api = new Monica_API();
            $api->post( "contacts/{$contact_id}/notes", [
                'body' => json_encode( [
                    'body' => $body,
                ] ),
            ] );

            wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
            exit;
        }
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
            <input type="hidden" name="monica_post_id" value="<?php echo esc_attr( $post->ID ); ?>" />
            <?php wp_nonce_field( 'monica_add_note', 'monica_add_note_nonce' ); ?>
            <input type="submit" name="monica_add_note" class="button" value="<?php _e( 'Add Note', 'monica-integration' ); ?>" />
        </form>
        <?php
    }
}
