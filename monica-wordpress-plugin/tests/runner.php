<?php

// Test harness execution script
$tests_dir = __DIR__;
require_once $tests_dir . '/bootstrap.php';

$test_files = glob($tests_dir . '/test-*.php');
$failures = 0;
$passes = 0;

foreach ($test_files as $file) {
    require_once $file;
    $class_name = 'Test_' . str_replace('-', '_', basename($file, '.php'));
    // Make Test_Class_Name instead of Test_test_class_name
    $class_name = str_replace('Test_test_', 'Test_', $class_name);
    $class_name = str_replace('Test_class_', 'Test_', $class_name);

    if (class_exists($class_name)) {
        $test_instance = new $class_name();
        $methods = get_class_methods($test_instance);
        foreach ($methods as $method) {
            if (strpos($method, 'test_') === 0) {
                try {
                    $test_instance->$method();
                    $passes++;
                    echo "Pass: $class_name::$method\n";
                } catch (Exception $e) {
                    $failures++;
                    echo "Fail: $class_name::$method - " . $e->getMessage() . "\n";
                }
            }
        }
    }
}

echo "\nTests completed: " . ($passes + $failures) . "\n";
echo "Passes: $passes\n";
echo "Failures: $failures\n";

exit($failures > 0 ? 1 : 0);
