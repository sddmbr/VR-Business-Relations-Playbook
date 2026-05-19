<?php
/**
 * Tests for Monica_API class.
 */

function test_monica_api_get_no_access_token() {
    // Ensure no access token is set
    delete_option( 'monica_access_token' );

    $api = new Monica_API();
    $result = $api->get( 'contacts' );

    assert_wp_error( $result );
    assert_equals( 'no_access_token', $result->get_error_code() );
}

function test_monica_api_post_no_access_token() {
    // Ensure no access token is set
    delete_option( 'monica_access_token' );

    $api = new Monica_API();
    $result = $api->post( 'contacts' );

    assert_wp_error( $result );
    assert_equals( 'no_access_token', $result->get_error_code() );
}

function test_monica_api_put_no_access_token() {
    // Ensure no access token is set
    delete_option( 'monica_access_token' );

    $api = new Monica_API();
    $result = $api->put( 'contacts/1' );

    assert_wp_error( $result );
    assert_equals( 'no_access_token', $result->get_error_code() );
}
