<?php

require_once __DIR__ . '/../includes/class-monica-contacts.php';

class Test_Class_Monica_Contacts {

    public function test_construct_adds_actions() {
        global $mock_actions;
        $mock_actions = [];

        $contacts = new Monica_Contacts();

        $expected_actions = [
            'init' => 'register_contact_post_type',
            'add_meta_boxes' => 'add_contact_meta_boxes',
            'save_post_monica_contact' => 'save_contact_meta_data'
        ];

        $found_actions = [];
        foreach ( $mock_actions as $action ) {
            if ( is_array( $action['function'] ) && $action['function'][0] === $contacts ) {
                $found_actions[ $action['tag'] ] = $action['function'][1];
            }
        }

        foreach ( $expected_actions as $tag => $method ) {
            assertEquals( true, isset( $found_actions[ $tag ] ), "Action {$tag} was not added." );
            assertEquals( $method, $found_actions[ $tag ], "Action {$tag} method mismatch." );
        }
    }

    public function test_register_contact_post_type() {
        global $mock_calls;
        $mock_calls = [];

        $contacts = new Monica_Contacts();
        $contacts->register_contact_post_type();

        assertEquals( false, empty( $mock_calls['register_post_type'] ), "register_post_type was not called." );

        $call = $mock_calls['register_post_type'][0];
        assertEquals( 'monica_contact', $call['post_type'], "Post type should be monica_contact" );
        assertEquals( true, $call['args']['public'], "Post type should be public" );
        assertEquals( 'dashicons-groups', $call['args']['menu_icon'], "Menu icon should be dashicons-groups" );
    }

    public function test_add_contact_meta_boxes() {
        global $mock_meta_boxes;
        $mock_meta_boxes = [];

        $contacts = new Monica_Contacts();
        $contacts->add_contact_meta_boxes();

        assertEquals( false, empty( $mock_meta_boxes ), "add_meta_box was not called." );

        $box = $mock_meta_boxes[0];
        assertEquals( 'monica_contact_details', $box['id'], "Meta box ID mismatch" );
        assertEquals( 'monica_contact', $box['screen'], "Meta box screen mismatch" );
    }

    public function test_render_contact_details_meta_box() {
        global $mock_post_meta;
        $post = new stdClass();
        $post->ID = 100;

        $mock_post_meta[100]['_monica_first_name'] = 'John';
        $mock_post_meta[100]['_monica_last_name'] = 'Doe';
        $mock_post_meta[100]['_monica_email'] = 'john@example.com';

        $contacts = new Monica_Contacts();

        ob_start();
        $contacts->render_contact_details_meta_box( $post );
        $output = ob_get_clean();

        assertEquals( true, strpos( $output, 'name="monica_contact_details_nonce"' ) !== false, "Nonce field missing from output." );
        assertEquals( true, strpos( $output, 'value="John"' ) !== false, "First name value missing from output." );
        assertEquals( true, strpos( $output, 'value="Doe"' ) !== false, "Last name value missing from output." );
        assertEquals( true, strpos( $output, 'value="john@example.com"' ) !== false, "Email value missing from output." );
    }

    public function test_save_contact_meta_data_no_nonce() {
        global $mock_calls;
        $mock_calls = [];
        $_POST = [];

        $contacts = new Monica_Contacts();
        $contacts->save_contact_meta_data( 100 );

        assertEquals( false, isset( $mock_calls['update_post_meta'] ), "Should not save data without nonce." );
    }

    public function test_save_contact_meta_data_invalid_nonce() {
        global $mock_calls;
        $mock_calls = [];
        $mock_calls['wp_verify_nonce_return'] = false;

        $_POST = [
            'monica_contact_details_nonce' => 'invalid',
        ];

        $contacts = new Monica_Contacts();
        $contacts->save_contact_meta_data( 100 );

        assertEquals( false, isset( $mock_calls['update_post_meta'] ), "Should not save data with invalid nonce." );
    }

    public function test_save_contact_meta_data_no_permission() {
        global $mock_calls;
        $mock_calls = [];
        $mock_calls['wp_verify_nonce_return'] = true;
        $mock_calls['current_user_can_return'] = false;

        $_POST = [
            'monica_contact_details_nonce' => 'valid',
        ];

        $contacts = new Monica_Contacts();
        $contacts->save_contact_meta_data( 100 );

        assertEquals( false, isset( $mock_calls['update_post_meta'] ), "Should not save data without edit_post permission." );
    }

    public function test_save_contact_meta_data_valid_create() {
        global $mock_calls, $mock_post_meta;
        $mock_calls = [];
        $mock_calls['wp_verify_nonce_return'] = true;
        $mock_calls['current_user_can_return'] = true;

        $post_id = 100;
        $mock_post_meta[$post_id] = [];

        $_POST = [
            'monica_contact_details_nonce' => 'valid',
            'monica_first_name' => 'John',
            'monica_last_name' => 'Doe',
            'monica_email' => 'john@example.com',
        ];

        update_option( 'monica_access_token', 'mock_token' );

        $contacts = new Monica_Contacts();
        $contacts->save_contact_meta_data( $post_id );

        assertEquals( false, empty( $mock_calls['update_post_meta'] ), "update_post_meta was not called." );

        $meta_keys = array_column( $mock_calls['update_post_meta'], 'meta_key' );
        assertEquals( true, in_array( '_monica_first_name', $meta_keys ), "First name meta not updated." );

        assertEquals( false, empty( $mock_calls['wp_remote_post'] ), "API POST request was not made." );

        $api_call = $mock_calls['wp_remote_post'][0];
        assertEquals( true, strpos( $api_call['url'], 'contacts' ) !== false, "API POST request URL incorrect." );
    }

    public function test_save_contact_meta_data_valid_update() {
        global $mock_calls, $mock_post_meta;
        $mock_calls = [];
        $mock_calls['wp_verify_nonce_return'] = true;
        $mock_calls['current_user_can_return'] = true;

        $post_id = 101;
        $mock_post_meta[$post_id]['_monica_contact_id'] = 456;

        $_POST = [
            'monica_contact_details_nonce' => 'valid',
            'monica_first_name' => 'Jane',
            'monica_last_name' => 'Smith',
            'monica_email' => 'jane@example.com',
        ];

        update_option( 'monica_access_token', 'mock_token' );

        $contacts = new Monica_Contacts();
        $contacts->save_contact_meta_data( $post_id );

        assertEquals( false, empty( $mock_calls['wp_remote_request'] ), "API PUT request was not made." );

        $api_call = $mock_calls['wp_remote_request'][0];
        assertEquals( true, strpos( $api_call['url'], 'contacts/456' ) !== false, "API PUT request URL incorrect." );
        assertEquals( 'PUT', $api_call['args']['method'], "API request method is not PUT." );
    }

    public function test_save_contact_meta_data_autosave() {
        global $mock_calls;
        $mock_calls = [];
        $mock_calls['wp_verify_nonce_return'] = true;

        if ( ! defined( 'DOING_AUTOSAVE' ) ) {
            define( 'DOING_AUTOSAVE', true );
        }

        $_POST = [
            'monica_contact_details_nonce' => 'valid',
        ];

        $contacts = new Monica_Contacts();
        $contacts->save_contact_meta_data( 100 );

        assertEquals( false, isset( $mock_calls['update_post_meta'] ), "Should not save data during autosave." );
    }
}
