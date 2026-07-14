<?php

class Test_Monica_Relationships extends PHPUnit\Framework\TestCase {
    public function test_relationships_loads() {
        require_once __DIR__ . '/../includes/class-monica-relationships.php';
        $this->assertTrue(class_exists('Monica_Relationships'));
    }
}
