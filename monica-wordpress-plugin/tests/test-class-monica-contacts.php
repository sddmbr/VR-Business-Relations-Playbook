<?php
require_once __DIR__ . '/../includes/class-monica-contacts.php';

class Test_Class_Monica_Contacts {
    public function setUp() {
        global $mock_update_post_meta_calls, $mock_verify_nonce_return, $mock_current_user_can_return, $mock_calls;
        $mock_update_post_meta_calls = [];
        $mock_verify_nonce_return = null;
        $mock_current_user_can_return = null;
        $mock_calls = [];
        $_POST = [];
    }

    public function test_save_contact_meta_data_no_nonce() {
        $this->setUp();
        $contact = new Monica_Contacts();
        $contact->save_contact_meta_data( 123 );

        global $mock_update_post_meta_calls;
        assertEquals( 0, count( $mock_update_post_meta_calls ), 'Should not update meta when nonce is missing' );
    }

    public function test_save_contact_meta_data_invalid_nonce() {
        $this->setUp();
        $_POST['monica_contact_details_nonce'] = 'invalid_nonce';
        global $mock_verify_nonce_return;
        $mock_verify_nonce_return = false;

        $contact = new Monica_Contacts();
        $contact->save_contact_meta_data( 123 );

        global $mock_update_post_meta_calls;
        assertEquals( 0, count( $mock_update_post_meta_calls ), 'Should not update meta when nonce is invalid' );
    }

    public function test_save_contact_meta_data_cannot_edit_post() {
        $this->setUp();
        $_POST['monica_contact_details_nonce'] = 'valid_nonce';
        global $mock_verify_nonce_return, $mock_current_user_can_return;
        $mock_verify_nonce_return = true;
        $mock_current_user_can_return = false;

        $contact = new Monica_Contacts();
        $contact->save_contact_meta_data( 123 );

        global $mock_update_post_meta_calls;
        assertEquals( 0, count( $mock_update_post_meta_calls ), 'Should not update meta when user cannot edit post' );
    }

    // This test defines a constant which affects subsequent tests, so we place it last
    public function test_save_contact_meta_data_doing_autosave() {
        $this->setUp();
        $_POST['monica_contact_details_nonce'] = 'valid_nonce';
        global $mock_verify_nonce_return;
        $mock_verify_nonce_return = true;

        if ( ! defined( 'DOING_AUTOSAVE' ) ) {
            define( 'DOING_AUTOSAVE', true );
        }

        $contact = new Monica_Contacts();
        $contact->save_contact_meta_data( 123 );

        global $mock_update_post_meta_calls;
        assertEquals( 0, count( $mock_update_post_meta_calls ), 'Should not update meta during autosave' );
    }
}
