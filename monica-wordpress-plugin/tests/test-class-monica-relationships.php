<?php

require_once __DIR__ . '/../includes/class-monica-relationships.php';

class Test_Class_Monica_Relationships {

    public function setUp() {
        $GLOBALS['mock_actions'] = [];
        $GLOBALS['mock_meta_boxes'] = [];
        $GLOBALS['mock_post_meta'] = [];
        $GLOBALS['mock_calls'] = [];
        $GLOBALS['mock_transients'] = [];
        $GLOBALS['wp_options']['monica_access_token'] = 'mock_token';
    }

    public function test_add_relationships_meta_box() {
        $this->setUp();

        $relationships = new Monica_Relationships();
        $relationships->add_relationships_meta_box();

        $found = false;
        foreach ( $GLOBALS['mock_meta_boxes'] as $box ) {
            if ( $box['id'] === 'monica_relationships' ) {
                $found = true;
                assertEquals( 'monica_contact', $box['screen'] );
                assertEquals( 'side', $box['context'] );
                break;
            }
        }
        assertEquals( true, $found, "Meta box 'monica_relationships' should be added." );
    }

    public function test_render_relationships_meta_box_no_contact() {
        $this->setUp();

        $relationships = new Monica_Relationships();

        $post = new stdClass();
        $post->ID = 123;

        // No contact ID set in meta
        ob_start();
        $relationships->render_relationships_meta_box( $post );
        $output = ob_get_clean();

        assertEquals( true, strpos( $output, 'Save the contact to view relationships.' ) !== false );
    }

    public function test_render_relationships_meta_box_api_error() {
        $this->setUp();

        $relationships = new Monica_Relationships();

        $post = new stdClass();
        $post->ID = 123;
        $GLOBALS['mock_post_meta'][123]['_monica_contact_id'] = 456;

        $GLOBALS['mock_calls']['wp_remote_get']["https://app.monicahq.com/api/contacts/456/relationships"] = new WP_Error( 'api_error', 'API is down' );

        ob_start();
        $relationships->render_relationships_meta_box( $post );
        $output = ob_get_clean();

        assertEquals( true, strpos( $output, 'API is down' ) !== false );
    }

    public function test_render_relationships_meta_box_no_relationships() {
        $this->setUp();

        $relationships = new Monica_Relationships();

        $post = new stdClass();
        $post->ID = 123;
        $GLOBALS['mock_post_meta'][123]['_monica_contact_id'] = 456;

        // Mock empty relationships
        $GLOBALS['mock_calls']['wp_remote_get']["https://app.monicahq.com/api/contacts/456/relationships"] = [
            'response' => [ 'code' => 200 ],
            'body' => json_encode( [ 'data' => [] ] )
        ];

        // Mock API call for relationship types (fallback when transient is not set)
        $GLOBALS['mock_calls']['wp_remote_get']["https://app.monicahq.com/api/relationshiptypes"] = [
            'response' => [ 'code' => 200 ],
            'body' => json_encode( [ 'data' => [] ] )
        ];

        ob_start();
        $relationships->render_relationships_meta_box( $post );
        $output = ob_get_clean();

        assertEquals( true, strpos( $output, 'No relationships found.' ) !== false );
        assertEquals( true, strpos( $output, 'Add New Relationship' ) !== false );
    }

    public function test_render_relationships_meta_box_success() {
        $this->setUp();

        $relationships = new Monica_Relationships();

        $post = new stdClass();
        $post->ID = 123;
        $GLOBALS['mock_post_meta'][123]['_monica_contact_id'] = 456;

        // Mock relationships
        $GLOBALS['mock_calls']['wp_remote_get']["https://app.monicahq.com/api/contacts/456/relationships"] = [
            'response' => [ 'code' => 200 ],
            'body' => json_encode( [
                'data' => [
                    [
                        'relationship_type' => [ 'name' => 'Friend' ],
                        'contact' => [ 'first_name' => 'John', 'last_name' => 'Doe' ]
                    ]
                ]
            ] )
        ];

        // Set transient for relationship types to test that logic
        set_transient( 'monica_relationship_types', [
            'data' => [
                [ 'id' => 1, 'name' => 'Parent' ],
                [ 'id' => 2, 'name' => 'Child' ]
            ]
        ] );

        // Mock get_posts for contacts dropdown
        $contact1 = new stdClass();
        $contact1->ID = 201;
        $contact1->post_title = 'Alice';
        $GLOBALS['mock_post_meta'][201]['_monica_contact_id'] = 789;

        $contact2 = new stdClass();
        $contact2->ID = 202;
        $contact2->post_title = 'Bob (No ID)';
        // No _monica_contact_id for contact2

        $GLOBALS['mock_calls']['get_posts_return'] = [ $contact1, $contact2 ];

        ob_start();
        $relationships->render_relationships_meta_box( $post );
        $output = ob_get_clean();

        // Check relationships list
        assertEquals( true, strpos( $output, 'Friend: John Doe' ) !== false );

        // Check relationship types dropdown
        assertEquals( true, strpos( $output, '<option value="1">Parent</option>' ) !== false );
        assertEquals( true, strpos( $output, '<option value="2">Child</option>' ) !== false );

        // Check contacts dropdown (should include Alice, skip Bob)
        assertEquals( true, strpos( $output, '<option value="789">Alice</option>' ) !== false );
        assertEquals( false, strpos( $output, 'Bob (No ID)' ) !== false );

        // Check hidden fields
        assertEquals( true, strpos( $output, 'name="monica_contact_id" value="456"' ) !== false );
        assertEquals( true, strpos( $output, 'name="monica_post_id" value="123"' ) !== false );

        // Verify get_posts was called with correct arguments
        $last_get_posts_call = end( $GLOBALS['mock_calls']['get_posts'] );
        assertEquals( 'monica_contact', $last_get_posts_call['post_type'] );
        assertEquals( -1, $last_get_posts_call['posts_per_page'] );
        assertEquals( [ 123 ], $last_get_posts_call['post__not_in'] );
    }
}
