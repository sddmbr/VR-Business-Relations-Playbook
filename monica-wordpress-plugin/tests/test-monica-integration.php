<?php

require_once __DIR__ . '/../monica.php';

class Test_Monica_Integration {

    public function setUp() {
        global $mock_actions, $mock_meta_boxes, $mock_post_meta, $mock_calls, $wp_options, $mock_transients;
        $mock_actions = [];
        $mock_meta_boxes = [];
        $mock_post_meta = [];
        $mock_calls = [];
        $mock_transients = [];
        $wp_options = [];

        $_GET = [];
        $_POST = [];
        $_SERVER['REQUEST_URI'] = '/wp-admin/';
    }

    // monica_integration_init doesn't really have side effects that are easy to test here
    // as it just instantiates classes and that is already triggered on include.

    public function test_monica_integration_oauth_redirect_not_admin() {
        $this->setUp();
        global $mock_calls;

        $mock_calls['current_user_can'] = false;

        monica_integration_oauth_redirect();

        assertEquals( false, isset($mock_calls['wp_safe_redirect']), 'Should return early if user cannot manage_options' );
    }

    public function test_monica_integration_oauth_redirect_invalid_state() {
        $this->setUp();
        global $mock_calls;

        $mock_calls['current_user_can'] = true;
        $_GET['page'] = 'monica-integration';
        $_GET['code'] = 'testcode';
        $_GET['state'] = 'invalid_state';

        $exception_thrown = false;
        try {
            monica_integration_oauth_redirect();
        } catch ( WpRedirectException $e ) {
            $exception_thrown = true;
        }

        assertEquals( true, $exception_thrown, 'Should redirect with exception on invalid state' );
        assertEquals( true, strpos($mock_calls['wp_safe_redirect'], 'monica_error=invalid_state') !== false, 'Should redirect with invalid_state error' );
    }

    public function test_monica_integration_oauth_redirect_success() {
        $this->setUp();
        global $mock_calls, $wp_options;

        $mock_calls['current_user_can'] = true;
        $_GET['page'] = 'monica-integration';
        $_GET['code'] = 'testcode';
        $_GET['state'] = 'mock_nonce_monica_oauth_state';

        $mock_calls['wp_remote_post']['https://app.monicahq.com/oauth/token'] = [
            'body' => json_encode(['access_token' => 'new_mock_token'])
        ];

        $exception_thrown = false;
        try {
            monica_integration_oauth_redirect();
        } catch ( WpRedirectException $e ) {
            $exception_thrown = true;
        }

        assertEquals( true, $exception_thrown, 'Should redirect on success' );
        assertEquals( 'https://example.com/wp-admin/options-general.php?page=monica-integration', $mock_calls['wp_redirect'], 'Should redirect to settings page' );
        assertEquals( 'new_mock_token', $wp_options['monica_access_token'], 'Access token should be saved' );
    }

    public function test_monica_integration_admin_notices() {
        $this->setUp();

        $_GET['monica_error'] = 'invalid_state';

        ob_start();
        monica_integration_admin_notices();
        $output = ob_get_clean();

        $contains = strpos($output, 'OAuth authorization failed: Invalid state parameter. Possible CSRF attack.') !== false;
        assertEquals( true, $contains, 'Should output error notice' );
    }

    public function test_monica_integration_add_reminder_missing_nonce() {
        $this->setUp();
        global $mock_calls;

        $_POST['monica_add_reminder'] = 1;
        // No nonce
        monica_integration_add_reminder();
        assertEquals( false, isset($mock_calls['wp_safe_redirect']), 'Should return early without nonce' );
    }

    public function test_monica_integration_add_reminder_invalid_nonce() {
        $this->setUp();
        global $mock_calls;

        $_POST['monica_add_reminder'] = 1;
        $_POST['monica_add_reminder_nonce'] = 'invalid';

        monica_integration_add_reminder();
        assertEquals( false, isset($mock_calls['wp_safe_redirect']), 'Should return early with invalid nonce' );
    }

    public function test_monica_integration_add_reminder_empty_fields() {
        $this->setUp();
        global $mock_calls;

        $_POST['monica_add_reminder'] = 1;
        $_POST['monica_add_reminder_nonce'] = 'mock_nonce_monica_add_reminder';
        $_POST['monica_post_id'] = 123;

        $mock_calls['current_user_can'] = true; // Can edit post

        // Missing contact_id, title, date
        $exception_thrown = false;
        try {
            monica_integration_add_reminder();
        } catch ( WpRedirectException $e ) {
            $exception_thrown = true;
        }

        assertEquals( true, $exception_thrown, 'Should redirect on empty fields' );
        assertEquals( true, strpos($mock_calls['wp_safe_redirect'], 'monica_error=empty_fields') !== false, 'Should redirect with empty_fields error' );
    }

    public function test_monica_integration_add_reminder_success() {
        $this->setUp();
        global $mock_calls, $mock_transients;

        $_POST['monica_add_reminder'] = 1;
        $_POST['monica_add_reminder_nonce'] = 'mock_nonce_monica_add_reminder';
        $_POST['monica_post_id'] = 123;
        $_POST['monica_contact_id'] = 456;
        $_POST['monica_reminder_title'] = 'Test Reminder';
        $_POST['monica_reminder_date'] = '2023-12-01';

        $mock_calls['current_user_can'] = true;
        $mock_calls['wp_get_referer'] = 'https://example.com/wp-admin/post.php?post=123&action=edit';
        $mock_calls['wp_remote_post']['https://app.monicahq.com/api/contacts/456/reminders'] = [
            'body' => json_encode(['data' => []])
        ];

        $mock_transients["monica_reminders_456"] = "old_data";

        $exception_thrown = false;
        try {
            monica_integration_add_reminder();
        } catch ( WpRedirectException $e ) {
            $exception_thrown = true;
        }

        assertEquals( true, $exception_thrown, 'Should redirect on success' );
        assertEquals( 'https://example.com/wp-admin/post.php?post=123&action=edit', $mock_calls['wp_safe_redirect'], 'Should redirect back to referer' );
        assertEquals( false, isset($mock_transients["monica_reminders_456"]), 'Should delete transient cache' );
    }

    public function test_monica_integration_add_note_success() {
        $this->setUp();
        global $mock_calls, $mock_transients;

        $_POST['monica_add_note'] = 1;
        $_POST['monica_add_note_nonce'] = 'mock_nonce_monica_add_note';
        $_POST['monica_post_id'] = 123;
        $_POST['monica_contact_id'] = 456;
        $_POST['monica_note_body'] = 'Test Note Body';

        $mock_calls['current_user_can'] = true;
        $mock_calls['wp_get_referer'] = 'https://example.com/wp-admin/post.php?post=123&action=edit';
        $mock_calls['wp_remote_post']['https://app.monicahq.com/api/contacts/456/notes'] = [
            'body' => json_encode(['data' => []])
        ];

        $exception_thrown = false;
        try {
            monica_integration_add_note();
        } catch ( WpRedirectException $e ) {
            $exception_thrown = true;
        }

        assertEquals( true, $exception_thrown, 'Should redirect on success' );
        assertEquals( 'https://example.com/wp-admin/post.php?post=123&action=edit', $mock_calls['wp_safe_redirect'], 'Should redirect back to referer' );
    }

    public function test_monica_integration_add_relationship_success() {
        $this->setUp();
        global $mock_calls;

        $_POST['monica_add_relationship'] = 1;
        $_POST['monica_add_relationship_nonce'] = 'mock_nonce_monica_add_relationship';
        $_POST['monica_post_id'] = 123;
        $_POST['monica_contact_id'] = 456;
        $_POST['monica_related_contact_id'] = 789;
        $_POST['monica_relationship_type_id'] = 10;

        $mock_calls['current_user_can'] = true;
        $mock_calls['wp_get_referer'] = 'https://example.com/wp-admin/post.php?post=123&action=edit';
        $mock_calls['wp_remote_post']['https://app.monicahq.com/api/relationships'] = [
            'body' => json_encode(['data' => []])
        ];

        $exception_thrown = false;
        try {
            monica_integration_add_relationship();
        } catch ( WpRedirectException $e ) {
            $exception_thrown = true;
        }

        assertEquals( true, $exception_thrown, 'Should redirect on success' );
        assertEquals( 'https://example.com/wp-admin/post.php?post=123&action=edit', $mock_calls['wp_safe_redirect'], 'Should redirect back to referer' );
    }

    public function test_monica_integration_admin_notices_empty_fields() {
        $this->setUp();

        $_GET['monica_error'] = 'empty_fields';

        ob_start();
        monica_integration_admin_notices_empty_fields();
        $output = ob_get_clean();

        $contains = strpos($output, 'Please fill in all required fields.') !== false;
        assertEquals( true, $contains, 'Should output empty fields error notice' );
    }
}
