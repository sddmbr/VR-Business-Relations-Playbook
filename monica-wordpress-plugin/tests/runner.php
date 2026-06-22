<?php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../includes/class-monica-contacts.php';
require_once __DIR__ . '/../includes/class-monica-settings.php';

$test_files = glob(__DIR__ . '/test-*.php');
foreach ($test_files as $file) {
    require_once $file;
}

$functions = get_defined_functions()['user'];
$test_functions = array_filter($functions, function($fn) {
    return strpos($fn, 'test_') === 0;
});

$passed = 0;
$failed = 0;

foreach ($test_functions as $test) {
    if ($test === 'test_save_contact_meta_data_autosave') {
        continue;
    }

    reset_mock_calls();
    $_POST = [];

    try {
        $test();
        echo "✅ $test\n";
        $passed++;
    } catch (Exception $e) {
        echo "❌ $test\n";
        echo "   " . $e->getMessage() . "\n";
        $failed++;
    }
}

if (in_array('test_save_contact_meta_data_autosave', $test_functions)) {
    if (!defined('DOING_AUTOSAVE')) {
        define('DOING_AUTOSAVE', true);
    }
    reset_mock_calls();
    $_POST = [];
    try {
        test_save_contact_meta_data_autosave();
        echo "✅ test_save_contact_meta_data_autosave\n";
        $passed++;
    } catch (Exception $e) {
        echo "❌ test_save_contact_meta_data_autosave\n";
        echo "   " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\nTests completed. Passed: $passed, Failed: $failed\n";
if ($failed > 0) {
    exit(1);
}
