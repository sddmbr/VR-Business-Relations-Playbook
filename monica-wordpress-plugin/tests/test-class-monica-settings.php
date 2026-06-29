<?php

require_once __DIR__ . '/../includes/class-monica-settings.php';

class Test_monica_settings {

    public function setUp() {
        global $mock_calls;
        $mock_calls = [
            'get_option' => [],
            'update_option' => [],
            'register_setting' => [],
        ];
    }

    public function test_sanitize_client_secret_with_empty_value_preserves_existing() {
        $this->setUp();
        global $mock_calls;
        $mock_calls['get_option']['monica_client_secret'] = 'existing_secret_123';

        $settings = new Monica_Settings();
        $result = $settings->sanitize_client_secret('');

        if ($result !== 'existing_secret_123') {
            throw new Exception("Expected existing secret to be preserved, got: " . var_export($result, true));
        }
    }

    public function test_sanitize_client_secret_with_new_value_updates() {
        $this->setUp();

        $settings = new Monica_Settings();
        $result = $settings->sanitize_client_secret('new_secret_456');

        if ($result !== 'new_secret_456') {
            throw new Exception("Expected new secret to be used, got: " . var_export($result, true));
        }
    }

    public function test_render_settings_page_masks_existing_secret() {
        $this->setUp();
        global $mock_calls;
        $mock_calls['get_option']['monica_client_secret'] = 'existing_secret_123';

        $settings = new Monica_Settings();

        ob_start();
        $settings->render_settings_page();
        $output = ob_get_clean();

        if (strpos($output, 'existing_secret_123') !== false) {
            throw new Exception("Client secret was exposed in output!");
        }

        if (strpos($output, 'placeholder="****************"') === false) {
            throw new Exception("Expected placeholder was not found in output!");
        }

        if (strpos($output, 'value=""') === false) {
             throw new Exception("Expected value attribute to be empty!");
        }
    }

    public function test_render_settings_page_no_secret() {
        $this->setUp();
        global $mock_calls;
        $mock_calls['get_option']['monica_client_secret'] = '';

        $settings = new Monica_Settings();

        ob_start();
        $settings->render_settings_page();
        $output = ob_get_clean();

        if (strpos($output, 'placeholder="****************"') !== false) {
            throw new Exception("Placeholder should not be shown when secret is empty!");
        }

        if (strpos($output, 'placeholder=""') === false) {
            throw new Exception("Expected empty placeholder when no secret exists!");
        }
    }
}
