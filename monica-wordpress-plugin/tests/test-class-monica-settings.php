<?php

function test_register_settings() {
    require_once __DIR__ . '/../includes/class-monica-settings.php';

    global $mock_calls;

    $settings = new Monica_Settings();
    $settings->register_settings();

    assert_not_empty($mock_calls['register_setting'], 'register_setting should be called');
    assert_equals(2, count($mock_calls['register_setting']), 'Should register 2 settings');

    assert_equals('monica_integration', $mock_calls['register_setting'][0][0]);
    assert_equals('monica_client_id', $mock_calls['register_setting'][0][1]);

    assert_equals('monica_integration', $mock_calls['register_setting'][1][0]);
    assert_equals('monica_client_secret', $mock_calls['register_setting'][1][1]);
}
