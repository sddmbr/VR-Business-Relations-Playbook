<?php

// Mock WordPress functions
if ( ! function_exists( 'get_option' ) ) {
    function get_option( $option, $default = false ) {
        global $wp_options;
        return isset( $wp_options[ $option ] ) ? $wp_options[ $option ] : $default;
    }
}

if ( ! function_exists( 'update_option' ) ) {
    function update_option( $option, $value, $autoload = null ) {
        global $wp_options;
        $wp_options[ $option ] = $value;
        return true;
    }
}

if ( ! function_exists( '__' ) ) {
    function __( $text, $domain = 'default' ) {
        return $text;
    }
}

if ( ! function_exists( 'esc_url' ) ) {
    function esc_url( $url ) {
        return $url;
    }
}

if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES );
    }
}

if ( ! function_exists( 'admin_url' ) ) {
    function admin_url( $path = '', $scheme = 'admin' ) {
        return 'https://example.com/wp-admin/' . $path;
    }
}

// Include plugin files
require_once __DIR__ . '/../includes/class-monica-api.php';

// Global variable to store mocked options
$GLOBALS['wp_options'] = [];

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
        global $mock_actions;
        $mock_actions[] = [ 'tag' => $tag, 'function' => $function_to_add ];
        return true;
    }
}

if ( ! function_exists( 'add_meta_box' ) ) {
    function add_meta_box( $id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null ) {
        global $mock_meta_boxes;
        $mock_meta_boxes[] = [
            'id'       => $id,
            'title'    => $title,
            'callback' => $callback,
            'screen'   => $screen,
            'context'  => $context,
            'priority' => $priority
        ];
    }
}

if ( ! function_exists( 'get_post_meta' ) ) {
    function get_post_meta( $post_id, $key = '', $single = false ) {
        global $mock_post_meta;
        if ( isset( $mock_post_meta[ $post_id ][ $key ] ) ) {
            return $mock_post_meta[ $post_id ][ $key ];
        }
        return $single ? '' : [];
    }
}

if ( ! function_exists( 'is_wp_error' ) ) {
    function is_wp_error( $thing ) {
        return ( $thing instanceof WP_Error );
    }
}

if ( ! class_exists( 'WP_Error' ) ) {
    class WP_Error {
        public $errors = [];
        public $error_data = [];

        public function __construct( $code = '', $message = '', $data = '' ) {
            if ( empty( $code ) ) {
                return;
            }
            $this->errors[$code][] = $message;
            if ( ! empty( $data ) ) {
                $this->error_data[$code] = $data;
            }
        }

        public function get_error_message( $code = '' ) {
            if ( empty( $code ) ) {
                $code = $this->get_error_code();
            }
            if ( isset( $this->errors[$code][0] ) ) {
                return $this->errors[$code][0];
            }
            return '';
        }

        public function get_error_code() {
            $codes = array_keys( $this->errors );
            if ( empty( $codes ) ) {
                return '';
            }
            return $codes[0];
        }
    }
}

if ( ! function_exists( '_e' ) ) {
    function _e( $text, $domain = 'default' ) {
        echo $text;
    }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES );
    }
}

if ( ! function_exists( 'wp_nonce_field' ) ) {
    function wp_nonce_field( $action = -1, $name = "_wpnonce", $referer = true, $echo = true ) {
        $output = '<input type="hidden" name="' . esc_attr($name) . '" value="mock_nonce" />';
        if ( $echo ) {
            echo $output;
        }
        return $output;
    }
}

// Global state for mocks
$GLOBALS['mock_actions'] = [];
$GLOBALS['mock_meta_boxes'] = [];
$GLOBALS['mock_post_meta'] = [];


if ( ! function_exists( 'wp_remote_get' ) ) {
    function wp_remote_get( $url, $args = [] ) {
        global $mock_calls;
        if ( isset( $mock_calls['wp_remote_get'][$url] ) ) {
            return $mock_calls['wp_remote_get'][$url];
        }
        return new WP_Error('http_request_failed', 'A valid URL was not provided.');
    }
}

if ( ! function_exists( 'wp_remote_retrieve_body' ) ) {
    function wp_remote_retrieve_body( $response ) {
        if ( is_wp_error( $response ) ) {
            return '';
        }
        return isset( $response['body'] ) ? $response['body'] : '';
    }
}

$GLOBALS['mock_calls'] = [];

$GLOBALS['mock_transients'] = [];

if ( ! function_exists( 'get_transient' ) ) {
    function get_transient( $transient ) {
        global $mock_transients;
        return isset( $mock_transients[ $transient ] ) ? $mock_transients[ $transient ] : false;
    }
}

if ( ! function_exists( 'set_transient' ) ) {
    function set_transient( $transient, $value, $expiration = 0 ) {
        global $mock_transients;
        $mock_transients[ $transient ] = $value;
        return true;
    }
}

if ( ! function_exists( 'delete_transient' ) ) {
    function delete_transient( $transient ) {
        global $mock_transients;
        unset( $mock_transients[ $transient ] );
        return true;
    }
}

if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
    define( 'MINUTE_IN_SECONDS', 60 );
}

if ( ! function_exists( 'wp_create_nonce' ) ) {
    function wp_create_nonce( $action = -1 ) {
        return 'mock_nonce_' . $action;
    }
}
