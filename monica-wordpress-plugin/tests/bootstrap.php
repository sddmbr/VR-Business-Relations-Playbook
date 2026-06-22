<?php

global $mock_calls;
$mock_calls = [];

function reset_mock_calls() {
    global $mock_calls;
    $mock_calls = [
        'update_post_meta' => [],
        'get_post_meta' => [],
        'Monica_API_post' => [],
        'Monica_API_put' => [],
        'wp_verify_nonce' => true,
        'current_user_can' => true,
        'get_post_meta_return' => null,
        'Monica_API_post_return' => ['data' => ['id' => 999]],
        'is_wp_error_return' => false,
        'register_setting' => [],
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
    return $mock_calls['is_wp_error_return'];
}

class Monica_API {
    public function post($endpoint, $args = []) {
        global $mock_calls;
        $mock_calls['Monica_API_post'][] = func_get_args();
        return $mock_calls['Monica_API_post_return'];
    }

    public function put($endpoint, $args = []) {
        global $mock_calls;
        $mock_calls['Monica_API_put'][] = func_get_args();
        return ['data' => ['id' => 999]];
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
