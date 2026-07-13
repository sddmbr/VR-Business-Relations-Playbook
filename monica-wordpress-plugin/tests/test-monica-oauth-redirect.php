<?php

require_once __DIR__ . '/../monica.php';

function test_oauth_redirect_capability_check() {
    global $mock_calls;

    // Arrange
    $mock_calls['current_user_can'] = false;

    // Act
    monica_integration_oauth_redirect();

    // Assert
    assert_empty($mock_calls['wp_safe_redirect'], 'wp_safe_redirect should not be called');
    assert_empty($mock_calls['wp_redirect'], 'wp_redirect should not be called');
}

function test_oauth_redirect_missing_state() {
    global $mock_calls;

    // Arrange
    $mock_calls['current_user_can'] = true;
    $_GET['page'] = 'monica-integration';
    $_GET['code'] = 'some_code';
    unset($_GET['state']);

    // Act
    $ex_message = '';
    try {
        monica_integration_oauth_redirect();
    } catch (Exception $e) {
        $ex_message = $e->getMessage();
    }

    // Assert
    assert_equals('wp_safe_redirect', $ex_message, 'Should exit via wp_safe_redirect exception');
    assert_not_empty($mock_calls['wp_safe_redirect'], 'wp_safe_redirect should be called');
    assert_equals('http://example.com/wp-admin/options-general.php?page=monica-integration&monica_error=invalid_state', $mock_calls['wp_safe_redirect'][0][0], 'Should redirect to correct error URL');
}

function test_oauth_redirect_invalid_state() {
    global $mock_calls;

    // Arrange
    $mock_calls['current_user_can'] = true;
    $mock_calls['wp_verify_nonce'] = false;
    $_GET['page'] = 'monica-integration';
    $_GET['code'] = 'some_code';
    $_GET['state'] = 'invalid_state';

    // Act
    $ex_message = '';
    try {
        monica_integration_oauth_redirect();
    } catch (Exception $e) {
        $ex_message = $e->getMessage();
    }

    // Assert
    assert_equals('wp_safe_redirect', $ex_message, 'Should exit via wp_safe_redirect exception');
    assert_not_empty($mock_calls['wp_safe_redirect'], 'wp_safe_redirect should be called');
    assert_equals('http://example.com/wp-admin/options-general.php?page=monica-integration&monica_error=invalid_state', $mock_calls['wp_safe_redirect'][0][0], 'Should redirect to correct error URL');
}

function test_oauth_redirect_success() {
    global $mock_calls;

    // Arrange
    $mock_calls['current_user_can'] = true;
    $mock_calls['wp_verify_nonce'] = true;
    $_GET['page'] = 'monica-integration';
    $_GET['code'] = 'valid_auth_code';
    $_GET['state'] = 'valid_state';

    // API mock returns a token
    $mock_calls['wp_remote_retrieve_body_return'] = json_encode(['access_token' => 'super_secret_token']);

    // Act
    $ex_message = '';
    try {
        monica_integration_oauth_redirect();
    } catch (Exception $e) {
        $ex_message = $e->getMessage();
    }

    // Assert
    assert_equals('wp_redirect', $ex_message, 'Should exit via wp_redirect exception');

    assert_not_empty($mock_calls['wp_remote_post'], 'API should be called to get token');
    assert_equals('valid_auth_code', $mock_calls['wp_remote_post'][0][1]['body']['code'], 'Should pass auth code to API');

    assert_not_empty($mock_calls['update_option'], 'Option should be updated');
    assert_equals('monica_access_token', $mock_calls['update_option'][0][0], 'Should update token option');
    assert_equals('super_secret_token', $mock_calls['update_option'][0][1], 'Should save the returned token');

    assert_not_empty($mock_calls['wp_redirect'], 'wp_redirect should be called');
    assert_equals('http://example.com/wp-admin/options-general.php?page=monica-integration', $mock_calls['wp_redirect'][0][0], 'Should redirect back to settings page');
}
