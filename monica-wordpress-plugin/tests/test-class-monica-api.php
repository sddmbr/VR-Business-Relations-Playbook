<?php

require_once __DIR__ . '/../includes/class-monica-api.php';

function test_monica_api_get_missing_token() {
    $api = new Monica_API();
    global $mock_calls;

    // Ensure access token is not set
    $mock_calls['get_option']['monica_access_token'] = false;

    $result = $api->get('some/endpoint');

    assert_true(is_wp_error($result), 'Should return WP_Error when access token is missing');
    assert_equals('no_access_token', $result->get_error_code(), 'Error code should be no_access_token');
    assert_equals('No access token found.', $result->get_error_message(), 'Error message should match');
}
