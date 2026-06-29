<?php
/**
 * Bootstrap the mock WordPress environment for testing.
 */

// Mock __ for translations
if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) {
        return $text;
    }
}

// Mock WP_Error class
if ( ! class_exists( 'WP_Error' ) ) {
    class WP_Error {
        public $code;
        public $message;
        public $data;

        public function __construct( $code = '', $message = '', $data = '' ) {
            $this->code    = $code;
            $this->message = $message;
            $this->data    = $data;
        }

        public function get_error_code() {
            return $this->code;
        }

        public function get_error_message() {
            return $this->message;
        }
    }
}

if ( ! function_exists( 'is_wp_error' ) ) {
    function is_wp_error( $thing ) {
        return ( $thing instanceof WP_Error );
    }
}

// Mock Options API
$GLOBALS['wp_options'] = [];

if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) {
        return isset( $GLOBALS['wp_options'][ $option ] ) ? $GLOBALS['wp_options'][ $option ] : $default;
    }
}

if ( ! function_exists( 'update_option' ) ) {
    function update_option( $option, $value, $autoload = null ) {
        $GLOBALS['wp_options'][ $option ] = $value;
        return true;
    }
}

if ( ! function_exists( 'delete_option' ) ) {
    function delete_option( $option ) {
        unset( $GLOBALS['wp_options'][ $option ] );
        return true;
    }
}

// Mock wp_remote functions as they are used in Monica_API
if ( ! function_exists( 'wp_remote_get' ) ) {
    function wp_remote_get( $url, $args = [] ) {
        return [];
    }
}

if ( ! function_exists( 'wp_remote_post' ) ) {
    function wp_remote_post( $url, $args = [] ) {
        return [];
    }
}

if ( ! function_exists( 'wp_remote_request' ) ) {
    function wp_remote_request( $url, $args = [] ) {
        return [];
    }
}

if ( ! function_exists( 'wp_remote_retrieve_body' ) ) {
    function wp_remote_retrieve_body( $response ) {
        return is_string( $response ) ? $response : '';
    }
}

// Load the plugin files
require_once dirname( __DIR__ ) . '/includes/class-monica-api.php';
