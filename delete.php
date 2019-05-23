<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS `" . $wpdb->base_prefix . "otfm_friends`" );
$wpdb->query( "DROP TABLE IF EXISTS `" . $wpdb->base_prefix . "otfm_friends_messages`" );

delete_option( 'friends_db_ver' );
delete_option( 'friends_mess_db_ver' );
