<?php

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../includes/class-monica-contacts.php';

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
    global $mock_calls;
    $mock_calls = [];
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

echo "\nTests completed. Passed: $passed, Failed: $failed\n";
if ($failed > 0) {
    exit(1);
}
