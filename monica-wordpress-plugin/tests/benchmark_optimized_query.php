<?php
require_once __DIR__ . '/bootstrap.php';

// Mock get_posts to return a large number of contacts
if (!function_exists('get_posts_mock')) {
    function get_posts_mock( $count ) {
        $contacts = [];
        for ( $i = 1; $i <= $count; $i++ ) {
            $contact = new stdClass();
            $contact->ID = $i;
            $contact->post_title = "Contact $i";
            $contacts[] = $contact;
        }
        return $contacts;
    }
}

// Mock get_post_meta to return a Monica ID
if (!function_exists('get_post_meta_mock')) {
    function get_post_meta_mock( $post_id ) {
        return "monica_$post_id";
    }
}

$counts = [10, 100, 1000, 5000];

echo "Benchmarking OPTIMIZED query (limited to 20 results)...\n";
echo str_pad("Total Pool", 10) . " | " . str_pad("Time (ms)", 15) . " | " . str_pad("Memory (KB)", 15) . "\n";
echo str_repeat("-", 45) . "\n";

foreach ( $counts as $count ) {
    $start_time = microtime( true );
    $start_memory = memory_get_usage();

    // Simulated AJAX search: always returns max 20 results regardless of total pool size
    $contacts = get_posts_mock( min($count, 20) );
    $results = [];
    foreach ( $contacts as $contact ) {
        $monica_id = get_post_meta_mock( $contact->ID );
        if ( $monica_id ) {
            $results[] = [
                'id'    => $monica_id,
                'label' => $contact->post_title,
                'value' => $contact->post_title,
            ];
        }
    }
    $json = json_encode($results);

    $end_time = microtime( true );
    $end_memory = memory_get_usage();

    $time_ms = ( $end_time - $start_time ) * 1000;
    $memory_kb = ( $end_memory - $start_memory ) / 1024;

    echo str_pad($count, 10) . " | " . str_pad(number_format($time_ms, 2), 15) . " | " . str_pad(number_format($memory_kb, 2), 15) . "\n";
}
