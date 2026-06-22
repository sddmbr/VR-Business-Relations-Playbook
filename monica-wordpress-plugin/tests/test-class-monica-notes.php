<?php

require_once __DIR__ . '/../includes/class-monica-notes.php';

function test_render_notes_no_monica_id() {
    global $mock_calls;
    $notes = new Monica_Notes();

    // Simulate no Monica ID
    $mock_calls['get_post_meta_return'] = false;

    $post = new stdClass();
    $post->ID = 1;

    ob_start();
    $notes->render_notes_meta_box($post);
    $output = ob_get_clean();

    assert_true(strpos($output, 'Save the contact to view notes.') !== false, 'Should show save contact message');
    assert_empty($mock_calls['Monica_API_get'], 'Should not call API');
}

function test_render_notes_api_error() {
    global $mock_calls;
    $notes = new Monica_Notes();

    // Simulate Monica ID present
    $mock_calls['get_post_meta_return'] = 123;

    // Simulate API Error
    $mock_calls['is_wp_error_return'] = true;
    $mock_calls['Monica_API_get_return_map'] = [
        'contacts/123/notes' => new WP_Error('api_error', 'Failed to fetch notes')
    ];

    $post = new stdClass();
    $post->ID = 1;

    ob_start();
    $notes->render_notes_meta_box($post);
    $output = ob_get_clean();

    assert_true(strpos($output, 'Failed to fetch notes') !== false, 'Should show API error message');
    assert_not_empty($mock_calls['Monica_API_get'], 'Should call API');
}

function test_render_notes_empty() {
    global $mock_calls;
    $notes = new Monica_Notes();

    // Simulate Monica ID present
    $mock_calls['get_post_meta_return'] = 123;

    // Simulate empty notes
    $mock_calls['is_wp_error_return'] = false;
    $mock_calls['Monica_API_get_return_map'] = [
        'contacts/123/notes' => ['data' => []]
    ];

    $post = new stdClass();
    $post->ID = 1;

    ob_start();
    $notes->render_notes_meta_box($post);
    $output = ob_get_clean();

    assert_true(strpos($output, 'No notes found.') !== false, 'Should show no notes message');
    assert_true(strpos($output, 'Add New Note') !== false, 'Should show add note form');
    assert_true(strpos($output, "name='monica_add_note_nonce'") !== false, 'Should output nonce field');
}

function test_render_notes_with_data() {
    global $mock_calls;
    $notes = new Monica_Notes();

    // Simulate Monica ID present
    $mock_calls['get_post_meta_return'] = 123;

    // Simulate notes data
    $mock_calls['is_wp_error_return'] = false;
    $mock_calls['Monica_API_get_return_map'] = [
        'contacts/123/notes' => [
            'data' => [
                ['body' => 'This is test note 1'],
                ['body' => 'This is test note 2']
            ]
        ]
    ];

    $post = new stdClass();
    $post->ID = 1;

    ob_start();
    $notes->render_notes_meta_box($post);
    $output = ob_get_clean();

    assert_true(strpos($output, 'This is test note 1') !== false, 'Should render note 1');
    assert_true(strpos($output, 'This is test note 2') !== false, 'Should render note 2');
    assert_true(strpos($output, '<ul>') !== false, 'Should render list start tag');
    assert_true(strpos($output, '<li>') !== false, 'Should render list item tags');
    assert_true(strpos($output, 'Add New Note') !== false, 'Should show add note form');
}
