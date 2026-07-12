<?php
/** Minimal WordPress stubs for architecture and Workspace-slice tests. */

$GLOBALS['fbsa_test_options'] = array();
$GLOBALS['fbsa_test_user_options'] = array();
$GLOBALS['fbsa_test_actions'] = array();
$GLOBALS['fbsa_test_filters'] = array();
$GLOBALS['fbsa_test_activation_hooks'] = array();
$GLOBALS['fbsa_test_is_admin'] = true;
$GLOBALS['fbsa_test_capabilities'] = array('manage_options' => true);
$GLOBALS['fbsa_test_current_blog'] = 1;
$GLOBALS['fbsa_test_current_user'] = 1;
$GLOBALS['fbsa_test_deleted_metadata'] = array();
$GLOBALS['fbsa_test_rest_routes'] = array();
$GLOBALS['fbsa_test_enqueued_styles'] = array();
$GLOBALS['fbsa_test_enqueued_scripts'] = array();
$GLOBALS['fbsa_test_localized_scripts'] = array();
$GLOBALS['fbsa_test_valid_nonces'] = array('wp_rest' => 'valid-wp-rest-nonce');

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/wordpress/');
}

class FBSA_Test_WPDB {
    public function get_blog_prefix($blog_id = null) {
        $blog_id = $blog_id === null ? $GLOBALS['fbsa_test_current_blog'] : (int) $blog_id;
        return $blog_id === 1 ? 'wp_' : 'wp_' . $blog_id . '_';
    }
}
$GLOBALS['wpdb'] = new FBSA_Test_WPDB();

function plugin_dir_path($file) { return rtrim(dirname($file), '/\\') . DIRECTORY_SEPARATOR; }
function plugin_basename($file) { return basename(dirname($file)) . '/' . basename($file); }
function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
    $GLOBALS['fbsa_test_actions'][] = compact('hook', 'callback', 'priority', 'accepted_args');
    return true;
}
function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
    $GLOBALS['fbsa_test_filters'][] = compact('hook', 'callback', 'priority', 'accepted_args');
    return true;
}
function do_action($hook, ...$args) {
    $callbacks = array_filter($GLOBALS['fbsa_test_actions'], function ($item) use ($hook) { return $item['hook'] === $hook; });
    usort($callbacks, function ($a, $b) { return $a['priority'] <=> $b['priority']; });
    foreach ($callbacks as $item) {
        call_user_func_array($item['callback'], array_slice($args, 0, (int) $item['accepted_args']));
    }
}
function apply_filters($hook, $value, ...$args) {
    $callbacks = array_filter($GLOBALS['fbsa_test_filters'], function ($item) use ($hook) { return $item['hook'] === $hook; });
    usort($callbacks, function ($a, $b) { return $a['priority'] <=> $b['priority']; });
    foreach ($callbacks as $item) {
        $call_args = array_merge(array($value), $args);
        $value = call_user_func_array($item['callback'], array_slice($call_args, 0, (int) $item['accepted_args']));
    }
    return $value;
}
function register_activation_hook($file, $callback) {
    $GLOBALS['fbsa_test_activation_hooks'][] = array('file' => $file, 'callback' => $callback);
}
function get_option($key, $default = false) {
    return array_key_exists($key, $GLOBALS['fbsa_test_options']) ? $GLOBALS['fbsa_test_options'][$key] : $default;
}
function update_option($key, $value, $autoload = null) {
    $changed = !array_key_exists($key, $GLOBALS['fbsa_test_options']) || $GLOBALS['fbsa_test_options'][$key] !== $value;
    $GLOBALS['fbsa_test_options'][$key] = $value;
    return $changed;
}
function add_option($key, $value = '', $deprecated = '', $autoload = 'yes') {
    if (array_key_exists($key, $GLOBALS['fbsa_test_options'])) {
        return false;
    }
    $GLOBALS['fbsa_test_options'][$key] = $value;
    return true;
}
function delete_option($key) {
    if (!array_key_exists($key, $GLOBALS['fbsa_test_options'])) {
        return false;
    }
    unset($GLOBALS['fbsa_test_options'][$key]);
    return true;
}
function get_user_option($key, $user_id = 0) {
    $user_id = $user_id ? (int) $user_id : (int) $GLOBALS['fbsa_test_current_user'];
    return isset($GLOBALS['fbsa_test_user_options'][$user_id]) && array_key_exists($key, $GLOBALS['fbsa_test_user_options'][$user_id])
        ? $GLOBALS['fbsa_test_user_options'][$user_id][$key]
        : false;
}
function update_user_option($user_id, $key, $value, $global = false) {
    $user_id = (int) $user_id;
    if (!isset($GLOBALS['fbsa_test_user_options'][$user_id])) {
        $GLOBALS['fbsa_test_user_options'][$user_id] = array();
    }
    $GLOBALS['fbsa_test_user_options'][$user_id][$key] = $value;
    return true;
}
function delete_user_option($user_id, $key, $global = false) {
    $user_id = (int) $user_id;
    if (!isset($GLOBALS['fbsa_test_user_options'][$user_id]) || !array_key_exists($key, $GLOBALS['fbsa_test_user_options'][$user_id])) {
        return false;
    }
    unset($GLOBALS['fbsa_test_user_options'][$user_id][$key]);
    return true;
}
function delete_metadata($meta_type, $object_id, $meta_key, $meta_value = '', $delete_all = false) {
    $GLOBALS['fbsa_test_deleted_metadata'][] = compact('meta_type', 'object_id', 'meta_key', 'meta_value', 'delete_all');
    if ($meta_type === 'user' && $delete_all) {
        foreach ($GLOBALS['fbsa_test_user_options'] as $user_id => $values) {
            unset($GLOBALS['fbsa_test_user_options'][$user_id][$meta_key]);
        }
    }
    return true;
}
function is_admin() { return (bool) $GLOBALS['fbsa_test_is_admin']; }
function current_user_can($capability) { return !empty($GLOBALS['fbsa_test_capabilities'][$capability]); }
function user_can($user_id, $capability) { return current_user_can($capability); }
function get_current_user_id() { return (int) $GLOBALS['fbsa_test_current_user']; }
function wp_generate_uuid4() { return '12345678-1234-4234-8234-123456789abc'; }
function current_time($type, $gmt = false) { return '2026-07-12 12:00:00'; }
function is_multisite() { return false; }
function get_sites($args = array()) { return array(1); }
function switch_to_blog($site_id) { $GLOBALS['fbsa_test_current_blog'] = (int) $site_id; }
function restore_current_blog() { $GLOBALS['fbsa_test_current_blog'] = 1; }
function get_current_blog_id() { return (int) $GLOBALS['fbsa_test_current_blog']; }
function load_plugin_textdomain() { return true; }
function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
function sanitize_key($value) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $value)); }
function wp_unslash($value) { return $value; }
function __($text, $domain = 'default') { return $text; }


class WP_Error {
    private $code;
    private $message;
    private $data;
    public function __construct($code = '', $message = '', $data = null) {
        $this->code = (string) $code;
        $this->message = (string) $message;
        $this->data = $data;
    }
    public function get_error_code() { return $this->code; }
    public function get_error_message() { return $this->message; }
    public function get_error_data() { return $this->data; }
}

class FBSA_Test_REST_Request {
    private $headers;
    private $params;
    private $json;
    public function __construct(array $headers = array(), array $params = array(), array $json = array()) {
        $this->headers = $headers;
        $this->params = $params;
        $this->json = $json;
    }
    public function get_header($name) {
        foreach ($this->headers as $key => $value) {
            if (strtolower($key) === strtolower($name)) {
                return $value;
            }
        }
        return '';
    }
    public function get_param($name) { return array_key_exists($name, $this->params) ? $this->params[$name] : null; }
    public function get_json_params() { return $this->json; }
}

function register_rest_route($namespace, $route, $args = array(), $override = false) {
    $GLOBALS['fbsa_test_rest_routes'][$namespace . $route] = $args;
    return true;
}
function rest_ensure_response($data) { return $data; }
function wp_create_nonce($action = -1) {
    return isset($GLOBALS['fbsa_test_valid_nonces'][$action]) ? $GLOBALS['fbsa_test_valid_nonces'][$action] : 'nonce-' . $action;
}
function wp_verify_nonce($nonce, $action = -1) {
    return isset($GLOBALS['fbsa_test_valid_nonces'][$action]) && hash_equals($GLOBALS['fbsa_test_valid_nonces'][$action], (string) $nonce) ? 1 : false;
}
function plugin_dir_url($file) { return 'https://example.test/wp-content/plugins/' . basename(dirname($file)) . '/'; }
function admin_url($path = '') { return 'https://example.test/wp-admin/' . ltrim((string) $path, '/'); }
function site_url($path = '') { return 'https://example.test/' . ltrim((string) $path, '/'); }
function rest_url($path = '') { return 'https://example.test/wp-json/' . ltrim((string) $path, '/'); }
function esc_url_raw($value) { return (string) $value; }
function esc_url($value) { return (string) $value; }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_html__($text, $domain = 'default') { return esc_html(__($text, $domain)); }
function esc_attr__($text, $domain = 'default') { return esc_attr(__($text, $domain)); }
function checked($checked, $current = true, $echo = true) {
    $result = ((string) $checked === (string) $current) ? ' checked="checked"' : '';
    if ($echo) { echo $result; }
    return $result;
}
function disabled($disabled, $current = true, $echo = true) {
    $result = ((string) $disabled === (string) $current) ? ' disabled="disabled"' : '';
    if ($echo) { echo $result; }
    return $result;
}
function wp_enqueue_style($handle, $src = '', $deps = array(), $ver = false, $media = 'all') {
    $GLOBALS['fbsa_test_enqueued_styles'][$handle] = compact('src', 'deps', 'ver', 'media');
    return true;
}
function wp_enqueue_script($handle, $src = '', $deps = array(), $ver = false, $in_footer = false) {
    $GLOBALS['fbsa_test_enqueued_scripts'][$handle] = compact('src', 'deps', 'ver', 'in_footer');
    return true;
}
function wp_localize_script($handle, $object_name, $l10n) {
    $GLOBALS['fbsa_test_localized_scripts'][$handle] = compact('object_name', 'l10n');
    return true;
}
function wp_set_script_translations($handle, $domain, $path = '') { return true; }
function wp_enqueue_media() { return true; }
function determine_locale() { return 'en_US'; }
function is_user_logged_in() { return get_current_user_id() > 0; }
function absint($value) { return abs((int) $value); }
