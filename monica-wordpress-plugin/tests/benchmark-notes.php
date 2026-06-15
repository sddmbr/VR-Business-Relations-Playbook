<?php
require_once __DIR__ . '/bootstrap.php';

// Add get() to mock API
class MockAPI extends Monica_API {
    public function get($endpoint, $args = []) {
        usleep(500000); // 500ms delay to simulate network
        return ['data' => [['body' => 'Note 1']]];
    }
}

// Override the class before including the original
if (!class_exists('Monica_API', false)) {
    // Wait, it's defined in bootstrap.php
}
