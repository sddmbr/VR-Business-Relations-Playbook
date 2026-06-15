<?php

require_once __DIR__ . '/../includes/class-monica-relationships.php';

class Test_Class_Monica_Relationships {

    public function test_render_relationships_meta_box_no_contact_id() {
        reset_mock_calls();
        global $mock_calls;

        $mock_calls['get_post_meta_return'] = false; // No contact ID saved

        $relationships = new Monica_Relationships();

        $post = new stdClass();
        $post->ID = 123;

        ob_start();
        $relationships->render_relationships_meta_box( $post );
        $output = ob_get_clean();

        assert_true( strpos( $output, 'Save the contact to view relationships.' ) !== false, 'Should prompt to save contact if no contact ID is found.' );
    }

    public function test_render_relationships_meta_box_api_error() {
        reset_mock_calls();
        global $mock_calls;

        $mock_calls['get_post_meta_return'] = 456; // Has contact ID
        $mock_calls['Monica_API_get_return_map'] = [
            'contacts/456/relationships' => new WP_Error( 'api_error', 'Failed to connect to API' )
        ];
        $mock_calls['is_wp_error_return'] = true;

        $relationships = new Monica_Relationships();

        $post = new stdClass();
        $post->ID = 123;

        ob_start();
        $relationships->render_relationships_meta_box( $post );
        $output = ob_get_clean();

        assert_true( strpos( $output, 'Failed to connect to API' ) !== false, 'Should output error message if API returns an error.' );
    }

    public function test_render_relationships_meta_box_empty_relationships() {
        reset_mock_calls();
        global $mock_calls;

        $mock_calls['get_post_meta_return'] = 456;
        $mock_calls['Monica_API_get_return_map'] = [
            'contacts/456/relationships' => [ 'data' => [] ],
            'relationshiptypes' => [ 'data' => [] ]
        ];
        $mock_calls['is_wp_error_return'] = false;

        $relationships = new Monica_Relationships();

        $post = new stdClass();
        $post->ID = 123;

        ob_start();
        $relationships->render_relationships_meta_box( $post );
        $output = ob_get_clean();

        assert_true( strpos( $output, 'No relationships found.' ) !== false, 'Should indicate no relationships if data is empty.' );
        // Also check if add relationship form is rendered
        assert_true( strpos( $output, 'Add New Relationship' ) !== false, 'Should display form to add new relationship.' );
        assert_true( strpos( $output, 'monica_add_relationship_nonce' ) !== false, 'Should contain nonce field.' );
    }

    public function test_render_relationships_meta_box_with_relationships() {
        reset_mock_calls();
        global $mock_calls;

        $mock_calls['get_post_meta_return'] = 456;
        $mock_calls['Monica_API_get_return_map'] = [
            'contacts/456/relationships' => [
                'data' => [
                    [
                        'relationship_type' => [ 'name' => 'Friend' ],
                        'contact' => [ 'first_name' => 'John', 'last_name' => 'Doe' ]
                    ],
                    [
                        'relationship_type' => [ 'name' => 'Colleague' ],
                        'contact' => [ 'first_name' => 'Jane', 'last_name' => 'Smith' ]
                    ]
                ]
            ],
            'relationshiptypes' => [
                'data' => [
                    [ 'id' => 1, 'name' => 'Friend' ],
                    [ 'id' => 2, 'name' => 'Colleague' ]
                ]
            ]
        ];
        $mock_calls['is_wp_error_return'] = false;

        $contact_post = new stdClass();
        $contact_post->ID = 124;
        $contact_post->post_title = 'Bob Ross';

        $mock_calls['get_posts_return'] = [ $contact_post ];

        $relationships = new Monica_Relationships();

        $post = new stdClass();
        $post->ID = 123;

        ob_start();
        $relationships->render_relationships_meta_box( $post );
        $output = ob_get_clean();

        // Verify that list items are rendered correctly
        assert_true( strpos( $output, 'Friend: John Doe' ) !== false, 'Should output Friend relationship correctly.' );
        assert_true( strpos( $output, 'Colleague: Jane Smith' ) !== false, 'Should output Colleague relationship correctly.' );
        // Form should also be rendered
        assert_true( strpos( $output, 'Add New Relationship' ) !== false, 'Should display form to add new relationship.' );
        assert_true( strpos( $output, 'monica_add_relationship_nonce' ) !== false, 'Should contain nonce field.' );
        // Verify relationship types are in select
        assert_true( strpos( $output, '<option value="1">Friend</option>' ) !== false, 'Should render relationship type options.' );
        assert_true( strpos( $output, '<option value="2">Colleague</option>' ) !== false, 'Should render relationship type options.' );
        // Verify contacts are in select
        assert_true( strpos( $output, 'Bob Ross' ) !== false, 'Should render contact options.' );
    }
}
