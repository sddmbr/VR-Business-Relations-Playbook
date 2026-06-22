<?php

require_once __DIR__ . '/../includes/class-monica-relationships.php';

function test_add_relationships_meta_box() {
    global $mock_calls;
    $relationships = new Monica_Relationships();

    // reset mock calls to clear any add_action calls from constructor
    reset_mock_calls();

    $relationships->add_relationships_meta_box();

    assert_not_empty($mock_calls['add_meta_box'], 'add_meta_box should be called');
    assert_equals('monica_relationships', $mock_calls['add_meta_box'][0][0]);
    assert_equals('Relationships', $mock_calls['add_meta_box'][0][1]);
    assert_equals('monica_contact', $mock_calls['add_meta_box'][0][3]);
    assert_equals('side', $mock_calls['add_meta_box'][0][4]);
    assert_equals('default', $mock_calls['add_meta_box'][0][5]);
}

function test_render_relationships_meta_box_no_monica_id() {
    global $mock_calls;
    $relationships = new Monica_Relationships();

    // Simulate contact having no Monica ID
    $mock_calls['get_post_meta_return'] = false;

    $post = new stdClass();
    $post->ID = 1;

    ob_start();
    $relationships->render_relationships_meta_box($post);
    $output = ob_get_clean();

    assert_true(strpos($output, 'Save the contact to view relationships.') !== false, 'Should prompt to save contact');
}

function test_render_relationships_meta_box_api_error() {
    global $mock_calls;
    $relationships = new Monica_Relationships();

    // Simulate contact having a Monica ID
    $mock_calls['get_post_meta_return'] = 123;

    // Simulate API error
    $mock_calls['Monica_API_get_return_map'] = [
        "contacts/123/relationships" => new WP_Error()
    ];

    $post = new stdClass();
    $post->ID = 1;

    ob_start();
    $relationships->render_relationships_meta_box($post);
    $output = ob_get_clean();

    assert_true(strpos($output, 'Mock API Error') !== false, 'Should output API error message');
}

function test_render_relationships_meta_box_empty_relationships() {
    global $mock_calls;
    $relationships = new Monica_Relationships();

    $mock_calls['get_post_meta_return'] = 123;

    $mock_calls['Monica_API_get_return_map'] = [
        "contacts/123/relationships" => ['data' => []],
        "relationshiptypes" => ['data' => [
            ['id' => 1, 'name' => 'Friend'],
            ['id' => 2, 'name' => 'Colleague']
        ]]
    ];

    $post = new stdClass();
    $post->ID = 1;

    ob_start();
    $relationships->render_relationships_meta_box($post);
    $output = ob_get_clean();

    assert_true(strpos($output, 'No relationships found.') !== false, 'Should output no relationships message');
    assert_true(strpos($output, '<option value="1">Friend</option>') !== false, 'Should render relationship type options');
    assert_true(strpos($output, '<option value="2">Colleague</option>') !== false, 'Should render relationship type options');
    assert_true(strpos($output, 'value="12345"') !== false, 'Should render nonce field');
}

function test_render_relationships_meta_box_with_relationships() {
    global $mock_calls;
    $relationships = new Monica_Relationships();

    $mock_calls['get_post_meta_return'] = 123;

    $mock_calls['Monica_API_get_return_map'] = [
        "contacts/123/relationships" => ['data' => [
            [
                'relationship_type' => ['name' => 'Spouse'],
                'contact' => ['first_name' => 'Jane', 'last_name' => 'Doe']
            ]
        ]],
        "relationshiptypes" => ['data' => [
            ['id' => 1, 'name' => 'Friend']
        ]]
    ];

    $dummy_contact = new stdClass();
    $dummy_contact->ID = 2;
    $dummy_contact->post_title = 'John Smith';
    $mock_calls['get_posts_return'] = [$dummy_contact];

    $post = new stdClass();
    $post->ID = 1;

    ob_start();
    $relationships->render_relationships_meta_box($post);
    $output = ob_get_clean();

    assert_false(strpos($output, 'No relationships found.') !== false, 'Should not output no relationships message');
    assert_true(strpos($output, 'Spouse: Jane Doe') !== false, 'Should render relationship item');
    assert_true(strpos($output, '<option value="123">John Smith</option>') !== false, 'Should render related contact options');
}
