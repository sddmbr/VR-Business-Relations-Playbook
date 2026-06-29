<?php

require_once __DIR__ . '/../includes/class-monica-reminders.php';

class Test_Class_Monica_Reminders {

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

        $reminders = new Monica_Reminders();

        $action_found = false;
        foreach ( $mock_actions as $action ) {
            if ( $action['tag'] === 'add_meta_boxes' && $action['function'] === [ $reminders, 'add_reminders_meta_box' ] ) {
                $action_found = true;
                break;
            }
        }

        assertEquals( true, $action_found, 'Monica_Reminders constructor should hook into add_meta_boxes.' );
    }

    public function test_add_reminders_meta_box() {
        $this->setUp();
        global $mock_meta_boxes;

        $reminders = new Monica_Reminders();
        $reminders->add_reminders_meta_box();

        $meta_box_found = false;
        foreach ( $mock_meta_boxes as $meta_box ) {
            if ( $meta_box['id'] === 'monica_reminders' &&
                 $meta_box['title'] === 'Reminders' &&
                 $meta_box['callback'] === [ $reminders, 'render_reminders_meta_box' ] &&
                 $meta_box['screen'] === 'monica_contact' &&
                 $meta_box['context'] === 'side' ) {
                $meta_box_found = true;
                break;
            }
        }

        assertEquals( true, $meta_box_found, 'add_reminders_meta_box should add the meta box correctly.' );
    }

    public function test_render_reminders_meta_box_no_contact_id() {
        $this->setUp();
        global $mock_post_meta;

        $post = new stdClass();
        $post->ID = 123;

        $mock_post_meta[123]['_monica_contact_id'] = false;

        $reminders = new Monica_Reminders();

        ob_start();
        $reminders->render_reminders_meta_box( $post );
        $output = ob_get_clean();

        $expected = '<p>Save the contact to view reminders.</p>';
        assertEquals( $expected, $output, 'Should return early with message if no contact ID.' );
    }

    public function test_render_reminders_meta_box_api_error() {
        $this->setUp();
        global $mock_post_meta, $wp_options;

        $post = new stdClass();
        $post->ID = 123;
        $mock_post_meta[123]['_monica_contact_id'] = 'monica_123';

        // In the current branch Monica_API::get does not pass through WP_Error from wp_remote_get.
        // But if get_option('monica_access_token') is empty, it returns a WP_Error 'no_access_token'.
        $wp_options['monica_access_token'] = false;

        $reminders = new Monica_Reminders();

        ob_start();
        $reminders->render_reminders_meta_box( $post );
        $output = ob_get_clean();

        $expected = '<p>No access token found.</p>';
        assertEquals( $expected, $output, 'Should handle API WP_Error correctly.' );
    }

    public function test_render_reminders_meta_box_no_reminders() {
        $this->setUp();
        global $mock_post_meta, $mock_calls;

        $post = new stdClass();
        $post->ID = 123;
        $mock_post_meta[123]['_monica_contact_id'] = 'monica_123';

        $mock_calls['wp_remote_get']['https://app.monicahq.com/api/contacts/monica_123/reminders'] = [
            'body' => json_encode(['data' => []])
        ];

        $reminders = new Monica_Reminders();

        ob_start();
        $reminders->render_reminders_meta_box( $post );
        $output = ob_get_clean();

        // Check for 'No reminders found.'
        $contains_no_reminders = strpos($output, '<p>No reminders found.</p>') !== false;
        assertEquals( true, $contains_no_reminders, 'Should output no reminders message.' );

        // Check for the form being rendered
        $contains_form = strpos($output, '<form action="" method="post">') !== false;
        assertEquals( true, $contains_form, 'Should render add new reminder form.' );
    }

    public function test_render_reminders_meta_box_with_reminders() {
        $this->setUp();
        global $mock_post_meta, $mock_calls;

        $post = new stdClass();
        $post->ID = 123;
        $mock_post_meta[123]['_monica_contact_id'] = 'monica_123';

        $mock_calls['wp_remote_get']['https://app.monicahq.com/api/contacts/monica_123/reminders'] = [
            'body' => json_encode([
                'data' => [
                    [
                        'title' => 'Buy a gift',
                        'reminder_date' => '2023-12-25'
                    ]
                ]
            ])
        ];

        $reminders = new Monica_Reminders();

        ob_start();
        $reminders->render_reminders_meta_box( $post );
        $output = ob_get_clean();

        // Check for the reminder
        $contains_reminder = strpos($output, '<li>Buy a gift - 2023-12-25</li>') !== false;
        assertEquals( true, $contains_reminder, 'Should output reminders in a list.' );

        // Check for the form being rendered
        $contains_form = strpos($output, '<form action="" method="post">') !== false;
        assertEquals( true, $contains_form, 'Should render add new reminder form.' );
    }
}
