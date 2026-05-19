<?php
require_once __DIR__ . '/bootstrap.php';
require_once dirname(__DIR__) . '/includes/class-monica-relationships.php';

function test_monica_relationships_instantiation() {
    $relationships = new Monica_Relationships();
    if ( ! ( $relationships instanceof Monica_Relationships ) ) {
        throw new Exception( "Monica_Relationships class not found" );
    }
}

function test_ajax_search_contacts_exists() {
    $relationships = new Monica_Relationships();
    if ( ! method_exists( $relationships, 'ajax_search_contacts' ) ) {
        throw new Exception( "ajax_search_contacts method not found" );
    }
}
