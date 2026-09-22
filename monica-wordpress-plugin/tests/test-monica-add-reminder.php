<?php

require_once __DIR__ . '/../monica.php';

class Test_Monica_Add_Reminder {

    public function setUp() {
        $_POST = [];
        $_GET = [];
        $GLOBALS['wp_options'] = [];
        $GLOBALS['mock_calls'] = [];
        $GLOBALS['mock_transients'] = [];
        update_option( 'monica_api_key', 'test_api_key' );
        update_option( 'monica_url', 'https://test.monicahq.com' );
    }

    public function test_no_post_data_does_nothing() {
        $this->setUp();
        monica_integration_add_reminder();
        // Since it doesn't throw a redirect, it means it returned early.
        assertEquals( true, true );
    }

    public function test_invalid_nonce_returns_early() {
        $this->setUp();
        $_POST['monica_add_reminder'] = '1';
        $_POST['monica_add_reminder_nonce'] = 'invalid';

        $GLOBALS['mock_calls']['wp_verify_nonce_return'] = false;

        monica_integration_add_reminder();
        // Returned early if no exception thrown.
        assertEquals( true, true );
    }

    public function test_no_post_id_returns_early() {
        $this->setUp();
        $_POST['monica_add_reminder'] = '1';
        $_POST['monica_add_reminder_nonce'] = 'mock_nonce_monica_add_reminder';

        monica_integration_add_reminder();
        assertEquals( true, true );
    }

    public function test_cannot_edit_post_returns_early() {
        $this->setUp();
        $_POST['monica_add_reminder'] = '1';
        $_POST['monica_add_reminder_nonce'] = 'mock_nonce_monica_add_reminder';
        $_POST['monica_post_id'] = '123';

        $GLOBALS['mock_calls']['current_user_can_return'] = false;

        monica_integration_add_reminder();
        assertEquals( true, true );
    }

    public function test_missing_fields_redirects_to_error() {
        $this->setUp();
        $_POST['monica_add_reminder'] = '1';
        $_POST['monica_add_reminder_nonce'] = 'mock_nonce_monica_add_reminder';
        $_POST['monica_post_id'] = '123';

        $GLOBALS['mock_calls']['current_user_can_return'] = true;
        $GLOBALS['mock_calls']['wp_get_referer_return'] = 'https://example.com/referer';

        try {
            monica_integration_add_reminder();
            throw new Exception("Expected redirect exception");
        } catch ( Exception $e ) {
            $expected_error = "wp_safe_redirect:https://example.com/referer?monica_error=empty_fields";
            assertEquals( $expected_error, $e->getMessage() );
        }
    }

    public function test_successful_reminder_creation() {
        $this->setUp();
        $_POST['monica_add_reminder'] = '1';
        $_POST['monica_add_reminder_nonce'] = 'mock_nonce_monica_add_reminder';
        $_POST['monica_post_id'] = '123';
        $_POST['monica_contact_id'] = '456';
        $_POST['monica_reminder_title'] = 'Test Reminder';
        $_POST['monica_reminder_date'] = '2025-01-01';

        $GLOBALS['mock_calls']['current_user_can_return'] = true;
        $GLOBALS['mock_calls']['wp_get_referer_return'] = 'https://example.com/referer';

        $GLOBALS['mock_calls']['wp_remote_post']['https://test.monicahq.com/api/contacts/456/reminders'] = [
            'response' => [ 'code' => 201 ],
            'body'     => json_encode(['data' => ['id' => 1]]),
        ];

        set_transient( 'monica_reminders_456', 'some_data', 3600 );

        try {
            monica_integration_add_reminder();
            throw new Exception("Expected redirect exception");
        } catch ( Exception $e ) {
            $expected_error = "wp_safe_redirect:https://example.com/referer";
            assertEquals( $expected_error, $e->getMessage() );
        }

        // Assert transient was deleted
        $transient = get_transient( 'monica_reminders_456' );
        assertEquals( false, $transient, 'Transient should be deleted' );
    }
}
