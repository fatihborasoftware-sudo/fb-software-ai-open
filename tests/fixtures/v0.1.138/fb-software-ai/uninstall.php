<?php
/**
 * FB Software AI uninstall cleanup.
 *
 * Removes plugin-owned options only. Content created by the administrator,
 * WordPress reading settings, menus, themes, and third-party plugins are preserved.
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

function fbsa_demo_delete_site_options() {
    delete_option('fbsa_demo_settings');
    delete_option('fbsa_demo_menu_sync_version'); // Legacy option from releases before 0.1.118.
    delete_option('fbsa_duplicate_cleanup_version');
    delete_option('fbsa_duplicate_cleanup_notice');
    delete_option('fbsa_schema_versions');
    delete_option('fbsa_migration_lock');
    delete_option('fbsa_workspace_default_layouts');
}

function fbsa_delete_workspace_user_layouts() {
    if (!function_exists('delete_metadata')) {
        return;
    }

    global $wpdb;
    $meta_key = 'fbsa_workspace_layouts';
    if (is_multisite() && isset($wpdb) && method_exists($wpdb, 'get_blog_prefix')) {
        $meta_key = $wpdb->get_blog_prefix(get_current_blog_id()) . $meta_key;
    }
    delete_metadata('user', 0, $meta_key, '', true);
}

if (is_multisite()) {
    $site_ids = get_sites(array('fields' => 'ids', 'number' => 0));
    foreach ($site_ids as $site_id) {
        switch_to_blog((int) $site_id);
        fbsa_demo_delete_site_options();
        fbsa_delete_workspace_user_layouts();
        restore_current_blog();
    }
} else {
    fbsa_demo_delete_site_options();
    fbsa_delete_workspace_user_layouts();
}
