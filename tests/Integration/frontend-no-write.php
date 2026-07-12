<?php
require_once dirname(__DIR__) . '/support/wp-stubs.php';
require_once dirname(__DIR__) . '/support/assertions.php';

$GLOBALS['fbsa_test_is_admin'] = false;
$GLOBALS['fbsa_test_options'] = array(
    'fbsa_demo_settings' => array('preserve' => 'front-end'),
    'unrelated_option' => array('same' => true),
);
$before = $GLOBALS['fbsa_test_options'];
$root = dirname(__DIR__, 2);
require_once $root . '/fb-software-ai.php';

fbsa_assert_same($before, $GLOBALS['fbsa_test_options'], 'Loading the plugin on a front-end request performs no option writes.');
fbsa_assert_true(!array_key_exists('fbsa_schema_versions', $GLOBALS['fbsa_test_options']), 'Front-end bootstrap does not create schema versions.');
fbsa_finish_tests();
