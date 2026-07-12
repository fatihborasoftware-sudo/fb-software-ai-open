<?php
require_once dirname(__DIR__) . '/support/wp-stubs.php';
require_once dirname(__DIR__) . '/support/assertions.php';

$GLOBALS['fbsa_test_current_user'] = 1;
$GLOBALS['fbsa_test_capabilities'] = array('manage_options' => true);
$_GET['page'] = 'fb-software-ai';

$root = dirname(__DIR__, 2);
require_once $root . '/fb-software-ai.php';

ob_start();
do_action('fbsa_settings_tabs');
$tab_html = ob_get_clean();
fbsa_assert_true(strpos($tab_html, 'data-fbsa-settings-tab="workspace"') !== false, 'Workspace settings tab markup is rendered.');

ob_start();
do_action('fbsa_settings_panels');
$panel_html = ob_get_clean();
fbsa_assert_true(strpos($panel_html, 'data-fbsa-workspace-controls') !== false, 'Workspace controls panel markup is rendered.');
fbsa_assert_same(4, substr_count($panel_html, 'data-fbsa-workspace-widget '), 'Workspace controls panel renders the four registered widgets.');
fbsa_assert_true(strpos($panel_html, 'data-fbsa-workspace-save') !== false, 'Workspace Save control is present.');
fbsa_assert_true(strpos($panel_html, 'data-fbsa-workspace-reset') !== false, 'Workspace Reset control is present.');

$workspace_enqueue_called = false;
foreach ($GLOBALS['fbsa_test_actions'] as $registered) {
    if ($registered['hook'] === 'admin_enqueue_scripts'
        && is_array($registered['callback'])
        && $registered['callback'][0] instanceof \FBSoftwareAI\Workspace\WorkspaceSettingsRenderer) {
        call_user_func($registered['callback'], 'tools_page_fb-software-ai');
        $workspace_enqueue_called = true;
    }
}
fbsa_assert_true($workspace_enqueue_called, 'Workspace settings asset callback is registered.');
fbsa_assert_true(isset($GLOBALS['fbsa_test_enqueued_styles']['fbsa-workspace-settings']), 'Workspace controls stylesheet is enqueued only on the settings page.');
fbsa_assert_true(isset($GLOBALS['fbsa_test_enqueued_scripts']['fbsa-workspace-settings']), 'Workspace controls script is enqueued only on the settings page.');
fbsa_assert_same('fbsaWorkspaceControls', $GLOBALS['fbsa_test_localized_scripts']['fbsa-workspace-settings']['object_name'], 'Workspace controls script receives its localized configuration object.');
fbsa_assert_same('valid-wp-rest-nonce', $GLOBALS['fbsa_test_localized_scripts']['fbsa-workspace-settings']['l10n']['nonce'], 'Workspace controls script receives a WordPress REST nonce.');

fbsa_finish_tests();
