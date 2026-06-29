<?php

function test_save_contact_meta_data_schedules_event() {
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
    $mock_calls['wp_next_scheduled'] = false;

    $contacts->save_contact_meta_data(1);

    assert_not_empty($mock_calls['update_post_meta'], 'Meta should be updated');
    assert_equals(3, count($mock_calls['update_post_meta']), 'Should update 3 meta fields (first, last, email)');

    assert_not_empty($mock_calls['wp_schedule_single_event'], 'Event should be scheduled');
    assert_equals('monica_sync_contact', $mock_calls['wp_schedule_single_event'][0][1]);
    assert_equals([1], $mock_calls['wp_schedule_single_event'][0][2]);

    assert_empty($mock_calls['Monica_API_post'] ?? [], 'API post should NOT be called synchronously');
    assert_empty($mock_calls['Monica_API_put'] ?? [], 'API put should NOT be called synchronously');
}

function test_save_contact_meta_data_does_not_schedule_if_already_scheduled() {
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
    $mock_calls['wp_next_scheduled'] = true;

    $contacts->save_contact_meta_data(1);

    assert_empty($mock_calls['wp_schedule_single_event'] ?? [], 'Event should NOT be scheduled if already scheduled');
}

function test_sync_contact_with_api_create_new() {
    $contacts = new Monica_Contacts();
    global $mock_calls;

    $mock_calls['get_post_meta_return'] = [
        '_monica_first_name' => 'John',
        '_monica_last_name' => 'Doe',
        '_monica_email' => 'john@example.com',
        '_monica_contact_id' => false // No existing ID
    ];
    $mock_calls['Monica_API_post_return'] = ['data' => ['id' => 12345]];

    $contacts->sync_contact_with_api(1);

    assert_not_empty($mock_calls['update_post_meta'], 'Meta should be updated with new ID');
    assert_equals('_monica_contact_id', $mock_calls['update_post_meta'][0][1]);
    assert_equals(12345, $mock_calls['update_post_meta'][0][2]);

    assert_not_empty($mock_calls['Monica_API_post'], 'API post should be called');
    assert_empty($mock_calls['Monica_API_put'] ?? [], 'API put should not be called');
    assert_equals('contacts', $mock_calls['Monica_API_post'][0][0]);
}

function test_sync_contact_with_api_update_existing() {
    $contacts = new Monica_Contacts();
    global $mock_calls;

    $mock_calls['get_post_meta_return'] = [
        '_monica_first_name' => 'Jane',
        '_monica_last_name' => 'Smith',
        '_monica_email' => 'jane@example.com',
        '_monica_contact_id' => 67890 // Existing ID
    ];

    $contacts->sync_contact_with_api(1);

    assert_empty($mock_calls['update_post_meta'] ?? [], 'Meta ID should NOT be updated for existing contact');

    assert_empty($mock_calls['Monica_API_post'] ?? [], 'API post should not be called');
    assert_not_empty($mock_calls['Monica_API_put'], 'API put should be called');
    assert_equals('contacts/67890', $mock_calls['Monica_API_put'][0][0]);
}
