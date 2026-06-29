<?php
/**
 * Test runner for Monica WordPress plugin.
 */

require_once __DIR__ . '/bootstrap.php';

// Ensure the class name to file logic handles both 'test-monica-api.php' and 'test-class-monica-api.php'
class TestRunner {
    protected $passed = 0;
    protected $failed = 0;

    public function run( $testFile ) {
        echo "Running tests in $testFile...\n";
        require_once $testFile;

        $className = str_replace( '.php', '', basename( $testFile ) );
        $className = str_replace( '-', '_', $className );

        // Basic conversion from test-class-monica-api.php to Test_Class_Monica_API
        $className = implode( '_', array_map( 'ucfirst', explode( '_', $className ) ) );

        if ( ! class_exists( $className ) ) {
            // Also try running user functions starting with 'test_'
            $this->run_functions();
            return;
        }

        $testClass = new $className();
        $methods = get_class_methods( $testClass );

        foreach ( $methods as $method ) {
            if ( strpos( $method, 'test_' ) === 0 ) {
                echo "  Running $method...";
                try {
                    // Reset global state before each test if needed
                    $GLOBALS['wp_options'] = [];

                    $testClass->$method();
                    echo " PASSED\n";
                    $this->passed++;
                } catch ( Exception $e ) {
                    echo " FAILED\n";
                    echo "    " . $e->getMessage() . "\n";
                    $this->failed++;
                } catch ( Error $e ) {
                    echo " ERROR\n";
                    echo "    " . $e->getMessage() . "\n";
                    $this->failed++;
                }
            }
        }
    }

    public function run_functions() {
        $functions = get_defined_functions()['user'];
        foreach ( $functions as $function ) {
            if ( strpos( $function, 'test_' ) === 0 ) {
                echo "  Running $function...";
                try {
                    $GLOBALS['wp_options'] = [];
                    $function();
                    echo " PASSED\n";
                    $this->passed++;
                } catch ( Exception $e ) {
                    echo " FAILED\n";
                    echo "    " . $e->getMessage() . "\n";
                    $this->failed++;
                } catch ( Error $e ) {
                    echo " ERROR\n";
                    echo "    " . $e->getMessage() . "\n";
                    $this->failed++;
                }
            }
        }
    }

    public function report() {
        echo "\nSummary:\n";
        echo "  Passed: {$this->passed}\n";
        echo "  Failed: {$this->failed}\n";

        if ( $this->failed > 0 ) {
            exit( 1 );
        }
    }
}

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

$runner = new TestRunner();
$testFiles = glob( __DIR__ . '/test-*.php' );

if ( empty( $testFiles ) ) {
    echo "No tests found.\n";
    exit(0);
}

foreach ( $testFiles as $file ) {
    $runner->run( $file );
}

$runner->report();
