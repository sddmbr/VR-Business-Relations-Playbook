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
    if ( isset( $_GET['page'] ) && 'monica-integration' === $_GET['page'] && isset( $_GET['code'] ) ) {
        $api = new Monica_API();
        $redirect_uri = admin_url( 'options-general.php?page=monica-integration' );
        $data = $api->get_access_token( $_GET['code'], $redirect_uri );

        if ( ! is_wp_error( $data ) && isset( $data['access_token'] ) ) {
            update_option( 'monica_access_token', $data['access_token'] );
        }

        wp_redirect( $redirect_uri );
        exit;
    }
}
add_action( 'admin_init', 'monica_integration_oauth_redirect' );

function monica_integration_add_reminder() {
    if ( isset( $_POST['monica_add_reminder'] ) && isset( $_POST['monica_add_reminder_nonce'] ) ) {
        if ( ! wp_verify_nonce( $_POST['monica_add_reminder_nonce'], 'monica_add_reminder' ) ) {
            return;
        }

        $contact_id = absint( $_POST['monica_contact_id'] );
        $title      = sanitize_text_field( $_POST['monica_reminder_title'] );
        $date       = sanitize_text_field( $_POST['monica_reminder_date'] );

        if ( ! $contact_id || ! $title || ! $date ) {
            return;
        }

        $api = new Monica_API();
        $api->post( "contacts/{$contact_id}/reminders", [
            'body' => json_encode( [
                'title'          => $title,
                'reminder_date' => $date,
            ] ),
        ] );

        wp_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
        exit;
    }
}
add_action( 'admin_init', 'monica_integration_add_reminder' );

function monica_integration_add_note() {
    if ( isset( $_POST['monica_add_note'] ) && isset( $_POST['monica_add_note_nonce'] ) ) {
        if ( ! wp_verify_nonce( $_POST['monica_add_note_nonce'], 'monica_add_note' ) ) {
            return;
        }

        $contact_id = absint( $_POST['monica_contact_id'] );
        $body       = wp_kses_post( $_POST['monica_note_body'] );

        if ( ! $contact_id || ! $body ) {
            return;
        }

        $api = new Monica_API();
        $api->post( "contacts/{$contact_id}/notes", [
            'body' => json_encode( [
                'body' => $body,
            ] ),
        ] );

        wp_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
        exit;
    }
}
add_action( 'admin_init', 'monica_integration_add_note' );

function monica_integration_add_relationship() {
    if ( isset( $_POST['monica_add_relationship'] ) && isset( $_POST['monica_add_relationship_nonce'] ) ) {
        if ( ! wp_verify_nonce( $_POST['monica_add_relationship_nonce'], 'monica_add_relationship' ) ) {
            return;
        }

        $contact_id           = absint( $_POST['monica_contact_id'] );
        $related_contact_id   = absint( $_POST['monica_related_contact_id'] );
        $relationship_type_id = absint( $_POST['monica_relationship_type_id'] );

        if ( ! $contact_id || ! $related_contact_id || ! $relationship_type_id ) {
            return;
        }

        $api = new Monica_API();
        $api->post( 'relationships', [
            'body' => json_encode( [
                'contact_id'           => $contact_id,
                'with_contact_id'      => $related_contact_id,
                'relationship_type_id' => $relationship_type_id,
            ] ),
        ] );

        wp_redirect( wp_get_referer() ? wp_get_referer() : admin_url() );
        exit;
    }
}
add_action( 'admin_init', 'monica_integration_add_relationship' );
