<?php
$GLOBALS['fbsa_test_assertions'] = 0;
$GLOBALS['fbsa_test_failures'] = array();

function fbsa_assert_true($condition, $message) {
    $GLOBALS['fbsa_test_assertions']++;
    if (!$condition) {
        $GLOBALS['fbsa_test_failures'][] = $message;
        echo "FAIL: {$message}\n";
        return;
    }
    echo "PASS: {$message}\n";
}

function fbsa_assert_same($expected, $actual, $message) {
    fbsa_assert_true($expected === $actual, $message . ' (expected ' . var_export($expected, true) . ', got ' . var_export($actual, true) . ')');
}

function fbsa_finish_tests() {
    $count = (int) $GLOBALS['fbsa_test_assertions'];
    $failures = count($GLOBALS['fbsa_test_failures']);
    echo "\nAssertions: {$count}; Failures: {$failures}\n";
    exit($failures === 0 ? 0 : 1);
}
