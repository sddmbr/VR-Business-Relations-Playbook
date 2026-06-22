<?php

function test_render_settings_page() {
    global $mock_calls;
    $mock_calls['get_option']['monica_client_id'] = 'test_client_id_123';
    $mock_calls['get_option']['monica_client_secret'] = 'test_client_secret_456';

    $settings = new Monica_Settings();

    ob_start();
    $settings->render_settings_page();
    $output = ob_get_clean();

    // Assert page structure and headers
    assert_contains('<div class="wrap">', $output, 'Missing wrap div');
    assert_contains('<h1>Monica Integration Settings</h1>', $output, 'Missing or incorrect page title');
    assert_contains('<form action="options.php" method="post">', $output, 'Missing form tag');

    // Assert settings fields output from mocked WP functions
    assert_contains("<input type='hidden' name='settings_fields' value='monica_integration' />", $output, 'Missing settings_fields');
    assert_contains('<!-- settings sections for monica_integration -->', $output, 'Missing do_settings_sections');

    // Assert client ID and secret fields with populated options
    assert_contains('<th scope="row">Client ID</th>', $output, 'Missing Client ID label');
    assert_contains('<input type="text" name="monica_client_id" value="test_client_id_123" />', $output, 'Missing or incorrect Client ID input');

    assert_contains('<th scope="row">Client Secret</th>', $output, 'Missing Client Secret label');
    assert_contains('<input type="text" name="monica_client_secret" value="test_client_secret_456" />', $output, 'Missing or incorrect Client Secret input');

    // Assert submit button output
    assert_contains("<button type='submit'>Save Changes</button>", $output, 'Missing submit button');

    // Assert authorization link
    $expected_url = 'https://app.monicahq.com/oauth/authorize?redirect_uri=http%3A%2F%2Fexample.com%2Fwp-admin%2Foptions-general.php%3Fpage%3Dmonica-integration';
    assert_contains('href="' . $expected_url . '"', $output, 'Missing or incorrect authorize URL');
    assert_contains('Authorize with Monica', $output, 'Missing authorize button text');
    assert_contains('class="button button-primary"', $output, 'Missing authorize button classes');
}
