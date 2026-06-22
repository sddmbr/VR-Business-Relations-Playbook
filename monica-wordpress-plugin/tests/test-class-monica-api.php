<?php

// require_once was moved to bootstrap.php

function test_monica_api_get_authorization_url() {
    $api = new Monica_API();
    global $mock_calls;

    $mock_calls['get_option']['monica_client_id'] = 'test_client_id';
    $redirect_uri = 'https://example.com/callback';

    $url = $api->get_authorization_url($redirect_uri);

    $expected_url = 'https://app.monicahq.com/oauth/authorize?' . http_build_query([
        'client_id'     => 'test_client_id',
        'redirect_uri'  => $redirect_uri,
        'response_type' => 'code',
    ]);

    assert_equals($expected_url, $url);
}

function test_monica_api_get_access_token() {
    $api = new Monica_API();
    global $mock_calls;

    $mock_calls['get_option']['monica_client_id'] = 'test_client_id';
    $mock_calls['get_option']['monica_client_secret'] = 'test_client_secret';
    $mock_calls['wp_remote_retrieve_body_return'] = '{"access_token": "test_token"}';

    $code = 'test_code';
    $redirect_uri = 'https://example.com/callback';

    $result = $api->get_access_token($code, $redirect_uri);

    assert_not_empty($mock_calls['wp_remote_post']);
    $post_call = $mock_calls['wp_remote_post'][0];

    assert_equals('https://app.monicahq.com/oauth/token', $post_call[0]);
    assert_equals([
        'grant_type'    => 'authorization_code',
        'client_id'     => 'test_client_id',
        'client_secret' => 'test_client_secret',
        'redirect_uri'  => $redirect_uri,
        'code'          => $code,
    ], $post_call[1]['body']);

    assert_equals(['access_token' => 'test_token'], $result);
}

function test_monica_api_get_access_token_error() {
    $api = new Monica_API();
    global $mock_calls;

    $mock_calls['is_wp_error_return'] = true;

    $result = $api->get_access_token('code', 'uri');

    assert_true($result instanceof WP_Error);
}

function test_monica_api_get() {
    $api = new Monica_API();
    global $mock_calls;

    $mock_calls['get_option']['monica_access_token'] = 'test_access_token';
    $mock_calls['wp_remote_retrieve_body_return'] = '{"data": []}';

    $result = $api->get('contacts');

    assert_not_empty($mock_calls['wp_remote_get']);
    $get_call = $mock_calls['wp_remote_get'][0];

    assert_equals('https://app.monicahq.com/api/contacts', $get_call[0]);
    assert_equals('Bearer test_access_token', $get_call[1]['headers']['Authorization']);

    assert_equals(['data' => []], $result);
}

function test_monica_api_get_error() {
    $api = new Monica_API();
    global $mock_calls;

    $mock_calls['get_option']['monica_access_token'] = 'test_access_token';
    $mock_calls['is_wp_error_return'] = true;

    $result = $api->get('contacts');

    assert_true($result instanceof WP_Error);
}

function test_monica_api_get_no_token() {
    $api = new Monica_API();
    global $mock_calls;

    $mock_calls['get_option']['monica_access_token'] = false;

    $result = $api->get('contacts');

    assert_empty($mock_calls['wp_remote_get']);
    assert_true($result instanceof WP_Error);
    assert_equals('no_access_token', $result->get_error_code());
}

function test_monica_api_post() {
    $api = new Monica_API();
    global $mock_calls;

    $mock_calls['get_option']['monica_access_token'] = 'test_access_token';
    $mock_calls['wp_remote_retrieve_body_return'] = '{"data": {"id": 1}}';

    $result = $api->post('contacts', ['body' => json_encode(['first_name' => 'John'])]);

    assert_not_empty($mock_calls['wp_remote_post']);
    $post_call = $mock_calls['wp_remote_post'][0];

    assert_equals('https://app.monicahq.com/api/contacts', $post_call[0]);
    assert_equals('Bearer test_access_token', $post_call[1]['headers']['Authorization']);
    assert_equals('application/json', $post_call[1]['headers']['Content-Type']);

    assert_equals(['data' => ['id' => 1]], $result);
}

function test_monica_api_post_error() {
    $api = new Monica_API();
    global $mock_calls;

    $mock_calls['get_option']['monica_access_token'] = 'test_access_token';
    $mock_calls['is_wp_error_return'] = true;

    $result = $api->post('contacts', ['body' => json_encode(['first_name' => 'John'])]);

    assert_true($result instanceof WP_Error);
}

function test_monica_api_post_no_token() {
    $api = new Monica_API();
    global $mock_calls;

    $mock_calls['get_option']['monica_access_token'] = false;

    $result = $api->post('contacts', ['body' => json_encode(['first_name' => 'John'])]);

    assert_empty($mock_calls['wp_remote_post']);
    assert_true($result instanceof WP_Error);
    assert_equals('no_access_token', $result->get_error_code());
}

function test_monica_api_put() {
    $api = new Monica_API();
    global $mock_calls;

    $mock_calls['get_option']['monica_access_token'] = 'test_access_token';
    $mock_calls['wp_remote_retrieve_body_return'] = '{"data": {"id": 1}}';

    $result = $api->put('contacts/1', ['body' => json_encode(['first_name' => 'Jane'])]);

    assert_not_empty($mock_calls['wp_remote_request']);
    $put_call = $mock_calls['wp_remote_request'][0];

    assert_equals('https://app.monicahq.com/api/contacts/1', $put_call[0]);
    assert_equals('PUT', $put_call[1]['method']);
    assert_equals('Bearer test_access_token', $put_call[1]['headers']['Authorization']);
    assert_equals('application/json', $put_call[1]['headers']['Content-Type']);

    assert_equals(['data' => ['id' => 1]], $result);
}

function test_monica_api_put_error() {
    $api = new Monica_API();
    global $mock_calls;

    $mock_calls['get_option']['monica_access_token'] = 'test_access_token';
    $mock_calls['is_wp_error_return'] = true;

    $result = $api->put('contacts/1', ['body' => json_encode(['first_name' => 'Jane'])]);

    assert_true($result instanceof WP_Error);
}

function test_monica_api_put_no_token() {
    $api = new Monica_API();
    global $mock_calls;

    $mock_calls['get_option']['monica_access_token'] = false;

    $result = $api->put('contacts/1', ['body' => json_encode(['first_name' => 'Jane'])]);

    assert_empty($mock_calls['wp_remote_request']);
    assert_true($result instanceof WP_Error);
    assert_equals('no_access_token', $result->get_error_code());
}
