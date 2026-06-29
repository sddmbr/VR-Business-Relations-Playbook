<?php

class Monica_API {

    private $api_url = 'https://app.monicahq.com/api/';

    public function __construct() {
        // Constructor
    }

    public function get_authorization_url( $redirect_uri ) {
        $params = [
            'client_id'     => get_option( 'monica_client_id' ),
            'redirect_uri'  => $redirect_uri,
            'response_type' => 'code',
            'state'         => wp_create_nonce( 'monica_oauth_state' ),
        ];

        return 'https://app.monicahq.com/oauth/authorize?' . http_build_query( $params );
    }

    public function get_access_token( $code, $redirect_uri ) {
        $params = [
            'grant_type'    => 'authorization_code',
            'client_id'     => get_option( 'monica_client_id' ),
            'client_secret' => get_option( 'monica_client_secret' ),
            'redirect_uri'  => $redirect_uri,
            'code'          => $code,
        ];

        $response = wp_remote_post( 'https://app.monicahq.com/oauth/token', [
            'body' => $params,
        ] );

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        return $data;
    }

    private function get_access_token_or_error() {
        $access_token = get_option( 'monica_access_token' );

        if ( ! $access_token ) {
            return new WP_Error( 'no_access_token', __( 'No access token found.', 'monica-integration' ) );
        }

        return $access_token;
    }

    public function get( $endpoint, $args = [] ) {
        $access_token = $this->get_access_token_or_error();

        if ( is_wp_error( $access_token ) ) {
            return $access_token;
        }

        $args['headers'] = [
            'Authorization' => 'Bearer ' . $access_token,
        ];

        $response = wp_remote_get( $this->api_url . $endpoint, $args );

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        return $data;
    }

    public function post( $endpoint, $args = [] ) {
        $access_token = $this->get_access_token_or_error();

        if ( is_wp_error( $access_token ) ) {
            return $access_token;
        }

        $args['headers'] = [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json',
        ];

        $response = wp_remote_post( $this->api_url . $endpoint, $args );

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        return $data;
    }

    public function put( $endpoint, $args = [] ) {
        $access_token = $this->get_access_token_or_error();

        if ( is_wp_error( $access_token ) ) {
            return $access_token;
        }

        $args['method']  = 'PUT';
        $args['headers'] = [
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type'  => 'application/json',
        ];

        $response = wp_remote_request( $this->api_url . $endpoint, $args );

        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        return $data;
    }
}
