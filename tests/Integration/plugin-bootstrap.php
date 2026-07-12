<?php
require_once dirname(__DIR__) . '/support/wp-stubs.php';
require_once dirname(__DIR__) . '/support/assertions.php';

$root = dirname(__DIR__, 2);
$GLOBALS['fbsa_test_options']['fbsa_demo_settings'] = array(
    'custom_unknown_field' => 'must-stay',
    'video_profiles' => array('tr' => array('custom' => 'same')),
);
$settings_before = $GLOBALS['fbsa_test_options']['fbsa_demo_settings'];

require_once $root . '/fb-software-ai.php';

fbsa_assert_true(class_exists('FBSA_Demo_Plugin', false), 'Legacy facade remains available.');
fbsa_assert_same('0.1.139', FBSA_Demo_Plugin::VERSION, 'Legacy facade version is synchronized.');
fbsa_assert_same('fbsa_website_steps_widget', FBSA_Demo_Plugin::DASHBOARD_WIDGET_WEBSITE_STEPS_ID, 'Website Steps widget ID is preserved.');
fbsa_assert_same('fbsa_plugin_setup_widget', FBSA_Demo_Plugin::DASHBOARD_WIDGET_PLUGIN_SETUP_ID, 'Plugin Setup widget ID is preserved.');
fbsa_assert_same('fbsa_website_settings_widget', FBSA_Demo_Plugin::DASHBOARD_WIDGET_WEBSITE_SETTINGS_ID, 'Website Settings widget ID is preserved.');
fbsa_assert_same('fbsa_help_tutorials_widget', FBSA_Demo_Plugin::DASHBOARD_WIDGET_HELP_TUTORIALS_ID, 'Help and Tutorials widget ID is preserved.');

$ajax = array();
$core_upgrade_hook = false;
$workspace_extension_hook = false;
foreach ($GLOBALS['fbsa_test_actions'] as $registered) {
    if (strpos($registered['hook'], 'wp_ajax_') === 0) {
        $ajax[] = substr($registered['hook'], 8);
    }
    if ($registered['hook'] === 'admin_init' && is_array($registered['callback']) && $registered['callback'][1] === 'maybe_upgrade' && (int) $registered['priority'] === 5) {
        $core_upgrade_hook = true;
    }
    if ($registered['hook'] === 'plugins_loaded' && is_array($registered['callback']) && $registered['callback'][1] === 'register_extensions' && (int) $registered['priority'] === 20) {
        $workspace_extension_hook = true;
    }
}
sort($ajax);
fbsa_assert_same(array('fbsa_create_content', 'fbsa_get_status', 'fbsa_run_command'), $ajax, 'All three legacy AJAX action names remain registered.');
fbsa_assert_true($core_upgrade_hook, 'Architecture admin-upgrade hook is registered at priority 5.');
fbsa_assert_true($workspace_extension_hook, 'Workspace extension registration hook is registered at plugins_loaded priority 20.');
$rest_hook = false;
$settings_tab_hook = false;
$settings_panel_hook = false;
foreach ($GLOBALS['fbsa_test_actions'] as $registered) {
    if ($registered['hook'] === 'rest_api_init') { $rest_hook = true; }
    if ($registered['hook'] === 'fbsa_settings_tabs') { $settings_tab_hook = true; }
    if ($registered['hook'] === 'fbsa_settings_panels') { $settings_panel_hook = true; }
}
fbsa_assert_true($rest_hook, 'Workspace REST registration hook is present.');
fbsa_assert_true($settings_tab_hook && $settings_panel_hook, 'Workspace settings tab and panel hooks are registered.');
fbsa_assert_same(1, count($GLOBALS['fbsa_test_activation_hooks']), 'One architecture activation hook is registered.');

$facade = FBSA_Demo_Plugin::instance();
$reflection = new ReflectionClass($facade);
$registry_property = $reflection->getProperty('workspace_widget_registry');
$registry_property->setAccessible(true);
$widget_registry = $registry_property->getValue($facade);
fbsa_assert_true($widget_registry instanceof \FBSoftwareAI\Workspace\WidgetRegistryInterface, 'Legacy facade is attached to the modular Widget Registry.');
$definitions = $widget_registry->for_context('dashboard');
fbsa_assert_same(4, count($definitions), 'Widget Registry contains four Dashboard definitions.');
$registered_ids = array_map(function ($definition) { return $definition->id(); }, $definitions);
sort($registered_ids);
fbsa_assert_same(array(
    'fbsa_help_tutorials_widget',
    'fbsa_plugin_setup_widget',
    'fbsa_website_settings_widget',
    'fbsa_website_steps_widget',
), $registered_ids, 'Registry preserves all Dashboard widget IDs.');

$callback = $GLOBALS['fbsa_test_activation_hooks'][0]['callback'];
call_user_func($callback, false);
fbsa_assert_same($settings_before, $GLOBALS['fbsa_test_options']['fbsa_demo_settings'], 'Activation migrations do not alter existing settings.');
fbsa_assert_same(1, $GLOBALS['fbsa_test_options']['fbsa_schema_versions']['core'], 'Activation establishes core schema version 1.');
fbsa_assert_same(2, $GLOBALS['fbsa_test_options']['fbsa_schema_versions']['workspace'], 'Activation establishes Workspace schema version 2.');
fbsa_assert_true(isset($GLOBALS['fbsa_test_options']['fbsa_workspace_default_layouts']), 'Activation seeds the default Workspace layout option.');
fbsa_assert_true(!array_key_exists('fbsa_migration_lock', $GLOBALS['fbsa_test_options']), 'Activation releases migration lock.');

$before_upgrade = $GLOBALS['fbsa_test_options']['fbsa_schema_versions'];
$before_defaults = $GLOBALS['fbsa_test_options']['fbsa_workspace_default_layouts'];
foreach ($GLOBALS['fbsa_test_actions'] as $registered) {
    if ($registered['hook'] === 'admin_init' && is_array($registered['callback']) && $registered['callback'][1] === 'maybe_upgrade') {
        call_user_func($registered['callback']);
    }
}
fbsa_assert_same($before_upgrade, $GLOBALS['fbsa_test_options']['fbsa_schema_versions'], 'Admin upgrade path is a no-op after all schemas are current.');
fbsa_assert_same($before_defaults, $GLOBALS['fbsa_test_options']['fbsa_workspace_default_layouts'], 'Admin upgrade path does not rewrite Workspace defaults.');

fbsa_finish_tests();
