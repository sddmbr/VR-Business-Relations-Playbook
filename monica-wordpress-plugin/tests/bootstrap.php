<?php

global $mock_calls;
$mock_calls = [];

function reset_mock_calls() {
    global $mock_calls;
    $mock_calls = [
        'update_post_meta' => [],
        'get_post_meta' => [],
        'wp_remote_post' => [],
        'wp_remote_get' => [],
        'wp_remote_request' => [],
        'wp_verify_nonce' => true,
        'current_user_can' => true,
        'get_post_meta_return' => null,
        'wp_remote_post_return' => ['body' => json_encode(['data' => ['id' => 999]])],
        'wp_remote_put_return' => ['body' => json_encode(['data' => ['id' => 999]])],
        'is_wp_error_return' => false,
        'get_option' => ['monica_access_token' => 'test-token'],
    ];
}
reset_mock_calls();

// Mock WordPress functions
function add_action() {}
function _x($a, $b, $c) { return $a; }
function __($a, $b) { return $a; }
function register_post_type($a, $b) {}
function add_meta_box() {}
function wp_nonce_field() {}
function _e($a, $b) {}
function esc_attr($a) { return $a; }

function wp_verify_nonce($nonce, $action) {
    global $mock_calls;
    return $mock_calls['wp_verify_nonce'];
}

function current_user_can($cap, $post_id) {
    global $mock_calls;
    return $mock_calls['current_user_can'];
}

function sanitize_text_field($str) { return $str; }
function sanitize_email($str) { return $str; }

function update_post_meta($post_id, $meta_key, $meta_value) {
    global $mock_calls;
    $mock_calls['update_post_meta'][] = func_get_args();
}

function get_post_meta($post_id, $key, $single = false) {
    global $mock_calls;
    $mock_calls['get_post_meta'][] = func_get_args();
    if ($key === '_monica_contact_id') {
        return $mock_calls['get_post_meta_return'];
    }
    return '';
}

function is_wp_error($thing) {
    global $mock_calls;
    if ($thing instanceof WP_Error) {
        return true;
    }
    return $mock_calls['is_wp_error_return'];
}

function get_option($option, $default = false) {
    global $mock_calls;
    if (isset($mock_calls['get_option'][$option])) {
        return $mock_calls['get_option'][$option];
    }
    return $default;
}

class WP_Error {
    public $code;
    public $message;
    public $data;

    public function __construct($code = '', $message = '', $data = '') {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }

    public function get_error_code() {
        return $this->code;
    }

    public function get_error_message($code = '') {
        return $this->message;
    }
}

// Mock wp_remote_* functions directly to support Monica_API testing without conflicts
function wp_remote_post($url, $args = []) {
    global $mock_calls;
    $mock_calls['wp_remote_post'][] = func_get_args();

    if ($mock_calls['is_wp_error_return']) {
        return new WP_Error('http_error', 'HTTP Error');
    }
    return $mock_calls['wp_remote_post_return'];
}

function wp_remote_get($url, $args = []) {
    global $mock_calls;
    $mock_calls['wp_remote_get'][] = func_get_args();

    if ($mock_calls['is_wp_error_return']) {
        return new WP_Error('http_error', 'HTTP Error');
    }
    return ['body' => json_encode([])];
}

function wp_remote_request($url, $args = []) {
    global $mock_calls;
    $mock_calls['wp_remote_request'][] = func_get_args();

    if ($mock_calls['is_wp_error_return']) {
        return new WP_Error('http_error', 'HTTP Error');
    }

    return $mock_calls['wp_remote_put_return'];
}

function wp_remote_retrieve_body($response) {
    return $response['body'] ?? '';
}

// Basic assertions
function assert_equals($expected, $actual, $message = '') {
    if ($expected !== $actual) {
        throw new Exception("Assertion failed: Expected " . print_r($expected, true) . ", got " . print_r($actual, true) . ". $message");
    }
}

function assert_true($actual, $message = '') {
    assert_equals(true, $actual, $message);
}

function assert_false($actual, $message = '') {
    assert_equals(false, $actual, $message);
}

function assert_empty($actual, $message = '') {
    if (!empty($actual)) {
        throw new Exception("Assertion failed: Expected empty, got " . print_r($actual, true) . ". $message");
    }
}

function assert_not_empty($actual, $message = '') {
    if (empty($actual)) {
        throw new Exception("Assertion failed: Expected not empty. $message");
    }
}
