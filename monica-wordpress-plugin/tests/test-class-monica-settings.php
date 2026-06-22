<?php

class Test_Class_Monica_Settings {

    public function test_render_settings_page_outputs_html() {
        $settings = new Monica_Settings();

        update_option( 'monica_client_id', 'test_client_id_123' );
        update_option( 'monica_client_secret', 'test_secret_456' );

        ob_start();
        $settings->render_settings_page();
        $output = ob_get_clean();

        if ( strpos( $output, '<div class="wrap">' ) === false ) {
            throw new Exception( "Output should contain wrap div." );
        }

        if ( strpos( $output, '<h1>Mock Admin Page Title</h1>' ) === false ) {
            throw new Exception( "Output should contain the admin page title." );
        }

        if ( strpos( $output, '<form action="options.php" method="post">' ) === false ) {
            throw new Exception( "Output should contain the form tag." );
        }

        if ( strpos( $output, 'name="monica_client_id"' ) === false ) {
            throw new Exception( "Output should contain client ID input field." );
        }
        if ( strpos( $output, 'value="test_client_id_123"' ) === false ) {
            throw new Exception( "Output should contain client ID value." );
        }

        if ( strpos( $output, 'name="monica_client_secret"' ) === false ) {
            throw new Exception( "Output should contain client secret input field." );
        }
        if ( strpos( $output, 'value="test_secret_456"' ) === false ) {
            throw new Exception( "Output should contain client secret value." );
        }

        if ( strpos( $output, 'Authorize with Monica' ) === false ) {
            throw new Exception( "Output should contain authorize button." );
        }

        $expected_redirect_uri = admin_url( 'options-general.php?page=monica-integration' );
        $encoded_uri = urlencode( $expected_redirect_uri );
        if ( strpos( $output, 'client_id=test_client_id_123' ) === false ) {
            throw new Exception( "Output should contain authorization URL with client ID." );
        }
        if ( strpos( $output, $encoded_uri ) === false ) {
            throw new Exception( "Output should contain authorization URL with encoded redirect URI." );
        }
    }

    public function test_render_settings_page_escapes_attributes() {
        $settings = new Monica_Settings();

        // Use values that need escaping
        update_option( 'monica_client_id', 'id_with_quotes"<script>alert(1)</script>' );
        update_option( 'monica_client_secret', 'secret_with_quotes"<script>alert(1)</script>' );

        ob_start();
        $settings->render_settings_page();
        $output = ob_get_clean();

        // Check if quotes and brackets are escaped
        if ( strpos( $output, 'value="id_with_quotes&quot;&lt;script&gt;alert(1)&lt;/script&gt;"' ) === false ) {
            throw new Exception( "Output should properly escape the client ID attribute." );
        }

        if ( strpos( $output, 'value="secret_with_quotes&quot;&lt;script&gt;alert(1)&lt;/script&gt;"' ) === false ) {
            throw new Exception( "Output should properly escape the client secret attribute." );
        }
    }
}
