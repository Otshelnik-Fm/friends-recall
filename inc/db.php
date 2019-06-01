<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * Статусы:
 *
 * NULL - никакой связи не было
 * 0    - не используется
 * 1    - заявка на дружбу подана. Рассматривается
 * 2    - дружит
 * 3    - отклонён. Значит подписчик
 * 4    - заблокирован. Бан
 *
 *  */


/* проверить - этот в друзьях */
function frnd_get_friend_by_id( $user_id, $friend ) {
    global $wpdb;

    $status = $wpdb->get_var( $wpdb->prepare(
            "SELECT status "
            . "FROM " . FRND_DB . " "
            . "WHERE (owner_id,friend_id) IN (('%d','%d'),('%d','%d'))", $user_id, $friend, $friend, $user_id
        ) );

    return $status;
}

// запрос в друзья
function frnd_insert_offer_db( $user_id, $friend ) {
    global $wpdb;

    $status = $wpdb->insert(
        FRND_DB, array( 'owner_id' => $user_id, 'friend_id' => $friend, 'actions_date' => current_time( 'mysql' ), 'status' => 1 ), array( '%d', '%d', '%s', '%d' )
    );

    if ( $status ) {
        do_action( 'frnd_offer', $user_id, $friend );
    }

    return $status;
}

// текст запроса в друзья
function frnd_insert_offer_message_db( $user_id, $friend, $mess ) {
    global $wpdb;

    $status = $wpdb->insert(
        FRND_MESS_DB, array( 'from_id' => $user_id, 'to_id' => $friend, 'message' => $mess ), array( '%d', '%d', '%s' )
    );

    return $status;
}

// получим текст запроса в друзья
function frnd_get_messages_db( $user_id, $friend ) {
    global $wpdb;

    $mess = $wpdb->get_var( $wpdb->prepare(
            "SELECT message "
            . "FROM " . FRND_MESS_DB . " "
            . "WHERE (from_id,to_id) IN (('%d','%d'),('%d','%d'))", $user_id, $friend, $friend, $user_id
        ) );

    return $mess;
}

// одобрим запрос
function frnd_confirm_offer_db( $from, $to_user ) {
    global $wpdb;

    $update = $wpdb->update( FRND_DB, array( 'owner_id' => $to_user, 'friend_id' => $from, 'actions_date' => current_time( 'mysql' ), 'status' => 2 ), array( 'owner_id' => $to_user, 'friend_id' => $from ), array( '%d', '%d', '%s', '%d' ), array( '%d', '%d' )
    );

    $insert = $wpdb->insert(
        FRND_DB, array( 'owner_id' => $from, 'friend_id' => $to_user, 'actions_date' => current_time( 'mysql' ), 'status' => 2 ), array( '%d', '%d', '%s', '%d' )
    );

    frnd_del_message( $from, $to_user );

    if ( $update && $insert ) {
        do_action( 'frnd_confirm_offer', $from, $to_user );
        return true;
    } else {
        return false;
    }
}

// удалим сообщение к дружбе
function frnd_del_message( $from, $to_user ) {
    global $wpdb;

    $del = $wpdb->delete( FRND_MESS_DB, array( 'from_id' => $to_user, 'to_id' => $from ), array( '%d', '%d' ) );

    return $del;
}

// отклоним запрос
function frnd_reject_offer_db( $from, $to_user ) {
    global $wpdb;

    $update = $wpdb->update( FRND_DB, array( 'owner_id' => $to_user, 'friend_id' => $from, 'actions_date' => current_time( 'mysql' ), 'status' => 3 ), array( 'owner_id' => $to_user, 'friend_id' => $from ), array( '%d', '%d', '%s', '%d' ), array( '%d', '%d' )
    );

    frnd_del_message( $from, $to_user );

    // опция "Подписывать при отказе в дружбе"
    if ( rcl_get_option( 'frnd_rej_subs', 'yes' ) === 'yes' && function_exists( 'rcl_insert_feed_data' ) ) {
        frnd_sign_it_feed( $from, $to_user );
    }

    if ( $update ) {
        do_action( 'frnd_reject_offer', $from, $to_user );
        return true;
    } else {
        return false;
    }
}

// удалим из друзей
function frnd_delete_friend_db( $from, $to_user ) {
    global $wpdb;

    $del = $wpdb->query( $wpdb->prepare( "DELETE FROM " . FRND_DB . " WHERE (owner_id,friend_id) IN (('%d','%d'),('%d','%d'))", $from, $to_user, $to_user, $from ) );

    frnd_del_message( $from, $to_user );

    // опция "Подписывать при удалении из друзей"
    if ( rcl_get_option( 'frnd_del_subs', 'yes' ) === 'yes' && function_exists( 'rcl_insert_feed_data' ) ) {
        frnd_sign_it_feed( $from, $to_user );
    }

    if ( $del ) {
        return true;
    } else {
        return false;
    }
}

//
// ко мне есть запросы в друзья? - посчитаем
function frnd_incoming_friend_count( $user_id ) {
    global $wpdb;

    $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(id) "
            . "FROM " . FRND_DB . " "
            . "WHERE friend_id = %d AND status = 1", $user_id
        ) );

    return $count;
}

// от меня есть запросы в друзья? - посчитаем
function frnd_outcoming_friend_count( $user_id ) {
    global $wpdb;

    $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(id) "
            . "FROM " . FRND_DB . " "
            . "WHERE owner_id = %d AND status = 1", $user_id
        ) );

    return $count;
}

// друзья по ID - посчитаем
function frnd_user_friend_count( $user_id ) {
    global $wpdb;

    $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(id) "
            . "FROM " . FRND_DB . " "
            . "WHERE friend_id = %d AND status = 2", $user_id
        ) );

    return $count;
}

// подписан ли юзер
function frnd_is_feed( $from, $to_user ) {
    global $wpdb;

    $count = $wpdb->get_var( "SELECT COUNT(feed_id) FROM " . RCL_PREF . "feeds WHERE user_id={$to_user} AND object_id={$from}" );

    return $count;
}

// проверим подписку и подпишем
function frnd_sign_it_feed( $from, $to_user ) {
    $subs = frnd_is_feed( $from, $to_user );

    if ( ! $subs || $subs == 0 ) {

        $args = array(
            'user_id'     => $to_user,
            'object_id'   => $from,
            'feed_type'   => 'author',
            'feed_status' => 1
        );

        rcl_insert_feed_data( $args );
    }
}
