<?php

function test_save_contact_meta_data_missing_nonce() {
    $contacts = new Monica_Contacts();
    global $mock_calls;

    $_POST = [];
    $contacts->save_contact_meta_data(1);

    assert_empty($mock_calls['update_post_meta'], 'Meta should not be updated');
    assert_empty($mock_calls['Monica_API_post'], 'API post should not be called');
    assert_empty($mock_calls['Monica_API_put'], 'API put should not be called');
}

function test_save_contact_meta_data_invalid_nonce() {
    $contacts = new Monica_Contacts();
    global $mock_calls;

    $_POST = ['monica_contact_details_nonce' => 'invalid'];
    $mock_calls['wp_verify_nonce'] = false;

    $contacts->save_contact_meta_data(1);

    assert_empty($mock_calls['update_post_meta'], 'Meta should not be updated');
    assert_empty($mock_calls['Monica_API_post'], 'API post should not be called');
    assert_empty($mock_calls['Monica_API_put'], 'API put should not be called');
}

function test_save_contact_meta_data_autosave() {
    $contacts = new Monica_Contacts();
    global $mock_calls;

    $_POST = ['monica_contact_details_nonce' => 'valid'];
    $mock_calls['wp_verify_nonce'] = true;

    $contacts->save_contact_meta_data(1);

    assert_empty($mock_calls['update_post_meta'], 'Meta should not be updated');
    assert_empty($mock_calls['Monica_API_post'], 'API post should not be called');
    assert_empty($mock_calls['Monica_API_put'], 'API put should not be called');
}

function test_save_contact_meta_data_no_capability() {
    $contacts = new Monica_Contacts();
    global $mock_calls;

    $_POST = ['monica_contact_details_nonce' => 'valid'];
    $mock_calls['wp_verify_nonce'] = true;
    $mock_calls['current_user_can'] = false;

    $contacts->save_contact_meta_data(1);

    assert_empty($mock_calls['update_post_meta'], 'Meta should not be updated');
    assert_empty($mock_calls['Monica_API_post'], 'API post should not be called');
    assert_empty($mock_calls['Monica_API_put'], 'API put should not be called');
}

function test_save_contact_meta_data_create_new() {
    $contacts = new Monica_Contacts();
    global $mock_calls;

    $_POST = [
        'monica_contact_details_nonce' => 'valid',
        'monica_first_name' => 'John',
        'monica_last_name' => 'Doe',
        'monica_email' => 'john@example.com'
    ];
    $mock_calls['wp_verify_nonce'] = true;
    $mock_calls['current_user_can'] = true;
    $mock_calls['get_post_meta_return'] = false; // No existing Monica ID
    $mock_calls['Monica_API_post_return'] = ['data' => ['id' => 12345]];

    $contacts->save_contact_meta_data(1);

    assert_not_empty($mock_calls['update_post_meta'], 'Meta should be updated');
    assert_equals(4, count($mock_calls['update_post_meta']), 'Should update 4 meta fields (first, last, email, id)');
    assert_equals('_monica_first_name', $mock_calls['update_post_meta'][0][1]);
    assert_equals('John', $mock_calls['update_post_meta'][0][2]);
    assert_equals('_monica_contact_id', $mock_calls['update_post_meta'][3][1]);
    assert_equals(12345, $mock_calls['update_post_meta'][3][2]);

    assert_not_empty($mock_calls['Monica_API_post'], 'API post should be called');
    assert_empty($mock_calls['Monica_API_put'], 'API put should not be called');
    assert_equals('contacts', $mock_calls['Monica_API_post'][0][0]);
}

function test_save_contact_meta_data_update_existing() {
    $contacts = new Monica_Contacts();
    global $mock_calls;

    $_POST = [
        'monica_contact_details_nonce' => 'valid',
        'monica_first_name' => 'Jane',
        'monica_last_name' => 'Smith',
        'monica_email' => 'jane@example.com'
    ];
    $mock_calls['wp_verify_nonce'] = true;
    $mock_calls['current_user_can'] = true;
    $mock_calls['get_post_meta_return'] = 67890; // Existing Monica ID

    $contacts->save_contact_meta_data(1);

    assert_not_empty($mock_calls['update_post_meta'], 'Meta should be updated');
    assert_equals(3, count($mock_calls['update_post_meta']), 'Should update 3 meta fields (first, last, email)');
    assert_equals('_monica_first_name', $mock_calls['update_post_meta'][0][1]);
    assert_equals('Jane', $mock_calls['update_post_meta'][0][2]);

    assert_empty($mock_calls['Monica_API_post'], 'API post should not be called');
    assert_not_empty($mock_calls['Monica_API_put'], 'API put should be called');
    assert_equals('contacts/67890', $mock_calls['Monica_API_put'][0][0]);
}

function test_render_contact_details_meta_box_empty() {
    $contacts = new Monica_Contacts();
    global $mock_calls;

    $post = new stdClass();
    $post->ID = 1;

    ob_start();
    $contacts->render_contact_details_meta_box($post);
    $html = ob_get_clean();

    assert_true(strpos($html, 'name="monica_contact_details_nonce"') !== false, 'Should contain nonce field');
    assert_true(strpos($html, 'name="monica_first_name" value=""') !== false, 'First name should be empty');
    assert_true(strpos($html, 'name="monica_last_name" value=""') !== false, 'Last name should be empty');
    assert_true(strpos($html, 'name="monica_email" value=""') !== false, 'Email should be empty');
}

function test_render_contact_details_meta_box_with_data() {
    $contacts = new Monica_Contacts();
    global $mock_calls;

    $mock_calls['get_post_meta_returns'] = [
        '_monica_first_name' => 'Alice',
        '_monica_last_name' => 'Wonderland',
        '_monica_email' => 'alice@example.com'
    ];

    $post = new stdClass();
    $post->ID = 1;

    ob_start();
    $contacts->render_contact_details_meta_box($post);
    $html = ob_get_clean();

    assert_true(strpos($html, 'name="monica_contact_details_nonce"') !== false, 'Should contain nonce field');
    assert_true(strpos($html, 'name="monica_first_name" value="Alice"') !== false, 'First name should be populated');
    assert_true(strpos($html, 'name="monica_last_name" value="Wonderland"') !== false, 'Last name should be populated');
    assert_true(strpos($html, 'name="monica_email" value="alice@example.com"') !== false, 'Email should be populated');
}

function test_save_contact_meta_data_create_new_api_error() {
    $contacts = new Monica_Contacts();
    global $mock_calls;

    $_POST = [
        'monica_contact_details_nonce' => 'valid',
        'monica_first_name' => 'John',
        'monica_last_name' => 'Doe',
        'monica_email' => 'john@example.com'
    ];
    $mock_calls['wp_verify_nonce'] = true;
    $mock_calls['current_user_can'] = true;
    $mock_calls['get_post_meta_return'] = false; // No existing Monica ID

    // Simulate API error
    $mock_calls['is_wp_error_return'] = true;

    $contacts->save_contact_meta_data(1);

    assert_not_empty($mock_calls['update_post_meta'], 'Meta should be updated');
    assert_equals(3, count($mock_calls['update_post_meta']), 'Should update only 3 meta fields (first, last, email), ID update should be skipped');
    assert_equals('_monica_first_name', $mock_calls['update_post_meta'][0][1]);
    assert_equals('John', $mock_calls['update_post_meta'][0][2]);

    assert_not_empty($mock_calls['Monica_API_post'], 'API post should be called');
    assert_empty($mock_calls['Monica_API_put'], 'API put should not be called');
}
