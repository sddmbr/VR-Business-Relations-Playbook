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
require_once __DIR__ . '/../includes/class-monica-settings.php';

// Global variable to store mocked options
$GLOBALS['wp_options'] = [];

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $hook_name, $callback, $priority = 10, $accepted_args = 1 ) {
        return true;
    }
}

if ( ! function_exists( 'get_admin_page_title' ) ) {
    function get_admin_page_title() {
        return 'Mock Admin Page Title';
    }
}

if ( ! function_exists( 'esc_html' ) ) {
    function esc_html( $text ) {
        return htmlspecialchars( $text, ENT_QUOTES );
    }
}

if ( ! function_exists( 'settings_fields' ) ) {
    function settings_fields( $option_group ) {
        echo '<input type="hidden" name="option_page" value="' . esc_attr($option_group) . '" />';
    }
}

if ( ! function_exists( 'do_settings_sections' ) ) {
    function do_settings_sections( $page ) {
        // mock implementation
    }
}

if ( ! function_exists( '_e' ) ) {
    function _e( $text, $domain = 'default' ) {
        echo $text;
    }
}

if ( ! function_exists( 'submit_button' ) ) {
    function submit_button( $text = null, $type = 'primary', $name = 'submit', $wrap = true, $other_attributes = null ) {
        echo '<input type="submit" name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" class="button button-' . esc_attr($type) . '" value="Save Changes" />';
    }
}
