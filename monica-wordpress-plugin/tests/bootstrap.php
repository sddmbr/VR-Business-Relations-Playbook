<?php

$GLOBALS['mock_calls'] = [];

if ( ! function_exists( 'add_action' ) ) {
    function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
        global $mock_calls;
        $mock_calls['add_action'][] = [ 'tag' => $tag, 'function' => $function_to_add ];
        return true;
    }
}
if ( ! function_exists( 'add_meta_box' ) ) {
    function add_meta_box() {}
}
if ( ! function_exists( 'wp_nonce_field' ) ) {
    function wp_nonce_field() {}
}
if ( ! function_exists( '_e' ) ) {
    function _e($str) {}
}
if ( ! function_exists( 'esc_attr' ) ) {
    function esc_attr($str) { return $str; }
}
if ( ! function_exists( 'get_post_meta' ) ) {
    function get_post_meta( $post_id, $key = '', $single = false ) {
        global $mock_calls;
        if (isset($mock_calls['get_post_meta_return'][$key])) {
            return $mock_calls['get_post_meta_return'][$key];
        }
        return false;
    }
}
if ( ! function_exists( 'update_post_meta' ) ) {
    function update_post_meta( $post_id, $meta_key, $meta_value, $prev_value = '' ) {
        global $mock_calls;
        $mock_calls['update_post_meta'][] = func_get_args();
        return true;
    }
}
if ( ! function_exists( 'is_wp_error' ) ) {
    function is_wp_error( $thing ) {
        global $mock_calls;
        if ( isset($mock_calls['is_wp_error_return']) ) return $mock_calls['is_wp_error_return'];
        return false;
    }
}
if ( ! function_exists( 'wp_verify_nonce' ) ) {
    function wp_verify_nonce( $nonce, $action = -1 ) {
        global $mock_calls;
        if ( isset($mock_calls['wp_verify_nonce']) ) return $mock_calls['wp_verify_nonce'];
        return true;
    }
}
if ( ! function_exists( 'current_user_can' ) ) {
    function current_user_can( $capability, ...$args ) {
        global $mock_calls;
        if ( isset($mock_calls['current_user_can']) ) return $mock_calls['current_user_can'];
        return true;
    }
}
if ( ! function_exists( 'sanitize_text_field' ) ) {
    function sanitize_text_field( $str ) {
        return $str;
    }
}
if ( ! function_exists( 'sanitize_email' ) ) {
    function sanitize_email( $str ) {
        return $str;
    }
}
if ( ! function_exists( 'wp_schedule_single_event' ) ) {
    function wp_schedule_single_event( $timestamp, $hook, $args = [] ) {
        global $mock_calls;
        $mock_calls['wp_schedule_single_event'][] = func_get_args();
        return true;
    }
}
if ( ! function_exists( 'wp_next_scheduled' ) ) {
    function wp_next_scheduled( $hook, $args = [] ) {
        global $mock_calls;
        if ( isset($mock_calls['wp_next_scheduled']) ) return $mock_calls['wp_next_scheduled'];
        return false;
    }
}

class Monica_API {
    public function post($endpoint, $args) {
        global $mock_calls;
        $mock_calls['Monica_API_post'][] = func_get_args();
        if (isset($mock_calls['Monica_API_post_return'])) return $mock_calls['Monica_API_post_return'];
        return ['data' => ['id' => 123]];
    }
    public function put($endpoint, $args) {
        global $mock_calls;
        $mock_calls['Monica_API_put'][] = func_get_args();
        return ['data' => ['id' => 123]];
    }
}

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
