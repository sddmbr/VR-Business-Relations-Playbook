<?php

require_once __DIR__ . '/../includes/class-monica-contacts.php';

class Test_Class_Monica_Contacts {

    public function setUp() {
        global $mock_actions, $mock_meta_boxes, $mock_post_meta, $mock_calls, $wp_options, $mock_transients;
        $mock_actions = [];
        $mock_meta_boxes = [];
        $mock_post_meta = [];
        $mock_calls = [];
        $mock_transients = [];
        $wp_options = [];
        $_POST = [];
    }

    public function test_save_contact_meta_data_no_nonce() {
        $this->setUp();

        $contacts = new Monica_Contacts();
        $result = $contacts->save_contact_meta_data( 123 );

        assertEquals( null, $result, 'Should return early when nonce is missing' );
    }

    public function test_save_contact_meta_data_invalid_nonce() {
        $this->setUp();

        $_POST['monica_contact_details_nonce'] = 'invalid_mock_nonce';

        $contacts = new Monica_Contacts();
        $result = $contacts->save_contact_meta_data( 123 );

        assertEquals( null, $result, 'Should return early when nonce is invalid' );
    }

    public function test_save_contact_meta_data_doing_autosave() {
        $this->setUp();

        if ( ! defined( 'DOING_AUTOSAVE' ) ) {
            define( 'DOING_AUTOSAVE', true );
        }

        $_POST['monica_contact_details_nonce'] = 'valid_mock_nonce';

        $contacts = new Monica_Contacts();
        $result = $contacts->save_contact_meta_data( 123 );

        assertEquals( null, $result, 'Should return early when DOING_AUTOSAVE is defined and true' );
    }
}
