<?php

require_once __DIR__ . '/../includes/class-monica-contacts.php';

class Test_Class_Monica_Contacts {

    public function setUp() {
        global $mock_actions, $mock_meta_boxes, $mock_post_meta, $mock_update_post_meta, $mock_calls, $wp_options, $mock_transients;
        $mock_actions = [];
        $mock_meta_boxes = [];
        $mock_post_meta = [];
        $mock_update_post_meta = [];
        $mock_calls = [];
        $mock_transients = [];
        $wp_options = [];
        $wp_options['monica_access_token'] = 'test_token';

        $_POST = [];
    }

    public function test_save_contact_meta_data_missing_nonce() {
        $this->setUp();
        global $mock_update_post_meta;

        $contacts = new Monica_Contacts();
        $contacts->save_contact_meta_data( 123 );

        assertEquals( 0, count( $mock_update_post_meta ), 'Should not update meta when nonce is missing in $_POST' );
    }

    public function test_save_contact_meta_data_invalid_nonce() {
        $this->setUp();
        global $mock_update_post_meta, $mock_calls;

        $_POST['monica_contact_details_nonce'] = 'invalid_nonce';
        $mock_calls['wp_verify_nonce'] = false;

        $contacts = new Monica_Contacts();
        $contacts->save_contact_meta_data( 123 );

        assertEquals( 0, count( $mock_update_post_meta ), 'Should not update meta when nonce verification fails' );
    }

    public function test_save_contact_meta_data_doing_autosave() {
        $this->setUp();

        // Because constants cannot be undefined in PHP, defining DOING_AUTOSAVE here
        // would permanently pollute the global state for subsequent tests in the runner.
        // Therefore, we execute this specific test in an isolated PHP process.

        $script = '<?php
require_once "' . __DIR__ . '/bootstrap.php";
require_once "' . __DIR__ . '/../includes/class-monica-contacts.php";

$GLOBALS["mock_update_post_meta"] = [];
$_POST["monica_contact_details_nonce"] = "valid_nonce";
$GLOBALS["mock_calls"]["wp_verify_nonce"] = true;

define("DOING_AUTOSAVE", true);

$contacts = new Monica_Contacts();
$contacts->save_contact_meta_data(123);

if (count($GLOBALS["mock_update_post_meta"]) !== 0) {
    exit(1);
}
exit(0);
';
        $tmp_file = tempnam(sys_get_temp_dir(), 'test_autosave');
        file_put_contents($tmp_file, $script);
        exec('php ' . escapeshellarg($tmp_file), $output, $return_var);
        unlink($tmp_file);

        assertEquals(0, $return_var, 'Should not update meta during autosave (isolated test)');
    }
}
