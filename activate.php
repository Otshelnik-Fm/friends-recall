<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$frnd_friends_db_version = '1.0';
update_option( 'friends_db_ver', $frnd_friends_db_version, false );

$frnd_friends_messages_db_version = '1.0';
update_option( 'friends_mess_db_ver', $frnd_friends_messages_db_version, false );


global $wpdb;

$wpdb->hide_errors();

$frnd_collate = '';

if ( $wpdb->has_cap( 'collation' ) ) {
    if ( ! empty( $wpdb->charset ) ) {
        $frnd_collate .= "DEFAULT CHARACTER SET $wpdb->charset";
    }
    if ( ! empty( $wpdb->collate ) ) {
        $frnd_collate .= " COLLATE $wpdb->collate";
    }
}

require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

$frnd_friends_name = $wpdb->base_prefix . 'otfm_friends';

$frnd_friends_table = "CREATE TABLE IF NOT EXISTS `{$frnd_friends_name}` (
                                `id` bigint(20) unsigned NOT NULL auto_increment,
				`owner_id` bigint(20) unsigned NOT NULL,
				`friend_id` bigint(20) unsigned NOT NULL,
                                `actions_date` datetime NOT NULL default '0000-00-00 00:00:00',
                                `status` tinyint(1) unsigned NOT NULL default '0',
                                PRIMARY KEY (`id`),
                                KEY `owner_id` (`owner_id`),
                                KEY `friend_id` (`friend_id`)
                        ) $frnd_collate;
                ";

dbDelta( $frnd_friends_table );


$frnd_mess_name = $wpdb->base_prefix . 'otfm_friends_messages';

$frnd_mess_table = "CREATE TABLE IF NOT EXISTS `{$frnd_mess_name}` (
                                `id` bigint(20) unsigned NOT NULL auto_increment,
				`from_id` bigint(20) unsigned NOT NULL,
				`to_id` bigint(20) unsigned NOT NULL,
                                `message` longtext NOT NULL,
                                PRIMARY KEY (`id`),
                                KEY `from_id` (`from_id`),
                                KEY `to_id` (`to_id`)
                        ) $frnd_collate;
                ";

dbDelta( $frnd_mess_table );

