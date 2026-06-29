<?php

function test_render_notes_meta_box_api_error() {
    $notes = new Monica_Notes();
    global $mock_calls;

    $post = (object) ['ID' => 1];

    $mock_calls['get_post_meta_return'] = 12345;
    $mock_calls['is_wp_error_return'] = true;
    $mock_calls['Monica_API_get_return'] = new WP_Error('api_error', 'Mock API Error Message');

    ob_start();
    $notes->render_notes_meta_box($post);
    $output = ob_get_clean();

    assert_true(strpos($output, 'Mock API Error Message') !== false, 'Output should contain the API error message');
    assert_not_empty($mock_calls['Monica_API_get'], 'API get should be called');
    assert_equals('contacts/12345/notes', $mock_calls['Monica_API_get'][0][0]);
}
