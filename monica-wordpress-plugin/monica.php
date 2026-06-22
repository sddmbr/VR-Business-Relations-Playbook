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
