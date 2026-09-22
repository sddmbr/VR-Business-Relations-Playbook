<?php

require_once __DIR__ . '/../includes/class-monica-contacts.php';

class Test_Class_Monica_Contacts {

    public function setUp() {
        global $mock_actions, $mock_meta_boxes, $mock_post_meta, $mock_calls, $wp_options, $mock_transients, $mock_current_user_can;
        $mock_actions = [];
        $mock_meta_boxes = [];
        $mock_post_meta = [];
        $mock_calls = [];
        $mock_transients = [];
        $wp_options = [];
        $mock_current_user_can = true;

        $_POST = [];
    }

    public function test_save_contact_meta_data_missing_nonce() {
        $this->setUp();
        $contacts = new Monica_Contacts();
        $post_id = 123;

        $contacts->save_contact_meta_data( $post_id );

        global $mock_post_meta, $mock_calls;
        assertEquals( true, empty( $mock_post_meta ), 'Post meta should not be updated.' );
        assertEquals( true, empty( $mock_calls ), 'API should not be called.' );
    }

    public function test_save_contact_meta_data_invalid_nonce() {
        $this->setUp();
        $contacts = new Monica_Contacts();
        $post_id = 123;

        $_POST['monica_contact_details_nonce'] = 'invalid_nonce';

        $contacts->save_contact_meta_data( $post_id );

        global $mock_post_meta, $mock_calls;
        assertEquals( true, empty( $mock_post_meta ), 'Post meta should not be updated.' );
        assertEquals( true, empty( $mock_calls ), 'API should not be called.' );
    }

    public function test_save_contact_meta_data_cannot_edit_post() {
        $this->setUp();
        global $mock_current_user_can;
        $mock_current_user_can = false;

        $contacts = new Monica_Contacts();
        $post_id = 123;

        $_POST['monica_contact_details_nonce'] = 'mock_nonce_valid';

        $contacts->save_contact_meta_data( $post_id );

        global $mock_post_meta, $mock_calls;
        assertEquals( true, empty( $mock_post_meta ), 'Post meta should not be updated.' );
        assertEquals( true, empty( $mock_calls ), 'API should not be called.' );
    }

    public function test_save_contact_meta_data_doing_autosave() {
        $this->setUp();
        if ( ! defined( 'DOING_AUTOSAVE' ) ) {
            define( 'DOING_AUTOSAVE', true );
        }

        $contacts = new Monica_Contacts();
        $post_id = 123;

        $_POST['monica_contact_details_nonce'] = 'mock_nonce_valid';

        $contacts->save_contact_meta_data( $post_id );

        global $mock_post_meta, $mock_calls;
        assertEquals( true, empty( $mock_post_meta ), 'Post meta should not be updated.' );
        assertEquals( true, empty( $mock_calls ), 'API should not be called.' );
    }
}
