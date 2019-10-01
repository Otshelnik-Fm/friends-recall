<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS `" . $wpdb->base_prefix . "otfm_friends`" );
$wpdb->query( "DROP TABLE IF EXISTS `" . $wpdb->base_prefix . "otfm_friends_messages`" );

delete_option( 'friends_db_ver' );
delete_option( 'friends_mess_db_ver' );

// при удалении аддона удаляем из wp_usermeta ключи: frnd_total_friends, frnd_incoming_call, frnd_outgoing_call

$meta_type  = 'user';
$user_id    = 0;        // у всех пользователей.
$meta_value = '';       // любые значения.
$delete_all = true;     // удаляем все

delete_metadata( $meta_type, $user_id, 'frnd_total_friends', $meta_value, $delete_all );
delete_metadata( $meta_type, $user_id, 'frnd_incoming_call', $meta_value, $delete_all );
delete_metadata( $meta_type, $user_id, 'frnd_outgoing_call', $meta_value, $delete_all );
