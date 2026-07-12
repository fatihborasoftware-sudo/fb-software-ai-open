<?php
require_once dirname(__DIR__) . '/support/wp-stubs.php';
require_once dirname(__DIR__) . '/support/assertions.php';

if (!defined('WP_UNINSTALL_PLUGIN')) {
    define('WP_UNINSTALL_PLUGIN', true);
}
$GLOBALS['fbsa_test_options'] = array(
    'fbsa_demo_settings' => array('x' => 1),
    'fbsa_demo_menu_sync_version' => 'old',
    'fbsa_duplicate_cleanup_version' => '0.1.137',
    'fbsa_duplicate_cleanup_notice' => array('message' => 'x'),
    'fbsa_schema_versions' => array('core' => 1, 'workspace' => 1),
    'fbsa_migration_lock' => array('owner' => 'stale'),
    'fbsa_workspace_default_layouts' => array('schemaVersion' => 1),
    'unrelated_option' => 'preserve',
);
$GLOBALS['fbsa_test_user_options'] = array(
    1 => array('fbsa_workspace_layouts' => array('dashboard' => array()), 'unrelated_user_option' => 'keep'),
);
$root = dirname(__DIR__, 2);
require $root . '/uninstall.php';

foreach (array(
    'fbsa_demo_settings',
    'fbsa_demo_menu_sync_version',
    'fbsa_duplicate_cleanup_version',
    'fbsa_duplicate_cleanup_notice',
    'fbsa_schema_versions',
    'fbsa_migration_lock',
    'fbsa_workspace_default_layouts',
) as $key) {
    fbsa_assert_true(!array_key_exists($key, $GLOBALS['fbsa_test_options']), 'Uninstall removes ' . $key . '.');
}
fbsa_assert_same('preserve', $GLOBALS['fbsa_test_options']['unrelated_option'], 'Uninstall preserves unrelated options.');
fbsa_assert_true(!isset($GLOBALS['fbsa_test_user_options'][1]['fbsa_workspace_layouts']), 'Uninstall removes per-user Workspace layouts.');
fbsa_assert_same('keep', $GLOBALS['fbsa_test_user_options'][1]['unrelated_user_option'], 'Uninstall preserves unrelated user options.');
fbsa_finish_tests();
