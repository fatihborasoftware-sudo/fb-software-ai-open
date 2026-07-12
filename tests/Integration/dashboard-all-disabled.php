<?php
require_once dirname(__DIR__) . '/support/wp-stubs.php';
require_once dirname(__DIR__) . '/support/assertions.php';

$GLOBALS['fbsa_test_current_user'] = 1;
$GLOBALS['fbsa_test_capabilities'] = array('manage_options' => true);
$GLOBALS['fbsa_test_user_options'] = array(
    1 => array(
        'fbsa_workspace_layouts' => array(
            'dashboard' => array(
                'schemaVersion' => 1,
                'context' => 'dashboard',
                'layoutId' => 'all-disabled',
                'roleTemplate' => null,
                'widgets' => array(
                    array('id' => 'fbsa_website_steps_widget', 'visible' => false, 'column' => 3, 'order' => 0, 'collapsed' => false, 'width' => null, 'height' => null, 'settings' => array()),
                    array('id' => 'fbsa_website_settings_widget', 'visible' => false, 'column' => 3, 'order' => 1, 'collapsed' => false, 'width' => null, 'height' => null, 'settings' => array()),
                    array('id' => 'fbsa_plugin_setup_widget', 'visible' => false, 'column' => 4, 'order' => 2, 'collapsed' => false, 'width' => null, 'height' => null, 'settings' => array()),
                    array('id' => 'fbsa_help_tutorials_widget', 'visible' => false, 'column' => 4, 'order' => 3, 'collapsed' => false, 'width' => null, 'height' => null, 'settings' => array()),
                ),
                'updatedAt' => '2026-07-12 12:00:00',
                'source' => 'user',
            ),
        ),
    ),
);

$root = dirname(__DIR__, 2);
require_once $root . '/fb-software-ai.php';

$facade = FBSA_Demo_Plugin::instance();
$reflection = new ReflectionClass($facade);
$method = $reflection->getMethod('get_dashboard_widgets');
$method->setAccessible(true);
$widgets = $method->invoke($facade);

fbsa_assert_same(array(), $widgets, 'An authoritative empty Workspace layout does not fall back to the four legacy widgets.');
fbsa_finish_tests();
