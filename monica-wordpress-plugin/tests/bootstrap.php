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
        'wp_remote_retrieve_body_return' => '{"data": {"id": 999}}',
        'is_wp_error_return' => false,
        'register_setting' => [],
        'wp_safe_redirect' => [],
        'wp_redirect' => [],
        'update_option' => [],
        'get_option' => 'mock_token',
    ];
}
reset_mock_calls();

// Mock WordPress functions
if (!defined('WPINC')) {
    define('WPINC', true);
}
function plugin_dir_path($file) { return dirname($file) . '/'; }
function plugin_dir_url($file) { return 'http://example.com/wp-content/plugins/monica/'; }
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

function current_user_can($cap, $post_id = null) {
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
    return $mock_calls['is_wp_error_return'];
}

function admin_url($path = '') {
    return 'http://example.com/wp-admin/' . $path;
}

function wp_safe_redirect($location) {
    global $mock_calls;
    $mock_calls['wp_safe_redirect'][] = func_get_args();
    throw new Exception('wp_safe_redirect');
}

function wp_redirect($location) {
    global $mock_calls;
    $mock_calls['wp_redirect'][] = func_get_args();
    throw new Exception('wp_redirect');
}

function update_option($option, $value) {
    global $mock_calls;
    $mock_calls['update_option'][] = func_get_args();
}

function get_option($option) {
    global $mock_calls;
    return $mock_calls['get_option'];
}

function wp_remote_post($url, $args = []) {
    global $mock_calls;
    $mock_calls['wp_remote_post'][] = func_get_args();
    return [];
}

function wp_remote_get($url, $args = []) {
    global $mock_calls;
    $mock_calls['wp_remote_get'][] = func_get_args();
    return [];
}

function wp_remote_request($url, $args = []) {
    global $mock_calls;
    $mock_calls['wp_remote_request'][] = func_get_args();
    return [];
}

function wp_remote_retrieve_body($response) {
    global $mock_calls;
    return $mock_calls['wp_remote_retrieve_body_return'];
}

class WP_Error {
    public $code;
    public $message;
    public function __construct($code = '', $message = '') {
        $this->code = $code;
        $this->message = $message;
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

function register_setting($option_group, $option_name, $args = []) {
    global $mock_calls;
    $mock_calls['register_setting'][] = func_get_args();
}
