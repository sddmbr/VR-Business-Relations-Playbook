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
    }

    public function test_construct_adds_action() {
        $this->setUp();
        global $mock_actions;

        $relationships = new Monica_Relationships();

        $action_found = false;
        foreach ( $mock_actions as $action ) {
            if ( $action['tag'] === 'add_meta_boxes' && $action['function'] === [ $relationships, 'add_relationships_meta_box' ] ) {
                $action_found = true;
                break;
            }
        }

        assertEquals( true, $action_found, 'Monica_Relationships constructor should hook into add_meta_boxes.' );
    }

    public function test_add_relationships_meta_box() {
        $this->setUp();
        global $mock_meta_boxes;

        $relationships = new Monica_Relationships();
        $relationships->add_relationships_meta_box();

        $meta_box_found = false;
        foreach ( $mock_meta_boxes as $meta_box ) {
            if ( $meta_box['id'] === 'monica_relationships' &&
                 $meta_box['title'] === 'Relationships' &&
                 $meta_box['callback'] === [ $relationships, 'render_relationships_meta_box' ] &&
                 $meta_box['screen'] === 'monica_contact' &&
                 $meta_box['context'] === 'side' ) {
                $meta_box_found = true;
                break;
            }
        }

        assertEquals( true, $meta_box_found, 'add_relationships_meta_box should add the meta box correctly.' );
    }

    public function test_render_relationships_meta_box_no_contact_id() {
        $this->setUp();
        global $mock_post_meta;

        $post = new stdClass();
        $post->ID = 123;

        $mock_post_meta[123]['_monica_contact_id'] = false;

        $relationships = new Monica_Relationships();

        ob_start();
        $relationships->render_relationships_meta_box( $post );
        $output = ob_get_clean();

        $expected = '<p>Save the contact to view relationships.</p>';
        assertEquals( $expected, $output, 'Should return early with message if no contact ID.' );
    }

    public function test_render_relationships_meta_box_api_error() {
        $this->setUp();
        global $mock_post_meta, $wp_options;

        $post = new stdClass();
        $post->ID = 123;
        $mock_post_meta[123]['_monica_contact_id'] = 'monica_123';

        $wp_options['monica_access_token'] = false;

        $relationships = new Monica_Relationships();

        ob_start();
        $relationships->render_relationships_meta_box( $post );
        $output = ob_get_clean();

        $expected = '<p>No access token found.</p>';
        assertEquals( $expected, $output, 'Should handle API WP_Error correctly.' );
    }

    public function test_render_relationships_meta_box_no_relationships() {
        $this->setUp();
        global $mock_post_meta, $mock_calls;

        $post = new stdClass();
        $post->ID = 123;
        $mock_post_meta[123]['_monica_contact_id'] = 'monica_123';

        $mock_calls['wp_remote_get']['https://app.monicahq.com/api/contacts/monica_123/relationships'] = [
            'body' => json_encode(['data' => []])
        ];

        $relationships = new Monica_Relationships();

        ob_start();
        $relationships->render_relationships_meta_box( $post );
        $output = ob_get_clean();

        $contains_no_relationships = strpos($output, '<p>No relationships found.</p>') !== false;
        assertEquals( true, $contains_no_relationships, 'Should output no relationships message.' );

        $contains_form = strpos($output, '<form action="" method="post">') !== false;
        assertEquals( true, $contains_form, 'Should render add new relationship form.' );
    }

    public function test_render_relationships_meta_box_with_relationships() {
        $this->setUp();
        global $mock_post_meta, $mock_calls;

        $post = new stdClass();
        $post->ID = 123;
        $mock_post_meta[123]['_monica_contact_id'] = 'monica_123';

        $mock_calls['wp_remote_get']['https://app.monicahq.com/api/contacts/monica_123/relationships'] = [
            'body' => json_encode([
                'data' => [
                    [
                        'relationship_type' => ['name' => 'Friend'],
                        'contact' => ['first_name' => 'John', 'last_name' => 'Doe']
                    ]
                ]
            ])
        ];

        $relationships = new Monica_Relationships();

        ob_start();
        $relationships->render_relationships_meta_box( $post );
        $output = ob_get_clean();

        $contains_relationship = strpos($output, '<li>Friend: John Doe</li>') !== false;
        assertEquals( true, $contains_relationship, 'Should output relationships in a list.' );

        $contains_form = strpos($output, '<form action="" method="post">') !== false;
        assertEquals( true, $contains_form, 'Should render add new relationship form.' );
    }
}
