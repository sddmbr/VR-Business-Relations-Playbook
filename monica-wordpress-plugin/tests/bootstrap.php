<?php

// WordPress mock functions and globals for testing

global $mock_calls;
$mock_calls = [
    'get_option' => [],
    'update_option' => [],
    'register_setting' => [],
];

function add_action($hook, $callback) {
    global $mock_calls;
    $mock_calls['add_action'][] = func_get_args();
}

function add_options_page($page_title, $menu_title, $capability, $menu_slug, $callback) {
    global $mock_calls;
    $mock_calls['add_options_page'][] = func_get_args();
}

function register_setting($option_group, $option_name, $args = []) {
    global $mock_calls;
    $mock_calls['register_setting'][] = func_get_args();
}

function get_option($option) {
    global $mock_calls;
    return $mock_calls['get_option'][$option] ?? false;
}

function update_option($option, $value) {
    global $mock_calls;
    $mock_calls['get_option'][$option] = $value;
}

function sanitize_text_field($str) {
    return strip_tags(trim($str));
}

function get_admin_page_title() {
    return 'Monica Integration';
}

function settings_fields($option_group) {
    echo "<!-- settings_fields: $option_group -->";
}

function do_settings_sections($page) {
    echo "<!-- do_settings_sections: $page -->";
}

function submit_button() {
    echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"  /></p>';
}

function admin_url($path) {
    return 'http://example.com/wp-admin/' . $path;
}

function _e($text, $domain) {
    echo $text;
}

function __($text, $domain) {
    return $text;
}

function esc_html($text) {
    return htmlspecialchars($text);
}

function esc_attr($text) {
    return htmlspecialchars($text);
}

function esc_url($url) {
    return $url;
}

// Simple Monica_API mock to avoid fatals when testing the render output
class Monica_API {
    public function get_authorization_url($redirect_uri) {
        return 'http://example.com/oauth/authorize?redirect_uri=' . urlencode($redirect_uri);
    }
}
