<?php

class Monica_Reminders {

    public function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'add_reminders_meta_box' ] );
    }

    public function add_reminders_meta_box() {
        add_meta_box(
            'monica_reminders',
            __( 'Reminders', 'monica-integration' ),
            [ $this, 'render_reminders_meta_box' ],
            'monica_contact',
            'side',
            'default'
        );
    }

    public function render_reminders_meta_box( $post ) {
        $api = new Monica_API();
        $monica_contact_id = get_post_meta( $post->ID, '_monica_contact_id', true );

        if ( ! $monica_contact_id ) {
            echo '<p>' . __( 'Save the contact to view reminders.', 'monica-integration' ) . '</p>';
            return;
        }

        $reminders = $api->get( "contacts/{$monica_contact_id}/reminders" );

        if ( is_wp_error( $reminders ) ) {
            echo '<p>' . $reminders->get_error_message() . '</p>';
            return;
        }

        if ( empty( $reminders['data'] ) ) {
            echo '<p>' . __( 'No reminders found.', 'monica-integration' ) . '</p>';
        } else {
            echo '<ul>';
            foreach ( $reminders['data'] as $reminder ) {
                echo '<li>';
                echo esc_html( $reminder['title'] );
                echo ' - ';
                echo esc_html( $reminder['reminder_date'] );
                echo '</li>';
            }
            echo '</ul>';
        }
        ?>
        <h4><?php _e( 'Add New Reminder', 'monica-integration' ); ?></h4>
        <form action="" method="post">
            <p>
                <label for="monica_reminder_title"><?php _e( 'Title', 'monica-integration' ); ?></label>
                <input type="text" id="monica_reminder_title" name="monica_reminder_title" />
            </p>
            <p>
                <label for="monica_reminder_date"><?php _e( 'Date', 'monica-integration' ); ?></label>
                <input type="date" id="monica_reminder_date" name="monica_reminder_date" />
            </p>
            <input type="hidden" name="monica_contact_id" value="<?php echo esc_attr( $monica_contact_id ); ?>" />
            <?php wp_nonce_field( 'monica_add_reminder', 'monica_add_reminder_nonce' ); ?>
            <input type="submit" name="monica_add_reminder" class="button" value="<?php _e( 'Add Reminder', 'monica-integration' ); ?>" />
        </form>
        <?php
    }
}
