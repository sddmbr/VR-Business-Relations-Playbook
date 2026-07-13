<?php

require_once __DIR__ . '/bootstrap.php';

$test_files = [
    __DIR__ . '/test-monica-api.php',
];

$failed = false;

foreach ( $test_files as $file ) {
    if ( file_exists( $file ) ) {
        require_once $file;
    }
}

// Function to run tests
function run_test( $name, $callback ) {
    global $failed;
    try {
        $callback();
        echo "PASS: $name\n";
    } catch ( Exception $e ) {
        echo "FAIL: $name - " . $e->getMessage() . "\n";
        $failed = true;
    }
}

// Call test functions (they will be defined in test files)
$test_functions = get_defined_functions()['user'];
foreach ( $test_functions as $func ) {
    if ( strpos( $func, 'test_' ) === 0 ) {
        run_test( $func, $func );
    }
}

if ( $failed ) {
    exit( 1 );
}

exit( 0 );
