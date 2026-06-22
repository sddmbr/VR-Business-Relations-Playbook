<?php

require_once __DIR__ . '/../includes/class-monica-api.php';

global $mock_calls;
$mock_calls = [];

function reset_mock_calls() {
    global $mock_calls;
    $mock_calls = [
        'update_post_meta' => [],
        'get_post_meta' => [],
        'wp_verify_nonce' => true,
        'current_user_can' => true,
        'get_post_meta_return' => null,
        'is_wp_error_return' => false,
        'wp_remote_post' => [],
        'wp_remote_get' => [],
        'wp_remote_request' => [],
        'get_option' => [],
        'wp_remote_retrieve_body_return' => '{}',
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
    if ($mock_calls['is_wp_error_return'] && is_array($thing) && isset($thing['is_error'])) {
        return true;
    }
    if ($thing === 'error' || (is_object($thing) && get_class($thing) === 'WP_Error')) {
        return true;
    }
    return $mock_calls['is_wp_error_return'];
}

function get_option($option) {
    global $mock_calls;
    if (isset($mock_calls['get_option'][$option])) {
        return $mock_calls['get_option'][$option];
    }
    return false;
}

function wp_remote_post($url, $args = []) {
    global $mock_calls;
    $mock_calls['wp_remote_post'][] = func_get_args();
    return $mock_calls['is_wp_error_return'] ? new WP_Error('error', 'error') : ['response' => ['code' => 200]];
}

function wp_remote_get($url, $args = []) {
    global $mock_calls;
    $mock_calls['wp_remote_get'][] = func_get_args();
    return $mock_calls['is_wp_error_return'] ? new WP_Error('error', 'error') : ['response' => ['code' => 200]];
}

function wp_remote_request($url, $args = []) {
    global $mock_calls;
    $mock_calls['wp_remote_request'][] = func_get_args();
    return $mock_calls['is_wp_error_return'] ? new WP_Error('error', 'error') : ['response' => ['code' => 200]];
}

function wp_remote_retrieve_body($response) {
    global $mock_calls;
    return $mock_calls['wp_remote_retrieve_body_return'];
}

class WP_Error {
    public $errors = [];
    public $error_data = [];

    public function __construct($code = '', $message = '', $data = '') {
        if (!empty($code)) {
            $this->errors[$code] = (array) $message;
            if (!empty($data)) {
                $this->error_data[$code] = $data;
            }
        }
    }

    public function get_error_code() {
        if (empty($this->errors)) {
            return '';
        }
        return array_key_first($this->errors);
    }
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
