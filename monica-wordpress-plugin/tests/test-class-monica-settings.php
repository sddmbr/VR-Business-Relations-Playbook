<?php

function test_register_settings() {
    global $mock_calls;

    $settings = new Monica_Settings();
    $settings->register_settings();

    assert_equals(2, count($mock_calls['register_setting']), 'Two settings should be registered');

    assert_equals('monica_integration', $mock_calls['register_setting'][0][0]);
    assert_equals('monica_client_id', $mock_calls['register_setting'][0][1]);

    assert_equals('monica_integration', $mock_calls['register_setting'][1][0]);
    assert_equals('monica_client_secret', $mock_calls['register_setting'][1][1]);
}
