<?php

require_once __DIR__ . '/bootstrap.php';

class TestRunner {
    protected $passed = 0;
    protected $failed = 0;
    protected $errors = [];

    public function run( $testFile ) {
        echo "Running tests in $testFile...\n";
        require_once $testFile;

        $className = str_replace( '.php', '', basename( $testFile ) );
        $className = str_replace( '-', '_', $className );
        // Basic conversion from test-class-monica-api.php to Test_Class_Monica_API
        $className = implode( '_', array_map( 'ucfirst', explode( '_', $className ) ) );

        if ( ! class_exists( $className ) ) {
            echo "Error: Class $className not found in $testFile\n";
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

function assertEquals( $expected, $actual, $message = '' ) {
    if ( $expected !== $actual ) {
        throw new Exception( "Expected: $expected, Actual: $actual. $message" );
    }
}

$runner = new TestRunner();
$testFiles = glob( __DIR__ . '/test-*.php' );
foreach ( $testFiles as $file ) {
    $runner->run( $file );
}
$runner->report();
