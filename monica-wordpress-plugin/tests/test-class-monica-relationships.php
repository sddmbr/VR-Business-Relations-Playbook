<?php

require_once __DIR__ . '/../includes/class-monica-relationships.php';

class Test_Class_Monica_Relationships {

    public function setUp() {
        global $mock_actions, $mock_meta_boxes, $mock_post_meta, $mock_calls, $wp_options, $mock_transients;
        $mock_actions = [];
        $mock_meta_boxes = [];
        $mock_post_meta = [];
        $mock_calls = [];
        $mock_transients = [];
        $wp_options = [];
        $wp_options['monica_access_token'] = 'test_token';

        // Setup default mocks for required calls inside render_relationships_meta_box
        if ( ! function_exists( 'get_posts' ) ) {
            function get_posts( $args = null ) {
                return [];
            }
        }
    }

    public function test_transient_caching_relationships() {
        $this->setUp();
        global $mock_post_meta, $mock_calls, $mock_transients;

        $post = new stdClass();
        $post->ID = 123;
        $mock_post_meta[123]['_monica_contact_id'] = 'monica_123';

        // Set up the API response mock
        $mock_calls['wp_remote_get']['https://app.monicahq.com/api/contacts/monica_123/relationships'] = [
            'body' => json_encode([
                'data' => [
                    [
                        'relationship_type' => ['name' => 'Friend'],
                        'contact' => ['first_name' => 'Alice', 'last_name' => 'Smith']
                    ]
                ]
            ])
        ];

        // Mock get_posts since it is called in render_relationships_meta_box
        $GLOBALS['mock_calls']['get_posts'] = [];

        $relationships = new Monica_Relationships();

        // First call should hit the "API" and set the transient
        ob_start();
        $relationships->render_relationships_meta_box( $post );
        $output1 = ob_get_clean();

        // Verify that the transient was set
        $transient_key = "monica_relationships_monica_123";
        $transient_value = get_transient( $transient_key );

        assertEquals( true, (bool)$transient_value, 'Transient should be set after the first call.' );
        assertEquals( 'Alice Smith', $transient_value['data'][0]['contact']['first_name'] . ' ' . $transient_value['data'][0]['contact']['last_name'], 'Transient data should match the API response.' );

        // Second call: Let's remove the "API" mock to ensure it uses the transient
        unset( $mock_calls['wp_remote_get']['https://app.monicahq.com/api/contacts/monica_123/relationships'] );

        ob_start();
        $relationships->render_relationships_meta_box( $post );
        $output2 = ob_get_clean();

        $contains_relationship = strpos($output2, '<li>Friend: Alice Smith</li>') !== false;
        assertEquals( true, $contains_relationship, 'Should output relationships from the transient.' );
    }
}
