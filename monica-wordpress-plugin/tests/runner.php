<?php
/**
 * Test runner for Monica WordPress plugin.
 */

require_once __DIR__ . '/bootstrap.php';

$test_files = [
    __DIR__ . '/test-monica-api.php',
];

$passed = 0;
$failed = 0;
$total  = 0;

$already_run = [];

foreach ( $test_files as $file ) {
    if ( ! file_exists( $file ) ) {
        continue;
    }

    require_once $file;

    $functions = get_defined_functions();
    $user_functions = $functions['user'];

    foreach ( $user_functions as $function ) {
        if ( strpos( $function, 'test_' ) === 0 && ! in_array( $function, $already_run ) ) {
            $total++;
            $already_run[] = $function;
            try {
                echo "Running $function... ";
                $function();
                echo "PASSED\n";
                $passed++;
            } catch ( Exception $e ) {
                echo "FAILED: " . $e->getMessage() . "\n";
                $failed++;
            } catch ( Error $e ) {
                echo "ERROR: " . $e->getMessage() . "\n";
                $failed++;
            }
        }
    }
}

echo "\nTests completed: $total\n";
echo "Passed: $passed\n";
echo "Failed: $failed\n";

exit( $failed > 0 ? 1 : 0 );

/**
 * Basic assertion functions
 */
function assert_true( $condition, $message = 'Assertion failed' ) {
    if ( ! $condition ) {
        throw new Exception( $message );
    }
}

function assert_equals( $expected, $actual, $message = 'Assertion failed' ) {
    if ( $expected !== $actual ) {
        $message .= sprintf( " (Expected: %s, Actual: %s)", var_export( $expected, true ), var_export( $actual, true ) );
        throw new Exception( $message );
    }
}

function assert_wp_error( $actual, $message = 'Expected WP_Error' ) {
    if ( ! ( $actual instanceof WP_Error ) ) {
        throw new Exception( $message );
    }
}
