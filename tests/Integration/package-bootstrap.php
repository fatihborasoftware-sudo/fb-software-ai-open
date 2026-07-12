<?php
require_once dirname(__DIR__) . '/support/wp-stubs.php';
require_once dirname(__DIR__) . '/support/assertions.php';

$root = isset($argv[1]) ? rtrim($argv[1], '/\\') : '';
fbsa_assert_true($root !== '' && is_file($root . '/fb-software-ai.php'), 'Extracted package root contains the plugin bootstrap.');
$GLOBALS['fbsa_test_options']['fbsa_demo_settings'] = array('package' => 'preserve');
$before = $GLOBALS['fbsa_test_options']['fbsa_demo_settings'];
require_once $root . '/fb-software-ai.php';
fbsa_assert_true(class_exists('FBSoftwareAI\\Core\\Plugin'), 'Extracted package autoloader resolves the core plugin class.');
fbsa_assert_true(class_exists('FBSoftwareAI\\Migrations\\Core\\Version0001Baseline'), 'Extracted package autoloader resolves migration 0001.');
fbsa_assert_true(class_exists('FBSoftwareAI\\Migrations\\Workspace\\Version0002WorkspaceFoundation'), 'Extracted package autoloader resolves migration 0002.');
fbsa_assert_true(class_exists('FBSoftwareAI\\Migrations\\Workspace\\Version0003WorkspaceControls'), 'Extracted package autoloader resolves migration 0003.');
fbsa_assert_true(class_exists('FBSoftwareAI\\Workspace\\WidgetRegistry'), 'Extracted package autoloader resolves the Widget Registry.');
$callback = $GLOBALS['fbsa_test_activation_hooks'][0]['callback'];
call_user_func($callback, false);
fbsa_assert_same($before, $GLOBALS['fbsa_test_options']['fbsa_demo_settings'], 'Extracted package activation preserves legacy settings.');
fbsa_assert_same(1, $GLOBALS['fbsa_test_options']['fbsa_schema_versions']['core'], 'Extracted package activation applies core schema 1.');
fbsa_assert_same(2, $GLOBALS['fbsa_test_options']['fbsa_schema_versions']['workspace'], 'Extracted package activation applies Workspace schema 2.');
fbsa_assert_true(isset($GLOBALS['fbsa_test_options']['fbsa_workspace_default_layouts']), 'Extracted package activation creates Workspace defaults.');
fbsa_finish_tests();
