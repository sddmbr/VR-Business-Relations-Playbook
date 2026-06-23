<?php
/**
 * Plugin Name: Monica Integration
 * Plugin URI: https://example.com/
 * Description: Integrates Monica with WordPress.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://example.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: monica-integration
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

require_once plugin_dir_path( __FILE__ ) . 'includes/class-monica-api.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-monica-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-monica-contacts.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-monica-reminders.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-monica-notes.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-monica-relationships.php';

function monica_integration_init() {
    new Monica_Settings();
    new Monica_Contacts();
    new Monica_Reminders();
    new Monica_Notes();
    new Monica_Relationships();
}
add_action( 'plugins_loaded', 'monica_integration_init' );

function monica_integration_oauth_redirect() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    if ( isset( $_GET['page'] ) && 'monica-integration' === $_GET['page'] && isset( $_GET['code'] ) ) {
        if ( ! isset( $_GET['state'] ) || ! wp_verify_nonce( $_GET['state'], 'monica_oauth_state' ) ) {
            wp_safe_redirect( admin_url( 'options-general.php?page=monica-integration&monica_error=invalid_state' ) );
            exit;
        }

        $api = new Monica_API();
        $redirect_uri = admin_url( 'options-general.php?page=monica-integration' );
        $data = $api->get_access_token( $_GET['code'], $redirect_uri );

        if ( isset( $data['access_token'] ) ) {
            update_option( 'monica_access_token', $data['access_token'] );
        }

        wp_redirect( $redirect_uri );
        exit;
    }
}
add_action( 'admin_init', 'monica_integration_oauth_redirect' );

function monica_integration_admin_notices() {
    if ( isset( $_GET['monica_error'] ) && 'invalid_state' === $_GET['monica_error'] ) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e( 'OAuth authorization failed: Invalid state parameter. Possible CSRF attack.', 'monica-integration' ); ?></p>
        </div>
        <?php
    }
}
add_action( 'admin_notices', 'monica_integration_admin_notices' );

function monica_integration_add_reminder() {
    if ( isset( $_POST['monica_add_reminder'] ) && isset( $_POST['monica_add_reminder_nonce'] ) ) {
        if ( ! wp_verify_nonce( $_POST['monica_add_reminder_nonce'], 'monica_add_reminder' ) ) {
            return;
        }

        $post_id = absint( $_POST['monica_post_id'] ?? 0 );
        if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $contact_id = absint( $_POST['monica_contact_id'] ?? 0 );
        $title      = sanitize_text_field( $_POST['monica_reminder_title'] ?? '' );
        $date       = sanitize_text_field( $_POST['monica_reminder_date'] ?? '' );

        if ( ! $contact_id || ! $title || ! $date ) {
            wp_safe_redirect( add_query_arg( 'monica_error', 'empty_fields', wp_get_referer() ? wp_get_referer() : admin_url() ) );
            exit;
        }

        $api = new Monica_API();
        $api->post( "contacts/{$contact_id}/reminders", [
            'body' => json_encode( [
                'title'          => $title,
                'reminder_date' => $date,
            ] ),
        ] );

        wp_redirect( $_SERVER['HTTP_REFERER'] );
        exit;
    }
}
add_action( 'admin_init', 'monica_integration_add_reminder' );

function monica_integration_add_note() {
    if ( isset( $_POST['monica_add_note'] ) && isset( $_POST['monica_add_note_nonce'] ) ) {
        if ( ! wp_verify_nonce( $_POST['monica_add_note_nonce'], 'monica_add_note' ) ) {
            return;
        }

        $post_id = absint( $_POST['monica_post_id'] ?? 0 );
        if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $contact_id = absint( $_POST['monica_contact_id'] ?? 0 );
        $body       = wp_kses_post( $_POST['monica_note_body'] ?? '' );

        if ( ! $contact_id || ! $body ) {
            wp_safe_redirect( add_query_arg( 'monica_error', 'empty_fields', wp_get_referer() ? wp_get_referer() : admin_url() ) );
            exit;
        }

        $api = new Monica_API();
        $api->post( "contacts/{$contact_id}/notes", [
            'body' => json_encode( [
                'body' => $body,
            ] ),
        ] );

        wp_redirect( $_SERVER['HTTP_REFERER'] );
        exit;
    }
}
add_action( 'admin_init', 'monica_integration_add_note' );

function monica_integration_add_relationship() {
    if ( isset( $_POST['monica_add_relationship'] ) && isset( $_POST['monica_add_relationship_nonce'] ) ) {
        if ( ! wp_verify_nonce( $_POST['monica_add_relationship_nonce'], 'monica_add_relationship' ) ) {
            return;
        }

        $post_id = absint( $_POST['monica_post_id'] ?? 0 );
        if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        $contact_id           = absint( $_POST['monica_contact_id'] ?? 0 );
        $related_contact_id   = absint( $_POST['monica_related_contact_id'] ?? 0 );
        $relationship_type_id = absint( $_POST['monica_relationship_type_id'] ?? 0 );

        if ( ! $contact_id || ! $related_contact_id || ! $relationship_type_id ) {
            wp_safe_redirect( add_query_arg( 'monica_error', 'empty_fields', wp_get_referer() ? wp_get_referer() : admin_url() ) );
            exit;
        }

        $api = new Monica_API();
        $api->post( 'relationships', [
            'body' => json_encode( [
                'contact_id'           => $contact_id,
                'with_contact_id'      => $related_contact_id,
                'relationship_type_id' => $relationship_type_id,
            ] ),
        ] );

        wp_redirect( $_SERVER['HTTP_REFERER'] );
        exit;
    }
}
add_action( 'admin_init', 'monica_integration_add_relationship' );

function monica_integration_admin_notices() {
    if ( isset( $_GET['monica_error'] ) && 'empty_fields' === $_GET['monica_error'] ) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e( 'Please fill in all required fields.', 'monica-integration' ); ?></p>
        </div>
        <?php
    }
}
add_action( 'admin_notices', 'monica_integration_admin_notices' );
