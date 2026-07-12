<?php
require_once dirname(__DIR__) . '/support/wp-stubs.php';
require_once dirname(__DIR__) . '/support/assertions.php';

$GLOBALS['fbsa_test_options'] = array(
    'fbsa_demo_settings' => array(
        'video_profiles' => array(
            'tr' => array('welcome_video_url' => 'https://youtube.test/tr'),
            'en' => array('welcome_video_url' => 'https://youtube.test/en'),
        ),
        'unknown_existing_setting' => 'preserve',
    ),
    'fbsa_schema_versions' => array(
        'core' => 1,
        'workspace' => 1,
        '_meta' => array(
            'plugin_baseline' => '0.1.138',
            'last_migration_id' => '0002-workspace-foundation',
        ),
    ),
    'fbsa_workspace_default_layouts' => array(
        'schemaVersion' => 1,
        'layouts' => array(
            'dashboard' => array(
                'schemaVersion' => 1,
                'context' => 'dashboard',
                'layoutId' => 'fbsa-default-dashboard-v1',
                'roleTemplate' => 'administrator',
                'widgets' => array(
                    array('id' => 'fbsa_website_steps_widget', 'visible' => true, 'column' => 3, 'order' => 0, 'collapsed' => false, 'width' => null, 'height' => null, 'settings' => array()),
                    array('id' => 'fbsa_website_settings_widget', 'visible' => true, 'column' => 3, 'order' => 1, 'collapsed' => false, 'width' => null, 'height' => null, 'settings' => array()),
                    array('id' => 'fbsa_plugin_setup_widget', 'visible' => true, 'column' => 4, 'order' => 0, 'collapsed' => false, 'width' => null, 'height' => null, 'settings' => array()),
                    array('id' => 'fbsa_help_tutorials_widget', 'visible' => true, 'column' => 4, 'order' => 1, 'collapsed' => false, 'width' => null, 'height' => null, 'settings' => array()),
                ),
                'updatedAt' => '2026-07-12 12:00:00',
                'source' => 'default',
            ),
        ),
        'unknown_default_metadata' => 'keep',
    ),
);
$GLOBALS['fbsa_test_user_options'] = array(
    1 => array(
        'metaboxhidden_dashboard' => array('dashboard_site_health'),
        'meta-box-order_dashboard' => array(
            'normal' => 'dashboard_activity',
            'side' => 'dashboard_quick_press,wpvivid_dashboard_widget',
            'column3' => 'fbsa_website_steps_widget,fbsa_website_settings_widget',
            'column4' => 'fbsa_plugin_setup_widget,fbsa_help_tutorials_widget',
        ),
        'fbsa_workspace_layouts' => array(
            'dashboard' => array(
                'schemaVersion' => 1,
                'context' => 'dashboard',
                'layoutId' => 'existing-user-layout',
                'roleTemplate' => null,
                'widgets' => array(
                    array('id' => 'fbsa_website_steps_widget', 'visible' => false, 'column' => 3, 'order' => 0, 'collapsed' => false, 'width' => null, 'height' => null, 'settings' => array()),
                ),
                'updatedAt' => '2026-07-12 12:00:00',
                'source' => 'user',
            ),
        ),
    ),
);
$settings_before = $GLOBALS['fbsa_test_options']['fbsa_demo_settings'];
$user_options_before = $GLOBALS['fbsa_test_user_options'];
$layouts_before = $GLOBALS['fbsa_test_options']['fbsa_workspace_default_layouts']['layouts'];

$root = dirname(__DIR__, 2);
require_once $root . '/fb-software-ai.php';

foreach ($GLOBALS['fbsa_test_actions'] as $registered) {
    if ($registered['hook'] === 'admin_init' && is_array($registered['callback']) && $registered['callback'][1] === 'maybe_upgrade') {
        call_user_func($registered['callback']);
    }
}

fbsa_assert_same($settings_before, $GLOBALS['fbsa_test_options']['fbsa_demo_settings'], 'Upgrade from v0.1.138 preserves all legacy settings and bilingual video data.');
fbsa_assert_same(1, $GLOBALS['fbsa_test_options']['fbsa_schema_versions']['core'], 'Upgrade preserves core schema version 1.');
fbsa_assert_same(2, $GLOBALS['fbsa_test_options']['fbsa_schema_versions']['workspace'], 'Upgrade advances Workspace schema to version 2.');
fbsa_assert_same('0003-workspace-controls', $GLOBALS['fbsa_test_options']['fbsa_schema_versions']['_meta']['last_migration_id'], 'Upgrade records migration 0003.');
fbsa_assert_same(1, $GLOBALS['fbsa_test_options']['fbsa_workspace_default_layouts']['controlsVersion'], 'Upgrade adds the Workspace controls marker.');
fbsa_assert_same($layouts_before, $GLOBALS['fbsa_test_options']['fbsa_workspace_default_layouts']['layouts'], 'Upgrade preserves the existing default layout records.');
fbsa_assert_same('keep', $GLOBALS['fbsa_test_options']['fbsa_workspace_default_layouts']['unknown_default_metadata'], 'Upgrade preserves unknown default-layout metadata.');
fbsa_assert_same($user_options_before, $GLOBALS['fbsa_test_user_options'], 'Migration 0003 does not change native Dashboard preferences or existing user layouts.');
fbsa_assert_true(!array_key_exists('fbsa_migration_lock', $GLOBALS['fbsa_test_options']), 'Upgrade releases the migration lock.');

fbsa_finish_tests();
