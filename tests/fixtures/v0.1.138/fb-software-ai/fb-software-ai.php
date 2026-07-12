<?php
/**
 * Plugin Name: FB Software AI
 * Plugin URI: https://fbsoftwaresolutions.com
 * Description: Floating WordPress setup guide for FB Software AI. Admin-only widget with categorized editable guide video links, a customizable dashboard welcome panel, a draggable guide video player, and an attached premium backend shortcut rail.
 * Version: 0.1.138
 * Author: FB Software Solutions
 * Author URI: https://fbsoftwaresolutions.com
 * Text Domain: fb-software-ai
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('FBSA_PLUGIN_FILE')) {
    define('FBSA_PLUGIN_FILE', __FILE__);
}
if (!defined('FBSA_PLUGIN_DIR')) {
    define('FBSA_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

require_once FBSA_PLUGIN_DIR . 'src/Core/Autoloader.php';
\FBSoftwareAI\Core\Autoloader::register(FBSA_PLUGIN_DIR . 'src');

require_once FBSA_PLUGIN_DIR . 'includes/i18n-catalog.php';

final class FBSA_Demo_Plugin {
    const VERSION = '0.1.138';
    const VIDEO_LINKS_LOCKED = false;
    const KADENCE_THEME_SLUG = 'kadence';
    const KADENCE_THEME_OFFICIAL_URL = 'https://wordpress.org/themes/kadence/';
    const HELLO_ELEMENTOR_THEME_SLUG = 'hello-elementor';
    const HELLO_ELEMENTOR_THEME_OFFICIAL_URL = 'https://wordpress.org/themes/hello-elementor/';
    const ASTRA_THEME_SLUG = 'astra';
    const ASTRA_THEME_OFFICIAL_URL = 'https://wordpress.org/themes/astra/';
    const OCEANWP_THEME_SLUG = 'oceanwp';
    const OCEANWP_THEME_OFFICIAL_URL = 'https://wordpress.org/themes/oceanwp/';
    const BLOCKSY_THEME_SLUG = 'blocksy';
    const BLOCKSY_THEME_OFFICIAL_URL = 'https://wordpress.org/themes/blocksy/';
    const ZAKRA_THEME_SLUG = 'zakra';
    const ZAKRA_THEME_OFFICIAL_URL = 'https://wordpress.org/themes/zakra/';
    const ELEMENTOR_PLUGIN_SLUG = 'elementor';
    const ELEMENTOR_PLUGIN_FILE = 'elementor/elementor.php';
    const ELEMENTOR_PLUGIN_OFFICIAL_URL = 'https://wordpress.org/plugins/elementor/';
    const SITE_KIT_PLUGIN_SLUG = 'google-site-kit';
    const SITE_KIT_PLUGIN_FILE = 'google-site-kit/google-site-kit.php';
    const SITE_KIT_PLUGIN_OFFICIAL_URL = 'https://wordpress.org/plugins/google-site-kit/';
    const FLUENTSMTP_PLUGIN_SLUG = 'fluent-smtp';
    const FLUENTSMTP_PLUGIN_FILE = 'fluent-smtp/fluent-smtp.php';
    const FLUENTSMTP_PLUGIN_OFFICIAL_URL = 'https://wordpress.org/plugins/fluent-smtp/';
    const YOAST_DUPLICATE_POST_PLUGIN_SLUG = 'duplicate-post';
    const YOAST_DUPLICATE_POST_PLUGIN_FILE = 'duplicate-post/duplicate-post.php';
    const YOAST_DUPLICATE_POST_PLUGIN_OFFICIAL_URL = 'https://wordpress.org/plugins/duplicate-post/';
    const CONTACT_FORM_7_PLUGIN_SLUG = 'contact-form-7';
    const CONTACT_FORM_7_PLUGIN_FILE = 'contact-form-7/wp-contact-form-7.php';
    const CONTACT_FORM_7_PLUGIN_OFFICIAL_URL = 'https://wordpress.org/plugins/contact-form-7/';
    const LOCO_TRANSLATE_PLUGIN_SLUG = 'loco-translate';
    const LOCO_TRANSLATE_PLUGIN_FILE = 'loco-translate/loco.php';
    const LOCO_TRANSLATE_PLUGIN_OFFICIAL_URL = 'https://wordpress.org/plugins/loco-translate/';
    const LOCOAI_PLUGIN_SLUG = 'automatic-translator-addon-for-loco-translate';
    const LOCOAI_PLUGIN_FILE = 'automatic-translator-addon-for-loco-translate/automatic-translator-addon-for-loco-translate.php';
    const LOCOAI_PLUGIN_OFFICIAL_URL = 'https://wordpress.org/plugins/automatic-translator-addon-for-loco-translate/';
    const WOOCOMMERCE_PLUGIN_SLUG = 'woocommerce';
    const WOOCOMMERCE_PLUGIN_FILE = 'woocommerce/woocommerce.php';
    const WOOCOMMERCE_PLUGIN_OFFICIAL_URL = 'https://wordpress.org/plugins/woocommerce/';
    const WPVIVID_PLUGIN_SLUG = 'wpvivid-backuprestore';
    const WPVIVID_PLUGIN_FILE = 'wpvivid-backuprestore/wpvivid-backuprestore.php';
    const WPVIVID_PLUGIN_OFFICIAL_URL = 'https://wordpress.org/plugins/wpvivid-backuprestore/';
    const NONCE_ACTION = 'fbsa_widget_nonce_action';
    const OPTION_KEY = 'fbsa_demo_settings';
    const DUPLICATE_CLEANUP_VERSION_OPTION = 'fbsa_duplicate_cleanup_version';
    const DUPLICATE_CLEANUP_NOTICE_OPTION = 'fbsa_duplicate_cleanup_notice';
    const DASHBOARD_WIDGET_WEBSITE_STEPS_ID = 'fbsa_website_steps_widget';
    const DASHBOARD_WIDGET_PLUGIN_SETUP_ID = 'fbsa_plugin_setup_widget';
    const DASHBOARD_WIDGET_WEBSITE_SETTINGS_ID = 'fbsa_website_settings_widget';
    const DASHBOARD_WIDGET_HELP_TUTORIALS_ID = 'fbsa_help_tutorials_widget';
    const DASHBOARD_WIDGET_LAYOUT_VERSION = '0.1.135';

    private static $instance = null;
    private $workflow_cache = null;
    private $workflow_source_cache = null;
    private $workflow_error = '';
    private $workspace_widget_registry = null;

    public static function instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load bundled and Loco Translate language files for the plugin.
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'fb-software-ai',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages'
        );
    }

    /**
     * Return the generated English-to-current-locale catalogue.
     *
     * The source strings remain English. Loco Translate supplies translated
     * values through the normal WordPress gettext system.
     */
    private function get_translation_catalog() {
        static $catalog = null;

        if ($catalog === null) {
            $catalog = function_exists('fbsa_i18n_catalog') ? fbsa_i18n_catalog() : array();
        }

        return is_array($catalog) ? $catalog : array();
    }

    /**
     * Translate a known plugin string while preserving surrounding whitespace.
     * Placeholder catalogue entries such as "%s is installed." are supported
     * so existing dynamic status messages can be localized without changing
     * their internal command data.
     */
    private function translate_known_string($value) {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        $leading = '';
        $trailing = '';
        if (preg_match('/^\s+/u', $value, $match)) {
            $leading = $match[0];
        }
        if (preg_match('/\s+$/u', $value, $match)) {
            $trailing = $match[0];
        }

        $trimmed = trim(html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if ($trimmed === '') {
            return $value;
        }

        $catalog = $this->get_translation_catalog();
        if (array_key_exists($trimmed, $catalog)) {
            return $leading . $catalog[$trimmed] . $trailing;
        }

        static $placeholder_patterns = null;
        if ($placeholder_patterns === null) {
            $placeholder_patterns = array();
            foreach ($catalog as $source => $translated) {
                if (strpos($source, '%') === false) {
                    continue;
                }

                $parts = preg_split('/(%(?:\d+\$)?[sdf])/', $source, -1, PREG_SPLIT_DELIM_CAPTURE);
                $regex = '';
                foreach ($parts as $part) {
                    if (preg_match('/^%(?:\d+\$)?[sdf]$/', $part)) {
                        $regex .= '(.+?)';
                    } else {
                        $regex .= preg_quote($part, '/');
                    }
                }
                $placeholder_patterns[] = array(
                    'regex' => '/^' . $regex . '$/u',
                    'translation' => $translated,
                    'source_length' => strlen($source),
                );
            }

            usort($placeholder_patterns, function ($left, $right) {
                return $right['source_length'] <=> $left['source_length'];
            });
        }

        foreach ($placeholder_patterns as $pattern) {
            if (!preg_match($pattern['regex'], $trimmed, $matches)) {
                continue;
            }

            array_shift($matches);
            $translated = @vsprintf($pattern['translation'], $matches);
            if (is_string($translated)) {
                return $leading . $translated . $trailing;
            }
        }

        return $value;
    }

    /**
     * Recursively translate known user-facing strings in workflow and AJAX data.
     */
    private function translate_known_data($value) {
        if (is_string($value)) {
            return $this->translate_known_string($value);
        }

        if (!is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            $value[$key] = $this->translate_known_data($item);
        }

        return $value;
    }

    /**
     * Translate text nodes and common accessibility attributes in plugin HTML.
     * Only exact strings in the generated catalogue are changed, so URLs,
     * slugs, saved values, and technical identifiers stay untouched.
     */
    private function translate_plugin_html($html) {
        if (!is_string($html) || $html === '') {
            return $html;
        }

        $protected_blocks = array();
        $html = preg_replace_callback(
            '/<(script|style|code|pre|textarea)\b[^>]*>.*?<\/\1>/is',
            function ($matches) use (&$protected_blocks) {
                $token = '___FBSA_I18N_PROTECTED_' . count($protected_blocks) . '___';
                $protected_blocks[$token] = $matches[0];
                return $token;
            },
            $html
        );

        $html = preg_replace_callback('/>([^<>]+)</u', function ($matches) {
            $translated = $this->translate_known_string($matches[1]);
            if ($translated === $matches[1]) {
                return $matches[0];
            }
            return '>' . esc_html($translated) . '<';
        }, $html);

        $html = preg_replace_callback(
            '/\b(aria-label|title|placeholder|data-confirm-message|data-empty-message)=("|\')(.*?)\2/u',
            function ($matches) {
                $decoded = html_entity_decode($matches[3], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $translated = $this->translate_known_string($decoded);
                if ($translated === $decoded) {
                    return $matches[0];
                }
                return $matches[1] . '=' . $matches[2] . esc_attr($translated) . $matches[2];
            },
            $html
        );

        if (!empty($protected_blocks)) {
            $html = strtr($html, $protected_blocks);
        }

        return $html;
    }

    private function send_translated_json_success($data = null, $status_code = null) {
        wp_send_json_success($this->translate_known_data($data), $status_code);
    }

    private function send_translated_json_error($data = null, $status_code = null) {
        wp_send_json_error($this->translate_known_data($data), $status_code);
    }

    private function __construct() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('admin_init', array($this, 'cleanup_legacy_duplicate_plugins'), 1);
        add_action('admin_notices', array($this, 'render_duplicate_cleanup_notice'));
        add_action('admin_menu', array($this, 'register_settings_page'));
        add_action('load-index.php', array($this, 'setup_dashboard_welcome_panel'));
        add_action('load-index.php', array($this, 'repair_dashboard_widget_preferences'), 20);
        add_action('wp_dashboard_setup', array($this, 'register_dashboard_widgets'), 9999);
        add_action('admin_head-index.php', array($this, 'ensure_dashboard_widgets_registered'), 1);
        add_action('in_admin_header', array($this, 'ensure_dashboard_widgets_registered'), 9999);
        add_filter('admin_body_class', array($this, 'auto_collapse_admin_menu_body_class'));
        add_action('admin_footer', array($this, 'auto_collapse_admin_menu_script'), 1);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('customize_controls_enqueue_scripts', array($this, 'enqueue_assets'));

        add_action('admin_footer', array($this, 'render_widget'));
        add_action('wp_footer', array($this, 'render_widget'));
        add_action('customize_controls_print_footer_scripts', array($this, 'render_widget'));

        add_action('wp_ajax_fbsa_create_content', array($this, 'ajax_create_content'));
        add_action('wp_ajax_fbsa_run_command', array($this, 'ajax_run_command'));
        add_action('wp_ajax_fbsa_get_status', array($this, 'ajax_get_status'));
    }

    /**
     * Consolidate inactive legacy copies into the current stable plugin folder.
     *
     * Early v0.1.125 packages used Windows backslashes in ZIP entry names. On
     * Linux hosting those entries can be extracted as literal filenames rather
     * than folders, so WordPress lists them as extra plugins but its normal
     * plugin deletion API cannot always locate them. This cleanup supports both
     * ordinary duplicate folders and those malformed legacy archive entries.
     */
    public function cleanup_legacy_duplicate_plugins() {
        if (!is_admin() || !current_user_can('delete_plugins')) {
            return;
        }

        $completed_version = (string) get_option(self::DUPLICATE_CLEANUP_VERSION_OPTION, '');
        if ($completed_version !== '' && version_compare($completed_version, self::VERSION, '>=')) {
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';

        // Initialise the WordPress filesystem when direct access is available.
        // The safe PHP fallback below also handles literal backslashes in names.
        if (function_exists('WP_Filesystem')) {
            WP_Filesystem();
        }

        $current_plugin = plugin_basename(__FILE__);
        $removed = array();
        $errors = array();

        foreach ($this->find_inactive_fbsa_duplicates($current_plugin) as $plugin_file) {
            if ($this->remove_legacy_plugin_copy($plugin_file, $errors)) {
                $removed[] = $plugin_file;
            }
        }

        foreach ($this->remove_malformed_legacy_archives($errors) as $legacy_group) {
            $removed[] = $legacy_group;
        }

        wp_clean_plugins_cache(true);

        $remaining = $this->find_inactive_fbsa_duplicates($current_plugin);
        $malformed_remaining = $this->find_malformed_legacy_archives();

        if (empty($remaining) && empty($malformed_remaining)) {
            update_option(self::DUPLICATE_CLEANUP_VERSION_OPTION, self::VERSION, false);

            if (!empty($removed)) {
                update_option(
                    self::DUPLICATE_CLEANUP_NOTICE_OPTION,
                    array(
                        'type' => 'success',
                        'message' => sprintf(
                            /* translators: %d: number of old plugin copies consolidated. */
                            _n(
                                'FB Software AI merged and removed %d inactive legacy copy. Your current plugin and settings were kept.',
                                'FB Software AI merged and removed %d inactive legacy copies. Your current plugin and settings were kept.',
                                count($removed),
                                'fb-software-ai'
                            ),
                            count($removed)
                        ),
                    ),
                    false
                );
            } else {
                delete_option(self::DUPLICATE_CLEANUP_NOTICE_OPTION);
            }
            return;
        }

        $remaining_count = count($remaining) + count($malformed_remaining);
        $message = sprintf(
            /* translators: %d: number of plugin copies still present. */
            _n(
                'FB Software AI found %d old copy but the hosting filesystem did not allow it to be removed automatically.',
                'FB Software AI found %d old copies but the hosting filesystem did not allow them to be removed automatically.',
                $remaining_count,
                'fb-software-ai'
            ),
            $remaining_count
        );

        if (!empty($errors)) {
            $message .= ' ' . implode(' ', array_unique(array_map('sanitize_text_field', $errors)));
        }

        update_option(
            self::DUPLICATE_CLEANUP_NOTICE_OPTION,
            array(
                'type' => 'error',
                'message' => $message,
            ),
            false
        );
    }

    /**
     * Return inactive FB Software AI plugin entries other than this copy.
     */
    private function find_inactive_fbsa_duplicates($current_plugin) {
        $duplicates = array();

        foreach (get_plugins() as $plugin_file => $plugin_data) {
            if ($plugin_file === $current_plugin) {
                continue;
            }

            $plugin_name = isset($plugin_data['Name']) ? trim(wp_strip_all_tags($plugin_data['Name'])) : '';
            if ($plugin_name !== 'FB Software AI') {
                continue;
            }

            $text_domain = isset($plugin_data['TextDomain']) ? sanitize_key($plugin_data['TextDomain']) : '';
            $plugin_uri = isset($plugin_data['PluginURI']) ? untrailingslashit(esc_url_raw($plugin_data['PluginURI'])) : '';
            $looks_like_fbsa = $text_domain === 'fb-software-ai'
                || $plugin_uri === 'https://fbsoftwaresolutions.com'
                || basename(wp_normalize_path($plugin_file)) === 'fb-software-ai.php';

            if (!$looks_like_fbsa || is_plugin_active($plugin_file)) {
                continue;
            }

            if (is_multisite() && function_exists('is_plugin_active_for_network') && is_plugin_active_for_network($plugin_file)) {
                continue;
            }

            $legacy_version = isset($plugin_data['Version']) ? trim((string) $plugin_data['Version']) : '';
            if ($legacy_version !== '' && version_compare($legacy_version, self::VERSION, '>')) {
                continue;
            }

            $duplicates[] = $plugin_file;
        }

        return array_values(array_unique($duplicates));
    }

    /**
     * Remove a normal duplicate directory or a malformed backslash entry group.
     */
    private function remove_legacy_plugin_copy($plugin_file, &$errors) {
        $plugin_file = ltrim(wp_normalize_path((string) $plugin_file), '/');
        $parts = explode('/', $plugin_file);
        $root_name = isset($parts[0]) ? $parts[0] : '';

        if ($root_name !== '') {
            $standard_path = trailingslashit(WP_PLUGIN_DIR) . $root_name;
            if (file_exists($standard_path) || is_link($standard_path)) {
                if ($this->remove_path_safely($standard_path)) {
                    return true;
                }
                $errors[] = sprintf(__('Could not remove legacy path: %s.', 'fb-software-ai'), $root_name);
            }
        }

        // Linux can preserve the ZIP's Windows separators as literal filename
        // characters. Convert the normalized plugin path back to that raw form.
        if (DIRECTORY_SEPARATOR === '/') {
            $raw_name = str_replace('/', '\\', $plugin_file);
            $separator_position = strpos($raw_name, '\\');
            if ($separator_position !== false) {
                $legacy_prefix = substr($raw_name, 0, $separator_position);
                if ($legacy_prefix !== '' && $this->remove_malformed_legacy_group($legacy_prefix, $errors)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Find malformed legacy package groups represented by literal backslashes.
     */
    private function find_malformed_legacy_archives() {
        if (DIRECTORY_SEPARATOR !== '/' || !is_dir(WP_PLUGIN_DIR)) {
            return array();
        }

        $groups = array();
        $entries = @scandir(WP_PLUGIN_DIR);
        if (!is_array($entries)) {
            return array();
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..' || strpos($entry, '\\') === false) {
                continue;
            }

            list($prefix) = explode('\\', $entry, 2);
            if ($prefix === '' || stripos($prefix, 'fb-software-ai') !== 0) {
                continue;
            }

            $groups[$prefix][] = $entry;
        }

        $valid_groups = array();
        foreach ($groups as $prefix => $files) {
            $main_entry = $prefix . '\\fb-software-ai.php';
            $main_path = trailingslashit(WP_PLUGIN_DIR) . $main_entry;
            if (!is_file($main_path)) {
                continue;
            }

            $plugin_data = get_plugin_data($main_path, false, false);
            $plugin_name = isset($plugin_data['Name']) ? trim(wp_strip_all_tags($plugin_data['Name'])) : '';
            if ($plugin_name !== 'FB Software AI') {
                continue;
            }

            $normalized_plugin_file = wp_normalize_path($main_entry);
            if (is_plugin_active($normalized_plugin_file)) {
                continue;
            }

            if (is_multisite() && function_exists('is_plugin_active_for_network') && is_plugin_active_for_network($normalized_plugin_file)) {
                continue;
            }

            $legacy_version = isset($plugin_data['Version']) ? trim((string) $plugin_data['Version']) : '';
            if ($legacy_version !== '' && version_compare($legacy_version, self::VERSION, '>')) {
                continue;
            }

            $valid_groups[$prefix] = $files;
        }

        return $valid_groups;
    }

    /**
     * Delete all malformed files belonging to eligible legacy package groups.
     */
    private function remove_malformed_legacy_archives(&$errors) {
        $removed = array();
        foreach ($this->find_malformed_legacy_archives() as $prefix => $files) {
            if ($this->remove_malformed_legacy_group($prefix, $errors, $files)) {
                $removed[] = $prefix;
            }
        }
        return $removed;
    }

    private function remove_malformed_legacy_group($prefix, &$errors, $known_files = null) {
        if (DIRECTORY_SEPARATOR !== '/' || !is_dir(WP_PLUGIN_DIR)) {
            return false;
        }

        $files = is_array($known_files) ? $known_files : array();
        if (empty($files)) {
            $entries = @scandir(WP_PLUGIN_DIR);
            if (!is_array($entries)) {
                return false;
            }
            foreach ($entries as $entry) {
                if (strpos($entry, $prefix . '\\') === 0) {
                    $files[] = $entry;
                }
            }
        }

        if (empty($files)) {
            return false;
        }

        $all_removed = true;
        foreach ($files as $entry) {
            $path = trailingslashit(WP_PLUGIN_DIR) . $entry;
            if (!$this->remove_path_safely($path)) {
                $all_removed = false;
                $errors[] = sprintf(__('Could not remove malformed legacy file group: %s.', 'fb-software-ai'), $prefix);
                break;
            }
        }

        return $all_removed;
    }

    /**
     * Delete only paths inside the plugin directory and never the current copy.
     */
    private function remove_path_safely($path) {
        $plugin_root = wp_normalize_path(untrailingslashit(WP_PLUGIN_DIR));
        $normalized_path = wp_normalize_path($path);
        $current_dir = wp_normalize_path(dirname(__FILE__));

        if (strpos($normalized_path, $plugin_root . '/') !== 0) {
            return false;
        }

        if ($normalized_path === $current_dir || strpos($current_dir, trailingslashit($normalized_path)) === 0) {
            return false;
        }

        if (!file_exists($path) && !is_link($path)) {
            return true;
        }

        if (is_link($path) || is_file($path)) {
            return @unlink($path) || (!file_exists($path) && !is_link($path));
        }

        if (!is_dir($path)) {
            return false;
        }

        $entries = @scandir($path);
        if (!is_array($entries)) {
            return false;
        }

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            if (!$this->remove_path_safely($path . DIRECTORY_SEPARATOR . $entry)) {
                return false;
            }
        }

        return @rmdir($path) || !is_dir($path);
    }

    public function render_duplicate_cleanup_notice() {
        if (!current_user_can('delete_plugins')) {
            return;
        }

        $notice = get_option(self::DUPLICATE_CLEANUP_NOTICE_OPTION, array());
        if (!is_array($notice) || empty($notice['message'])) {
            return;
        }

        $type = isset($notice['type']) && $notice['type'] === 'error' ? 'error' : 'success';
        printf(
            '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
            esc_attr($type),
            esc_html($this->translate_known_string($notice['message']))
        );

        delete_option(self::DUPLICATE_CLEANUP_NOTICE_OPTION);
    }

    public function register_settings_page() {
        add_management_page(
            __('FB Software AI', 'fb-software-ai'),
            __('FB Software AI', 'fb-software-ai'),
            'manage_options',
            'fb-software-ai',
            array($this, 'render_settings_page')
        );
    }


    private function is_settings_page() {
        return is_admin()
            && isset($_GET['page'])
            && sanitize_key(wp_unslash($_GET['page'])) === 'fb-software-ai';
    }

    private function is_dashboard_screen() {
        if (!is_admin() || !function_exists('get_current_screen')) {
            return false;
        }

        $screen = get_current_screen();
        return $screen && $screen->id === 'dashboard';
    }

    /**
     * Replace the standard WordPress welcome content with the FB Software AI
     * welcome experience. WordPress keeps control of the outer panel, Dismiss
     * action, and the Dashboard Screen Options checkbox.
     */
    public function setup_dashboard_welcome_panel() {
        if (!current_user_can('manage_options')) {
            return;
        }

        remove_action('welcome_panel', 'wp_welcome_panel');
        add_action('welcome_panel', array($this, 'render_dashboard_welcome_panel'));
    }

    /**
     * Attach the modular Widget Registry while keeping this facade as the
     * renderer authority during the staged Workspace extraction.
     *
     * @param object $registry Widget registry service.
     * @return void
     */
    public function set_workspace_widget_registry($registry) {
        if (is_object($registry) && method_exists($registry, 'available_for_current_user')) {
            $this->workspace_widget_registry = $registry;
        }
    }

    /**
     * Return the FB Software AI Dashboard widgets and their default columns.
     * Each widget has its own ID so WordPress can independently drag, collapse,
     * hide, and remember it through Screen Options.
     */
    private function get_dashboard_widgets() {
        if (is_object($this->workspace_widget_registry)) {
            $registered_widgets = $this->workspace_widget_registry->available_for_current_user('dashboard');
            $widgets = array();
            foreach ($registered_widgets as $definition) {
                if (!is_object($definition)
                    || !method_exists($definition, 'id')
                    || !method_exists($definition, 'title')
                    || !method_exists($definition, 'renderer')
                    || !method_exists($definition, 'default_placement')) {
                    continue;
                }

                $placement = $definition->default_placement();
                $widgets[] = array(
                    'id' => $definition->id(),
                    'title' => __($definition->title(), 'fb-software-ai'),
                    'callback' => $definition->renderer(),
                    'context' => isset($placement['context']) ? (string) $placement['context'] : 'normal',
                    'priority' => isset($placement['priority']) ? (string) $placement['priority'] : 'default',
                );
            }

            if ($widgets !== array()) {
                return $widgets;
            }
        }

        return array(
            array(
                'id' => self::DASHBOARD_WIDGET_WEBSITE_STEPS_ID,
                'title' => __('FB Software AI', 'fb-software-ai'),
                'callback' => 'render_dashboard_website_steps_widget',
                'context' => 'column3',
                'priority' => 'high',
            ),
            array(
                'id' => self::DASHBOARD_WIDGET_PLUGIN_SETUP_ID,
                'title' => __('FB Software AI — Plugin Setup', 'fb-software-ai'),
                'callback' => 'render_dashboard_plugin_setup_widget',
                'context' => 'column4',
                'priority' => 'high',
            ),
            array(
                'id' => self::DASHBOARD_WIDGET_WEBSITE_SETTINGS_ID,
                'title' => __('FB Software AI — Website Settings', 'fb-software-ai'),
                'callback' => 'render_dashboard_website_settings_widget',
                'context' => 'column3',
                'priority' => 'default',
            ),
            array(
                'id' => self::DASHBOARD_WIDGET_HELP_TUTORIALS_ID,
                'title' => __('FB Software AI — Help and Tutorials', 'fb-software-ai'),
                'callback' => 'render_dashboard_help_tutorials_widget',
                'context' => 'column4',
                'priority' => 'default',
            ),
        );
    }

    /**
     * Determine whether a specific FB Software AI Dashboard widget is already
     * present in any of WordPress's four Dashboard meta-box columns.
     */
    private function dashboard_widget_is_registered($widget_id) {
        global $wp_meta_boxes;

        if (empty($wp_meta_boxes['dashboard']) || !is_array($wp_meta_boxes['dashboard'])) {
            return false;
        }

        foreach (array('normal', 'side', 'column3', 'column4') as $context) {
            if (empty($wp_meta_boxes['dashboard'][$context]) || !is_array($wp_meta_boxes['dashboard'][$context])) {
                continue;
            }

            foreach ($wp_meta_boxes['dashboard'][$context] as $priority_boxes) {
                if (is_array($priority_boxes) && isset($priority_boxes[$widget_id])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Resolve a registry renderer to a callable and expose render lifecycle
     * hooks without changing the preserved legacy render methods.
     *
     * @param mixed  $renderer Renderer method name or callable.
     * @param string $widget_id Stable widget ID.
     * @return callable|null
     */
    private function resolve_dashboard_widget_callback($renderer, $widget_id) {
        if (is_string($renderer) && method_exists($this, $renderer)) {
            $renderer = array($this, $renderer);
        }
        if (!is_callable($renderer)) {
            return null;
        }

        return function () use ($renderer, $widget_id) {
            $arguments = func_get_args();
            do_action('fbsa_widget_render_before', $widget_id, $arguments);
            call_user_func_array($renderer, $arguments);
            do_action('fbsa_widget_render_after', $widget_id, $arguments);
        };
    }

    /**
     * Register all FB Software AI Dashboard widgets late so plugins that
     * rebuild Dashboard boxes at the default priority do not remove them.
     */
    public function register_dashboard_widgets() {
        if (!current_user_can('manage_options')) {
            return;
        }

        foreach ($this->get_dashboard_widgets() as $widget) {
            if ($this->dashboard_widget_is_registered($widget['id'])) {
                continue;
            }

            $callback = $this->resolve_dashboard_widget_callback($widget['callback'], $widget['id']);
            if (!$callback) {
                continue;
            }

            wp_add_dashboard_widget(
                $widget['id'],
                $widget['title'],
                $callback,
                null,
                null,
                $widget['context'],
                $widget['priority']
            );
        }
    }

    /**
     * Final safety registration after wp_dashboard_setup has completely run.
     * This protects every FB Software AI box when another plugin clears or
     * replaces the global Dashboard meta-box registry.
     */
    public function ensure_dashboard_widgets_registered() {
        if (!current_user_can('manage_options') || !$this->is_dashboard_screen()) {
            return;
        }

        foreach ($this->get_dashboard_widgets() as $widget) {
            if ($this->dashboard_widget_is_registered($widget['id'])) {
                continue;
            }

            $callback = $this->resolve_dashboard_widget_callback($widget['callback'], $widget['id']);
            if (!$callback) {
                continue;
            }

            add_meta_box(
                $widget['id'],
                $widget['title'],
                $callback,
                'dashboard',
                $widget['context'],
                $widget['priority']
            );
        }
    }

    /**
     * Repair saved Dashboard preferences for existing administrators once per
     * release. All FB Software AI widgets are made visible and assigned to the
     * third and fourth columns while unrelated widget positions are preserved.
     */
    public function repair_dashboard_widget_preferences() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $user_id = get_current_user_id();
        if (!$user_id) {
            return;
        }

        $completed_version = (string) get_user_option('fbsa_dashboard_widget_layout_version', $user_id);
        if ($completed_version === self::DASHBOARD_WIDGET_LAYOUT_VERSION) {
            return;
        }

        $widgets = $this->get_dashboard_widgets();
        $widget_ids = array_map(function ($widget) {
            return $widget['id'];
        }, $widgets);

        $hidden = get_user_option('metaboxhidden_dashboard', $user_id);
        if (is_array($hidden)) {
            $repaired_hidden = array_values(array_filter($hidden, function ($widget_id) use ($widget_ids) {
                return !in_array((string) $widget_id, $widget_ids, true);
            }));

            if ($repaired_hidden !== $hidden) {
                update_user_option($user_id, 'metaboxhidden_dashboard', $repaired_hidden, false);
            }
        }

        $order = get_user_option('meta-box-order_dashboard', $user_id);
        if (!is_array($order)) {
            $order = array();
        }

        foreach (array('normal', 'side', 'column3', 'column4') as $context) {
            if (!isset($order[$context]) || !is_string($order[$context])) {
                $order[$context] = '';
                continue;
            }

            $box_ids = array_values(array_filter(array_map('trim', explode(',', $order[$context]))));
            $box_ids = array_values(array_filter($box_ids, function ($widget_id) use ($widget_ids) {
                return !in_array($widget_id, $widget_ids, true);
            }));

            $order[$context] = implode(',', $box_ids);
        }

        $default_placements = array();
        foreach ($widgets as $widget) {
            $context = isset($widget['context']) ? (string) $widget['context'] : 'normal';
            if (!in_array($context, array('normal', 'side', 'column3', 'column4'), true)) {
                $context = 'normal';
            }
            if (!isset($default_placements[$context])) {
                $default_placements[$context] = array();
            }
            $default_placements[$context][] = $widget['id'];
        }

        foreach ($default_placements as $context => $placed_widget_ids) {
            $existing_ids = array_values(array_filter(array_map('trim', explode(',', $order[$context]))));
            $order[$context] = implode(',', array_values(array_unique(array_merge($placed_widget_ids, $existing_ids))));
        }

        update_user_option($user_id, 'meta-box-order_dashboard', $order, false);
        update_user_option($user_id, 'fbsa_dashboard_widget_layout_version', self::DASHBOARD_WIDGET_LAYOUT_VERSION, false);
    }

    private function get_dashboard_guide_groups() {
        return array(
            'website_steps' => array(
                'label' => __('Dashboard — Website Steps', 'fb-software-ai'),
                'description' => __('Videos used by the FB Software AI website-steps Dashboard widget.', 'fb-software-ai'),
                'intro' => __('Complete steps for your website:', 'fb-software-ai'),
                'message_id' => 'fbsa-dashboard-website-steps-message',
                'items' => array(
                    array('id' => 'dashboard_before_starting_website', 'label' => __('1. What to do before starting a website', 'fb-software-ai')),
                    array('id' => 'dashboard_make_backup', 'label' => __('2. Make a Back-up', 'fb-software-ai')),
                    array('id' => 'dashboard_install_theme', 'label' => __('3. Install a Theme', 'fb-software-ai')),
                    array('id' => 'dashboard_install_essential_plugins', 'label' => __('4. Install Essential Plugins', 'fb-software-ai')),
                    array('id' => 'dashboard_create_main_pages', 'label' => __('5. Create Your Main Website Pages', 'fb-software-ai')),
                    array('id' => 'dashboard_set_home_blog', 'label' => __('6. Set the Homepage and Blog Page', 'fb-software-ai')),
                    array('id' => 'dashboard_create_navigation_menu', 'label' => __('7. Create the Website Navigation Menu', 'fb-software-ai')),
                    array('id' => 'dashboard_configure_security', 'label' => __('8. Configure Website Security', 'fb-software-ai')),
                    array('id' => 'dashboard_test_before_launch', 'label' => __('9. Test the Website Before Launch', 'fb-software-ai')),
                    array('id' => 'dashboard_launch_website', 'label' => __('10. Launch the Website', 'fb-software-ai')),
                ),
            ),
            'plugin_setup' => array(
                'label' => __('Dashboard — Plugin Setup', 'fb-software-ai'),
                'description' => __('Videos used by the FB Software AI plugin-setup Dashboard widget.', 'fb-software-ai'),
                'intro' => __('Complete your essential plugin setup:', 'fb-software-ai'),
                'message_id' => 'fbsa-dashboard-plugin-setup-message',
                'items' => array(
                    array('id' => 'dashboard_install_elementor', 'label' => __('1. Install Elementor', 'fb-software-ai')),
                    array('id' => 'dashboard_install_site_kit', 'label' => __('2. Install Site Kit by Google', 'fb-software-ai')),
                    array('id' => 'dashboard_install_backup_plugin', 'label' => __('3. Install a Backup Plugin', 'fb-software-ai')),
                    array('id' => 'dashboard_install_smtp_plugin', 'label' => __('4. Install an SMTP Plugin', 'fb-software-ai')),
                    array('id' => 'dashboard_check_plugin_updates', 'label' => __('5. Check Plugin Updates', 'fb-software-ai')),
                ),
            ),
            'website_settings' => array(
                'label' => __('Dashboard — Website Settings', 'fb-software-ai'),
                'description' => __('Videos used by the FB Software AI website-settings Dashboard widget.', 'fb-software-ai'),
                'intro' => __('Complete your WordPress website settings:', 'fb-software-ai'),
                'message_id' => 'fbsa-dashboard-website-settings-message',
                'items' => array(
                    array('id' => 'dashboard_set_homepage', 'label' => __('1. Set the Homepage', 'fb-software-ai')),
                    array('id' => 'dashboard_set_blog_page', 'label' => __('2. Set the Blog Page', 'fb-software-ai')),
                    array('id' => 'dashboard_configure_permalinks', 'label' => __('3. Configure Permalinks', 'fb-software-ai')),
                    array('id' => 'dashboard_set_title_tagline', 'label' => __('4. Set the Website Title and Tagline', 'fb-software-ai')),
                    array('id' => 'dashboard_configure_reading', 'label' => __('5. Configure Reading Settings', 'fb-software-ai')),
                    array('id' => 'dashboard_configure_discussion', 'label' => __('6. Configure Discussion Settings', 'fb-software-ai')),
                ),
            ),
            'help_tutorials' => array(
                'label' => __('Dashboard — Help and Tutorials', 'fb-software-ai'),
                'description' => __('Videos used by the FB Software AI help-and-tutorials Dashboard widget.', 'fb-software-ai'),
                'intro' => __('Open FB Software AI help and tutorial resources:', 'fb-software-ai'),
                'message_id' => 'fbsa-dashboard-help-tutorials-message',
                'items' => array(
                    array('id' => 'dashboard_watch_welcome_video', 'label' => __('1. Watch the Welcome Video', 'fb-software-ai')),
                    array('id' => 'dashboard_open_guide_videos', 'label' => __('2. Open Guide Videos', 'fb-software-ai')),
                    array('id' => 'dashboard_open_documentation', 'label' => __('3. Open FB Software AI Documentation', 'fb-software-ai')),
                    array('id' => 'dashboard_contact_support', 'label' => __('4. Contact Support', 'fb-software-ai')),
                    array('id' => 'dashboard_visit_youtube_channel', 'label' => __('5. Visit the YouTube Channel', 'fb-software-ai')),
                ),
            ),
        );
    }

    private function get_dashboard_video_link_groups() {
        $groups = array();
        foreach ($this->get_dashboard_guide_groups() as $group_id => $dashboard_group) {
            $group = array(
                'id' => sanitize_key('dashboard_' . $group_id),
                'label' => $dashboard_group['label'],
                'description' => $dashboard_group['description'],
                'items' => array(),
                'subgroups' => array(),
                'count' => 0,
            );

            foreach ($dashboard_group['items'] as $item) {
                $row = $this->get_video_link_row($dashboard_group['label'], array(
                    'id' => $item['id'],
                    'label' => $item['label'],
                    'videoUrl' => '',
                ));
                if ($row !== null) {
                    $group['items'][] = $row;
                    $group['count']++;
                }
            }

            if ($group['count'] > 0) {
                $groups[] = $group;
            }
        }

        return $groups;
    }

    /**
     * Render a compact checklist shared by all FB Software AI Dashboard boxes.
     */
    private function render_dashboard_checklist($intro, $steps, $message_id) {
        $settings = $this->get_settings();
        $language_labels = $this->get_supported_video_languages();
        $primary_language = $this->get_current_video_language();
        ?>
        <div class="fbsa-dashboard-steps" data-fbsa-dashboard-checklist data-fbsa-dashboard-widget-version="<?php echo esc_attr(self::VERSION); ?>">
            <p class="fbsa-dashboard-steps__intro"><?php echo esc_html($intro); ?></p>
            <ul class="fbsa-dashboard-steps__list">
                <?php foreach ($steps as $step) : ?>
                    <?php
                    $step_id = isset($step['id']) ? sanitize_key($step['id']) : '';
                    $step_label = isset($step['label']) ? $step['label'] : '';
                    $profiles = $this->get_command_video_profiles(array('id' => $step_id, 'videoUrl' => ''), $settings);
                    $selected_video = $this->select_localized_video_value($profiles, $primary_language);
                    $language_label = isset($language_labels[$selected_video['language']]['label'])
                        ? $language_labels[$selected_video['language']]['label']
                        : $selected_video['language'];
                    ?>
                    <li class="fbsa-dashboard-steps__item<?php echo $selected_video['url'] !== '' ? ' has-video' : ''; ?>">
                        <span class="dashicons <?php echo $selected_video['url'] !== '' ? 'dashicons-controls-play' : 'dashicons-dismiss'; ?> fbsa-dashboard-steps__status" aria-hidden="true"></span>
                        <a
                            href="<?php echo esc_url($selected_video['url'] !== '' ? $selected_video['url'] : '#'); ?>"
                            class="fbsa-dashboard-steps__link"
                            data-fbsa-placeholder-guide="<?php echo esc_attr($step_label); ?>"
                            data-fbsa-guide-video-url="<?php echo esc_attr($selected_video['url']); ?>"
                            data-fbsa-guide-video-language="<?php echo esc_attr($language_label); ?>"
                            data-fbsa-guide-video-fallback="<?php echo $selected_video['is_fallback'] ? '1' : '0'; ?>"
                            aria-describedby="<?php echo esc_attr($message_id); ?>"
                        ><?php echo esc_html($step_label); ?></a>
                    </li>
                <?php endforeach; ?>
            </ul>
            <p id="<?php echo esc_attr($message_id); ?>" class="fbsa-dashboard-steps__message" role="status" aria-live="polite" hidden></p>
        </div>
        <?php
    }

    public function render_dashboard_website_steps_widget() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $group = $this->get_dashboard_guide_groups()['website_steps'];
        $this->render_dashboard_checklist($group['intro'], $group['items'], $group['message_id']);
    }

    public function render_dashboard_plugin_setup_widget() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $group = $this->get_dashboard_guide_groups()['plugin_setup'];
        $this->render_dashboard_checklist($group['intro'], $group['items'], $group['message_id']);
    }

    public function render_dashboard_website_settings_widget() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $group = $this->get_dashboard_guide_groups()['website_settings'];
        $this->render_dashboard_checklist($group['intro'], $group['items'], $group['message_id']);
    }

    public function render_dashboard_help_tutorials_widget() {
        if (!current_user_can('manage_options')) {
            return;
        }
        $group = $this->get_dashboard_guide_groups()['help_tutorials'];
        $this->render_dashboard_checklist($group['intro'], $group['items'], $group['message_id']);
    }

    public function auto_collapse_admin_menu_body_class($classes) {
        if ($this->is_settings_page()) {
            $classes .= ' folded fbsa-admin-menu-auto-collapsed';
        }

        if ($this->is_dashboard_screen()) {
            $classes .= ' fbsa-dashboard-welcome-active';
        }

        return $classes;
    }

    public function auto_collapse_admin_menu_script() {
        if (!$this->is_settings_page()) {
            return;
        }
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.body.classList.add('folded');
                document.body.classList.add('fbsa-admin-menu-auto-collapsed');
            });
        </script>
        <?php
    }


    public function enqueue_frontend_assets() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            return;
        }
        if (function_exists('is_customize_preview') && is_customize_preview()) {
            return;
        }
        $this->enqueue_assets();
    }

    public function enqueue_assets() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            return;
        }

        $plugin_url = plugin_dir_url(__FILE__);

        wp_enqueue_style('dashicons');

        wp_enqueue_style(
            'fbsa-widget',
            $plugin_url . 'assets/css/widget.css',
            array(),
            self::VERSION
        );

        wp_enqueue_style(
            'fbsa-video-player',
            $plugin_url . 'assets/css/video-player.css',
            array('fbsa-widget'),
            self::VERSION
        );

        if (is_admin()) {
            wp_enqueue_style(
                'fbsa-admin-menu',
                $plugin_url . 'assets/css/admin-menu.css',
                array(),
                self::VERSION
            );
        }

        if ($this->is_settings_page()) {
            wp_enqueue_style(
                'fbsa-settings',
                $plugin_url . 'assets/css/settings.css',
                array(),
                self::VERSION
            );
            wp_enqueue_media();
        }

        if ($this->is_dashboard_screen()) {
            wp_enqueue_style(
                'fbsa-dashboard-welcome',
                $plugin_url . 'assets/css/dashboard-welcome.css',
                array('dashicons'),
                self::VERSION
            );
        }

        wp_enqueue_script(
            'fbsa-admin',
            $plugin_url . 'assets/admin.js',
            array('wp-i18n'),
            self::VERSION,
            true
        );

        wp_set_script_translations(
            'fbsa-admin',
            'fb-software-ai',
            plugin_dir_path(__FILE__) . 'languages'
        );

        $workflow = $this->get_workflow_data();

        wp_localize_script('fbsa-admin', 'fbsaWidgetData', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'adminUrl' => admin_url(),
            'siteUrl' => site_url('/'),
            'nonce' => wp_create_nonce(self::NONCE_ACTION),
            'logoUrl' => $plugin_url . 'assets/fb-software-solutions-logo.svg',
            'settingsUrl' => admin_url('tools.php?page=fb-software-ai'),
            'commands' => $this->translate_known_data($workflow),
            'workflowError' => $this->get_workflow_error_message(),
            'installedPlugins' => $this->get_installed_required_plugin_slugs(),
            'activePlugins' => $this->get_active_required_plugin_slugs(),
            'translationCatalog' => $this->get_translation_catalog(),
            'locale' => determine_locale(),
            'installedThemes' => $this->get_installed_required_theme_slugs(),
            'i18n' => array(
                'chooseCommand' => __('Choose a command', 'fb-software-ai'),
                'startBuilding' => __('Start Building', 'fb-software-ai'),
                'loading' => __('Working...', 'fb-software-ai'),
                'done' => __('Done', 'fb-software-ai'),
                'error' => __('Something went wrong. Please try again.', 'fb-software-ai'),
                'videoPlaceholder' => __('Select a command to see the guide video area.', 'fb-software-ai'),
                'guideVideo' => __('Guide Video', 'fb-software-ai'),
                'light' => __('Light', 'fb-software-ai'),
                'dark' => __('Dark', 'fb-software-ai'),
                'create' => __('Create', 'fb-software-ai'),
                'open' => __('Open', 'fb-software-ai'),
                'close' => __('Close', 'fb-software-ai'),
            ),
        ));
    }

    private function is_customizer_controls_request() {
        if (!is_admin() || !function_exists('get_current_screen')) {
            return false;
        }

        $screen = get_current_screen();
        return $screen && $screen->base === 'customize';
    }

    public function render_dashboard_welcome_panel() {
        if (!current_user_can('manage_options')) {
            return;
        }

        ob_start();

        $settings = $this->get_settings();
        $welcome = isset($settings['welcome_dashboard']) && is_array($settings['welcome_dashboard'])
            ? $settings['welcome_dashboard']
            : array();
        $welcome = $this->get_localized_welcome_dashboard($welcome);

        $banner_url = '';
        $banner_id = isset($welcome['banner_attachment_id']) ? absint($welcome['banner_attachment_id']) : 0;
        if ($banner_id) {
            $attachment_url = wp_get_attachment_image_url($banner_id, 'full');
            if ($attachment_url) {
                $banner_url = $attachment_url;
            }
        }
        if ($banner_url === '' && !empty($welcome['banner_url'])) {
            $banner_url = esc_url_raw($welcome['banner_url']);
        }

        $video_url = !empty($welcome['welcome_video_url']) ? esc_url_raw($welcome['welcome_video_url']) : '';
        $video_embed_url = $this->get_youtube_embed_url($video_url);
        $watch_url = !empty($welcome['watch_youtube_url']) ? esc_url_raw($welcome['watch_youtube_url']) : $video_url;
        $subscribe_url = !empty($welcome['subscribe_youtube_url']) ? esc_url_raw($welcome['subscribe_youtube_url']) : '';
        $memberships_url = !empty($welcome['memberships_url']) ? esc_url_raw($welcome['memberships_url']) : '';
        $social_links = !empty($welcome['social_links']) && is_array($welcome['social_links']) ? $welcome['social_links'] : array();
        $banner_style = $banner_url !== '' ? '--fbsa-welcome-banner:url(' . esc_url($banner_url) . ');' : '';
        ?>
        <div class="welcome-panel-content fbsa-dashboard-welcome" style="<?php echo esc_attr($banner_style); ?>">
            <section class="fbsa-dashboard-welcome__hero">
                <div class="fbsa-dashboard-welcome__hero-copy">
                    <div class="fbsa-dashboard-welcome__brand">
                        <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/fb-software-solutions-logo.svg'); ?>" alt="FB Software AI" />
                        <span>FB Software AI</span>
                    </div>
                    <h2>Welcome to FB Software AI!</h2>
                    <p>Your WordPress setup assistant with step-by-step video guides, practical shortcuts, and clear website-building workflows.</p>
                    <div class="fbsa-dashboard-welcome__actions">
                        <?php if ($watch_url !== '') : ?>
                            <a class="button button-primary fbsa-dashboard-welcome__button fbsa-dashboard-welcome__button--primary" href="<?php echo esc_url($watch_url); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-controls-play" aria-hidden="true"></span>Watch on YouTube</a>
                        <?php endif; ?>
                        <?php if ($subscribe_url !== '') : ?>
                            <a class="button fbsa-dashboard-welcome__button fbsa-dashboard-welcome__button--light" href="<?php echo esc_url($subscribe_url); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-video-alt3" aria-hidden="true"></span>Subscribe on YouTube</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="fbsa-dashboard-welcome__feature-list" aria-label="FB Software AI highlights">
                    <span><i class="dashicons dashicons-video-alt3" aria-hidden="true"></i>Step-by-step video guides</span>
                    <span><i class="dashicons dashicons-admin-tools" aria-hidden="true"></i>Smart admin shortcuts</span>
                    <span><i class="dashicons dashicons-shield" aria-hidden="true"></i>Security and productivity</span>
                    <span><i class="dashicons dashicons-performance" aria-hidden="true"></i>Build websites faster</span>
                </div>
            </section>

            <section class="fbsa-dashboard-welcome__content">
                <div class="fbsa-dashboard-welcome__video-column">
                    <div class="fbsa-dashboard-welcome__video-card">
                        <?php if ($video_embed_url !== '') : ?>
                            <div class="fbsa-dashboard-welcome__video-frame">
                                <iframe src="<?php echo esc_url($video_embed_url); ?>" title="Welcome to FB Software AI" loading="lazy" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                            </div>
                        <?php else : ?>
                            <div class="fbsa-dashboard-welcome__video-placeholder">
                                <span class="dashicons dashicons-video-alt3" aria-hidden="true"></span>
                                <strong>Welcome video coming soon</strong>
                                <p>Add the YouTube video under Tools → FB Software AI → Guide Videos.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="fbsa-dashboard-welcome__benefits">
                        <div><span class="dashicons dashicons-book-alt" aria-hidden="true"></span><strong>Step-by-Step Guides</strong><small>Easy tutorials for every task.</small></div>
                        <div><span class="dashicons dashicons-admin-tools" aria-hidden="true"></span><strong>Admin Shortcuts</strong><small>Quick access to important screens.</small></div>
                        <div><span class="dashicons dashicons-shield-alt" aria-hidden="true"></span><strong>Security First</strong><small>Protect and manage your website.</small></div>
                        <div><span class="dashicons dashicons-performance" aria-hidden="true"></span><strong>Work Smarter</strong><small>Save time on repeated steps.</small></div>
                    </div>
                </div>

                <aside class="fbsa-dashboard-welcome__side-column">
                    <?php if ($subscribe_url !== '') : ?>
                        <div class="fbsa-dashboard-welcome__side-card">
                            <span class="dashicons dashicons-heart" aria-hidden="true"></span>
                            <div><h3>Support the Channel</h3><p>Subscribe for new tutorials, plugin updates, and website-building tips.</p><a href="<?php echo esc_url($subscribe_url); ?>" target="_blank" rel="noopener noreferrer">Subscribe on YouTube <b aria-hidden="true">→</b></a></div>
                        </div>
                    <?php endif; ?>
                    <?php if ($memberships_url !== '') : ?>
                        <div class="fbsa-dashboard-welcome__side-card">
                            <span class="dashicons dashicons-star-filled" aria-hidden="true"></span>
                            <div><h3>Memberships</h3><p>Explore membership levels, loyalty badges, and exclusive benefits.</p><a href="<?php echo esc_url($memberships_url); ?>" target="_blank" rel="noopener noreferrer">View Memberships <b aria-hidden="true">→</b></a></div>
                        </div>
                    <?php endif; ?>
                    <?php if ($this->has_social_links($social_links)) : ?>
                        <div class="fbsa-dashboard-welcome__social-card">
                            <h3>Connect With Us</h3>
                            <div class="fbsa-dashboard-welcome__social-links">
                                <?php $this->render_dashboard_social_link($social_links, 'youtube', 'YouTube', 'dashicons-video-alt3'); ?>
                                <?php $this->render_dashboard_social_link($social_links, 'website', 'Website', 'dashicons-admin-site-alt3'); ?>
                                <?php $this->render_dashboard_social_link($social_links, 'facebook', 'Facebook', 'dashicons-facebook'); ?>
                                <?php $this->render_dashboard_social_link($social_links, 'instagram', 'Instagram', 'dashicons-instagram'); ?>
                                <?php $this->render_dashboard_social_link($social_links, 'x', 'X', 'dashicons-twitter'); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    <a class="fbsa-dashboard-welcome__manage-link" href="<?php echo esc_url(admin_url('tools.php?page=fb-software-ai#fbsa-settings-videos')); ?>"><span class="dashicons dashicons-admin-generic" aria-hidden="true"></span>Manage welcome panel and links</a>
                </aside>
            </section>
        </div>
        <?php
        $fbsa_html = ob_get_clean();
        echo $this->translate_plugin_html($fbsa_html); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    private function render_dashboard_social_link($social_links, $key, $label, $dashicon) {
        $url = !empty($social_links[$key]) ? esc_url_raw($social_links[$key]) : '';
        if ($url === '') {
            return;
        }
        ?>
        <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($label); ?>" title="<?php echo esc_attr($label); ?>"><span class="dashicons <?php echo esc_attr($dashicon); ?>" aria-hidden="true"></span></a>
        <?php
    }

    private function has_social_links($social_links) {
        if (!is_array($social_links)) {
            return false;
        }
        foreach (array('youtube', 'website', 'facebook', 'instagram', 'x') as $key) {
            if (!empty($social_links[$key])) {
                return true;
            }
        }
        return false;
    }

    private function get_youtube_embed_url($url) {
        if (!is_string($url) || trim($url) === '') {
            return '';
        }

        $url = trim($url);
        $video_id = '';
        $parts = wp_parse_url($url);
        if (!is_array($parts)) {
            return '';
        }

        $host = isset($parts['host']) ? strtolower($parts['host']) : '';
        $path = isset($parts['path']) ? trim($parts['path'], '/') : '';
        if (strpos($host, 'youtu.be') !== false) {
            $video_id = strtok($path, '/');
        } elseif (strpos($host, 'youtube.com') !== false || strpos($host, 'youtube-nocookie.com') !== false) {
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $query_args);
                if (!empty($query_args['v'])) {
                    $video_id = $query_args['v'];
                }
            }
            if ($video_id === '' && preg_match('~^(?:shorts|embed|live)/([A-Za-z0-9_-]{6,})~', $path, $matches)) {
                $video_id = $matches[1];
            }
        }

        $video_id = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $video_id);
        if ($video_id === '') {
            return '';
        }

        return 'https://www.youtube-nocookie.com/embed/' . rawurlencode($video_id) . '?rel=0&modestbranding=1';
    }

    public function render_widget() {
        if (!is_user_logged_in() || !current_user_can('manage_options')) {
            return;
        }

        // The Customizer already has a dedicated controls-footer render path.
        if ($this->is_customizer_controls_request() && current_filter() === 'admin_footer') {
            return;
        }

        // Prevent a second widget from rendering inside the Customizer preview iframe.
        if (!is_admin() && function_exists('is_customize_preview') && is_customize_preview()) {
            return;
        }

        ob_start();

        $this->get_workflow_data();
        $workflow_error = $this->get_workflow_error_message();
        ?>
        <div id="fbsa-floating-widget" class="fbsa-widget fbsa-widget--preinit" aria-live="polite" data-fbsa-theme="dark">
            <div class="fbsa-widget__dock">
                <div class="fbsa-widget__panel">
                    <div class="fbsa-widget__header" data-fbsa-widget-drag-handle>
                    <div class="fbsa-widget__brand">
                        <img class="fbsa-widget__logo" src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/fb-software-solutions-logo.svg'); ?>" alt="FB Software AI" />
                        <div>
                            <strong>FB Software AI</strong>
                            <span>WordPress GPS Guide</span>
                        </div>
                    </div>
                    <div class="fbsa-widget__header-actions">
                        <button type="button" class="fbsa-widget__theme-toggle" data-fbsa-theme-toggle aria-label="Switch to light theme" title="Switch theme">
                            <span class="fbsa-widget__theme-toggle-dot" aria-hidden="true"></span>
                            <span class="fbsa-widget__theme-toggle-label">Dark</span>
                        </button>
                        <button type="button" class="fbsa-widget__toggle" aria-label="Close FB Software AI popup" aria-expanded="true">−</button>
                    </div>
                </div>

                <div class="fbsa-widget__body">
                    <div class="fbsa-workspace-card">
                        <label class="fbsa-label" for="fbsa-category-select">Workspace</label>
                        <select id="fbsa-category-select" class="fbsa-select">
                            <option value="">Start Building</option>
                        </select>

                        <div id="fbsa-subcategory-field" class="fbsa-subcategory-field" hidden>
                            <label class="fbsa-label fbsa-label--small" for="fbsa-subcategory-select">Sub Category</label>
                            <select id="fbsa-subcategory-select" class="fbsa-select" disabled>
                                <option value="">Choose a sub category</option>
                            </select>
                        </div>

                        <div id="fbsa-command-field" class="fbsa-command-field" hidden>
                            <label class="fbsa-label fbsa-label--small" for="fbsa-command-select">Command</label>
                            <select id="fbsa-command-select" class="fbsa-select" disabled>
                                <option value="">Choose a command</option>
                            </select>
                        </div>

                        <button type="button" id="fbsa-action-button" class="fbsa-action-button" hidden>Create</button>
                        <div id="fbsa-command-message" class="fbsa-command-message<?php echo $workflow_error !== '' ? ' fbsa-command-message--error' : ''; ?>"><?php echo esc_html($workflow_error !== '' ? $workflow_error : 'Select a workspace command to begin.'); ?></div>
                    </div>

                    <div class="fbsa-progress-card">
                        <div class="fbsa-progress-card__top">
                            <span>Website Progress</span>
                            <strong id="fbsa-progress-percent">0%</strong>
                        </div>
                        <div class="fbsa-progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                            <span id="fbsa-progress-fill"></span>
                        </div>
                        <p id="fbsa-progress-text">Create your core pages to increase progress.</p>
                    </div>

                    <div class="fbsa-video-card">
                        <div class="fbsa-video-card__title">Guide Video</div>
                        <div id="fbsa-video-box" class="fbsa-video-box">Select a command to see the guide video area.</div>
                    </div>

                    <a class="fbsa-settings-link" href="<?php echo esc_url(admin_url('tools.php?page=fb-software-ai')); ?>">Open FB Software AI Settings</a>
                    </div>
                </div>

                <nav class="fbsa-widget__shortcut-rail" aria-label="FB Software AI WordPress backend shortcuts">
                    <a class="fbsa-rail-link" href="<?php echo esc_url(admin_url('index.php')); ?>" title="Dashboard" aria-label="Open Dashboard"><span class="dashicons dashicons-dashboard" aria-hidden="true"></span></a>
                    <a class="fbsa-rail-link" href="<?php echo esc_url(admin_url('edit.php')); ?>" title="Posts" aria-label="Open Posts"><span class="dashicons dashicons-admin-post" aria-hidden="true"></span></a>
                    <a class="fbsa-rail-link" href="<?php echo esc_url(admin_url('upload.php')); ?>" title="Media Library" aria-label="Open Media Library"><span class="dashicons dashicons-admin-media" aria-hidden="true"></span></a>
                    <a class="fbsa-rail-link" href="<?php echo esc_url(admin_url('edit.php?post_type=page')); ?>" title="Pages" aria-label="Open Pages"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span></a>
                    <a class="fbsa-rail-link" href="<?php echo esc_url(admin_url('edit-comments.php')); ?>" title="Comments" aria-label="Open Comments"><span class="dashicons dashicons-admin-comments" aria-hidden="true"></span></a>
                    <a class="fbsa-rail-link" href="<?php echo esc_url(admin_url('themes.php')); ?>" title="Appearance" aria-label="Open Appearance"><span class="dashicons dashicons-admin-appearance" aria-hidden="true"></span></a>
                    <a class="fbsa-rail-link" href="<?php echo esc_url(admin_url('plugins.php')); ?>" title="Plugins" aria-label="Open Plugins"><span class="dashicons dashicons-admin-plugins" aria-hidden="true"></span></a>
                    <a class="fbsa-rail-link" href="<?php echo esc_url(admin_url('users.php')); ?>" title="Users" aria-label="Open Users"><span class="dashicons dashicons-admin-users" aria-hidden="true"></span></a>
                    <a class="fbsa-rail-link fbsa-rail-link--active" href="<?php echo esc_url(admin_url('tools.php?page=fb-software-ai')); ?>" title="FB Software AI Settings" aria-label="Open FB Software AI Settings"><span class="dashicons dashicons-admin-tools" aria-hidden="true"></span></a>
                    <a class="fbsa-rail-link" href="<?php echo esc_url(admin_url('options-general.php')); ?>" title="Settings" aria-label="Open Settings"><span class="dashicons dashicons-admin-settings" aria-hidden="true"></span></a>
                    <a class="fbsa-rail-link" href="<?php echo esc_url($this->get_plugin_destination_url(self::WOOCOMMERCE_PLUGIN_SLUG, self::WOOCOMMERCE_PLUGIN_FILE, 'admin.php?page=wc-admin')); ?>" title="WooCommerce" aria-label="Open WooCommerce"><span class="dashicons dashicons-cart" aria-hidden="true"></span></a>
                    <a class="fbsa-rail-link" href="<?php echo esc_url(admin_url('customize.php')); ?>" title="Customizer" aria-label="Open Customizer"><span class="dashicons dashicons-admin-customizer" aria-hidden="true"></span></a>
                </nav>
            </div>
        </div>
        <?php
        $fbsa_html = ob_get_clean();
        echo $this->translate_plugin_html($fbsa_html); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    public function render_settings_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        ob_start();
        ob_start();
        $this->handle_settings_postbacks();
        $fbsa_postback_notice_html = trim(ob_get_clean());
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $settings = $this->get_settings();
        $status = $this->calculate_status();
        $workflow = $this->get_workflow_data();
        $video_source_workflow = $this->get_workflow_source_data();
        $workflow_error = $this->get_workflow_error_message();
        $category_options = $this->get_category_options($workflow);
        $video_link_groups = array_merge(
            $this->get_dashboard_video_link_groups(),
            $this->get_video_link_groups($video_source_workflow)
        );
        $welcome_dashboard = isset($settings['welcome_dashboard']) && is_array($settings['welcome_dashboard']) ? $settings['welcome_dashboard'] : array();
        $video_languages = $this->get_supported_video_languages();
        $current_video_language = $this->get_current_video_language();
        $current_video_language_label = isset($video_languages[$current_video_language]['label']) ? $video_languages[$current_video_language]['label'] : $current_video_language;
        if (empty($welcome_dashboard['banner_url']) && !empty($welcome_dashboard['banner_attachment_id'])) {
            $welcome_banner_url = wp_get_attachment_image_url(absint($welcome_dashboard['banner_attachment_id']), 'full');
            if ($welcome_banner_url) {
                $welcome_dashboard['banner_url'] = $welcome_banner_url;
            }
        }
        $kadence_theme = wp_get_theme(self::KADENCE_THEME_SLUG);
        $kadence_installed = $kadence_theme->exists();
        $kadence_active = get_stylesheet() === self::KADENCE_THEME_SLUG || get_template() === self::KADENCE_THEME_SLUG;
        $hello_elementor_theme = wp_get_theme(self::HELLO_ELEMENTOR_THEME_SLUG);
        $hello_elementor_installed = $hello_elementor_theme->exists();
        $hello_elementor_active = get_stylesheet() === self::HELLO_ELEMENTOR_THEME_SLUG || get_template() === self::HELLO_ELEMENTOR_THEME_SLUG;
        $astra_theme = wp_get_theme(self::ASTRA_THEME_SLUG);
        $astra_installed = $astra_theme->exists();
        $astra_active = get_stylesheet() === self::ASTRA_THEME_SLUG || get_template() === self::ASTRA_THEME_SLUG;
        $oceanwp_theme = wp_get_theme(self::OCEANWP_THEME_SLUG);
        $oceanwp_installed = $oceanwp_theme->exists();
        $oceanwp_active = get_stylesheet() === self::OCEANWP_THEME_SLUG || get_template() === self::OCEANWP_THEME_SLUG;
        $blocksy_theme = wp_get_theme(self::BLOCKSY_THEME_SLUG);
        $blocksy_installed = $blocksy_theme->exists();
        $blocksy_active = get_stylesheet() === self::BLOCKSY_THEME_SLUG || get_template() === self::BLOCKSY_THEME_SLUG;
        $zakra_theme = wp_get_theme(self::ZAKRA_THEME_SLUG);
        $zakra_installed = $zakra_theme->exists();
        $zakra_active = get_stylesheet() === self::ZAKRA_THEME_SLUG || get_template() === self::ZAKRA_THEME_SLUG;
        $elementor_plugin_file = $this->get_plugin_file_for_slug(self::ELEMENTOR_PLUGIN_SLUG, self::ELEMENTOR_PLUGIN_FILE);
        $elementor_installed = $elementor_plugin_file !== '';
        $elementor_active = $elementor_installed && is_plugin_active($elementor_plugin_file);
        $site_kit_plugin_file = $this->get_plugin_file_for_slug(self::SITE_KIT_PLUGIN_SLUG, self::SITE_KIT_PLUGIN_FILE);
        $site_kit_installed = $site_kit_plugin_file !== '';
        $site_kit_active = $site_kit_installed && is_plugin_active($site_kit_plugin_file);
        $fluentsmtp_plugin_file = $this->get_plugin_file_for_slug(self::FLUENTSMTP_PLUGIN_SLUG, self::FLUENTSMTP_PLUGIN_FILE);
        $fluentsmtp_installed = $fluentsmtp_plugin_file !== '';
        $fluentsmtp_active = $fluentsmtp_installed && is_plugin_active($fluentsmtp_plugin_file);
        $duplicate_post_plugin_file = $this->get_plugin_file_for_slug(self::YOAST_DUPLICATE_POST_PLUGIN_SLUG, self::YOAST_DUPLICATE_POST_PLUGIN_FILE);
        $duplicate_post_installed = $duplicate_post_plugin_file !== '';
        $duplicate_post_active = $duplicate_post_installed && is_plugin_active($duplicate_post_plugin_file);
        $contact_form_7_plugin_file = $this->get_plugin_file_for_slug(self::CONTACT_FORM_7_PLUGIN_SLUG, self::CONTACT_FORM_7_PLUGIN_FILE);
        $contact_form_7_installed = $contact_form_7_plugin_file !== '';
        $contact_form_7_active = $contact_form_7_installed && is_plugin_active($contact_form_7_plugin_file);
        $loco_translate_plugin_file = $this->get_plugin_file_for_slug(self::LOCO_TRANSLATE_PLUGIN_SLUG, self::LOCO_TRANSLATE_PLUGIN_FILE);
        $loco_translate_installed = $loco_translate_plugin_file !== '';
        $loco_translate_active = $loco_translate_installed && is_plugin_active($loco_translate_plugin_file);
        $locoai_plugin_file = $this->get_plugin_file_for_slug(self::LOCOAI_PLUGIN_SLUG, self::LOCOAI_PLUGIN_FILE);
        $locoai_installed = $locoai_plugin_file !== '';
        $locoai_active = $locoai_installed && is_plugin_active($locoai_plugin_file);
        $woocommerce_plugin_file = $this->get_plugin_file_for_slug(self::WOOCOMMERCE_PLUGIN_SLUG, self::WOOCOMMERCE_PLUGIN_FILE);
        $woocommerce_installed = $woocommerce_plugin_file !== '';
        $woocommerce_active = $woocommerce_installed && is_plugin_active($woocommerce_plugin_file);
        $wpvivid_plugin_file = $this->get_plugin_file_for_slug(self::WPVIVID_PLUGIN_SLUG, self::WPVIVID_PLUGIN_FILE);
        $wpvivid_installed = $wpvivid_plugin_file !== '';
        $wpvivid_active = $wpvivid_installed && is_plugin_active($wpvivid_plugin_file);
        $progress_percent = isset($status['percent']) ? absint($status['percent']) : 0;
        ?>
        <div class="wrap fbsa-settings-page">
            <div class="fbsa-settings-shell">
                <section class="fbsa-settings-hero">
                    <div>
                        <span class="fbsa-settings-eyebrow">FB Software AI Settings</span>
                        <h1>Premium WordPress Setup Control Panel</h1>
                        <p>Configure the floating widget, custom commands, fixed setup installers, and progress controls from one clean premium panel.</p>
                    </div>
                    <div class="fbsa-settings-hero__meta">
                        <span>Version <?php echo esc_html(self::VERSION); ?></span>
                        <strong><?php echo esc_html($progress_percent); ?>%</strong>
                        <small>Core progress</small>
                    </div>
                </section>

                <nav class="fbsa-settings-tabs" role="tablist" aria-label="FB Software AI settings sections">
                    <button type="button" class="fbsa-settings-tab is-active" id="fbsa-settings-tab-overview" role="tab" aria-selected="true" aria-controls="fbsa-settings-panel-overview" data-fbsa-settings-tab="overview"><span class="dashicons dashicons-dashboard" aria-hidden="true"></span>Overview</button>
                    <button type="button" class="fbsa-settings-tab" id="fbsa-settings-tab-videos" role="tab" aria-selected="false" aria-controls="fbsa-settings-panel-videos" data-fbsa-settings-tab="videos"><span class="dashicons dashicons-video-alt3" aria-hidden="true"></span>Guide Videos</button>
                    <button type="button" class="fbsa-settings-tab" id="fbsa-settings-tab-themes" role="tab" aria-selected="false" aria-controls="fbsa-settings-panel-themes" data-fbsa-settings-tab="themes"><span class="dashicons dashicons-admin-appearance" aria-hidden="true"></span>Themes</button>
                    <button type="button" class="fbsa-settings-tab" id="fbsa-settings-tab-plugins" role="tab" aria-selected="false" aria-controls="fbsa-settings-panel-plugins" data-fbsa-settings-tab="plugins"><span class="dashicons dashicons-admin-plugins" aria-hidden="true"></span>Plugins</button>
                    <button type="button" class="fbsa-settings-tab" id="fbsa-settings-tab-commands" role="tab" aria-selected="false" aria-controls="fbsa-settings-panel-commands" data-fbsa-settings-tab="commands"><span class="dashicons dashicons-editor-code" aria-hidden="true"></span>Commands</button>
                </nav>

                <div id="fbsa-settings-panel-overview" class="fbsa-settings-tab-panel is-active" role="tabpanel" aria-labelledby="fbsa-settings-tab-overview" data-fbsa-settings-panel="overview">
                <?php if ($fbsa_postback_notice_html !== '' || $fluentsmtp_active || $workflow_error !== '') : ?>
                    <section id="fbsa-setup-alerts" class="fbsa-settings-card fbsa-notification-center" role="region" aria-label="FB Software AI setup alerts">
                        <div class="fbsa-settings-card__header">
                            <div>
                                <span class="fbsa-settings-eyebrow">Notifications</span>
                                <h2>Setup Alerts</h2>
                                <p class="fbsa-muted">Important plugin messages appear only in this dedicated section, never inside the top banner.</p>
                            </div>
                        </div>
                        <?php if ($fbsa_postback_notice_html !== '') : ?>
                            <div class="fbsa-postback-notices">
                                <?php echo wp_kses_post($fbsa_postback_notice_html); ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($workflow_error !== '') : ?>
                            <div class="notice notice-error inline"><p><?php echo esc_html($workflow_error); ?></p></div>
                        <?php endif; ?>
                        <?php if ($fluentsmtp_active) : ?>
                            <div class="fbsa-fluent-notice">
                                <div>
                                    <strong>FluentSMTP needs to be configured for it to work.</strong>
                                    <p>Open the FluentSMTP settings and connect your mail sender before using website email forms.</p>
                                </div>
                                <a class="button button-primary fbsa-premium-button" href="<?php echo esc_url(admin_url('options-general.php?page=fluent-mail')); ?>">Configure FluentSMTP</a>
                            </div>
                        <?php endif; ?>
                    </section>
                <?php endif; ?>

                <div class="fbsa-settings-grid fbsa-settings-grid--top">
                    <section class="fbsa-settings-card">
                        <div class="fbsa-settings-card__header">
                            <div>
                                <span class="fbsa-settings-eyebrow">Main Configuration</span>
                                <h2>Widget Setup</h2>
                            </div>
                        </div>
                        <form method="post" action="" class="fbsa-premium-form">
                            <?php wp_nonce_field('fbsa_save_settings_action'); ?>
                            <div class="fbsa-field-grid">
                                <label class="fbsa-field-card" for="fbsa_level">
                                    <span>User Level</span>
                                    <select id="fbsa_level" name="fbsa_level">
                                        <option value="starter" <?php selected($settings['level'], 'starter'); ?>>Starter Level - show video guidance</option>
                                        <option value="pro" <?php selected($settings['level'], 'pro'); ?>>Pro Level - shortcut command mode</option>
                                    </select>
                                    <small>Starter mode is prepared for tutorial videos. Pro mode is prepared for click-saving commands.</small>
                                </label>

                                <label class="fbsa-field-card" for="fbsa_website_type">
                                    <span>Website Workflow</span>
                                    <select id="fbsa_website_type" name="fbsa_website_type">
                                        <option value="corporate" <?php selected($settings['website_type'], 'corporate'); ?>>Corporate</option>
                                        <option value="blog" <?php selected($settings['website_type'], 'blog'); ?>>Blog</option>
                                        <option value="ecommerce" <?php selected($settings['website_type'], 'ecommerce'); ?>>Ecommerce</option>
                                    </select>
                                    <small>This keeps the plugin ready for updateable future workflow presets.</small>
                                </label>
                            </div>
                            <p class="fbsa-settings-actions"><button type="submit" name="fbsa_save_settings" class="button button-primary fbsa-premium-button">Save Settings</button></p>
                        </form>
                    </section>

                    <aside class="fbsa-settings-card fbsa-widget-status-card">
                        <div class="fbsa-settings-card__header">
                            <div>
                                <span class="fbsa-settings-eyebrow">Live Status</span>
                                <h2>Floating Widget</h2>
                            </div>
                        </div>
                        <div class="fbsa-status-list">
                            <div><span>Visibility</span><strong>Admin only</strong></div>
                            <div><span>Locations</span><strong>Admin, Frontend, Customizer</strong></div>
                            <div><span>Commands</span><strong><?php echo esc_html(count($settings['custom_commands'])); ?> custom</strong></div>
                            <div><span>Progress</span><strong><?php echo esc_html($status['completed']); ?> / <?php echo esc_html($status['total']); ?></strong></div>
                        </div>
                    </aside>
                </div>

                </div>

                <div id="fbsa-settings-panel-videos" class="fbsa-settings-tab-panel" role="tabpanel" aria-labelledby="fbsa-settings-tab-videos" data-fbsa-settings-panel="videos" hidden>
                <section class="fbsa-settings-card fbsa-video-link-manager">
                    <div class="fbsa-settings-card__header fbsa-video-link-manager__header">
                        <div>
                            <span class="fbsa-settings-eyebrow">Guide Videos</span>
                            <h2>Command Video Links</h2>
                            <p class="fbsa-muted">Manage separate Turkish and English YouTube channels, welcome links, Dashboard guides, and command videos from one place. The current administrator language selects the matching video automatically, with fallback to the other language when needed.</p>
                            <p class="fbsa-video-active-language"><strong>Current administrator video language:</strong> <?php echo esc_html($current_video_language_label); ?>. Missing videos use the other language automatically.</p>
                        </div>
                    </div>
                    <form method="post" action="" class="fbsa-premium-form fbsa-video-link-form">
                        <?php if (!self::VIDEO_LINKS_LOCKED) { wp_nonce_field('fbsa_save_video_links_action'); } ?>
                        <details class="fbsa-video-category fbsa-welcome-settings-category" open>
                            <summary class="fbsa-video-category__summary">
                                <span>
                                    <strong>Welcome Banner &amp; YouTube</strong>
                                    <small>Controls the shared Dashboard banner plus separate Turkish and English welcome videos, buttons, memberships, and YouTube channels.</small>
                                </span>
                                <em>Bilingual links</em>
                            </summary>
                            <div class="fbsa-video-category__body fbsa-welcome-settings-body">
                                <div class="fbsa-welcome-banner-settings">
                                    <div class="fbsa-welcome-banner-preview<?php echo empty($welcome_dashboard['banner_url']) ? ' is-empty' : ''; ?>" data-fbsa-banner-preview>
                                        <?php if (!empty($welcome_dashboard['banner_url'])) : ?>
                                            <img src="<?php echo esc_url($welcome_dashboard['banner_url']); ?>" alt="Current FB Software AI welcome banner" />
                                        <?php else : ?>
                                            <span class="dashicons dashicons-format-image" aria-hidden="true"></span>
                                            <strong>No banner selected</strong>
                                            <small>Recommended size: 1600 × 500 px or wider.</small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="fbsa-welcome-banner-controls">
                                        <input type="hidden" id="fbsa_welcome_banner_id"<?php if (!self::VIDEO_LINKS_LOCKED) : ?> name="fbsa_welcome_dashboard[banner_attachment_id]"<?php endif; ?> value="<?php echo esc_attr(isset($welcome_dashboard['banner_attachment_id']) ? absint($welcome_dashboard['banner_attachment_id']) : 0); ?>" />
                                        <input type="hidden" id="fbsa_welcome_banner_url"<?php if (!self::VIDEO_LINKS_LOCKED) : ?> name="fbsa_welcome_dashboard[banner_url]"<?php endif; ?> value="<?php echo esc_attr(isset($welcome_dashboard['banner_url']) ? $welcome_dashboard['banner_url'] : ''); ?>" />
                                        <button type="button" class="button button-primary fbsa-premium-button" data-fbsa-banner-upload<?php disabled(self::VIDEO_LINKS_LOCKED); ?>>Upload Banner</button>
                                        <button type="button" class="button fbsa-secondary-button" data-fbsa-banner-remove<?php disabled(self::VIDEO_LINKS_LOCKED); ?>>Remove Banner</button>
                                        <p>Use the WordPress Media Library. The image appears as the top Dashboard welcome banner.</p>
                                    </div>
                                </div>

                                <div class="fbsa-welcome-link-section">
                                    <div class="fbsa-settings-card__header"><div><span class="fbsa-settings-eyebrow">Video Languages</span><h3>Turkish and English Welcome Links</h3><p class="fbsa-muted">Existing single-language links are preserved in the Turkish profile. Leave either language empty to use the other language as a fallback.</p></div></div>
                                    <div class="fbsa-video-language-grid">
                                        <?php foreach ($this->get_supported_video_languages() as $language_key => $language_data) : ?>
                                            <?php $this->render_welcome_language_profile($language_key, $language_data, $welcome_dashboard); ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="fbsa-welcome-link-section">
                                    <div class="fbsa-settings-card__header"><div><span class="fbsa-settings-eyebrow">Shared Social Profiles</span><h3>Connect With Us Links</h3><p class="fbsa-muted">These non-YouTube profiles are shared by both languages.</p></div></div>
                                    <div class="fbsa-video-command-grid fbsa-welcome-link-grid">
                                        <?php $this->render_welcome_social_field('website', 'Website', $welcome_dashboard); ?>
                                        <?php $this->render_welcome_social_field('facebook', 'Facebook', $welcome_dashboard); ?>
                                        <?php $this->render_welcome_social_field('instagram', 'Instagram', $welcome_dashboard); ?>
                                        <?php $this->render_welcome_social_field('x', 'X / Twitter', $welcome_dashboard); ?>
                                    </div>
                                </div>
                            </div>
                        </details>

                        <?php if (empty($video_link_groups)) : ?>
                            <p class="fbsa-muted">No commands found yet.</p>
                        <?php else : ?>
                            <div class="fbsa-video-category-stack">
                                <?php foreach ($video_link_groups as $group_index => $group) : ?>
                                    <?php $group_count = intval($group['count']); ?>
                                    <details class="fbsa-video-category" <?php echo $group_index === 0 ? 'open' : ''; ?>>
                                        <summary class="fbsa-video-category__summary">
                                            <span>
                                                <strong><?php echo esc_html($group['label']); ?></strong>
                                                <?php if (!empty($group['description'])) : ?>
                                                    <small><?php echo esc_html($group['description']); ?></small>
                                                <?php endif; ?>
                                            </span>
                                            <em><?php echo esc_html($group_count); ?> guides</em>
                                        </summary>

                                        <div class="fbsa-video-category__body">
                                            <?php if (!empty($group['items'])) : ?>
                                                <div class="fbsa-video-command-grid">
                                                    <?php foreach ($group['items'] as $row) : ?>
                                                        <?php $this->render_video_link_field($row); ?>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($group['subgroups'])) : ?>
                                                <div class="fbsa-video-subcategory-stack">
                                                    <?php foreach ($group['subgroups'] as $subgroup_index => $subgroup) : ?>
                                                        <details class="fbsa-video-subcategory" <?php echo $subgroup_index === 0 ? 'open' : ''; ?>>
                                                            <summary class="fbsa-video-subcategory__summary">
                                                                <strong><?php echo esc_html($subgroup['label']); ?></strong>
                                                                <em><?php echo esc_html(count($subgroup['items'])); ?> guides</em>
                                                            </summary>
                                                            <div class="fbsa-video-command-grid">
                                                                <?php foreach ($subgroup['items'] as $row) : ?>
                                                                    <?php $this->render_video_link_field($row); ?>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </details>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </details>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (self::VIDEO_LINKS_LOCKED) : ?>
                            <div class="fbsa-video-links-locked" role="status">
                                <span class="dashicons dashicons-lock" aria-hidden="true"></span>
                                <div><strong>Video links are locked in this demo release.</strong><p>Administrators can view and copy the links, but they cannot edit or save replacements.</p></div>
                            </div>
                        <?php else : ?>
                            <p class="fbsa-settings-actions fbsa-video-link-actions"><button type="submit" name="fbsa_save_video_links" class="button button-primary fbsa-premium-button">Save Bilingual Video Links</button></p>
                        <?php endif; ?>
                    </form>
                </section>

                </div>

                <div id="fbsa-settings-panel-themes" class="fbsa-settings-tab-panel" role="tabpanel" aria-labelledby="fbsa-settings-tab-themes" data-fbsa-settings-panel="themes" hidden>
                <section class="fbsa-settings-card fbsa-install-themes-drawer fbsa-install-drawer">
                    <div class="fbsa-settings-card__header fbsa-drawer-header">
                        <div>
                            <span class="fbsa-settings-eyebrow">Fixed Commands</span>
                            <h2>Install Themes</h2>
                            <p class="fbsa-muted">Install, activate, deactivate, or uninstall locked theme setup tools from official WordPress.org sources.</p>
                        </div>
                    </div>

                    <div id="fbsa-install-themes-panel" class="fbsa-install-panel fbsa-install-panel--open">
                        <div class="fbsa-fixed-command-stack">
                    <section class="fbsa-settings-card fbsa-fixed-command-card">
                        <div class="fbsa-fixed-command-card__content">
                            <span class="fbsa-settings-eyebrow">Fixed Command</span>
                            <h2>Install Kadence Theme</h2>
                            <p>This command is locked into the plugin as a fixed setup action. It uses the official WordPress.org theme listing and the WordPress theme installer API, then activates Kadence when permissions allow it.</p>
                            <div class="fbsa-command-pills">
                                <span>Slug: <strong><?php echo esc_html(self::KADENCE_THEME_SLUG); ?></strong></span>
                                <span>Source: <strong>WordPress.org</strong></span>
                                <span>Status: <strong><?php echo $kadence_active ? 'Active' : ($kadence_installed ? 'Installed' : 'Not installed'); ?></strong></span>
                            </div>
                        </div>
                        <?php $this->render_fixed_theme_actions('Kadence', self::KADENCE_THEME_SLUG, $kadence_installed, $kadence_active, 'fbsa_install_kadence_theme', 'fbsa_deactivate_kadence_theme', 'fbsa_uninstall_kadence_theme', 'fbsa_install_kadence_action', self::KADENCE_THEME_OFFICIAL_URL); ?>
                    </section>

                    <section class="fbsa-settings-card fbsa-fixed-command-card">
                        <div class="fbsa-fixed-command-card__content">
                            <span class="fbsa-settings-eyebrow">Fixed Command</span>
                            <h2>Install Hello Elementor Theme</h2>
                            <p>This command is locked into the plugin as a fixed setup action. It uses the official WordPress.org theme listing and the WordPress theme installer API, then activates Hello Elementor when permissions allow it.</p>
                            <div class="fbsa-command-pills">
                                <span>Slug: <strong><?php echo esc_html(self::HELLO_ELEMENTOR_THEME_SLUG); ?></strong></span>
                                <span>Source: <strong>WordPress.org</strong></span>
                                <span>Status: <strong><?php echo $hello_elementor_active ? 'Active' : ($hello_elementor_installed ? 'Installed' : 'Not installed'); ?></strong></span>
                            </div>
                        </div>
                        <?php $this->render_fixed_theme_actions('Hello Elementor', self::HELLO_ELEMENTOR_THEME_SLUG, $hello_elementor_installed, $hello_elementor_active, 'fbsa_install_hello_elementor_theme', 'fbsa_deactivate_hello_elementor_theme', 'fbsa_uninstall_hello_elementor_theme', 'fbsa_install_hello_elementor_action', self::HELLO_ELEMENTOR_THEME_OFFICIAL_URL); ?>
                    </section>

                    <section class="fbsa-settings-card fbsa-fixed-command-card">
                        <div class="fbsa-fixed-command-card__content">
                            <span class="fbsa-settings-eyebrow">Fixed Command</span>
                            <h2>Install Astra Theme</h2>
                            <p>This command is locked into the plugin as a fixed setup action. It uses the official WordPress.org theme listing and the WordPress theme installer API, then activates Astra when permissions allow it.</p>
                            <div class="fbsa-command-pills">
                                <span>Slug: <strong><?php echo esc_html(self::ASTRA_THEME_SLUG); ?></strong></span>
                                <span>Source: <strong>WordPress.org</strong></span>
                                <span>Status: <strong><?php echo $astra_active ? 'Active' : ($astra_installed ? 'Installed' : 'Not installed'); ?></strong></span>
                            </div>
                        </div>
                        <?php $this->render_fixed_theme_actions('Astra', self::ASTRA_THEME_SLUG, $astra_installed, $astra_active, 'fbsa_install_astra_theme', 'fbsa_deactivate_astra_theme', 'fbsa_uninstall_astra_theme', 'fbsa_install_astra_action', self::ASTRA_THEME_OFFICIAL_URL); ?>
                    </section>

                    <section class="fbsa-settings-card fbsa-fixed-command-card">
                        <div class="fbsa-fixed-command-card__content">
                            <span class="fbsa-settings-eyebrow">Fixed Command</span>
                            <h2>Install OceanWP Theme</h2>
                            <p>This command is locked into the plugin as a fixed setup action. It uses the official WordPress.org theme listing and the WordPress theme installer API, then activates OceanWP when permissions allow it.</p>
                            <div class="fbsa-command-pills">
                                <span>Slug: <strong><?php echo esc_html(self::OCEANWP_THEME_SLUG); ?></strong></span>
                                <span>Source: <strong>WordPress.org</strong></span>
                                <span>Status: <strong><?php echo $oceanwp_active ? 'Active' : ($oceanwp_installed ? 'Installed' : 'Not installed'); ?></strong></span>
                            </div>
                        </div>
                        <?php $this->render_fixed_theme_actions('OceanWP', self::OCEANWP_THEME_SLUG, $oceanwp_installed, $oceanwp_active, 'fbsa_install_oceanwp_theme', 'fbsa_deactivate_oceanwp_theme', 'fbsa_uninstall_oceanwp_theme', 'fbsa_install_oceanwp_action', self::OCEANWP_THEME_OFFICIAL_URL); ?>
                    </section>

                    <section class="fbsa-settings-card fbsa-fixed-command-card">
                        <div class="fbsa-fixed-command-card__content">
                            <span class="fbsa-settings-eyebrow">Fixed Command</span>
                            <h2>Install Blocksy Theme</h2>
                            <p>This command is locked into the plugin as a fixed setup action. It uses the official WordPress.org theme listing and the WordPress theme installer API, then activates Blocksy when permissions allow it.</p>
                            <div class="fbsa-command-pills">
                                <span>Slug: <strong><?php echo esc_html(self::BLOCKSY_THEME_SLUG); ?></strong></span>
                                <span>Source: <strong>WordPress.org</strong></span>
                                <span>Status: <strong><?php echo $blocksy_active ? 'Active' : ($blocksy_installed ? 'Installed' : 'Not installed'); ?></strong></span>
                            </div>
                        </div>
                        <?php $this->render_fixed_theme_actions('Blocksy', self::BLOCKSY_THEME_SLUG, $blocksy_installed, $blocksy_active, 'fbsa_install_blocksy_theme', 'fbsa_deactivate_blocksy_theme', 'fbsa_uninstall_blocksy_theme', 'fbsa_install_blocksy_action', self::BLOCKSY_THEME_OFFICIAL_URL); ?>
                    </section>

                    <section class="fbsa-settings-card fbsa-fixed-command-card">
                        <div class="fbsa-fixed-command-card__content">
                            <span class="fbsa-settings-eyebrow">Fixed Command</span>
                            <h2>Install Zakra Theme</h2>
                            <p>This command is locked into the plugin as a fixed setup action. It uses the official WordPress.org theme listing and the WordPress theme installer API, then activates Zakra when permissions allow it.</p>
                            <div class="fbsa-command-pills">
                                <span>Slug: <strong><?php echo esc_html(self::ZAKRA_THEME_SLUG); ?></strong></span>
                                <span>Source: <strong>WordPress.org</strong></span>
                                <span>Status: <strong><?php echo $zakra_active ? 'Active' : ($zakra_installed ? 'Installed' : 'Not installed'); ?></strong></span>
                            </div>
                        </div>
                        <?php $this->render_fixed_theme_actions('Zakra', self::ZAKRA_THEME_SLUG, $zakra_installed, $zakra_active, 'fbsa_install_zakra_theme', 'fbsa_deactivate_zakra_theme', 'fbsa_uninstall_zakra_theme', 'fbsa_install_zakra_action', self::ZAKRA_THEME_OFFICIAL_URL); ?>
                    </section>
                        </div>
                    </div>
                </section>

                </div>

                <div id="fbsa-settings-panel-plugins" class="fbsa-settings-tab-panel" role="tabpanel" aria-labelledby="fbsa-settings-tab-plugins" data-fbsa-settings-panel="plugins" hidden>
                <section class="fbsa-settings-card fbsa-install-plugins-drawer fbsa-install-drawer">
                    <div class="fbsa-settings-card__header fbsa-drawer-header">
                        <div>
                            <span class="fbsa-settings-eyebrow">Fixed Commands</span>
                            <h2>Install Plugins</h2>
                            <p class="fbsa-muted">Open this drawer to install or activate the locked plugin setup tools from official WordPress.org sources.</p>
                        </div>
                    </div>

                    <div id="fbsa-install-plugins-panel" class="fbsa-install-panel fbsa-install-panel--open">
                        <div class="fbsa-fixed-command-stack">
                    <section class="fbsa-settings-card fbsa-fixed-command-card fbsa-fixed-command-card--plugin">
                        <div class="fbsa-fixed-command-card__content">
                            <span class="fbsa-settings-eyebrow">Fixed Command</span>
                            <h2>Install Elementor Plugin</h2>
                            <p>This command is locked into the plugin as a fixed setup action. It uses the official WordPress.org plugin listing and the WordPress plugin installer API, then activates Elementor Free when permissions allow it.</p>
                            <div class="fbsa-command-pills">
                                <span>Slug: <strong><?php echo esc_html(self::ELEMENTOR_PLUGIN_SLUG); ?></strong></span>
                                <span>Source: <strong>WordPress.org</strong></span>
                                <span>Status: <strong><?php echo $elementor_active ? 'Active' : ($elementor_installed ? 'Installed' : 'Not installed'); ?></strong></span>
                            </div>
                        </div>
                        <?php $this->render_fixed_plugin_actions('Elementor', self::ELEMENTOR_PLUGIN_SLUG, $elementor_installed, $elementor_active, 'fbsa_install_elementor_plugin', 'fbsa_deactivate_elementor_plugin', 'fbsa_uninstall_elementor_plugin', 'fbsa_install_elementor_action', self::ELEMENTOR_PLUGIN_OFFICIAL_URL); ?>
                    </section>


                    <section class="fbsa-settings-card fbsa-fixed-command-card fbsa-fixed-command-card--plugin">
                        <div class="fbsa-fixed-command-card__content">
                            <span class="fbsa-settings-eyebrow">Fixed Command</span>
                            <h2>Install Site Kit by Google Plugin</h2>
                            <p>This command is locked into the plugin as a fixed setup action. It uses the official WordPress.org plugin listing and the WordPress plugin installer API, then activates Site Kit by Google when permissions allow it.</p>
                            <div class="fbsa-command-pills">
                                <span>Slug: <strong><?php echo esc_html(self::SITE_KIT_PLUGIN_SLUG); ?></strong></span>
                                <span>Source: <strong>WordPress.org</strong></span>
                                <span>Status: <strong><?php echo $site_kit_active ? 'Active' : ($site_kit_installed ? 'Installed' : 'Not installed'); ?></strong></span>
                            </div>
                        </div>
                        <?php $this->render_fixed_plugin_actions('Site Kit by Google', self::SITE_KIT_PLUGIN_SLUG, $site_kit_installed, $site_kit_active, 'fbsa_install_site_kit_plugin', 'fbsa_deactivate_site_kit_plugin', 'fbsa_uninstall_site_kit_plugin', 'fbsa_install_site_kit_action', self::SITE_KIT_PLUGIN_OFFICIAL_URL); ?>
                    </section>

                    <section class="fbsa-settings-card fbsa-fixed-command-card fbsa-fixed-command-card--plugin">
                        <div class="fbsa-fixed-command-card__content">
                            <span class="fbsa-settings-eyebrow">Fixed Command</span>
                            <h2>Install FluentSMTP Plugin</h2>
                            <p>This command is locked into the plugin as a fixed setup action. It uses the official WordPress.org plugin listing and the WordPress plugin installer API, then activates FluentSMTP Free when permissions allow it.</p>
                            <div class="fbsa-command-pills">
                                <span>Slug: <strong><?php echo esc_html(self::FLUENTSMTP_PLUGIN_SLUG); ?></strong></span>
                                <span>Source: <strong>WordPress.org</strong></span>
                                <span>Status: <strong><?php echo $fluentsmtp_active ? 'Active' : ($fluentsmtp_installed ? 'Installed' : 'Not installed'); ?></strong></span>
                            </div>
                        </div>
                        <?php $this->render_fixed_plugin_actions('FluentSMTP', self::FLUENTSMTP_PLUGIN_SLUG, $fluentsmtp_installed, $fluentsmtp_active, 'fbsa_install_fluentsmtp_plugin', 'fbsa_deactivate_fluentsmtp_plugin', 'fbsa_uninstall_fluentsmtp_plugin', 'fbsa_install_fluentsmtp_action', self::FLUENTSMTP_PLUGIN_OFFICIAL_URL); ?>
                    </section>

                    <section class="fbsa-settings-card fbsa-fixed-command-card fbsa-fixed-command-card--plugin">
                        <div class="fbsa-fixed-command-card__content">
                            <span class="fbsa-settings-eyebrow">Fixed Command</span>
                            <h2>Install Yoast Duplicate Post Plugin</h2>
                            <p>This command is locked into the plugin as a fixed setup action. It uses the official WordPress.org plugin listing and the WordPress plugin installer API, then activates Yoast Duplicate Post Free when permissions allow it.</p>
                            <div class="fbsa-command-pills">
                                <span>Slug: <strong><?php echo esc_html(self::YOAST_DUPLICATE_POST_PLUGIN_SLUG); ?></strong></span>
                                <span>Source: <strong>WordPress.org</strong></span>
                                <span>Status: <strong><?php echo $duplicate_post_active ? 'Active' : ($duplicate_post_installed ? 'Installed' : 'Not installed'); ?></strong></span>
                            </div>
                        </div>
                        <?php $this->render_fixed_plugin_actions('Yoast Duplicate Post', self::YOAST_DUPLICATE_POST_PLUGIN_SLUG, $duplicate_post_installed, $duplicate_post_active, 'fbsa_install_duplicate_post_plugin', 'fbsa_deactivate_duplicate_post_plugin', 'fbsa_uninstall_duplicate_post_plugin', 'fbsa_install_duplicate_post_action', self::YOAST_DUPLICATE_POST_PLUGIN_OFFICIAL_URL); ?>
                    </section>

                    <section class="fbsa-settings-card fbsa-fixed-command-card fbsa-fixed-command-card--plugin">
                        <div class="fbsa-fixed-command-card__content">
                            <span class="fbsa-settings-eyebrow">Fixed Command</span>
                            <h2>Install Contact Form 7 Plugin</h2>
                            <p>This command is locked into the plugin as a fixed setup action. It uses the official WordPress.org plugin listing and the WordPress plugin installer API, then activates Contact Form 7 Free when permissions allow it.</p>
                            <div class="fbsa-command-pills">
                                <span>Slug: <strong><?php echo esc_html(self::CONTACT_FORM_7_PLUGIN_SLUG); ?></strong></span>
                                <span>Source: <strong>WordPress.org</strong></span>
                                <span>Status: <strong><?php echo $contact_form_7_active ? 'Active' : ($contact_form_7_installed ? 'Installed' : 'Not installed'); ?></strong></span>
                            </div>
                        </div>
                        <?php $this->render_fixed_plugin_actions('Contact Form 7', self::CONTACT_FORM_7_PLUGIN_SLUG, $contact_form_7_installed, $contact_form_7_active, 'fbsa_install_contact_form_7_plugin', 'fbsa_deactivate_contact_form_7_plugin', 'fbsa_uninstall_contact_form_7_plugin', 'fbsa_install_contact_form_7_action', self::CONTACT_FORM_7_PLUGIN_OFFICIAL_URL); ?>
                    </section>

                    <section class="fbsa-settings-card fbsa-fixed-command-card fbsa-fixed-command-card--plugin">
                        <div class="fbsa-fixed-command-card__content">
                            <span class="fbsa-settings-eyebrow">Fixed Command</span>
                            <h2>Install Loco Translate Plugin</h2>
                            <p>This command is locked into the plugin as a fixed setup action. It uses the official WordPress.org plugin listing and the WordPress plugin installer API, then activates Loco Translate Free when permissions allow it.</p>
                            <div class="fbsa-command-pills">
                                <span>Slug: <strong><?php echo esc_html(self::LOCO_TRANSLATE_PLUGIN_SLUG); ?></strong></span>
                                <span>Source: <strong>WordPress.org</strong></span>
                                <span>Status: <strong><?php echo $loco_translate_active ? 'Active' : ($loco_translate_installed ? 'Installed' : 'Not installed'); ?></strong></span>
                            </div>
                        </div>
                        <?php $this->render_fixed_plugin_actions('Loco Translate', self::LOCO_TRANSLATE_PLUGIN_SLUG, $loco_translate_installed, $loco_translate_active, 'fbsa_install_loco_translate_plugin', 'fbsa_deactivate_loco_translate_plugin', 'fbsa_uninstall_loco_translate_plugin', 'fbsa_install_loco_translate_action', self::LOCO_TRANSLATE_PLUGIN_OFFICIAL_URL); ?>
                    </section>

                    <section class="fbsa-settings-card fbsa-fixed-command-card fbsa-fixed-command-card--plugin">
                        <div class="fbsa-fixed-command-card__content">
                            <span class="fbsa-settings-eyebrow">Fixed Command</span>
                            <h2>Install LocoAI Plugin</h2>
                            <p>This command is locked into the plugin as a fixed setup action. It uses the official WordPress.org plugin listing and the WordPress plugin installer API, then activates LocoAI – Auto Translate for Loco Translate Free when permissions allow it.</p>
                            <div class="fbsa-command-pills">
                                <span>Slug: <strong><?php echo esc_html(self::LOCOAI_PLUGIN_SLUG); ?></strong></span>
                                <span>Source: <strong>WordPress.org</strong></span>
                                <span>Status: <strong><?php echo $locoai_active ? 'Active' : ($locoai_installed ? 'Installed' : 'Not installed'); ?></strong></span>
                            </div>
                        </div>
                        <?php $this->render_fixed_plugin_actions('LocoAI', self::LOCOAI_PLUGIN_SLUG, $locoai_installed, $locoai_active, 'fbsa_install_locoai_plugin', 'fbsa_deactivate_locoai_plugin', 'fbsa_uninstall_locoai_plugin', 'fbsa_install_locoai_action', self::LOCOAI_PLUGIN_OFFICIAL_URL); ?>
                    </section>

                    <section class="fbsa-settings-card fbsa-fixed-command-card fbsa-fixed-command-card--plugin">
                        <div class="fbsa-fixed-command-card__content">
                            <span class="fbsa-settings-eyebrow">Fixed Command</span>
                            <h2>Install WooCommerce Plugin</h2>
                            <p>This command is locked into the plugin as a fixed setup action. It uses the official WordPress.org plugin listing and the WordPress plugin installer API, then activates WooCommerce Free when permissions allow it.</p>
                            <div class="fbsa-command-pills">
                                <span>Slug: <strong><?php echo esc_html(self::WOOCOMMERCE_PLUGIN_SLUG); ?></strong></span>
                                <span>Source: <strong>WordPress.org</strong></span>
                                <span>Status: <strong><?php echo $woocommerce_active ? 'Active' : ($woocommerce_installed ? 'Installed' : 'Not installed'); ?></strong></span>
                            </div>
                        </div>
                        <?php $this->render_fixed_plugin_actions('WooCommerce', self::WOOCOMMERCE_PLUGIN_SLUG, $woocommerce_installed, $woocommerce_active, 'fbsa_install_woocommerce_plugin', 'fbsa_deactivate_woocommerce_plugin', 'fbsa_uninstall_woocommerce_plugin', 'fbsa_install_woocommerce_action', self::WOOCOMMERCE_PLUGIN_OFFICIAL_URL); ?>
                    </section>

                    <section class="fbsa-settings-card fbsa-fixed-command-card fbsa-fixed-command-card--plugin">
                        <div class="fbsa-fixed-command-card__content">
                            <span class="fbsa-settings-eyebrow">Fixed Command</span>
                            <h2>Install WPvivid Plugin</h2>
                            <p>This command is locked into the plugin as a fixed setup action. It uses the official WordPress.org plugin listing and the WordPress plugin installer API, then activates WPvivid Backup & Migration Free when permissions allow it.</p>
                            <div class="fbsa-command-pills">
                                <span>Slug: <strong><?php echo esc_html(self::WPVIVID_PLUGIN_SLUG); ?></strong></span>
                                <span>Source: <strong>WordPress.org</strong></span>
                                <span>Status: <strong><?php echo $wpvivid_active ? 'Active' : ($wpvivid_installed ? 'Installed' : 'Not installed'); ?></strong></span>
                            </div>
                        </div>
                        <?php $this->render_fixed_plugin_actions('WPvivid', self::WPVIVID_PLUGIN_SLUG, $wpvivid_installed, $wpvivid_active, 'fbsa_install_wpvivid_plugin', 'fbsa_deactivate_wpvivid_plugin', 'fbsa_uninstall_wpvivid_plugin', 'fbsa_install_wpvivid_action', self::WPVIVID_PLUGIN_OFFICIAL_URL); ?>
                    </section>
                        </div>
                    </div>
                </section>

                </div>

                <div id="fbsa-settings-panel-commands" class="fbsa-settings-tab-panel" role="tabpanel" aria-labelledby="fbsa-settings-tab-commands" data-fbsa-settings-panel="commands" hidden>
                <section class="fbsa-settings-card">
                    <div class="fbsa-settings-card__header">
                        <div>
                            <span class="fbsa-settings-eyebrow">Custom Widget Commands</span>
                            <h2>Add Third-Party Plugin Commands</h2>
                        </div>
                    </div>
                    <p class="fbsa-muted">Add your own commands to the floating widget. Use this for third-party plugin setup pages, plugin dashboards, or extra theme installer commands.</p>

                    <div class="fbsa-custom-command-toggle-row">
                        <button type="button" id="fbsa-custom-command-toggle" class="button button-primary fbsa-premium-button fbsa-custom-command-toggle" aria-expanded="false" aria-controls="fbsa-custom-command-panel">Add Custom Command</button>
                    </div>

                    <form method="post" action="" id="fbsa-custom-command-panel" class="fbsa-premium-form fbsa-custom-command-form fbsa-custom-command-form--collapsed" hidden>
                        <?php wp_nonce_field('fbsa_add_custom_command_action'); ?>
                        <div class="fbsa-command-form-grid">
                            <label class="fbsa-field-card" for="fbsa_command_label">
                                <span>Command Label</span>
                                <input type="text" id="fbsa_command_label" name="fbsa_command_label" placeholder="Example: FluentSMTP Settings" required />
                            </label>

                            <label class="fbsa-field-card" for="fbsa_command_category">
                                <span>Widget Category</span>
                                <select id="fbsa_command_category" name="fbsa_command_category">
                                    <?php foreach ($category_options as $category_id => $category_label) : ?>
                                        <option value="<?php echo esc_attr($category_id); ?>"><?php echo esc_html($category_label); ?></option>
                                    <?php endforeach; ?>
                                    <option value="custom_commands">Custom Commands</option>
                                </select>
                            </label>

                            <label class="fbsa-field-card" for="fbsa_command_type">
                                <span>Command Type</span>
                                <select id="fbsa_command_type" name="fbsa_command_type">
                                    <option value="navigate">Open WordPress Admin Page</option>
                                    <option value="install_theme">Install Theme from WordPress.org</option>
                                    <option value="install_plugin">Install Plugin from WordPress.org</option>
                                </select>
                            </label>

                            <label class="fbsa-field-card" for="fbsa_admin_path">
                                <span>Admin Path</span>
                                <input type="text" id="fbsa_admin_path" name="fbsa_admin_path" placeholder="plugin-install.php or options-general.php?page=example" />
                                <small>For admin commands only. Do not include your domain.</small>
                            </label>

                            <label class="fbsa-field-card" for="fbsa_theme_slug">
                                <span>Theme Slug</span>
                                <input type="text" id="fbsa_theme_slug" name="fbsa_theme_slug" placeholder="kadence" value="kadence" />
                                <small>For theme installer commands only.</small>
                            </label>

                            <label class="fbsa-field-card" for="fbsa_plugin_slug">
                                <span>Plugin Slug</span>
                                <input type="text" id="fbsa_plugin_slug" name="fbsa_plugin_slug" placeholder="elementor" value="elementor" />
                                <small>For plugin installer commands only. Use the WordPress.org plugin slug.</small>
                            </label>

                            <label class="fbsa-field-card" for="fbsa_plugin_file">
                                <span>Plugin Main File</span>
                                <input type="text" id="fbsa_plugin_file" name="fbsa_plugin_file" placeholder="elementor/elementor.php" value="elementor/elementor.php" />
                                <small>Optional but recommended for activation. Example: elementor/elementor.php</small>
                            </label>

                            <label class="fbsa-field-card fbsa-field-card--checkbox">
                                <span>Activation</span>
                                <label class="fbsa-inline-checkbox"><input type="checkbox" name="fbsa_activate_after_install" value="1" checked /> Activate after install</label>
                            </label>

                            <label class="fbsa-field-card" for="fbsa_button_label">
                                <span>Button Label</span>
                                <input type="text" id="fbsa_button_label" name="fbsa_button_label" placeholder="Open Settings / Install Theme" />
                            </label>

                            <label class="fbsa-field-card" for="fbsa_video_url">
                                <span>Guide Video URL</span>
                                <input type="url" id="fbsa_video_url" name="fbsa_video_url" placeholder="https://www.youtube.com/watch?v=..." />
                            </label>
                        </div>

                        <label class="fbsa-field-card fbsa-field-card--full" for="fbsa_command_message_input">
                            <span>Command Message</span>
                            <textarea id="fbsa_command_message_input" name="fbsa_command_message" rows="3" placeholder="Explain what this command does for the user."></textarea>
                        </label>

                        <p class="fbsa-settings-actions"><button type="submit" name="fbsa_add_custom_command" class="button button-primary fbsa-premium-button">Add Custom Command</button></p>
                    </form>
                </section>

                <section class="fbsa-settings-card">
                    <div class="fbsa-settings-card__header">
                        <div>
                            <span class="fbsa-settings-eyebrow">Saved Commands</span>
                            <h2>Current Custom Commands</h2>
                        </div>
                    </div>
                    <?php if (empty($settings['custom_commands'])) : ?>
                        <p class="fbsa-muted">No custom commands have been added yet.</p>
                    <?php else : ?>
                        <table class="widefat striped fbsa-custom-command-table">
                            <thead>
                                <tr>
                                    <th>Label</th>
                                    <th>Category</th>
                                    <th>Type</th>
                                    <th>Target</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($settings['custom_commands'] as $command) : ?>
                                    <tr>
                                        <td><?php echo esc_html($command['label']); ?></td>
                                        <td><?php echo esc_html(isset($command['categoryId']) ? $command['categoryId'] : 'custom_commands'); ?></td>
                                        <td><?php echo esc_html(isset($command['type']) ? $command['type'] : 'navigate'); ?></td>
                                        <td><?php echo esc_html($this->get_command_target_label($command)); ?></td>
                                        <td>
                                            <form method="post" action="" class="fbsa-inline-form">
                                                <?php wp_nonce_field('fbsa_delete_custom_command_action'); ?>
                                                <input type="hidden" name="fbsa_delete_command_id" value="<?php echo esc_attr($command['id']); ?>" />
                                                <button type="submit" name="fbsa_delete_custom_command" class="button button-small">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </section>

                <section class="fbsa-settings-card fbsa-settings-footer-card">
                    <div>
                        <h2>Command Source</h2>
                        <p>Built-in workspace commands are loaded from <code>workflows/workflows.json</code>. Custom widget commands are saved from this settings page and merged into the floating widget automatically.</p>
                    </div>
                    <div>
                        <h2>Current Demo Progress</h2>
                        <p><strong><?php echo esc_html($status['completed']); ?></strong> of <strong><?php echo esc_html($status['total']); ?></strong> core page tasks completed.</p>
                    </div>
                </section>
                </div>

                <div id="fbsa-confirm-modal" class="fbsa-confirm-modal" hidden aria-hidden="true">
                    <div class="fbsa-confirm-modal__backdrop" data-fbsa-confirm-cancel></div>
                    <div class="fbsa-confirm-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="fbsa-confirm-modal-title" aria-describedby="fbsa-confirm-modal-message">
                        <span class="fbsa-settings-eyebrow">Confirm Action</span>
                        <h2 id="fbsa-confirm-modal-title">Are you sure?</h2>
                        <p id="fbsa-confirm-modal-message">Please confirm this action.</p>
                        <div class="fbsa-confirm-modal__actions">
                            <button type="button" class="button fbsa-secondary-button" data-fbsa-confirm-cancel>Cancel</button>
                            <button type="button" class="button fbsa-deactivate-button" id="fbsa-confirm-modal-yes">Yes deactivate</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $fbsa_html = ob_get_clean();
        echo $this->translate_plugin_html($fbsa_html); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    private function handle_settings_postbacks() {
        if (isset($_POST['fbsa_save_settings'])) {
            check_admin_referer('fbsa_save_settings_action');
            $settings = $this->get_settings();
            $settings['level'] = isset($_POST['fbsa_level']) ? sanitize_text_field(wp_unslash($_POST['fbsa_level'])) : 'starter';
            $settings['website_type'] = isset($_POST['fbsa_website_type']) ? sanitize_text_field(wp_unslash($_POST['fbsa_website_type'])) : 'corporate';
            update_option(self::OPTION_KEY, $settings);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            echo '<div class="notice notice-success"><p>FB Software AI settings saved.</p></div>';
        }


        if (isset($_POST['fbsa_save_video_links'])) {
            check_admin_referer('fbsa_save_video_links_action');

            if (self::VIDEO_LINKS_LOCKED) {
                echo '<div class="notice notice-error"><p>Guide video links are locked in this public demo release and were not changed.</p></div>';
            } else {
                $settings = $this->get_settings();
                $video_overrides_by_language = array(
                    'tr_TR' => array(),
                    'en_US' => array(),
                );

                if (isset($_POST['fbsa_video_overrides']) && is_array($_POST['fbsa_video_overrides'])) {
                    $raw_language_overrides = wp_unslash($_POST['fbsa_video_overrides']);
                    foreach (array('tr_TR', 'en_US') as $language_key) {
                        $video_overrides_by_language[$language_key] = $this->sanitize_video_override_map(
                            isset($raw_language_overrides[$language_key]) ? $raw_language_overrides[$language_key] : array()
                        );
                    }
                }

                $settings['video_overrides_by_language'] = $video_overrides_by_language;
                // Keep the Turkish map in the legacy key for safe rollback.
                $settings['video_overrides'] = $video_overrides_by_language['tr_TR'];

                $welcome_dashboard = isset($settings['welcome_dashboard']) && is_array($settings['welcome_dashboard'])
                    ? $settings['welcome_dashboard']
                    : array();
                if (isset($_POST['fbsa_welcome_dashboard']) && is_array($_POST['fbsa_welcome_dashboard'])) {
                    $raw_welcome = wp_unslash($_POST['fbsa_welcome_dashboard']);
                    $welcome_dashboard['banner_attachment_id'] = isset($raw_welcome['banner_attachment_id']) ? absint($raw_welcome['banner_attachment_id']) : 0;
                    $welcome_dashboard['banner_url'] = isset($raw_welcome['banner_url']) ? esc_url_raw($raw_welcome['banner_url']) : '';

                    $welcome_dashboard['language_profiles'] = array(
                        'tr_TR' => array(),
                        'en_US' => array(),
                    );
                    $raw_profiles = isset($raw_welcome['language_profiles']) && is_array($raw_welcome['language_profiles'])
                        ? $raw_welcome['language_profiles']
                        : array();
                    foreach (array('tr_TR', 'en_US') as $language_key) {
                        $raw_profile = isset($raw_profiles[$language_key]) && is_array($raw_profiles[$language_key])
                            ? $raw_profiles[$language_key]
                            : array();
                        foreach (array('welcome_video_url', 'watch_youtube_url', 'subscribe_youtube_url', 'memberships_url', 'youtube_channel_url') as $field_key) {
                            $welcome_dashboard['language_profiles'][$language_key][$field_key] = isset($raw_profile[$field_key])
                                ? esc_url_raw($raw_profile[$field_key])
                                : '';
                        }
                    }

                    $social_links = isset($raw_welcome['social_links']) && is_array($raw_welcome['social_links']) ? $raw_welcome['social_links'] : array();
                    $welcome_dashboard['social_links'] = array();
                    foreach (array('website', 'facebook', 'instagram', 'x') as $social_key) {
                        $welcome_dashboard['social_links'][$social_key] = isset($social_links[$social_key]) ? esc_url_raw($social_links[$social_key]) : '';
                    }
                    $welcome_dashboard['social_links']['youtube'] = $welcome_dashboard['language_profiles']['tr_TR']['youtube_channel_url'];

                    // Preserve the Turkish profile in legacy single-language keys for rollback.
                    foreach (array('welcome_video_url', 'watch_youtube_url', 'subscribe_youtube_url', 'memberships_url') as $legacy_field) {
                        $welcome_dashboard[$legacy_field] = $welcome_dashboard['language_profiles']['tr_TR'][$legacy_field];
                    }

                    if ($welcome_dashboard['banner_attachment_id']) {
                        $attachment_url = wp_get_attachment_image_url($welcome_dashboard['banner_attachment_id'], 'full');
                        if ($attachment_url) {
                            $welcome_dashboard['banner_url'] = esc_url_raw($attachment_url);
                        }
                    }
                }
                $settings['welcome_dashboard'] = $welcome_dashboard;
                update_option(self::OPTION_KEY, $settings);
                $this->workflow_cache = null;
                $this->workflow_source_cache = null;
                echo '<div class="notice notice-success"><p>Bilingual welcome and guide video links saved.</p></div>';
            }
        }

        $this->handle_fixed_install_state_postbacks();

        if (isset($_POST['fbsa_install_kadence_theme'])) {
            check_admin_referer('fbsa_install_kadence_action');
            $command = array(
                'id' => 'install_kadence_theme',
                'label' => 'Install Kadence Theme',
                'type' => 'install_theme',
                'themeSlug' => self::KADENCE_THEME_SLUG,
                'activate' => true,
                'officialUrl' => self::KADENCE_THEME_OFFICIAL_URL,
                'fixed' => true,
            );
            $result = $this->install_theme_result($command);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        if (isset($_POST['fbsa_install_hello_elementor_theme'])) {
            check_admin_referer('fbsa_install_hello_elementor_action');
            $command = array(
                'id' => 'install_hello_elementor_theme',
                'label' => 'Install Hello Elementor Theme',
                'type' => 'install_theme',
                'themeSlug' => self::HELLO_ELEMENTOR_THEME_SLUG,
                'activate' => true,
                'officialUrl' => self::HELLO_ELEMENTOR_THEME_OFFICIAL_URL,
                'fixed' => true,
            );
            $result = $this->install_theme_result($command);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        if (isset($_POST['fbsa_install_astra_theme'])) {
            check_admin_referer('fbsa_install_astra_action');
            $command = array(
                'id' => 'install_astra_theme',
                'label' => 'Install Astra Theme',
                'type' => 'install_theme',
                'themeSlug' => self::ASTRA_THEME_SLUG,
                'activate' => true,
                'officialUrl' => self::ASTRA_THEME_OFFICIAL_URL,
                'fixed' => true,
            );
            $result = $this->install_theme_result($command);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        if (isset($_POST['fbsa_install_oceanwp_theme'])) {
            check_admin_referer('fbsa_install_oceanwp_action');
            $command = array(
                'id' => 'install_oceanwp_theme',
                'label' => 'Install OceanWP Theme',
                'type' => 'install_theme',
                'themeSlug' => self::OCEANWP_THEME_SLUG,
                'activate' => true,
                'officialUrl' => self::OCEANWP_THEME_OFFICIAL_URL,
                'fixed' => true,
            );
            $result = $this->install_theme_result($command);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        if (isset($_POST['fbsa_install_blocksy_theme'])) {
            check_admin_referer('fbsa_install_blocksy_action');
            $command = array(
                'id' => 'install_blocksy_theme',
                'label' => 'Install Blocksy Theme',
                'type' => 'install_theme',
                'themeSlug' => self::BLOCKSY_THEME_SLUG,
                'activate' => true,
                'officialUrl' => self::BLOCKSY_THEME_OFFICIAL_URL,
                'fixed' => true,
            );
            $result = $this->install_theme_result($command);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        if (isset($_POST['fbsa_install_zakra_theme'])) {
            check_admin_referer('fbsa_install_zakra_action');
            $command = array(
                'id' => 'install_zakra_theme',
                'label' => 'Install Zakra Theme',
                'type' => 'install_theme',
                'themeSlug' => self::ZAKRA_THEME_SLUG,
                'activate' => true,
                'officialUrl' => self::ZAKRA_THEME_OFFICIAL_URL,
                'fixed' => true,
            );
            $result = $this->install_theme_result($command);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        if (isset($_POST['fbsa_install_elementor_plugin'])) {
            check_admin_referer('fbsa_install_elementor_action');
            $command = array(
                'id' => 'install_elementor_plugin',
                'label' => 'Install Elementor Plugin',
                'type' => 'install_plugin',
                'pluginSlug' => self::ELEMENTOR_PLUGIN_SLUG,
                'pluginFile' => self::ELEMENTOR_PLUGIN_FILE,
                'activate' => true,
                'officialUrl' => self::ELEMENTOR_PLUGIN_OFFICIAL_URL,
                'fixed' => true,
            );
            $result = $this->install_plugin_result($command);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }


        if (isset($_POST['fbsa_install_site_kit_plugin'])) {
            check_admin_referer('fbsa_install_site_kit_action');
            $command = array(
                'id' => 'install_site_kit_plugin',
                'label' => 'Install Site Kit by Google Plugin',
                'type' => 'install_plugin',
                'pluginSlug' => self::SITE_KIT_PLUGIN_SLUG,
                'pluginFile' => self::SITE_KIT_PLUGIN_FILE,
                'activate' => true,
                'officialUrl' => self::SITE_KIT_PLUGIN_OFFICIAL_URL,
                'fixed' => true,
            );
            $result = $this->install_plugin_result($command);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        if (isset($_POST['fbsa_install_fluentsmtp_plugin'])) {
            check_admin_referer('fbsa_install_fluentsmtp_action');
            $command = array(
                'id' => 'install_fluentsmtp_plugin',
                'label' => 'Install FluentSMTP Plugin',
                'type' => 'install_plugin',
                'pluginSlug' => self::FLUENTSMTP_PLUGIN_SLUG,
                'pluginFile' => self::FLUENTSMTP_PLUGIN_FILE,
                'activate' => true,
                'officialUrl' => self::FLUENTSMTP_PLUGIN_OFFICIAL_URL,
                'fixed' => true,
            );
            $result = $this->install_plugin_result($command);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        if (isset($_POST['fbsa_install_duplicate_post_plugin'])) {
            check_admin_referer('fbsa_install_duplicate_post_action');
            $command = array(
                'id' => 'install_duplicate_post_plugin',
                'label' => 'Install Yoast Duplicate Post Plugin',
                'type' => 'install_plugin',
                'pluginSlug' => self::YOAST_DUPLICATE_POST_PLUGIN_SLUG,
                'pluginFile' => self::YOAST_DUPLICATE_POST_PLUGIN_FILE,
                'activate' => true,
                'officialUrl' => self::YOAST_DUPLICATE_POST_PLUGIN_OFFICIAL_URL,
                'fixed' => true,
            );
            $result = $this->install_plugin_result($command);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        if (isset($_POST['fbsa_install_contact_form_7_plugin'])) {
            check_admin_referer('fbsa_install_contact_form_7_action');
            $command = array(
                'id' => 'install_contact_form_7_plugin',
                'label' => 'Install Contact Form 7 Plugin',
                'type' => 'install_plugin',
                'pluginSlug' => self::CONTACT_FORM_7_PLUGIN_SLUG,
                'pluginFile' => self::CONTACT_FORM_7_PLUGIN_FILE,
                'activate' => true,
                'officialUrl' => self::CONTACT_FORM_7_PLUGIN_OFFICIAL_URL,
                'fixed' => true,
            );
            $result = $this->install_plugin_result($command);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        if (isset($_POST['fbsa_install_loco_translate_plugin'])) {
            check_admin_referer('fbsa_install_loco_translate_action');
            $command = array(
                'id' => 'install_loco_translate_plugin',
                'label' => 'Install Loco Translate Plugin',
                'type' => 'install_plugin',
                'pluginSlug' => self::LOCO_TRANSLATE_PLUGIN_SLUG,
                'pluginFile' => self::LOCO_TRANSLATE_PLUGIN_FILE,
                'activate' => true,
                'officialUrl' => self::LOCO_TRANSLATE_PLUGIN_OFFICIAL_URL,
                'fixed' => true,
            );
            $result = $this->install_plugin_result($command);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        if (isset($_POST['fbsa_install_locoai_plugin'])) {
            check_admin_referer('fbsa_install_locoai_action');
            $command = array(
                'id' => 'install_locoai_plugin',
                'label' => 'Install LocoAI Plugin',
                'type' => 'install_plugin',
                'pluginSlug' => self::LOCOAI_PLUGIN_SLUG,
                'pluginFile' => self::LOCOAI_PLUGIN_FILE,
                'activate' => true,
                'officialUrl' => self::LOCOAI_PLUGIN_OFFICIAL_URL,
                'fixed' => true,
            );
            $result = $this->install_plugin_result($command);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        if (isset($_POST['fbsa_install_woocommerce_plugin'])) {
            check_admin_referer('fbsa_install_woocommerce_action');
            $command = array(
                'id' => 'install_woocommerce_plugin',
                'label' => 'Install WooCommerce Plugin',
                'type' => 'install_plugin',
                'pluginSlug' => self::WOOCOMMERCE_PLUGIN_SLUG,
                'pluginFile' => self::WOOCOMMERCE_PLUGIN_FILE,
                'activate' => true,
                'officialUrl' => self::WOOCOMMERCE_PLUGIN_OFFICIAL_URL,
                'fixed' => true,
            );
            $result = $this->install_plugin_result($command);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        if (isset($_POST['fbsa_install_wpvivid_plugin'])) {
            check_admin_referer('fbsa_install_wpvivid_action');
            $command = array(
                'id' => 'install_wpvivid_plugin',
                'label' => 'Install WPvivid Plugin',
                'type' => 'install_plugin',
                'pluginSlug' => self::WPVIVID_PLUGIN_SLUG,
                'pluginFile' => self::WPVIVID_PLUGIN_FILE,
                'activate' => true,
                'officialUrl' => self::WPVIVID_PLUGIN_OFFICIAL_URL,
                'fixed' => true,
            );
            $result = $this->install_plugin_result($command);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            if (is_wp_error($result)) {
                echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            } else {
                echo '<div class="notice notice-success"><p>' . esc_html($result['message']) . '</p></div>';
            }
        }

        if (isset($_POST['fbsa_add_custom_command'])) {
            check_admin_referer('fbsa_add_custom_command_action');
            $settings = $this->get_settings();
            $command = $this->build_custom_command_from_post();

            if (is_wp_error($command)) {
                echo '<div class="notice notice-error"><p>' . esc_html($command->get_error_message()) . '</p></div>';
            } else {
                $settings['custom_commands'][] = $command;
                update_option(self::OPTION_KEY, $settings);
                $this->workflow_cache = null;
                $this->workflow_source_cache = null;
                echo '<div class="notice notice-success"><p>Custom command added to the floating widget.</p></div>';
            }
        }

        if (isset($_POST['fbsa_delete_custom_command'])) {
            check_admin_referer('fbsa_delete_custom_command_action');
            $settings = $this->get_settings();
            $delete_id = isset($_POST['fbsa_delete_command_id']) ? sanitize_key(wp_unslash($_POST['fbsa_delete_command_id'])) : '';
            $settings['custom_commands'] = array_values(array_filter($settings['custom_commands'], function($command) use ($delete_id) {
                return isset($command['id']) && $command['id'] !== $delete_id;
            }));
            update_option(self::OPTION_KEY, $settings);
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
            echo '<div class="notice notice-success"><p>Custom command deleted.</p></div>';
        }
    }


    private function handle_fixed_install_state_postbacks() {
        $plugins = array(
            array('label' => 'Elementor', 'slug' => self::ELEMENTOR_PLUGIN_SLUG, 'file' => self::ELEMENTOR_PLUGIN_FILE, 'nonce' => 'fbsa_install_elementor_action', 'deactivate' => 'fbsa_deactivate_elementor_plugin', 'uninstall' => 'fbsa_uninstall_elementor_plugin'),
            array('label' => 'Site Kit by Google', 'slug' => self::SITE_KIT_PLUGIN_SLUG, 'file' => self::SITE_KIT_PLUGIN_FILE, 'nonce' => 'fbsa_install_site_kit_action', 'deactivate' => 'fbsa_deactivate_site_kit_plugin', 'uninstall' => 'fbsa_uninstall_site_kit_plugin'),
            array('label' => 'FluentSMTP', 'slug' => self::FLUENTSMTP_PLUGIN_SLUG, 'file' => self::FLUENTSMTP_PLUGIN_FILE, 'nonce' => 'fbsa_install_fluentsmtp_action', 'deactivate' => 'fbsa_deactivate_fluentsmtp_plugin', 'uninstall' => 'fbsa_uninstall_fluentsmtp_plugin'),
            array('label' => 'Yoast Duplicate Post', 'slug' => self::YOAST_DUPLICATE_POST_PLUGIN_SLUG, 'file' => self::YOAST_DUPLICATE_POST_PLUGIN_FILE, 'nonce' => 'fbsa_install_duplicate_post_action', 'deactivate' => 'fbsa_deactivate_duplicate_post_plugin', 'uninstall' => 'fbsa_uninstall_duplicate_post_plugin'),
            array('label' => 'Contact Form 7', 'slug' => self::CONTACT_FORM_7_PLUGIN_SLUG, 'file' => self::CONTACT_FORM_7_PLUGIN_FILE, 'nonce' => 'fbsa_install_contact_form_7_action', 'deactivate' => 'fbsa_deactivate_contact_form_7_plugin', 'uninstall' => 'fbsa_uninstall_contact_form_7_plugin'),
            array('label' => 'Loco Translate', 'slug' => self::LOCO_TRANSLATE_PLUGIN_SLUG, 'file' => self::LOCO_TRANSLATE_PLUGIN_FILE, 'nonce' => 'fbsa_install_loco_translate_action', 'deactivate' => 'fbsa_deactivate_loco_translate_plugin', 'uninstall' => 'fbsa_uninstall_loco_translate_plugin'),
            array('label' => 'LocoAI', 'slug' => self::LOCOAI_PLUGIN_SLUG, 'file' => self::LOCOAI_PLUGIN_FILE, 'nonce' => 'fbsa_install_locoai_action', 'deactivate' => 'fbsa_deactivate_locoai_plugin', 'uninstall' => 'fbsa_uninstall_locoai_plugin'),
            array('label' => 'WooCommerce', 'slug' => self::WOOCOMMERCE_PLUGIN_SLUG, 'file' => self::WOOCOMMERCE_PLUGIN_FILE, 'nonce' => 'fbsa_install_woocommerce_action', 'deactivate' => 'fbsa_deactivate_woocommerce_plugin', 'uninstall' => 'fbsa_uninstall_woocommerce_plugin'),
            array('label' => 'WPvivid', 'slug' => self::WPVIVID_PLUGIN_SLUG, 'file' => self::WPVIVID_PLUGIN_FILE, 'nonce' => 'fbsa_install_wpvivid_action', 'deactivate' => 'fbsa_deactivate_wpvivid_plugin', 'uninstall' => 'fbsa_uninstall_wpvivid_plugin'),
        );

        foreach ($plugins as $plugin) {
            if (isset($_POST[$plugin['deactivate']])) {
                check_admin_referer($plugin['nonce']);
                $this->print_admin_result_notice($this->deactivate_plugin_result($plugin['slug'], $plugin['file'], $plugin['label']));
                $this->workflow_cache = null;
                $this->workflow_source_cache = null;
            }

            if (isset($_POST[$plugin['uninstall']])) {
                check_admin_referer($plugin['nonce']);
                $this->print_admin_result_notice($this->uninstall_plugin_result($plugin['slug'], $plugin['file'], $plugin['label']));
                $this->workflow_cache = null;
                $this->workflow_source_cache = null;
            }
        }

        if (isset($_POST['fbsa_deactivate_kadence_theme'])) {
            check_admin_referer('fbsa_install_kadence_action');
            $this->print_admin_result_notice($this->deactivate_theme_result(self::KADENCE_THEME_SLUG, 'Kadence'));
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
        }

        if (isset($_POST['fbsa_uninstall_kadence_theme'])) {
            check_admin_referer('fbsa_install_kadence_action');
            $this->print_admin_result_notice($this->uninstall_theme_result(self::KADENCE_THEME_SLUG, 'Kadence'));
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
        }

        if (isset($_POST['fbsa_deactivate_hello_elementor_theme'])) {
            check_admin_referer('fbsa_install_hello_elementor_action');
            $this->print_admin_result_notice($this->deactivate_theme_result(self::HELLO_ELEMENTOR_THEME_SLUG, 'Hello Elementor'));
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
        }

        if (isset($_POST['fbsa_uninstall_hello_elementor_theme'])) {
            check_admin_referer('fbsa_install_hello_elementor_action');
            $this->print_admin_result_notice($this->uninstall_theme_result(self::HELLO_ELEMENTOR_THEME_SLUG, 'Hello Elementor'));
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
        }

        if (isset($_POST['fbsa_deactivate_astra_theme'])) {
            check_admin_referer('fbsa_install_astra_action');
            $this->print_admin_result_notice($this->deactivate_theme_result(self::ASTRA_THEME_SLUG, 'Astra'));
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
        }

        if (isset($_POST['fbsa_uninstall_astra_theme'])) {
            check_admin_referer('fbsa_install_astra_action');
            $this->print_admin_result_notice($this->uninstall_theme_result(self::ASTRA_THEME_SLUG, 'Astra'));
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
        }

        if (isset($_POST['fbsa_deactivate_oceanwp_theme'])) {
            check_admin_referer('fbsa_install_oceanwp_action');
            $this->print_admin_result_notice($this->deactivate_theme_result(self::OCEANWP_THEME_SLUG, 'OceanWP'));
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
        }

        if (isset($_POST['fbsa_uninstall_oceanwp_theme'])) {
            check_admin_referer('fbsa_install_oceanwp_action');
            $this->print_admin_result_notice($this->uninstall_theme_result(self::OCEANWP_THEME_SLUG, 'OceanWP'));
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
        }

        if (isset($_POST['fbsa_deactivate_blocksy_theme'])) {
            check_admin_referer('fbsa_install_blocksy_action');
            $this->print_admin_result_notice($this->deactivate_theme_result(self::BLOCKSY_THEME_SLUG, 'Blocksy'));
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
        }

        if (isset($_POST['fbsa_uninstall_blocksy_theme'])) {
            check_admin_referer('fbsa_install_blocksy_action');
            $this->print_admin_result_notice($this->uninstall_theme_result(self::BLOCKSY_THEME_SLUG, 'Blocksy'));
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
        }

        if (isset($_POST['fbsa_deactivate_zakra_theme'])) {
            check_admin_referer('fbsa_install_zakra_action');
            $this->print_admin_result_notice($this->deactivate_theme_result(self::ZAKRA_THEME_SLUG, 'Zakra'));
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
        }

        if (isset($_POST['fbsa_uninstall_zakra_theme'])) {
            check_admin_referer('fbsa_install_zakra_action');
            $this->print_admin_result_notice($this->uninstall_theme_result(self::ZAKRA_THEME_SLUG, 'Zakra'));
            $this->workflow_cache = null;
            $this->workflow_source_cache = null;
        }
    }

    private function print_admin_result_notice($result) {
        if (is_wp_error($result)) {
            echo '<div class="notice notice-error"><p>' . esc_html($result->get_error_message()) . '</p></div>';
            return;
        }

        $message = is_array($result) && isset($result['message']) ? $result['message'] : 'Action completed.';
        echo '<div class="notice notice-success"><p>' . esc_html($message) . '</p></div>';
    }

    private function render_fixed_plugin_actions($label, $plugin_slug, $installed, $active, $install_name, $deactivate_name, $uninstall_name, $nonce_action, $official_url) {
        ?>
        <div class="fbsa-fixed-command-card__actions">
            <form method="post" action="" class="fbsa-card-action-form">
                <?php wp_nonce_field($nonce_action); ?>
                <?php if ($active) : ?>
                    <button type="button" class="button fbsa-installed-button" disabled aria-disabled="true">✓ Installed</button>
                    <button type="submit" name="<?php echo esc_attr($deactivate_name); ?>" class="button fbsa-deactivate-button">Deactivate <?php echo esc_html($label); ?></button>
                <?php elseif ($installed) : ?>
                    <button type="submit" name="<?php echo esc_attr($install_name); ?>" class="button button-primary fbsa-premium-button">Activate <?php echo esc_html($label); ?></button>
                    <button type="submit" name="<?php echo esc_attr($uninstall_name); ?>" class="button fbsa-uninstall-button" data-fbsa-confirm="Uninstall <?php echo esc_attr($label); ?> from this WordPress site?">Uninstall <?php echo esc_html($label); ?></button>
                <?php else : ?>
                    <button type="submit" name="<?php echo esc_attr($install_name); ?>" class="button button-primary fbsa-premium-button">Install / Activate <?php echo esc_html($label); ?></button>
                <?php endif; ?>
            </form>
            <a class="button fbsa-secondary-button" href="<?php echo esc_url($official_url); ?>" target="_blank" rel="noopener">View on WordPress.org</a>
        </div>
        <?php
    }

    private function render_fixed_theme_actions($label, $theme_slug, $installed, $active, $install_name, $deactivate_name, $uninstall_name, $nonce_action, $official_url) {
        ?>
        <div class="fbsa-fixed-command-card__actions">
            <form method="post" action="" class="fbsa-card-action-form">
                <?php wp_nonce_field($nonce_action); ?>
                <?php if ($active) : ?>
                    <button type="button" class="button fbsa-installed-button" disabled aria-disabled="true">✓ Installed</button>
                    <button type="submit" name="<?php echo esc_attr($deactivate_name); ?>" class="button fbsa-deactivate-button fbsa-theme-deactivate-button" data-fbsa-confirm="Are you sure you want to deactivate the <?php echo esc_attr($label); ?> theme? WordPress will switch to another installed theme." data-fbsa-confirm-action="Yes deactivate">Deactivate <?php echo esc_html($label); ?></button>
                <?php elseif ($installed) : ?>
                    <button type="submit" name="<?php echo esc_attr($install_name); ?>" class="button button-primary fbsa-premium-button">Activate <?php echo esc_html($label); ?></button>
                    <button type="submit" name="<?php echo esc_attr($uninstall_name); ?>" class="button fbsa-uninstall-button" data-fbsa-confirm="Uninstall the <?php echo esc_attr($label); ?> theme from this WordPress site?">Uninstall <?php echo esc_html($label); ?></button>
                <?php else : ?>
                    <button type="submit" name="<?php echo esc_attr($install_name); ?>" class="button button-primary fbsa-premium-button">Install / Activate <?php echo esc_html($label); ?></button>
                <?php endif; ?>
            </form>
            <a class="button fbsa-secondary-button" href="<?php echo esc_url($official_url); ?>" target="_blank" rel="noopener">View on WordPress.org</a>
        </div>
        <?php
    }

    private function build_custom_command_from_post() {
        $label = isset($_POST['fbsa_command_label']) ? sanitize_text_field(wp_unslash($_POST['fbsa_command_label'])) : '';
        $category_id = isset($_POST['fbsa_command_category']) ? sanitize_key(wp_unslash($_POST['fbsa_command_category'])) : 'custom_commands';
        $type = isset($_POST['fbsa_command_type']) ? sanitize_key(wp_unslash($_POST['fbsa_command_type'])) : 'navigate';
        $button_label = isset($_POST['fbsa_button_label']) ? sanitize_text_field(wp_unslash($_POST['fbsa_button_label'])) : '';
        $video_url = isset($_POST['fbsa_video_url']) ? esc_url_raw(wp_unslash($_POST['fbsa_video_url'])) : '';
        $message = isset($_POST['fbsa_command_message']) ? sanitize_textarea_field(wp_unslash($_POST['fbsa_command_message'])) : '';

        if ($label === '') {
            return new WP_Error('fbsa_missing_label', 'Please enter a command label.');
        }

        if (!in_array($type, array('navigate', 'install_theme', 'install_plugin'), true)) {
            return new WP_Error('fbsa_invalid_command_type', 'Please choose a valid command type.');
        }

        $id_base = sanitize_key(str_replace('-', '_', sanitize_title($label)));
        if ($id_base === '') {
            $id_base = 'custom_command';
        }

        $command = array(
            'id' => 'custom_' . $id_base . '_' . time(),
            'label' => $label,
            'categoryId' => $category_id ? $category_id : 'custom_commands',
            'type' => $type,
            'buttonLabel' => $button_label ? $button_label : ($type === 'install_theme' ? 'Install Theme' : ($type === 'install_plugin' ? 'Install Plugin' : 'Open')),
            'message' => $message ? $message : 'Custom command added from FB Software AI settings.',
            'videoUrl' => $video_url,
            'custom' => true,
        );

        if ($type === 'navigate') {
            $admin_path = isset($_POST['fbsa_admin_path']) ? sanitize_text_field(wp_unslash($_POST['fbsa_admin_path'])) : '';
            $admin_path = ltrim($admin_path, '/');
            if ($admin_path === '') {
                return new WP_Error('fbsa_missing_admin_path', 'Please enter an admin path for this navigation command.');
            }
            if (preg_match('#^https?://#i', $admin_path)) {
                return new WP_Error('fbsa_external_admin_path', 'Please enter only the WordPress admin path, not a full external URL.');
            }
            $command['adminPath'] = $admin_path;
        }

        if ($type === 'install_theme') {
            $theme_slug = isset($_POST['fbsa_theme_slug']) ? sanitize_key(wp_unslash($_POST['fbsa_theme_slug'])) : '';
            if ($theme_slug === '') {
                return new WP_Error('fbsa_missing_theme_slug', 'Please enter a WordPress.org theme slug.');
            }
            $command['themeSlug'] = $theme_slug;
            $command['activate'] = !empty($_POST['fbsa_activate_after_install']);
            $command['hideWhenInstalled'] = true;
        }

        if ($type === 'install_plugin') {
            $plugin_slug = isset($_POST['fbsa_plugin_slug']) ? sanitize_key(wp_unslash($_POST['fbsa_plugin_slug'])) : '';
            $plugin_file = isset($_POST['fbsa_plugin_file']) ? sanitize_text_field(wp_unslash($_POST['fbsa_plugin_file'])) : '';
            $plugin_file = ltrim($plugin_file, '/');
            if ($plugin_slug === '') {
                return new WP_Error('fbsa_missing_plugin_slug', 'Please enter a WordPress.org plugin slug.');
            }
            $command['pluginSlug'] = $plugin_slug;
            $command['pluginFile'] = $plugin_file ? $plugin_file : $plugin_slug . '/' . $plugin_slug . '.php';
            $command['activate'] = !empty($_POST['fbsa_activate_after_install']);
            $command['hideWhenInstalled'] = true;
        }

        return $command;
    }

    private function get_supported_video_languages() {
        return array(
            'tr_TR' => array(
                'label' => __('Turkish', 'fb-software-ai'),
                'short_label' => 'TR',
            ),
            'en_US' => array(
                'label' => __('English', 'fb-software-ai'),
                'short_label' => 'EN',
            ),
        );
    }

    private function get_current_video_language() {
        $locale = function_exists('get_user_locale') ? get_user_locale() : determine_locale();
        $locale = is_string($locale) ? strtolower($locale) : '';

        return strpos($locale, 'tr') === 0 ? 'tr_TR' : 'en_US';
    }

    private function get_video_language_fallback_order($primary_language = '') {
        $primary_language = in_array($primary_language, array('tr_TR', 'en_US'), true)
            ? $primary_language
            : $this->get_current_video_language();

        return $primary_language === 'tr_TR'
            ? array('tr_TR', 'en_US')
            : array('en_US', 'tr_TR');
    }

    private function sanitize_video_override_map($raw_overrides) {
        $clean = array();
        if (!is_array($raw_overrides)) {
            return $clean;
        }

        foreach ($raw_overrides as $raw_id => $raw_url) {
            if (is_array($raw_url)) {
                continue;
            }
            $command_id = sanitize_key($raw_id);
            $video_url = esc_url_raw((string) $raw_url);
            if ($command_id !== '' && $video_url !== '') {
                $clean[$command_id] = $video_url;
            }
        }

        return $clean;
    }

    private function get_settings() {
        $language_profile_defaults = array(
            'welcome_video_url' => '',
            'watch_youtube_url' => '',
            'subscribe_youtube_url' => '',
            'memberships_url' => '',
            'youtube_channel_url' => '',
        );
        $welcome_defaults = array(
            'banner_attachment_id' => 0,
            'banner_url' => '',
            // Legacy single-language keys are retained for safe rollback and migration.
            'welcome_video_url' => '',
            'watch_youtube_url' => '',
            'subscribe_youtube_url' => '',
            'memberships_url' => '',
            'language_profiles' => array(
                'tr_TR' => $language_profile_defaults,
                'en_US' => $language_profile_defaults,
            ),
            'social_links' => array(
                'youtube' => '',
                'website' => 'https://fbsoftwaresolutions.com',
                'facebook' => '',
                'instagram' => '',
                'x' => '',
            ),
        );
        $defaults = array(
            'level' => 'starter',
            'website_type' => 'corporate',
            'custom_commands' => array(),
            // Legacy flat map remains available for rollback to older releases.
            'video_overrides' => array(),
            'video_overrides_by_language' => array(
                'tr_TR' => array(),
                'en_US' => array(),
            ),
            'welcome_dashboard' => $welcome_defaults,
        );
        $saved = get_option(self::OPTION_KEY, array());
        $settings = wp_parse_args(is_array($saved) ? $saved : array(), $defaults);
        $settings['custom_commands'] = is_array($settings['custom_commands']) ? $settings['custom_commands'] : array();
        $settings['video_overrides'] = $this->sanitize_video_override_map($settings['video_overrides']);

        $saved_language_overrides = isset($settings['video_overrides_by_language']) && is_array($settings['video_overrides_by_language'])
            ? $settings['video_overrides_by_language']
            : array();
        $settings['video_overrides_by_language'] = array(
            'tr_TR' => $this->sanitize_video_override_map(isset($saved_language_overrides['tr_TR']) ? $saved_language_overrides['tr_TR'] : array()),
            'en_US' => $this->sanitize_video_override_map(isset($saved_language_overrides['en_US']) ? $saved_language_overrides['en_US'] : array()),
        );

        // Existing single-language command links become Turkish links at runtime.
        if (empty($settings['video_overrides_by_language']['tr_TR']) && !empty($settings['video_overrides'])) {
            $settings['video_overrides_by_language']['tr_TR'] = $settings['video_overrides'];
        }

        $settings['welcome_dashboard'] = wp_parse_args(
            isset($settings['welcome_dashboard']) && is_array($settings['welcome_dashboard']) ? $settings['welcome_dashboard'] : array(),
            $welcome_defaults
        );
        $settings['welcome_dashboard']['social_links'] = wp_parse_args(
            isset($settings['welcome_dashboard']['social_links']) && is_array($settings['welcome_dashboard']['social_links']) ? $settings['welcome_dashboard']['social_links'] : array(),
            $welcome_defaults['social_links']
        );

        $saved_profiles = isset($settings['welcome_dashboard']['language_profiles']) && is_array($settings['welcome_dashboard']['language_profiles'])
            ? $settings['welcome_dashboard']['language_profiles']
            : array();
        $settings['welcome_dashboard']['language_profiles'] = array();
        foreach (array('tr_TR', 'en_US') as $language_key) {
            $raw_profile = isset($saved_profiles[$language_key]) && is_array($saved_profiles[$language_key])
                ? $saved_profiles[$language_key]
                : array();
            $profile = wp_parse_args($raw_profile, $language_profile_defaults);
            foreach (array_keys($language_profile_defaults) as $field_key) {
                $profile[$field_key] = esc_url_raw(isset($profile[$field_key]) ? $profile[$field_key] : '');
            }
            $settings['welcome_dashboard']['language_profiles'][$language_key] = $profile;
        }

        // Existing welcome and YouTube values are migrated into the Turkish profile.
        $legacy_turkish_profile = array(
            'welcome_video_url' => isset($settings['welcome_dashboard']['welcome_video_url']) ? $settings['welcome_dashboard']['welcome_video_url'] : '',
            'watch_youtube_url' => isset($settings['welcome_dashboard']['watch_youtube_url']) ? $settings['welcome_dashboard']['watch_youtube_url'] : '',
            'subscribe_youtube_url' => isset($settings['welcome_dashboard']['subscribe_youtube_url']) ? $settings['welcome_dashboard']['subscribe_youtube_url'] : '',
            'memberships_url' => isset($settings['welcome_dashboard']['memberships_url']) ? $settings['welcome_dashboard']['memberships_url'] : '',
            'youtube_channel_url' => isset($settings['welcome_dashboard']['social_links']['youtube']) ? $settings['welcome_dashboard']['social_links']['youtube'] : '',
        );
        foreach ($legacy_turkish_profile as $field_key => $legacy_value) {
            if ($settings['welcome_dashboard']['language_profiles']['tr_TR'][$field_key] === '' && $legacy_value !== '') {
                $settings['welcome_dashboard']['language_profiles']['tr_TR'][$field_key] = esc_url_raw($legacy_value);
            }
        }

        return $settings;
    }

    private function select_localized_video_value($values, $primary_language = '') {
        $values = is_array($values) ? $values : array();
        $order = $this->get_video_language_fallback_order($primary_language);
        $primary = reset($order);

        foreach ($order as $language_key) {
            $url = isset($values[$language_key]) ? esc_url_raw($values[$language_key]) : '';
            if ($url !== '') {
                return array(
                    'url' => $url,
                    'language' => $language_key,
                    'is_fallback' => $language_key !== $primary,
                );
            }
        }

        return array(
            'url' => '',
            'language' => $primary,
            'is_fallback' => false,
        );
    }

    private function get_command_video_profiles($command, $settings = null) {
        $settings = is_array($settings) ? $settings : $this->get_settings();
        $command_id = !empty($command['id']) ? sanitize_key($command['id']) : '';
        $bundled_turkish_url = !empty($command['videoUrl']) ? esc_url_raw($command['videoUrl']) : '';
        $legacy_overrides = isset($settings['video_overrides']) && is_array($settings['video_overrides']) ? $settings['video_overrides'] : array();
        $language_overrides = isset($settings['video_overrides_by_language']) && is_array($settings['video_overrides_by_language'])
            ? $settings['video_overrides_by_language']
            : array();

        $turkish_url = $command_id !== '' && !empty($language_overrides['tr_TR'][$command_id])
            ? esc_url_raw($language_overrides['tr_TR'][$command_id])
            : ($command_id !== '' && !empty($legacy_overrides[$command_id]) ? esc_url_raw($legacy_overrides[$command_id]) : $bundled_turkish_url);
        $english_url = $command_id !== '' && !empty($language_overrides['en_US'][$command_id])
            ? esc_url_raw($language_overrides['en_US'][$command_id])
            : '';

        return array(
            'tr_TR' => $turkish_url,
            'en_US' => $english_url,
        );
    }

    private function get_localized_welcome_dashboard($welcome_dashboard) {
        $welcome_dashboard = is_array($welcome_dashboard) ? $welcome_dashboard : array();
        $profiles = isset($welcome_dashboard['language_profiles']) && is_array($welcome_dashboard['language_profiles'])
            ? $welcome_dashboard['language_profiles']
            : array();
        $primary_language = $this->get_current_video_language();
        $localized = $welcome_dashboard;
        $fallback_used = false;

        foreach (array('welcome_video_url', 'watch_youtube_url', 'subscribe_youtube_url', 'memberships_url', 'youtube_channel_url') as $field_key) {
            $values = array(
                'tr_TR' => isset($profiles['tr_TR'][$field_key]) ? $profiles['tr_TR'][$field_key] : '',
                'en_US' => isset($profiles['en_US'][$field_key]) ? $profiles['en_US'][$field_key] : '',
            );
            $selected = $this->select_localized_video_value($values, $primary_language);
            $localized[$field_key] = $selected['url'];
            $fallback_used = $fallback_used || $selected['is_fallback'];
        }

        $localized['social_links'] = isset($welcome_dashboard['social_links']) && is_array($welcome_dashboard['social_links'])
            ? $welcome_dashboard['social_links']
            : array();
        $localized['social_links']['youtube'] = isset($localized['youtube_channel_url']) ? $localized['youtube_channel_url'] : '';
        $localized['active_video_language'] = $primary_language;
        $localized['video_fallback_used'] = $fallback_used;

        return $localized;
    }

    private function get_workflow_source_data() {
        if ($this->workflow_source_cache !== null) {
            return $this->workflow_source_cache;
        }

        $path = plugin_dir_path(__FILE__) . 'workflows/workflows.json';
        $data = array('version' => self::VERSION, 'categories' => array());
        $this->workflow_error = '';

        if (!file_exists($path)) {
            $this->workflow_error = __('The bundled workflow file is missing. Reinstall the plugin package to restore the default commands.', 'fb-software-ai');
        } else {
            $raw = file_get_contents($path);
            if ($raw === false) {
                $this->workflow_error = __('The bundled workflow file could not be read. Reinstall the plugin package to restore the default commands.', 'fb-software-ai');
            } else {
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $data = $decoded;
                } else {
                    $this->workflow_error = __('The bundled workflow file is invalid. Reinstall the plugin package to restore the default commands.', 'fb-software-ai');
                }
            }
        }

        if (empty($data['categories']) || !is_array($data['categories'])) {
            $data['categories'] = array();
            if ($this->workflow_error === '') {
                $this->workflow_error = __('No default workflow categories were found in the bundled workflow file. Reinstall the plugin package to restore the default commands.', 'fb-software-ai');
            }
        }

        $data = $this->merge_custom_commands($data);
        $data['version'] = self::VERSION;
        $this->workflow_source_cache = $data;

        return $data;
    }

    private function get_workflow_data() {
        if ($this->workflow_cache !== null) {
            return $this->workflow_cache;
        }

        $data = $this->get_workflow_source_data();
        $data = $this->apply_video_overrides($data);
        $data['version'] = self::VERSION;

        $this->workflow_cache = $data;
        return $data;
    }

    private function get_workflow_error_message() {
        return is_string($this->workflow_error) ? $this->workflow_error : '';
    }

    private function merge_custom_commands($data) {
        $settings = $this->get_settings();
        if (empty($settings['custom_commands'])) {
            return $data;
        }

        foreach ($settings['custom_commands'] as $custom_command) {
            if (empty($custom_command['id']) || empty($custom_command['label'])) {
                continue;
            }

            $category_id = !empty($custom_command['categoryId']) ? sanitize_key($custom_command['categoryId']) : 'custom_commands';
            $category_index = $this->find_category_index($data['categories'], $category_id);

            if ($category_index === null) {
                $data['categories'][] = array(
                    'id' => $category_id,
                    'label' => $category_id === 'custom_commands' ? 'Custom Commands' : ucwords(str_replace('_', ' ', $category_id)),
                    'description' => 'Custom commands added from the FB Software AI settings page.',
                    'commands' => array(),
                );
                $category_index = count($data['categories']) - 1;
            }

            $data['categories'][$category_index]['commands'][] = $custom_command;
        }

        return $data;
    }

    private function apply_video_overrides($data) {
        if (empty($data['categories']) || !is_array($data['categories'])) {
            return $data;
        }

        $settings = $this->get_settings();
        $primary_language = $this->get_current_video_language();
        $language_labels = $this->get_supported_video_languages();

        foreach ($data['categories'] as &$category) {
            if (!empty($category['commands']) && is_array($category['commands'])) {
                foreach ($category['commands'] as &$command) {
                    $profiles = $this->get_command_video_profiles($command, $settings);
                    $selected = $this->select_localized_video_value($profiles, $primary_language);
                    $command['videoUrl'] = $selected['url'];
                    $command['videoLanguage'] = $selected['language'];
                    $command['videoLanguageLabel'] = isset($language_labels[$selected['language']]['label'])
                        ? $language_labels[$selected['language']]['label']
                        : $selected['language'];
                    $command['videoIsFallback'] = $selected['is_fallback'];
                }
                unset($command);
            }

            if (!empty($category['subcategories']) && is_array($category['subcategories'])) {
                foreach ($category['subcategories'] as &$subcategory) {
                    if (empty($subcategory['commands']) || !is_array($subcategory['commands'])) {
                        continue;
                    }
                    foreach ($subcategory['commands'] as &$command) {
                        $profiles = $this->get_command_video_profiles($command, $settings);
                        $selected = $this->select_localized_video_value($profiles, $primary_language);
                        $command['videoUrl'] = $selected['url'];
                        $command['videoLanguage'] = $selected['language'];
                        $command['videoLanguageLabel'] = isset($language_labels[$selected['language']]['label'])
                            ? $language_labels[$selected['language']]['label']
                            : $selected['language'];
                        $command['videoIsFallback'] = $selected['is_fallback'];
                    }
                    unset($command);
                }
                unset($subcategory);
            }
        }
        unset($category);

        return $data;
    }

    private function get_video_link_groups($workflow) {
        $groups = array();
        if (empty($workflow['categories']) || !is_array($workflow['categories'])) {
            return $groups;
        }

        foreach ($workflow['categories'] as $category) {
            $group = array(
                'id' => isset($category['id']) ? sanitize_key($category['id']) : sanitize_key(isset($category['label']) ? $category['label'] : 'workspace'),
                'label' => isset($category['label']) ? $category['label'] : 'Workspace',
                'description' => isset($category['description']) ? $category['description'] : '',
                'items' => array(),
                'subgroups' => array(),
                'count' => 0,
            );

            if (!empty($category['commands']) && is_array($category['commands'])) {
                foreach ($category['commands'] as $command) {
                    $row = $this->get_video_link_row($group['label'], $command);
                    if ($row !== null) {
                        $group['items'][] = $row;
                        $group['count']++;
                    }
                }
            }

            if (!empty($category['subcategories']) && is_array($category['subcategories'])) {
                foreach ($category['subcategories'] as $subcategory) {
                    $subcategory_label = isset($subcategory['label']) ? $subcategory['label'] : 'Sub Category';
                    $subgroup = array(
                        'id' => isset($subcategory['id']) ? sanitize_key($subcategory['id']) : sanitize_key($subcategory_label),
                        'label' => $subcategory_label,
                        'items' => array(),
                    );

                    if (!empty($subcategory['commands']) && is_array($subcategory['commands'])) {
                        foreach ($subcategory['commands'] as $command) {
                            $row = $this->get_video_link_row($group['label'] . ' → ' . $subcategory_label, $command);
                            if ($row !== null) {
                                $subgroup['items'][] = $row;
                                $group['count']++;
                            }
                        }
                    }

                    if (!empty($subgroup['items'])) {
                        $group['subgroups'][] = $subgroup;
                    }
                }
            }

            if ($group['count'] > 0) {
                $groups[] = $group;
            }
        }

        return $groups;
    }

    private function get_video_link_row($section_label, $command) {
        if (empty($command['id']) || empty($command['label'])) {
            return null;
        }

        return array(
            'id' => sanitize_key($command['id']),
            'section' => $section_label,
            'label' => $command['label'],
            'videoUrls' => $this->get_command_video_profiles($command),
        );
    }

    private function render_welcome_language_profile($language_key, $language_data, $welcome_dashboard) {
        $profiles = isset($welcome_dashboard['language_profiles']) && is_array($welcome_dashboard['language_profiles'])
            ? $welcome_dashboard['language_profiles']
            : array();
        $profile = isset($profiles[$language_key]) && is_array($profiles[$language_key]) ? $profiles[$language_key] : array();
        $fields = array(
            'welcome_video_url' => array(__('Welcome YouTube Video', 'fb-software-ai'), 'https://www.youtube.com/watch?v=...'),
            'watch_youtube_url' => array(__('Watch on YouTube Button', 'fb-software-ai'), __('Usually the same video watch URL', 'fb-software-ai')),
            'subscribe_youtube_url' => array(__('Subscribe on YouTube Button', 'fb-software-ai'), 'https://www.youtube.com/@channel?sub_confirmation=1'),
            'memberships_url' => array(__('View Memberships Button', 'fb-software-ai'), 'https://www.youtube.com/@channel/join'),
            'youtube_channel_url' => array(__('YouTube Channel', 'fb-software-ai'), 'https://www.youtube.com/@channel'),
        );
        ?>
        <section class="fbsa-video-language-card" data-fbsa-video-language="<?php echo esc_attr($language_key); ?>">
            <header class="fbsa-video-language-card__header">
                <span class="fbsa-video-language-badge"><?php echo esc_html($language_data['short_label']); ?></span>
                <div>
                    <h4><?php echo esc_html($language_data['label']); ?> <?php echo esc_html__('Channel and Welcome Video', 'fb-software-ai'); ?></h4>
                    <p><?php echo esc_html__('These links are used when the current administrator uses this language.', 'fb-software-ai'); ?></p>
                </div>
            </header>
            <div class="fbsa-video-language-card__fields">
                <?php foreach ($fields as $field_key => $field_data) : ?>
                    <?php $value = isset($profile[$field_key]) ? $profile[$field_key] : ''; ?>
                    <label class="fbsa-video-link-field" for="fbsa-welcome-<?php echo esc_attr($language_key . '-' . $field_key); ?>">
                        <span><?php echo esc_html($field_data[0]); ?></span>
                        <input type="url" id="fbsa-welcome-<?php echo esc_attr($language_key . '-' . $field_key); ?>"<?php if (!self::VIDEO_LINKS_LOCKED) : ?> name="fbsa_welcome_dashboard[language_profiles][<?php echo esc_attr($language_key); ?>][<?php echo esc_attr($field_key); ?>]"<?php endif; ?> value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_attr($field_data[1]); ?>"<?php echo self::VIDEO_LINKS_LOCKED ? ' readonly aria-readonly="true" title="Locked in this public demo release"' : ''; ?> />
                    </label>
                <?php endforeach; ?>
            </div>
        </section>
        <?php
    }

    private function render_welcome_social_field($key, $label, $welcome_dashboard) {
        $social_links = isset($welcome_dashboard['social_links']) && is_array($welcome_dashboard['social_links']) ? $welcome_dashboard['social_links'] : array();
        $value = isset($social_links[$key]) ? $social_links[$key] : '';
        ?>
        <label class="fbsa-video-link-field" for="fbsa-social-<?php echo esc_attr($key); ?>">
            <span><?php echo esc_html($label); ?></span>
            <input type="url" id="fbsa-social-<?php echo esc_attr($key); ?>"<?php if (!self::VIDEO_LINKS_LOCKED) : ?> name="fbsa_welcome_dashboard[social_links][<?php echo esc_attr($key); ?>]"<?php endif; ?> value="<?php echo esc_attr($value); ?>" placeholder="https://..."<?php echo self::VIDEO_LINKS_LOCKED ? ' readonly aria-readonly="true" title="Locked in this public demo release"' : ''; ?> />
        </label>
        <?php
    }

    private function render_video_link_field($row) {
        $languages = $this->get_supported_video_languages();
        $video_urls = isset($row['videoUrls']) && is_array($row['videoUrls']) ? $row['videoUrls'] : array();
        ?>
        <div class="fbsa-video-link-field fbsa-bilingual-video-field">
            <span class="fbsa-bilingual-video-field__title"><?php echo esc_html($row['label']); ?></span>
            <div class="fbsa-bilingual-video-field__languages">
                <?php foreach ($languages as $language_key => $language_data) : ?>
                    <?php $value = isset($video_urls[$language_key]) ? $video_urls[$language_key] : ''; ?>
                    <label for="fbsa-video-link-<?php echo esc_attr($language_key . '-' . $row['id']); ?>">
                        <span><b><?php echo esc_html($language_data['short_label']); ?></b> <?php echo esc_html($language_data['label']); ?></span>
                        <input type="url" id="fbsa-video-link-<?php echo esc_attr($language_key . '-' . $row['id']); ?>"<?php if (!self::VIDEO_LINKS_LOCKED) : ?> name="fbsa_video_overrides[<?php echo esc_attr($language_key); ?>][<?php echo esc_attr($row['id']); ?>]"<?php endif; ?> value="<?php echo esc_attr($value); ?>" placeholder="https://www.youtube.com/watch?v=..."<?php echo self::VIDEO_LINKS_LOCKED ? ' readonly aria-readonly="true" title="Locked in this public demo release"' : ''; ?> />
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }

    private function get_video_link_rows($workflow) {
        $rows = array();
        if (empty($workflow['categories']) || !is_array($workflow['categories'])) {
            return $rows;
        }

        foreach ($workflow['categories'] as $category) {
            $category_label = isset($category['label']) ? $category['label'] : 'Workspace';

            if (!empty($category['commands']) && is_array($category['commands'])) {
                foreach ($category['commands'] as $command) {
                    $this->append_video_link_row($rows, $category_label, $command);
                }
            }

            if (!empty($category['subcategories']) && is_array($category['subcategories'])) {
                foreach ($category['subcategories'] as $subcategory) {
                    $subcategory_label = isset($subcategory['label']) ? $subcategory['label'] : 'Sub Category';
                    $section_label = $category_label . ' → ' . $subcategory_label;
                    if (empty($subcategory['commands']) || !is_array($subcategory['commands'])) {
                        continue;
                    }
                    foreach ($subcategory['commands'] as $command) {
                        $this->append_video_link_row($rows, $section_label, $command);
                    }
                }
            }
        }

        return $rows;
    }

    private function append_video_link_row(&$rows, $section_label, $command) {
        if (empty($command['id']) || empty($command['label'])) {
            return;
        }

        $rows[] = array(
            'id' => sanitize_key($command['id']),
            'section' => $section_label,
            'label' => $command['label'],
            'videoUrl' => isset($command['videoUrl']) ? $command['videoUrl'] : '',
        );
    }

    private function find_category_index($categories, $category_id) {
        foreach ($categories as $index => $category) {
            if (isset($category['id']) && $category['id'] === $category_id) {
                return $index;
            }
        }
        return null;
    }

    private function get_category_options($workflow) {
        $options = array();
        if (!empty($workflow['categories']) && is_array($workflow['categories'])) {
            foreach ($workflow['categories'] as $category) {
                if (!empty($category['id']) && !empty($category['label'])) {
                    $options[$category['id']] = $category['label'];
                }
            }
        }
        return $options;
    }

    private function get_command_target_label($command) {
        $type = isset($command['type']) ? $command['type'] : 'navigate';
        if ($type === 'install_theme') {
            return isset($command['themeSlug']) ? 'Theme: ' . $command['themeSlug'] : 'Theme installer';
        }
        if ($type === 'install_plugin') {
            return isset($command['pluginSlug']) ? 'Plugin: ' . $command['pluginSlug'] : 'Plugin installer';
        }
        return isset($command['adminPath']) ? $command['adminPath'] : 'Admin page';
    }

    private function find_command($command_id) {
        $workflow = $this->get_workflow_data();
        if (empty($workflow['categories']) || !is_array($workflow['categories'])) {
            return null;
        }

        foreach ($workflow['categories'] as $category) {
            if (!empty($category['commands']) && is_array($category['commands'])) {
                foreach ($category['commands'] as $command) {
                    if (isset($command['id']) && $command['id'] === $command_id) {
                        return $command;
                    }
                }
            }

            if (!empty($category['subcategories']) && is_array($category['subcategories'])) {
                foreach ($category['subcategories'] as $subcategory) {
                    if (empty($subcategory['commands']) || !is_array($subcategory['commands'])) {
                        continue;
                    }
                    foreach ($subcategory['commands'] as $command) {
                        if (isset($command['id']) && $command['id'] === $command_id) {
                            return $command;
                        }
                    }
                }
            }
        }

        return null;
    }

    public function ajax_create_content() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');

        if (!current_user_can('manage_options')) {
            $this->send_translated_json_error(array('message' => 'You do not have permission to run this command.'), 403);
        }

        $command_id = isset($_POST['commandId']) ? sanitize_key(wp_unslash($_POST['commandId'])) : '';
        $command = $this->find_command($command_id);

        if (!$command) {
            $this->send_translated_json_error(array('message' => 'Command not found.'), 404);
        }

        $type = isset($command['type']) ? sanitize_key($command['type']) : '';

        if ($type === 'create_page') {
            $result = $this->create_page_from_command($command);
            $this->send_translated_json_success($result);
        }

        if ($type === 'create_post') {
            $result = $this->create_post_from_command($command);
            $this->send_translated_json_success($result);
        }

        $this->send_translated_json_error(array('message' => 'This command is not a content creation command.'), 400);
    }

    public function ajax_run_command() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');

        if (!current_user_can('manage_options')) {
            $this->send_translated_json_error(array('message' => 'You do not have permission to run this command.'), 403);
        }

        $command_id = isset($_POST['commandId']) ? sanitize_key(wp_unslash($_POST['commandId'])) : '';
        $command = $this->find_command($command_id);

        if (!$command) {
            $this->send_translated_json_error(array('message' => 'Command not found.'), 404);
        }

        $type = isset($command['type']) ? sanitize_key($command['type']) : '';

        if ($type === 'install_theme') {
            $result = $this->install_theme_from_command($command);
            $this->send_translated_json_success($result);
        }

        if ($type === 'install_plugin') {
            $result = $this->install_plugin_from_command($command);
            $this->send_translated_json_success($result);
        }

        $this->send_translated_json_error(array('message' => 'This command type cannot be run by AJAX yet.'), 400);
    }

    public function ajax_get_status() {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');

        if (!current_user_can('manage_options')) {
            $this->send_translated_json_error(array('message' => 'You do not have permission to view status.'), 403);
        }

        $this->send_translated_json_success($this->calculate_status());
    }

    private function create_page_from_command($command) {
        $title = isset($command['title']) ? sanitize_text_field($command['title']) : sanitize_text_field($command['label']);
        $slug = isset($command['slug']) ? sanitize_title($command['slug']) : sanitize_title($title);
        $command_id = isset($command['id']) ? sanitize_key($command['id']) : '';

        $existing_id = $this->find_existing_content_id('page', $slug);
        $content = '<!-- wp:paragraph --><p>' . esc_html__('This page was created by FB Software AI. Edit it with WordPress or your page builder.', 'fb-software-ai') . '</p><!-- /wp:paragraph -->';

        if ($existing_id) {
            $post_id = wp_update_post(array(
                'ID' => $existing_id,
                'post_title' => $title,
                'post_name' => $slug,
                'post_status' => 'publish',
            ), true);
        } else {
            $post_id = wp_insert_post(array(
                'post_title' => $title,
                'post_name' => $slug,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_type' => 'page',
                'post_author' => get_current_user_id(),
                'comment_status' => 'closed',
                'ping_status' => 'closed',
            ), true);
        }

        if (is_wp_error($post_id)) {
            $this->send_translated_json_error(array('message' => $post_id->get_error_message()), 500);
        }

        update_post_meta($post_id, '_fbsa_created_command', $command_id);

        $assign = isset($command['assign']) ? sanitize_key($command['assign']) : '';
        if ($assign === 'front_page') {
            update_option('show_on_front', 'page');
            update_option('page_on_front', absint($post_id));
        }

        if ($assign === 'posts_page') {
            update_option('page_for_posts', absint($post_id));
        }

        $this->sync_core_pages_to_primary_menu();

        return array(
            'message' => sprintf(
                /* translators: %s: page title */
                __('%s page created, published, and added to the main menu.', 'fb-software-ai'),
                $title
            ),
            'postId' => absint($post_id),
            'redirectUrl' => admin_url('edit.php?post_type=page'),
            'status' => $this->calculate_status(),
        );
    }

    private function sync_core_pages_to_primary_menu() {
        $core_pages = array(
            'home' => 'Home',
            'about' => 'About',
            'services' => 'Services',
            'products' => 'Products',
            'pricing' => 'Pricing',
            'portfolio' => 'Portfolio',
            'blog' => 'Blog',
            'faq' => 'FAQ',
            'contact' => 'Contact',
        );

        $classic_menu_synced = false;
        if (function_exists('wp_get_nav_menu_object')) {
            $menu = $this->get_or_create_primary_menu();
            if ($menu && !is_wp_error($menu)) {
                $this->assign_menu_to_header_locations(absint($menu->term_id));
                $this->enable_auto_add_for_menu(absint($menu->term_id));

                $position = 1;
                foreach ($core_pages as $slug => $fallback_title) {
                    $page_id = $this->find_existing_content_id('page', $slug);
                    if (!$page_id) {
                        continue;
                    }

                    if ($this->menu_has_published_page($menu->term_id, $page_id)) {
                        $this->update_menu_page_position($menu->term_id, $page_id, $position);
                        $position++;
                        continue;
                    }

                    $page_title = get_the_title($page_id);
                    if ($page_title === '') {
                        $page_title = $fallback_title;
                    }

                    wp_update_nav_menu_item($menu->term_id, 0, array(
                        'menu-item-title' => $page_title,
                        'menu-item-object' => 'page',
                        'menu-item-object-id' => absint($page_id),
                        'menu-item-type' => 'post_type',
                        'menu-item-status' => 'publish',
                        'menu-item-position' => $position,
                    ));

                    $position++;
                }

                $classic_menu_synced = true;
            }
        }

        $block_navigation_synced = $this->sync_core_pages_to_block_navigation($core_pages);

        return $classic_menu_synced || $block_navigation_synced;
    }

    private function get_or_create_primary_menu() {
        $locations = get_nav_menu_locations();
        $preferred_locations = $this->get_preferred_header_menu_locations();

        foreach ($preferred_locations as $location_key) {
            if (!empty($locations[$location_key])) {
                $menu = wp_get_nav_menu_object($locations[$location_key]);
                if ($menu && !is_wp_error($menu)) {
                    return $menu;
                }
            }
        }

        $menus = wp_get_nav_menus();
        if (!empty($menus) && !is_wp_error($menus)) {
            foreach ($menus as $menu) {
                if ($menu && !is_wp_error($menu)) {
                    return $menu;
                }
            }
        }

        $menu_id = wp_create_nav_menu('FB Software AI Main Menu');
        if (is_wp_error($menu_id)) {
            return $menu_id;
        }

        return wp_get_nav_menu_object($menu_id);
    }

    private function get_preferred_header_menu_locations() {
        return array(
            'primary',
            'main',
            'menu-1',
            'primary-menu',
            'primary_menu',
            'main-menu',
            'main_menu',
            'header',
            'header-menu',
            'header_menu',
            'header-menu-1',
            'header_menu_1',
            'header-navigation',
            'main-navigation',
            'main_navigation',
            'top',
            'top-menu',
            'top_menu',
        );
    }

    private function assign_menu_to_header_locations($menu_id) {
        $registered_locations = get_registered_nav_menus();
        if (empty($registered_locations) || !is_array($registered_locations)) {
            return;
        }

        $locations = get_nav_menu_locations();
        $preferred_locations = $this->get_preferred_header_menu_locations();
        $targets = array();

        foreach ($preferred_locations as $location_key) {
            if (array_key_exists($location_key, $registered_locations)) {
                $targets[] = $location_key;
            }
        }

        if (empty($targets)) {
            $first_location = array_key_first($registered_locations);
            if ($first_location) {
                $targets[] = $first_location;
            }
        }

        foreach (array_unique($targets) as $location_key) {
            $locations[$location_key] = absint($menu_id);
        }

        set_theme_mod('nav_menu_locations', $locations);
    }

    private function enable_auto_add_for_menu($menu_id) {
        $nav_menu_options = get_option('nav_menu_options', array());
        if (!is_array($nav_menu_options)) {
            $nav_menu_options = array();
        }

        $auto_add = isset($nav_menu_options['auto_add']) && is_array($nav_menu_options['auto_add']) ? $nav_menu_options['auto_add'] : array();
        if (!in_array(absint($menu_id), array_map('absint', $auto_add), true)) {
            $auto_add[] = absint($menu_id);
        }

        $nav_menu_options['auto_add'] = array_values(array_unique(array_map('absint', $auto_add)));
        update_option('nav_menu_options', $nav_menu_options);
    }

    private function menu_has_published_page($menu_id, $page_id) {
        $items = wp_get_nav_menu_items($menu_id, array('post_status' => 'publish'));
        if (empty($items) || is_wp_error($items)) {
            return false;
        }

        foreach ($items as $item) {
            if (isset($item->object, $item->object_id) && $item->object === 'page' && absint($item->object_id) === absint($page_id)) {
                return true;
            }
        }

        return false;
    }

    private function update_menu_page_position($menu_id, $page_id, $position) {
        $items = wp_get_nav_menu_items($menu_id, array('post_status' => 'publish'));
        if (empty($items) || is_wp_error($items)) {
            return;
        }

        foreach ($items as $item) {
            if (!isset($item->ID, $item->object, $item->object_id) || $item->object !== 'page' || absint($item->object_id) !== absint($page_id)) {
                continue;
            }

            wp_update_nav_menu_item($menu_id, absint($item->ID), array(
                'menu-item-object-id' => absint($page_id),
                'menu-item-object' => 'page',
                'menu-item-type' => 'post_type',
                'menu-item-status' => 'publish',
                'menu-item-position' => absint($position),
            ));
            return;
        }
    }

    private function sync_core_pages_to_block_navigation($core_pages) {
        if (!function_exists('post_type_exists') || !post_type_exists('wp_navigation')) {
            return false;
        }

        $navigation_posts = get_posts(array(
            'post_type' => 'wp_navigation',
            'post_status' => array('publish', 'draft'),
            'numberposts' => -1,
        ));

        if (empty($navigation_posts)) {
            return false;
        }

        $changed_any = false;
        foreach ($navigation_posts as $navigation_post) {
            $content = isset($navigation_post->post_content) ? $navigation_post->post_content : '';
            $updated_content = $content;

            foreach ($core_pages as $slug => $fallback_title) {
                $page_id = $this->find_existing_content_id('page', $slug);
                if (!$page_id || $this->block_navigation_has_page($updated_content, $page_id, $slug)) {
                    continue;
                }

                $page_title = get_the_title($page_id);
                if ($page_title === '') {
                    $page_title = $fallback_title;
                }

                $attributes = array(
                    'label' => $page_title,
                    'type' => 'page',
                    'id' => absint($page_id),
                    'url' => get_permalink($page_id),
                    'kind' => 'post-type',
                    'isTopLevelLink' => true,
                );

                $updated_content .= "\n<!-- wp:navigation-link " . wp_json_encode($attributes) . " /-->";
            }

            if ($updated_content !== $content) {
                $result = wp_update_post(array(
                    'ID' => absint($navigation_post->ID),
                    'post_content' => $updated_content,
                ), true);

                if (!is_wp_error($result)) {
                    $changed_any = true;
                }
            }
        }

        return $changed_any;
    }

    private function block_navigation_has_page($content, $page_id, $slug) {
        $page_id = absint($page_id);
        $slug = sanitize_title($slug);
        $content = (string) $content;

        if (preg_match('/"id"\s*:\s*' . preg_quote((string) $page_id, '/') . '\b/', $content)) {
            return true;
        }

        if ($slug !== '' && preg_match('/"url"\s*:\s*"[^"]*\/' . preg_quote($slug, '/') . '\/?"/', $content)) {
            return true;
        }

        return false;
    }

    private function create_post_from_command($command) {
        $title = isset($command['title']) ? sanitize_text_field($command['title']) : 'New Blog Post';
        $command_id = isset($command['id']) ? sanitize_key($command['id']) : '';
        $status = isset($command['postStatus']) ? sanitize_key($command['postStatus']) : 'publish';
        if (!in_array($status, array('draft', 'publish'), true)) {
            $status = 'publish';
        }

        $post_id = wp_insert_post(array(
            'post_title' => $title,
            'post_content' => '<!-- wp:paragraph --><p>' . esc_html__('Write your blog post here.', 'fb-software-ai') . '</p><!-- /wp:paragraph -->',
            'post_status' => $status,
            'post_type' => 'post',
            'post_author' => get_current_user_id(),
        ), true);

        if (is_wp_error($post_id)) {
            $this->send_translated_json_error(array('message' => $post_id->get_error_message()), 500);
        }

        update_post_meta($post_id, '_fbsa_created_command', $command_id);

        return array(
            'message' => __('Blog post created and published.', 'fb-software-ai'),
            'postId' => absint($post_id),
            'redirectUrl' => admin_url('edit.php'),
            'status' => $this->calculate_status(),
        );
    }

    private function install_plugin_from_command($command) {
        $result = $this->install_plugin_result($command);

        if (is_wp_error($result)) {
            $this->send_translated_json_error(
                array('message' => $result->get_error_message()),
                $this->get_error_status_code($result)
            );
        }

        return $result;
    }

    private function install_plugin_result($command) {
        if (!current_user_can('install_plugins')) {
            return new WP_Error('fbsa_no_plugin_install_permission', 'You do not have permission to install plugins.', array('status' => 403));
        }

        $plugin_slug = isset($command['pluginSlug']) ? sanitize_key($command['pluginSlug']) : '';
        $plugin_file = isset($command['pluginFile']) ? sanitize_text_field($command['pluginFile']) : '';
        $plugin_file = ltrim($plugin_file, '/');
        $activate = !empty($command['activate']);

        if ($plugin_slug === '') {
            return new WP_Error('fbsa_missing_plugin_slug', 'Plugin slug missing from command.', array('status' => 400));
        }

        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';

        if ($plugin_file === '') {
            $plugin_file = $plugin_slug . '/' . $plugin_slug . '.php';
        }

        $found_plugin_file = $this->get_plugin_file_for_slug($plugin_slug, $plugin_file);
        $already_installed = $found_plugin_file !== '';
        $installed_now = false;

        if (!$already_installed) {
            $api = plugins_api('plugin_information', array(
                'slug' => $plugin_slug,
                'fields' => array('sections' => false),
            ));

            if (is_wp_error($api)) {
                return new WP_Error('fbsa_plugin_api_error', $api->get_error_message(), array('status' => 500));
            }

            if (empty($api->download_link)) {
                return new WP_Error('fbsa_missing_plugin_download_link', 'WordPress.org did not return an official plugin download link.', array('status' => 500));
            }

            $skin = new Automatic_Upgrader_Skin();
            $upgrader = new Plugin_Upgrader($skin);
            $result = $upgrader->install($api->download_link);

            if (is_wp_error($result)) {
                return new WP_Error('fbsa_plugin_install_error', $result->get_error_message(), array('status' => 500));
            }

            if (!$result) {
                $skin_errors = $skin->get_errors();
                $message = is_wp_error($skin_errors) && $skin_errors->has_errors() ? $skin_errors->get_error_message() : 'Plugin installation failed. WordPress may need FTP/file-system permissions.';
                return new WP_Error('fbsa_plugin_install_failed', $message, array('status' => 500));
            }

            $installed_now = true;
            $installed_file = $upgrader->plugin_info();
            if (is_string($installed_file) && $installed_file !== '') {
                $plugin_file = $installed_file;
            }
            $found_plugin_file = $this->get_plugin_file_for_slug($plugin_slug, $plugin_file);
        }

        if ($found_plugin_file === '') {
            return new WP_Error('fbsa_plugin_file_not_found', 'Plugin installed, but the main plugin file could not be found for activation.', array('status' => 500));
        }

        $activated = false;
        $already_active = is_plugin_active($found_plugin_file);
        if ($activate) {
            if (!current_user_can('activate_plugins')) {
                return new WP_Error('fbsa_no_plugin_activate_permission', 'Plugin installed, but you do not have permission to activate plugins.', array('status' => 403));
            }
            if (!$already_active) {
                $activation_result = activate_plugin($found_plugin_file);
                if (is_wp_error($activation_result)) {
                    return new WP_Error('fbsa_plugin_activation_error', $activation_result->get_error_message(), array('status' => 500));
                }
                $activated = true;
            }
        }

        $plugins = get_plugins();
        $plugin_name = isset($plugins[$found_plugin_file]['Name']) ? $plugins[$found_plugin_file]['Name'] : ucwords(str_replace('-', ' ', $plugin_slug));
        if ($already_active) {
            $message = $plugin_name . ' plugin is already installed and active.';
        } elseif ($activated) {
            $message = $plugin_name . ' plugin is ' . ($installed_now ? 'installed and activated.' : 'already installed and now activated.');
        } else {
            $message = $plugin_name . ' plugin is ' . ($installed_now ? 'installed.' : 'already installed.');
        }

        return array(
            'message' => $message,
            'pluginSlug' => $plugin_slug,
            'pluginFile' => $found_plugin_file,
            'officialUrl' => isset($command['officialUrl']) ? esc_url_raw($command['officialUrl']) : '',
            'installedNow' => $installed_now,
            'activated' => $activated || $already_active,
            'redirectUrl' => admin_url('plugins.php'),
            'status' => $this->calculate_status(),
        );
    }


    private function deactivate_plugin_result($plugin_slug, $preferred_file, $plugin_name) {
        if (!current_user_can('activate_plugins')) {
            return new WP_Error('fbsa_no_plugin_deactivate_permission', 'You do not have permission to deactivate plugins.', array('status' => 403));
        }

        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $found_plugin_file = $this->get_plugin_file_for_slug($plugin_slug, $preferred_file);
        if ($found_plugin_file === '') {
            return new WP_Error('fbsa_plugin_not_installed', $plugin_name . ' is not installed.', array('status' => 404));
        }

        if (!is_plugin_active($found_plugin_file)) {
            return array('message' => $plugin_name . ' is already deactivated. You can activate or uninstall it from the card.');
        }

        deactivate_plugins($found_plugin_file, false, false);
        if (is_plugin_active($found_plugin_file)) {
            return new WP_Error('fbsa_plugin_deactivate_failed', $plugin_name . ' could not be deactivated.', array('status' => 500));
        }

        return array('message' => $plugin_name . ' has been deactivated.');
    }

    private function uninstall_plugin_result($plugin_slug, $preferred_file, $plugin_name) {
        if (!current_user_can('delete_plugins')) {
            return new WP_Error('fbsa_no_plugin_delete_permission', 'You do not have permission to uninstall plugins.', array('status' => 403));
        }

        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $found_plugin_file = $this->get_plugin_file_for_slug($plugin_slug, $preferred_file);
        if ($found_plugin_file === '') {
            return new WP_Error('fbsa_plugin_not_installed', $plugin_name . ' is not installed.', array('status' => 404));
        }

        if (is_plugin_active($found_plugin_file)) {
            return new WP_Error('fbsa_plugin_active_delete_blocked', 'Deactivate ' . $plugin_name . ' before uninstalling it.', array('status' => 400));
        }

        $deleted = delete_plugins(array($found_plugin_file));
        if (is_wp_error($deleted)) {
            return new WP_Error('fbsa_plugin_delete_failed', $deleted->get_error_message(), array('status' => 500));
        }

        if (!$deleted) {
            return new WP_Error('fbsa_plugin_delete_failed', $plugin_name . ' could not be uninstalled. WordPress may need file-system permissions.', array('status' => 500));
        }

        return array('message' => $plugin_name . ' has been uninstalled.');
    }

    private function deactivate_theme_result($theme_slug, $theme_name) {
        if (!current_user_can('switch_themes')) {
            return new WP_Error('fbsa_no_theme_switch_permission', 'You do not have permission to deactivate themes.', array('status' => 403));
        }

        $is_active = get_stylesheet() === $theme_slug || get_template() === $theme_slug;
        if (!$is_active) {
            return array('message' => $theme_name . ' is already deactivated. You can activate or uninstall it from the card.');
        }

        $fallback_slug = $this->get_fallback_theme_slug($theme_slug);
        if ($fallback_slug === '') {
            return new WP_Error('fbsa_theme_deactivate_no_fallback', 'WordPress cannot deactivate the active theme without another installed theme to switch to.', array('status' => 400));
        }

        switch_theme($fallback_slug);
        return array('message' => $theme_name . ' has been deactivated by switching WordPress to ' . wp_get_theme($fallback_slug)->get('Name') . '.');
    }

    private function uninstall_theme_result($theme_slug, $theme_name) {
        if (!current_user_can('delete_themes')) {
            return new WP_Error('fbsa_no_theme_delete_permission', 'You do not have permission to uninstall themes.', array('status' => 403));
        }

        $theme = wp_get_theme($theme_slug);
        if (!$theme->exists()) {
            return new WP_Error('fbsa_theme_not_installed', $theme_name . ' is not installed.', array('status' => 404));
        }

        if (get_stylesheet() === $theme_slug || get_template() === $theme_slug) {
            return new WP_Error('fbsa_theme_active_delete_blocked', 'Deactivate ' . $theme_name . ' before uninstalling it.', array('status' => 400));
        }

        require_once ABSPATH . 'wp-admin/includes/theme.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';

        $deleted = delete_theme($theme_slug);
        if (is_wp_error($deleted)) {
            return new WP_Error('fbsa_theme_delete_failed', $deleted->get_error_message(), array('status' => 500));
        }

        if (!$deleted) {
            return new WP_Error('fbsa_theme_delete_failed', $theme_name . ' could not be uninstalled. WordPress may need file-system permissions.', array('status' => 500));
        }

        return array('message' => $theme_name . ' has been uninstalled.');
    }

    private function get_fallback_theme_slug($current_slug) {
        $themes = wp_get_themes();
        $preferred = array('twentytwentysix', 'twentytwentyfive', 'twentytwentyfour', 'twentytwentythree', 'twentytwentytwo');

        foreach ($preferred as $slug) {
            if ($slug !== $current_slug && isset($themes[$slug])) {
                return $slug;
            }
        }

        foreach ($themes as $slug => $theme) {
            if ($slug !== $current_slug && $theme->exists()) {
                return $slug;
            }
        }

        return '';
    }

    private function get_plugin_file_for_slug($plugin_slug, $preferred_file = '') {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $plugin_slug = sanitize_key($plugin_slug);
        $preferred_file = ltrim(sanitize_text_field($preferred_file), '/');

        if ($preferred_file !== '' && file_exists(WP_PLUGIN_DIR . '/' . $preferred_file)) {
            return $preferred_file;
        }

        $plugins = get_plugins();
        $best_match = '';
        foreach ($plugins as $plugin_file => $plugin_data) {
            if (strpos($plugin_file, $plugin_slug . '/') === 0) {
                if ($plugin_file === $plugin_slug . '/' . $plugin_slug . '.php') {
                    return $plugin_file;
                }
                if ($best_match === '') {
                    $best_match = $plugin_file;
                }
            }
        }

        return $best_match;
    }

    private function install_theme_from_command($command) {
        $result = $this->install_theme_result($command);

        if (is_wp_error($result)) {
            $this->send_translated_json_error(
                array('message' => $result->get_error_message()),
                $this->get_error_status_code($result)
            );
        }

        return $result;
    }

    private function install_theme_result($command) {
        if (!current_user_can('install_themes')) {
            return new WP_Error('fbsa_no_install_permission', 'You do not have permission to install themes.', array('status' => 403));
        }

        $theme_slug = isset($command['themeSlug']) ? sanitize_key($command['themeSlug']) : '';
        $activate = !empty($command['activate']);

        if ($theme_slug === '') {
            return new WP_Error('fbsa_missing_theme_slug', 'Theme slug missing from command.', array('status' => 400));
        }

        $theme = wp_get_theme($theme_slug);
        $already_installed = $theme->exists();
        $installed_now = false;

        if (!$already_installed) {
            require_once ABSPATH . 'wp-admin/includes/theme.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/misc.php';

            $api = themes_api('theme_information', array(
                'slug' => $theme_slug,
                'fields' => array('sections' => false),
            ));

            if (is_wp_error($api)) {
                return new WP_Error('fbsa_theme_api_error', $api->get_error_message(), array('status' => 500));
            }

            if (empty($api->download_link)) {
                return new WP_Error('fbsa_missing_download_link', 'WordPress.org did not return an official theme download link.', array('status' => 500));
            }

            $skin = new Automatic_Upgrader_Skin();
            $upgrader = new Theme_Upgrader($skin);
            $result = $upgrader->install($api->download_link);

            if (is_wp_error($result)) {
                return new WP_Error('fbsa_theme_install_error', $result->get_error_message(), array('status' => 500));
            }

            if (!$result) {
                $skin_errors = $skin->get_errors();
                $message = is_wp_error($skin_errors) && $skin_errors->has_errors() ? $skin_errors->get_error_message() : 'Theme installation failed. WordPress may need FTP/file-system permissions.';
                return new WP_Error('fbsa_theme_install_failed', $message, array('status' => 500));
            }

            $installed_now = true;
            $theme = wp_get_theme($theme_slug);
        }

        $activated = false;
        $already_active = get_stylesheet() === $theme_slug || get_template() === $theme_slug;
        if ($activate) {
            if (!current_user_can('switch_themes')) {
                return new WP_Error('fbsa_no_switch_permission', 'Theme installed, but you do not have permission to activate themes.', array('status' => 403));
            }
            if (!$already_active) {
                switch_theme($theme_slug);
                $activated = true;
            }
        }

        $theme_name = $theme->exists() ? $theme->get('Name') : ucwords(str_replace('-', ' ', $theme_slug));
        if ($already_active) {
            $message = $theme_name . ' theme is already installed and active.';
        } elseif ($activated) {
            $message = $theme_name . ' theme is ' . ($installed_now ? 'installed and activated.' : 'already installed and now activated.');
        } else {
            $message = $theme_name . ' theme is ' . ($installed_now ? 'installed.' : 'already installed.');
        }

        return array(
            'message' => $message,
            'themeSlug' => $theme_slug,
            'officialUrl' => isset($command['officialUrl']) ? esc_url_raw($command['officialUrl']) : '',
            'installedNow' => $installed_now,
            'activated' => $activated || $already_active,
            'redirectUrl' => admin_url('themes.php'),
            'status' => $this->calculate_status(),
        );
    }

    private function get_error_status_code($error) {
        $data = is_wp_error($error) ? $error->get_error_data() : null;
        if (is_array($data) && isset($data['status'])) {
            return absint($data['status']);
        }
        return 500;
    }

    private function find_existing_content_id($post_type, $slug) {
        $posts = get_posts(array(
            'name' => $slug,
            'post_type' => $post_type,
            'post_status' => array('publish', 'draft', 'pending', 'private', 'future'),
            'numberposts' => 1,
            'fields' => 'ids',
        ));

        if (!empty($posts[0])) {
            return absint($posts[0]);
        }

        return 0;
    }

    private function get_plugin_destination_url($plugin_slug, $fallback_plugin_file, $active_admin_path) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $plugin_file = $this->get_plugin_file_for_slug($plugin_slug, $fallback_plugin_file);
        if ($plugin_file !== '' && is_plugin_active($plugin_file)) {
            return admin_url(ltrim($active_admin_path, '/'));
        }

        return admin_url('plugins.php');
    }

    private function get_required_plugin_map() {
        return array(
            self::ELEMENTOR_PLUGIN_SLUG => self::ELEMENTOR_PLUGIN_FILE,
            self::SITE_KIT_PLUGIN_SLUG => self::SITE_KIT_PLUGIN_FILE,
            self::FLUENTSMTP_PLUGIN_SLUG => self::FLUENTSMTP_PLUGIN_FILE,
            self::YOAST_DUPLICATE_POST_PLUGIN_SLUG => self::YOAST_DUPLICATE_POST_PLUGIN_FILE,
            self::CONTACT_FORM_7_PLUGIN_SLUG => self::CONTACT_FORM_7_PLUGIN_FILE,
            self::LOCO_TRANSLATE_PLUGIN_SLUG => self::LOCO_TRANSLATE_PLUGIN_FILE,
            self::LOCOAI_PLUGIN_SLUG => self::LOCOAI_PLUGIN_FILE,
            self::WOOCOMMERCE_PLUGIN_SLUG => self::WOOCOMMERCE_PLUGIN_FILE,
            self::WPVIVID_PLUGIN_SLUG => self::WPVIVID_PLUGIN_FILE,
        );
    }

    private function get_installed_required_plugin_slugs() {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $installed = array();
        foreach ($this->get_required_plugin_map() as $plugin_slug => $plugin_file) {
            if ($this->get_plugin_file_for_slug($plugin_slug, $plugin_file) !== '') {
                $installed[] = $plugin_slug;
            }
        }

        return array_values(array_unique($installed));
    }


    private function get_required_theme_map() {
        return array(
            self::KADENCE_THEME_SLUG => self::KADENCE_THEME_SLUG,
            self::HELLO_ELEMENTOR_THEME_SLUG => self::HELLO_ELEMENTOR_THEME_SLUG,
            self::ASTRA_THEME_SLUG => self::ASTRA_THEME_SLUG,
            self::OCEANWP_THEME_SLUG => self::OCEANWP_THEME_SLUG,
            self::BLOCKSY_THEME_SLUG => self::BLOCKSY_THEME_SLUG,
            self::ZAKRA_THEME_SLUG => self::ZAKRA_THEME_SLUG,
        );
    }

    private function get_installed_required_theme_slugs() {
        $installed = array();
        foreach ($this->get_required_theme_map() as $theme_slug => $stylesheet) {
            $theme = wp_get_theme($stylesheet);
            if ($theme && $theme->exists()) {
                $installed[] = $theme_slug;
            }
        }

        return array_values(array_unique($installed));
    }

    private function get_active_required_plugin_slugs() {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';

        $active = array();
        foreach ($this->get_required_plugin_map() as $plugin_slug => $plugin_file) {
            $found_plugin_file = $this->get_plugin_file_for_slug($plugin_slug, $plugin_file);
            if ($found_plugin_file !== '' && is_plugin_active($found_plugin_file)) {
                $active[] = $plugin_slug;
            }
        }

        return array_values(array_unique($active));
    }

    private function calculate_status() {
        $workflow = $this->get_workflow_data();
        $completed = array();
        $total = 0;

        if (!empty($workflow['categories']) && is_array($workflow['categories'])) {
            foreach ($workflow['categories'] as $category) {
                if (empty($category['commands']) || !is_array($category['commands'])) {
                    continue;
                }
                foreach ($category['commands'] as $command) {
                    $hide_when_exists = !empty($command['hideWhenExists']);
                    $type = isset($command['type']) ? sanitize_key($command['type']) : '';
                    if (!$hide_when_exists || $type !== 'create_page') {
                        continue;
                    }

                    $total++;
                    $slug = isset($command['slug']) ? sanitize_title($command['slug']) : sanitize_title($command['label']);
                    $exists = $this->find_existing_content_id('page', $slug);
                    if ($exists) {
                        $completed[] = array(
                            'commandId' => sanitize_key($command['id']),
                            'postId' => absint($exists),
                        );
                    }
                }
            }
        }

        $completed_count = count($completed);
        $percent = $total > 0 ? round(($completed_count / $total) * 100) : 0;

        return array(
            'completed' => $completed_count,
            'total' => $total,
            'percent' => $percent,
            'completedCommands' => $completed,
            'installedPlugins' => $this->get_installed_required_plugin_slugs(),
            'activePlugins' => $this->get_active_required_plugin_slugs(),
            'installedThemes' => $this->get_installed_required_theme_slugs(),
            'publishedPosts' => wp_count_posts('post')->publish,
            'publishedPages' => wp_count_posts('page')->publish,
        );
    }
}

/**
 * Boot the new architecture foundation alongside the preserved legacy facade.
 *
 * The core object is retained by its registered WordPress callbacks, while the
 * existing singleton remains the compatibility authority for v0.1.136 behavior.
 *
 * @return void
 */
function fbsa_boot_architecture_foundation() {
    $core_plugin = new \FBSoftwareAI\Core\Plugin(FBSA_PLUGIN_FILE);
    $core_plugin->register();

    $legacy_facade = FBSA_Demo_Plugin::instance();
    $bridge = new \FBSoftwareAI\Compatibility\LegacyFacadeBridge($core_plugin);
    $bridge->attach($legacy_facade);
}

fbsa_boot_architecture_foundation();
