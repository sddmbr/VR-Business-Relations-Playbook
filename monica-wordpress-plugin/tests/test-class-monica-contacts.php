<?php

require_once __DIR__ . '/../includes/class-monica-contacts.php';

class Test_Class_Monica_Contacts {
    public function setUp() {
        $_POST = [];
        $GLOBALS['mock_update_post_meta'] = [];
        $GLOBALS['mock_verify_nonce'] = false;
        $GLOBALS['mock_current_user_can'] = true;
    }

    public function test_save_contact_meta_data_no_nonce() {
        $this->setUp();
        $contacts = new Monica_Contacts();
        $contacts->save_contact_meta_data( 123 );
        assertEquals( [], $GLOBALS['mock_update_post_meta'] );
    }

    public function test_save_contact_meta_data_invalid_nonce() {
        $this->setUp();
        $_POST['monica_contact_details_nonce'] = 'invalid';
        $GLOBALS['mock_verify_nonce'] = false;

        $contacts = new Monica_Contacts();
        $contacts->save_contact_meta_data( 123 );
        assertEquals( [], $GLOBALS['mock_update_post_meta'] );
    }

    public function test_save_contact_meta_data_cannot_edit() {
        $this->setUp();
        $_POST['monica_contact_details_nonce'] = 'valid';
        $GLOBALS['mock_verify_nonce'] = true;
        $GLOBALS['mock_current_user_can'] = false;

        $contacts = new Monica_Contacts();
        $contacts->save_contact_meta_data( 123 );
        assertEquals( [], $GLOBALS['mock_update_post_meta'] );
    }

    public function test_save_contact_meta_data_doing_autosave() {
        $this->setUp();
        $_POST['monica_contact_details_nonce'] = 'valid';
        $GLOBALS['mock_verify_nonce'] = true;

        if ( ! defined( 'DOING_AUTOSAVE' ) ) {
            define( 'DOING_AUTOSAVE', true );
        }

        $contacts = new Monica_Contacts();
        $contacts->save_contact_meta_data( 123 );
        assertEquals( [], $GLOBALS['mock_update_post_meta'] );
    }
}
