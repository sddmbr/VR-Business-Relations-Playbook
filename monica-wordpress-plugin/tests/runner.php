<?php
require_once __DIR__ . '/bootstrap.php';

$test_files = [
    __DIR__ . '/test_relationships.php',
];

$passed = 0;
$failed = 0;

foreach ( $test_files as $file ) {
    require_once $file;
    $functions = get_defined_functions()['user'];
    foreach ( $functions as $function ) {
        if ( strpos( $function, 'test_' ) === 0 ) {
            echo "Running $function... ";
            try {
                $function();
                echo "PASSED\n";
                $passed++;
            } catch ( Exception $e ) {
                echo "FAILED: " . $e->getMessage() . "\n";
                $failed++;
            }
        }
    }
}

echo "\nTests completed: $passed passed, $failed failed.\n";
exit( $failed > 0 ? 1 : 0 );
