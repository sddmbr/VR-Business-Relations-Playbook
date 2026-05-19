<?php

require_once __DIR__ . '/bootstrap.php';

$test_files = [
    __DIR__ . '/test-class-monica-api.php',
];

$passed = 0;
$failed = 0;

foreach ( $test_files as $file ) {
    if ( file_exists( $file ) ) {
        require_once $file;
    }
}

$functions = get_defined_functions()['user'];
$test_functions = array_filter($functions, function($func) {
    return strpos($func, 'test_') === 0;
});

echo "Running tests...\n";

foreach ( $test_functions as $func ) {
    try {
        $func();
        echo "✅ $func passed.\n";
        $passed++;
    } catch ( Exception $e ) {
        echo "❌ $func failed: " . $e->getMessage() . "\n";
        $failed++;
    }
}

echo "\nTests completed: $passed passed, $failed failed.\n";

if ( $failed > 0 ) {
    exit(1);
}
exit(0);
