<?php

require_once __DIR__ . '/../includes/class-monica-api.php';

function test_get_access_token_success() {
    $api = new Monica_API();

    // Mock successful response
    $GLOBALS['mock_wp_remote_post_response'] = [
        'response' => [ 'code' => 200 ],
        'body'     => json_encode( [
            'access_token' => 'mocked_access_token',
            'token_type'   => 'Bearer',
            'expires_in'   => 3600,
        ] ),
    ];

    $result = $api->get_access_token( 'test_code', 'http://example.com/redirect' );

    if ( ! is_array( $result ) ) {
        throw new Exception( 'Expected array return from get_access_token' );
    }

    if ( ! isset( $result['access_token'] ) || $result['access_token'] !== 'mocked_access_token' ) {
        throw new Exception( 'Failed to retrieve or parse correct access_token' );
    }

    if ( ! isset( $result['expires_in'] ) || $result['expires_in'] !== 3600 ) {
        throw new Exception( 'Failed to retrieve or parse correct expires_in' );
    }
}

function test_get_access_token_failure() {
    $api = new Monica_API();

    // Mock failed response (WP_Error)
    $GLOBALS['mock_wp_remote_post_response'] = new WP_Error( 'http_request_failed', 'A valid URL was not provided.' );

    $result = $api->get_access_token( 'test_code', 'http://example.com/redirect' );

    if ( ! is_wp_error( $result ) ) {
        throw new Exception( 'Expected WP_Error return when wp_remote_post fails' );
    }

    if ( $result->get_error_message() !== 'A valid URL was not provided.' ) {
        throw new Exception( 'Failed to retrieve correct WP_Error message' );
    }
}
