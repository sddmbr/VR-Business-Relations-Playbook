<?php

class Monica_API {

    private $api_url = 'https://app.monicahq.com/api/';

    /**
     * Get the authorization headers for the Monica API.
     *
     * @return array|WP_Error Headers array on success, WP_Error on failure.
     */
    private function get_auth_headers() {
        $access_token = get_option( "monica_access_token" );

        if ( ! $access_token ) {
            return new WP_Error( "no_access_token", __( "No access token found.", "monica-integration" ) );
        }

        return [
            "Authorization" => "Bearer " . $access_token,
        ];
    }

    public function get( $endpoint, $args = [] ) {
        $headers = $this->get_auth_headers();
        if ( is_wp_error( $headers ) ) {
            return $headers;
        }

        $args["headers"] = isset( $args["headers"] ) ? array_merge( $headers, $args["headers"] ) : $headers;

        $response = wp_remote_get( $this->api_url . $endpoint, $args );
        return $response;
    }

    public function post( $endpoint, $body = [], $args = [] ) {
        $headers = $this->get_auth_headers();
        if ( is_wp_error( $headers ) ) {
            return $headers;
        }

        $default_headers = array_merge( $headers, [ "Content-Type" => "application/json" ] );
        $args["headers"] = isset( $args["headers"] ) ? array_merge( $default_headers, $args["headers"] ) : $default_headers;

        $args["body"] = wp_json_encode( $body );

        $response = wp_remote_post( $this->api_url . $endpoint, $args );
        return $response;
    }

}
