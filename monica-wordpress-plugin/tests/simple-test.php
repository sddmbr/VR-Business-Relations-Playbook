<?php

class WP_Error {
    public $code;
    public $message;
    public $data;

    public function __construct( $code = '', $message = '', $data = '' ) {
        $this->code = $code;
        $this->message = $message;
        $this->data = $data;
    }
}

function is_wp_error( $thing ) {
    return ( $thing instanceof WP_Error );
}

function get_option( $option ) {
    if ( $option === 'monica_access_token' ) {
        return 'test_token';
    }
    return null;
}

function __($text, $domain) {
    return $text;
}

$mock_response = [];
function wp_remote_get( $url, $args = [] ) {
    global $mock_response;
    return $mock_response;
}

function wp_remote_retrieve_body( $response ) {
    return isset($response['body']) ? $response['body'] : '';
}

require_once __DIR__ . '/../includes/class-monica-api.php';

$api = new Monica_API();

// Test success
$mock_response = [
    'body' => json_encode(['data' => ['id' => 1, 'first_name' => 'John']])
];
$result = $api->get('contacts');
if (is_array($result) && isset($result['data']['id']) && $result['data']['id'] === 1) {
    echo "Success: GET request handled correctly.\n";
} else {
    echo "Failed: GET request not handled correctly.\n";
    exit(1);
}

// Test WP_Error
$mock_response = new WP_Error('http_error', 'Service unavailable');
$result = $api->get('contacts');
if (is_wp_error($result) && $result->code === 'http_error') {
    echo "Success: WP_Error handled correctly.\n";
} else {
    echo "Failed: WP_Error not handled correctly.\n";
    exit(1);
}

echo "All tests passed.\n";
