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

    public function test_post_missing_access_token() {
        // Clear options to ensure get_option('monica_access_token') returns false
        global $wp_options;
        $wp_options = [];

        $api = new Monica_API();
        $result = $api->post( 'contacts', [] );

        if ( ! is_a( $result, 'WP_Error' ) ) {
            throw new Exception( "Expected WP_Error, but got " . gettype( $result ) );
        }

        assertEquals( 'no_access_token', $result->get_error_code(), 'The error code should be no_access_token.' );
        assertEquals( 'No access token found.', $result->get_error_message(), 'The error message should be correctly set.' );
    }
}
