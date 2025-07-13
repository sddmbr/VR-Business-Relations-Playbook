<?php

class Monica_Settings {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
    }

    public function add_settings_page() {
        add_options_page(
            __( 'Monica Integration', 'monica-integration' ),
            __( 'Monica Integration', 'monica-integration' ),
            'manage_options',
            'monica-integration',
            [ $this, 'render_settings_page' ]
        );
    }

    public function register_settings() {
        register_setting( 'monica_integration', 'monica_client_id' );
        register_setting( 'monica_integration', 'monica_client_secret' );
    }

    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields( 'monica_integration' );
                do_settings_sections( 'monica_integration' );
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Client ID', 'monica-integration' ); ?></th>
                        <td><input type="text" name="monica_client_id" value="<?php echo esc_attr( get_option( 'monica_client_id' ) ); ?>" /></td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><?php _e( 'Client Secret', 'monica-integration' ); ?></th>
                        <td><input type="text" name="monica_client_secret" value="<?php echo esc_attr( get_option( 'monica_client_secret' ) ); ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
            <?php
            $api = new Monica_API();
            $redirect_uri = admin_url( 'options-general.php?page=monica-integration' );
            ?>
            <a href="<?php echo esc_url( $api->get_authorization_url( $redirect_uri ) ); ?>" class="button button-primary"><?php _e( 'Authorize with Monica', 'monica-integration' ); ?></a>
        </div>
        <?php
    }
}
