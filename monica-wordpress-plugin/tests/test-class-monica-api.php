<?php

class Test_Class_Monica_Api {

    public function test_get_authorization_url_with_valid_params() {
        update_option( 'monica_client_id', 'test_client_id' );
        $redirect_uri = 'https://example.com/callback';

        $api = new Monica_API();
        $url = $api->get_authorization_url( $redirect_uri );

        $expected = 'https://app.monicahq.com/oauth/authorize?client_id=test_client_id&redirect_uri=https%3A%2F%2Fexample.com%2Fcallback&response_type=code&state=mocked_nonce';

        assertEquals( $expected, $url, 'The authorization URL should be correctly constructed.' );
    }

    public function test_get_authorization_url_with_missing_client_id() {
        // monica_client_id is not set
        $redirect_uri = 'https://example.com/callback';

        $api = new Monica_API();
        $url = $api->get_authorization_url( $redirect_uri );

        // get_option returns false if not set in our mock, http_build_query converts false to 0
        $expected = 'https://app.monicahq.com/oauth/authorize?client_id=0&redirect_uri=https%3A%2F%2Fexample.com%2Fcallback&response_type=code&state=mocked_nonce';

        assertEquals( $expected, $url, 'The authorization URL should handle missing client_id gracefully.' );
    }

    public function test_get_authorization_url_encodes_redirect_uri() {
        update_option( 'monica_client_id', 'test_client_id' );
        $redirect_uri = 'https://example.com/callback?param=value&another=true';

        $api = new Monica_API();
        $url = $api->get_authorization_url( $redirect_uri );

        $encoded_uri = urlencode( $redirect_uri );
        $expected = "https://app.monicahq.com/oauth/authorize?client_id=test_client_id&redirect_uri=$encoded_uri&response_type=code&state=mocked_nonce";

        assertEquals( $expected, $url, 'The redirect URI should be URL encoded.' );
    }

    public function test_get_access_token_success() {
        update_option( 'monica_client_id', 'test_client_id' );
        update_option( 'monica_client_secret', 'test_client_secret' );
        $redirect_uri = 'https://example.com/callback';
        $code = 'test_authorization_code';

        $GLOBALS['mock_wp_remote_post_response'] = [
            'body' => json_encode( [
                'access_token' => 'mocked_access_token',
                'token_type'   => 'Bearer',
                'expires_in'   => 3600,
            ] ),
        ];

        $api = new Monica_API();
        $result = $api->get_access_token( $code, $redirect_uri );

        assertEquals( 'mocked_access_token', $result['access_token'], 'Should return the access token from the API response.' );
        assertEquals( 3600, $result['expires_in'], 'Should return the expires_in value from the API response.' );
    }

    public function test_get_access_token_failure() {
        update_option( 'monica_client_id', 'test_client_id' );
        update_option( 'monica_client_secret', 'test_client_secret' );
        $redirect_uri = 'https://example.com/callback';
        $code = 'test_authorization_code';

        $GLOBALS['mock_wp_remote_post_response'] = new WP_Error( 'http_request_failed', 'A valid URL was not provided.' );

        $api = new Monica_API();
        $result = $api->get_access_token( $code, $redirect_uri );

        if ( ! is_wp_error( $result ) ) {
            throw new Exception( 'Expected a WP_Error to be returned on failed request.' );
        }

        assertEquals( 'A valid URL was not provided.', $result->get_error_message(), 'Should return the correct error message.' );
        assertEquals( 'http_request_failed', $result->get_error_code(), 'Should return the correct error code.' );
    }
}
