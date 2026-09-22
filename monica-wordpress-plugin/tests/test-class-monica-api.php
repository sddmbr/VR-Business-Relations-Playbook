<?php

class Test_Class_Monica_Api {

    public function test_get_authorization_url_with_valid_params() {
        update_option( 'monica_client_id', 'test_client_id' );
        $redirect_uri = 'https://example.com/callback';

        $api = new Monica_API();
        $url = $api->get_authorization_url( $redirect_uri );

        $expected = 'https://app.monicahq.com/oauth/authorize?client_id=test_client_id&redirect_uri=https%3A%2F%2Fexample.com%2Fcallback&response_type=code&state=mock_nonce_valid';

        assertEquals( $expected, $url, 'The authorization URL should be correctly constructed.' );
    }

    public function test_get_authorization_url_with_missing_client_id() {
        // monica_client_id is not set
        $redirect_uri = 'https://example.com/callback';

        $api = new Monica_API();
        $url = $api->get_authorization_url( $redirect_uri );

        // get_option returns false if not set in our mock, http_build_query converts false to 0
        $expected = 'https://app.monicahq.com/oauth/authorize?client_id=0&redirect_uri=https%3A%2F%2Fexample.com%2Fcallback&response_type=code&state=mock_nonce_valid';

        assertEquals( $expected, $url, 'The authorization URL should handle missing client_id gracefully.' );
    }

    public function test_get_authorization_url_encodes_redirect_uri() {
        update_option( 'monica_client_id', 'test_client_id' );
        $redirect_uri = 'https://example.com/callback?param=value&another=true';

        $api = new Monica_API();
        $url = $api->get_authorization_url( $redirect_uri );

        $encoded_uri = urlencode( $redirect_uri );
        $expected = "https://app.monicahq.com/oauth/authorize?client_id=test_client_id&redirect_uri=$encoded_uri&response_type=code&state=mock_nonce_valid";

        assertEquals( $expected, $url, 'The redirect URI should be URL encoded.' );
    }

    public function test_get_access_token_api_error() {
        global $mock_calls;

        update_option( 'monica_client_id', 'client1' );
        update_option( 'monica_client_secret', 'secret1' );

        $error = new WP_Error( 'http_error', 'Service Unavailable' );
        $mock_calls['wp_remote_post']['https://app.monicahq.com/oauth/token'] = $error;

        $api = new Monica_API();
        $result = $api->get_access_token( 'code1', 'uri1' );

        assertEquals( true, is_wp_error( $result ), 'Expected a WP_Error to be returned' );
        assertEquals( 'http_error', $result->get_error_code(), 'Expected the same error code' );
    }

    public function test_get_api_error() {
        global $mock_calls;

        update_option( 'monica_access_token', 'token1' );

        $error = new WP_Error( 'http_error', 'Service Unavailable' );
        $mock_calls['wp_remote_get']['https://app.monicahq.com/api/contacts'] = $error;

        $api = new Monica_API();
        $result = $api->get( 'contacts' );

        assertEquals( true, is_wp_error( $result ), 'Expected a WP_Error to be returned' );
        assertEquals( 'http_error', $result->get_error_code(), 'Expected the same error code' );
    }

    public function test_post_api_error() {
        global $mock_calls;

        update_option( 'monica_access_token', 'token1' );

        $error = new WP_Error( 'http_error', 'Service Unavailable' );
        $mock_calls['wp_remote_post']['https://app.monicahq.com/api/contacts'] = $error;

        $api = new Monica_API();
        $result = $api->post( 'contacts' );

        assertEquals( true, is_wp_error( $result ), 'Expected a WP_Error to be returned' );
        assertEquals( 'http_error', $result->get_error_code(), 'Expected the same error code' );
    }

    public function test_put_api_error() {
        global $mock_calls;

        update_option( 'monica_access_token', 'token1' );

        $error = new WP_Error( 'http_error', 'Service Unavailable' );
        $mock_calls['wp_remote_request']['https://app.monicahq.com/api/contacts/1'] = $error;

        $api = new Monica_API();
        $result = $api->put( 'contacts/1' );

        assertEquals( true, is_wp_error( $result ), 'Expected a WP_Error to be returned' );
        assertEquals( 'http_error', $result->get_error_code(), 'Expected the same error code' );
    }
}
