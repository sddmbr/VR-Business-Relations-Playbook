<?php
// Mock WordPress functions
function __( $text, $domain = 'default' ) { return $text; }
function _x( $text, $context, $domain = 'default' ) { return $text; }
function _e( $text, $domain = 'default' ) { echo $text; }
function esc_attr_e( $text, $domain = 'default' ) { echo htmlspecialchars( $text, ENT_QUOTES ); }
function esc_attr( $text ) { return htmlspecialchars( $text, ENT_QUOTES ); }
function esc_html( $text ) { return htmlspecialchars( $text, ENT_QUOTES ); }
function add_action( $hook, $callback, $priority = 10, $accepted_args = 1 ) {}
function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) {}
function add_meta_box( $id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null ) {}
function register_post_type( $post_type, $args = [] ) {}
function wp_nonce_field( $action = -1, $name = "_wpnonce", $referer = true, $echo = true ) {}
function get_post_meta( $post_id, $key = '', $single = false ) { return ''; }
function update_post_meta( $post_id, $meta_key, $meta_value, $prev_value = '' ) {}
function get_posts( $args = null ) { return []; }
function admin_url( $path = '', $scheme = 'admin' ) { return 'http://example.com/wp-admin/' . $path; }
function wp_redirect( $location, $status = 302 ) {}
function wp_verify_nonce( $nonce, $action = -1 ) { return true; }
function absint( $maybeint ) { return abs( (int) $maybeint ); }
function sanitize_text_field( $str ) { return trim( $str ); }
function sanitize_email( $email ) { return trim( $email ); }
function wp_kses_post( $data ) { return $data; }
function current_user_can( $capability, ...$args ) { return true; }
function plugin_dir_path( $file ) { return dirname( $file ) . '/'; }
function plugin_dir_url( $file ) { return 'http://example.com/wp-content/plugins/monica-wordpress-plugin/'; }
function wp_enqueue_script( $handle, $src = '', $deps = [], $ver = false, $in_footer = false ) {}
function wp_enqueue_style( $handle, $src = '', $deps = [], $ver = false, $media = 'all' ) {}
function wp_localize_script( $handle, $object_name, $l10n ) {}
function wp_create_nonce( $action = -1 ) { return 'mock-nonce'; }
function check_ajax_referer( $action = -1, $query_arg = false, $die = true ) { return true; }
function wp_send_json( $response, $status_code = null ) { echo json_encode( $response ); exit; }
function wp_send_json_error( $data = null, $status_code = null ) { echo json_encode( [ 'success' => false, 'data' => $data ] ); exit; }
function get_the_ID() { return 1; }
function get_current_screen() { return (object) [ 'post_type' => 'monica_contact' ]; }
function wp_add_inline_style( $handle, $data ) {}
if ( ! function_exists( 'defined_mock' ) ) {
    function defined_mock( $name ) {
        if ($name === 'WPINC') return true;
        return false;
    }
}

class WP_Error {
    public $errors = [];
    public function __construct( $code = '', $message = '', $data = '' ) {
        if ( ! empty( $code ) ) $this->errors[$code][] = $message;
    }
    public function get_error_message() {
        foreach ( $this->errors as $code => $messages ) return $messages[0];
        return '';
    }
}
function is_wp_error( $thing ) { return $thing instanceof WP_Error; }

// Define constants
define( 'WPINC', 'wp-includes' );

// Mock Monica_API if needed or include it
require_once dirname( __DIR__ ) . '/includes/class-monica-api.php';
