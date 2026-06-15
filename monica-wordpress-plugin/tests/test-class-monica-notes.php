<?php

function test_render_notes_meta_box_no_contact() {
    $notes = new Monica_Notes();
    global $mock_calls;

    $post = new stdClass();
    $post->ID = 1;

    $mock_calls['get_post_meta_return'] = false; // No monica contact ID

    ob_start();
    $notes->render_notes_meta_box($post);
    $output = ob_get_clean();

    assert_true(strpos($output, 'Save the contact to view notes.') !== false, 'Should output save contact message');
    assert_empty($mock_calls['Monica_API_get'], 'Should not call API if no contact ID');
}

function test_render_notes_meta_box_api_error() {
    $notes = new Monica_Notes();
    global $mock_calls;

    $post = new stdClass();
    $post->ID = 1;

    $mock_calls['get_post_meta_return'] = 123; // Valid monica contact ID
    $mock_calls['is_wp_error_return'] = true;

    // The API get method in bootstrap needs to return something that has get_error_message() when is_wp_error is true
    // In our mock, is_wp_error just returns true, but we need the actual return value to have get_error_message()
    $mock_calls['Monica_API_get_return'] = new WP_Error('Mock API Error Message');

    ob_start();
    $notes->render_notes_meta_box($post);
    $output = ob_get_clean();

    assert_true(strpos($output, 'Mock API Error Message') !== false, 'Should output API error message');
    assert_not_empty($mock_calls['Monica_API_get'], 'Should call API');
    assert_equals('contacts/123/notes', $mock_calls['Monica_API_get'][0][0], 'Should call correct API endpoint');
}

function test_render_notes_meta_box_empty() {
    $notes = new Monica_Notes();
    global $mock_calls;

    $post = new stdClass();
    $post->ID = 1;

    $mock_calls['get_post_meta_return'] = 123; // Valid monica contact ID
    $mock_calls['Monica_API_get_return'] = ['data' => []]; // Empty notes

    ob_start();
    $notes->render_notes_meta_box($post);
    $output = ob_get_clean();

    assert_true(strpos($output, 'No notes found.') !== false, 'Should output no notes message');
    assert_true(strpos($output, 'monica_note_body') !== false, 'Should render add note form');
}

function test_render_notes_meta_box_with_notes() {
    $notes = new Monica_Notes();
    global $mock_calls;

    $post = new stdClass();
    $post->ID = 1;

    $mock_calls['get_post_meta_return'] = 123; // Valid monica contact ID
    $mock_calls['Monica_API_get_return'] = [
        'data' => [
            ['body' => 'This is a test note.'],
            ['body' => "This is another test note.\nWith a line break."]
        ]
    ];

    ob_start();
    $notes->render_notes_meta_box($post);
    $output = ob_get_clean();

    assert_true(strpos($output, 'This is a test note.') !== false, 'Should output first note');
    assert_true(strpos($output, 'This is another test note.') !== false, 'Should output second note');
    assert_true(strpos($output, 'monica_note_body') !== false, 'Should render add note form');
}
