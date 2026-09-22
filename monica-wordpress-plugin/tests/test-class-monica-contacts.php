<?php

require_once __DIR__ . '/../includes/class-monica-contacts.php';

class Test_Class_Monica_Contacts {

    public function setUp() {
        global $mock_post_meta, $mock_calls, $mock_current_user_can;
        $mock_post_meta = [];
        $mock_calls = [];
        $mock_current_user_can = true; // Default to true
        $_POST = [];
    }

    public function test_save_contact_meta_data_no_nonce() {
        $this->setUp();

        $contacts = new Monica_Contacts();
        $post_id = 123;

        // $_POST['monica_contact_details_nonce'] is not set

        $contacts->save_contact_meta_data( $post_id );

        // Assert meta was not updated
        global $mock_post_meta;
        assertEquals( false, isset( $mock_post_meta[ $post_id ] ) );
    }

    public function test_save_contact_meta_data_invalid_nonce() {
        $this->setUp();

        $contacts = new Monica_Contacts();
        $post_id = 123;

        $_POST['monica_contact_details_nonce'] = 'invalid_nonce';

        $contacts->save_contact_meta_data( $post_id );

        // Assert meta was not updated
        global $mock_post_meta;
        assertEquals( false, isset( $mock_post_meta[ $post_id ] ) );
    }

    public function test_save_contact_meta_data_no_capabilities() {
        $this->setUp();

        $contacts = new Monica_Contacts();
        $post_id = 123;

        $_POST['monica_contact_details_nonce'] = 'mock_nonce_monica_contact_details';

        global $mock_current_user_can;
        $mock_current_user_can = false;

        $contacts->save_contact_meta_data( $post_id );

        // Assert meta was not updated
        global $mock_post_meta;
        assertEquals( false, isset( $mock_post_meta[ $post_id ] ) );
    }

    public function test_save_contact_meta_data_autosave() {
        $this->setUp();

        // Test DOING_AUTOSAVE by running the test in a separate PHP process
        // to prevent constant bleeding into other tests.
        $bootstrap_path = realpath(__DIR__ . '/bootstrap.php');
        $class_path = realpath(__DIR__ . '/../includes/class-monica-contacts.php');

        $script = <<<PHP
<?php
require_once '{$bootstrap_path}';
require_once '{$class_path}';

define( 'DOING_AUTOSAVE', true );

\$contacts = new Monica_Contacts();
\$post_id = 123;
\$_POST['monica_contact_details_nonce'] = 'mock_nonce_monica_contact_details';
global \$mock_post_meta;
\$mock_post_meta = [];

\$contacts->save_contact_meta_data( \$post_id );
echo isset( \$mock_post_meta[ \$post_id ] ) ? 'FAIL' : 'PASS';
PHP;
        $tmp_file = tempnam(sys_get_temp_dir(), 'test_autosave_');
        file_put_contents($tmp_file, $script);

        $output = shell_exec('php ' . escapeshellarg($tmp_file));
        unlink($tmp_file);

        assertEquals('PASS', trim($output), 'Meta should not be updated during autosave');
    }
}
