<?php

function test_monica_api_get_returns_error_when_no_access_token() {
    // Ensure no access token is set
    unset( $GLOBALS['wp_options']['monica_access_token'] );

    $api = new Monica_API();
    $result = $api->get( 'contacts' );

    if ( ! ( $result instanceof WP_Error ) ) {
        throw new Exception( 'Result is not a WP_Error instance' );
    }

    if ( $result->get_error_code() !== 'no_access_token' ) {
        throw new Exception( 'Incorrect error code: ' . $result->get_error_code() );
    }

    if ( $result->get_error_message() !== 'No access token found.' ) {
        throw new Exception( 'Incorrect error message: ' . $result->get_error_message() );
    }
}
