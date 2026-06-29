<?php

// Mocking WP_Error class
class WP_Error {
    public $code;
    public $message;
    public $data;

    public function __construct( $code = '', $message = '', $data = '' ) {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    public function get_error_message() {
        return $this->message;
    }
}

// Global state for mocks
$GLOBALS['mock_wp_remote_post_response'] = null;
$GLOBALS['mock_options'] = [
    'monica_client_id' => 'test_client_id',
    'monica_client_secret' => 'test_client_secret',
    'monica_access_token' => 'test_access_token',
];

function get_option( $option, $default = false ) {
    if ( isset( $GLOBALS['mock_options'][$option] ) ) {
        return $GLOBALS['mock_options'][$option];
    }
    return $default;
}

function wp_remote_post( $url, $args = array() ) {
    return $GLOBALS['mock_wp_remote_post_response'];
}

function wp_remote_get( $url, $args = array() ) {
    return $GLOBALS['mock_wp_remote_get_response'] ?? null;
}

function wp_remote_request( $url, $args = array() ) {
    return $GLOBALS['mock_wp_remote_request_response'] ?? null;
}

function wp_remote_retrieve_body( $response ) {
    if ( is_wp_error( $response ) || ! isset( $response['body'] ) ) {
        return '';
    }
    return $response['body'];
}

function is_wp_error( $thing ) {
    return ( $thing instanceof WP_Error );
}

function __( $text, $domain = 'default' ) {
    return $text;
}
