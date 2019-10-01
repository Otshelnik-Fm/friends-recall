<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/*
 * В БД статусы:
 *
 * NULL - никакой связи не было
 * 0    - не используется. Но зарезервировано функцией ниже
 * 1    - заявка на дружбу подана. Рассматривается
 * 2    - дружит
 * 3    - отклонён. Значит подписчик
 * 4    - заблокирован. Бан
 *
 *  */

/**/
/**
 * Получим статус дружбы 2-х пользователей (числом)
 *
 * @since 2.0
 *
 * @param int $user_id  id юзера.
 *
 * @param int $friend   id друга.
 *
 * @return int          '0'     - Нет дружбы
 *                      '1'     - Заявка на дружбу подана. Рассматривается.
 *                      '2'     - Дружит.
 *                      '3'     - Отклонён. Значит подписчик.
 *                      '4'     - Заблокирован. Бан.
 */
function frnd_get_friendship_status_code( $user_id, $friend ) {
    global $wpdb;

    $status = $wpdb->get_var( $wpdb->prepare(
            "SELECT status "
            . "FROM " . FRND_DB . " "
            . "WHERE owner_id = '%d' "
            . "AND friend_id = '%d'", $user_id, $friend
        ) );

    if ( ! $status )
        $status = 0;

    return $status;
}

/**
 * Получим связи дружбы
 *
 * @since 2.0
 *
 * @param int $user_id  id юзера.
 *
 * @param int $friend   id друга.
 *
 * @return array        Связи:
 *                          [0]ты->друг->статус
 *                          [1]друг->ты->статус
 *                      Статусы:
 *                          NULL - Никакой связи нет.
 *                          1    - Заявка на дружбу подана. Рассматривается.
 *                          2    - Дружит.
 *                          3    - Отклонён. Значит подписчик.
 *                          4    - Заблокирован. Бан.
 */
function frnd_get_relation_friendship( $user_id, $friend ) {
    if ( ! $user_id || ! $friend )
        return;

    global $wpdb;

    $status = $wpdb->get_results(
        "SELECT `owner_id`, `friend_id`, `status` "
        . "FROM " . FRND_DB . " "
        . "WHERE (owner_id,friend_id) "
        . "IN ((" . $user_id . "," . $friend . "),(" . $friend . "," . $user_id . "))"
        . "", ARRAY_A
    );

    return $status;
}

// повторный запрос в друзья
function frnd_update_offer_db( $user_id, $friend ) {
    global $wpdb;

    $status = false;

    // проверяем связи "кто к кому"
    $relation = frnd_get_relation_friendship( $user_id, $friend );

    // этот запрос был. Обновим время, статус
    if ( ($relation[0]['owner_id'] == $user_id || $relation[0]['friend_id'] == $user_id ) && $relation[0]['status'] == 3 ) {
        $data   = array( 'actions_date' => current_time( 'mysql' ), 'status' => 1 );
        $where  = array( 'owner_id' => $user_id, 'friend_id' => $friend );
        $format = array( '%s', '%d' );

        $status = $wpdb->update(
            FRND_DB, $data, $where, $format
        );
    }

    if ( isset( $status ) && $status > 0 ) {
        do_action( 'frnd_send_request', $user_id, $friend );
    }

    return $status;
}

/**
 * Подаем запрос в друзья
 *
 * @since 2.0
 *
 * @param int $user_id  (from) id юзера.
 *
 * @param int $friend   (to) id друга.
 *
 * @return int|bool     'число' - при успешной вставке.
 *                      'false' — если данные не были вставлены в таблицу.
 */
function frnd_send_friend_request( $user_id, $friend ) {
    global $wpdb;

    $status = $wpdb->insert(
        FRND_DB, array( 'owner_id' => $user_id, 'friend_id' => $friend, 'actions_date' => current_time( 'mysql' ), 'status' => 1 ), array( '%d', '%d', '%s', '%d' )
    );

    if ( $status ) {
        do_action( 'frnd_send_request', $user_id, $friend );
    }

    return $status;
}

/**
 * Запишем в БД текст запроса в друзья
 *
 * @since 2.0
 *
 * @param int $user_id  id юзера.
 *
 * @param int $friend   id друга.
 *
 * @param string $mess  текст сообщения к дружбе.
 *
 * @return int|bool     'число' - при успешной вставке.
 *                      'false' — если данные не были вставлены в таблицу.
 */
function frnd_send_friend_request_message( $user_id, $friend, $mess ) {
    global $wpdb;

    $status = $wpdb->insert(
        FRND_MESS_DB, array( 'from_id' => $user_id, 'to_id' => $friend, 'message' => $mess ), array( '%d', '%d', '%s' )
    );

    if ( $status ) {
        do_action( 'frnd_offer_message', $user_id, $friend, $mess );
    }

    return $status;
}

/**
 * Получим текст запроса в друзья
 *
 * @since 2.0
 *
 * @param int $user_id  id юзера.
 *
 * @param int $friend   id друга.
 *
 *
 * @return text|null    'text' - Текст сообщения.
 *                      'null' - Если ничего не найдено
 */
function frnd_get_friend_request_message( $user_id, $friend ) {
    global $wpdb;

    $mess = $wpdb->get_var( $wpdb->prepare(
            "SELECT message "
            . "FROM " . FRND_MESS_DB . " "
            . "WHERE (from_id,to_id) IN (('%d','%d'),('%d','%d'))", $user_id, $friend, $friend, $user_id
        ) );

    return esc_html( $mess );
}

/**
 * Одобрим запрос в друзья (сменим статус первого и добавим второго как друга)
 *
 * @since 2.0
 *
 * @param int $from     id юзера.
 *
 * @param int $to_user  id друга.
 *
 * @return bool         'true' - при успешной отработке.
 *                      'false' — если данные не были вставлены в таблицу.
 */
function frnd_confirm_friend_request( $from, $to_user ) {
    global $wpdb;

    $update = $wpdb->update( FRND_DB, array( 'owner_id' => $to_user, 'friend_id' => $from, 'actions_date' => current_time( 'mysql' ), 'status' => 2 ), array( 'owner_id' => $to_user, 'friend_id' => $from ), array( '%d', '%d', '%s', '%d' ), array( '%d', '%d' )
    );

    // получим статус - вдруг уже был в подписчиках или забанен
    $status = frnd_get_status_friendship( $from, $to_user );

    // имелись связи - обновим
    if ( isset( $status ) && $status > 0 ) {
        $insert = $wpdb->update( FRND_DB, array( 'owner_id' => $from, 'friend_id' => $to_user, 'actions_date' => current_time( 'mysql' ), 'status' => 2 ), array( 'owner_id' => $from, 'friend_id' => $to_user ), array( '%d', '%d', '%s', '%d' ), array( '%d', '%d' )
        );
    } else {
        $insert = $wpdb->insert(
            FRND_DB, array( 'owner_id' => $from, 'friend_id' => $to_user, 'actions_date' => current_time( 'mysql' ), 'status' => 2 ), array( '%d', '%d', '%s', '%d' )
        );
    }

    frnd_del_message( $from, $to_user );

    if ( $update && $insert ) {
        do_action( 'frnd_confirm_request', $from, $to_user );
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

/**
 * Отклоним запрос в друзья (и переведем его в подписчики)
 *
 * @since 2.0
 *
 * @param int $from     id юзера.
 *
 * @param int $to_user  id друга.
 *
 * @return bool         'true' - при успешной отработке.
 *                      'false' — при ошибке.
 */
function frnd_reject_friend_request( $from, $to_user ) {
    global $wpdb;

    $update = $wpdb->update( FRND_DB, array( 'owner_id' => $to_user, 'friend_id' => $from, 'actions_date' => current_time( 'mysql' ), 'status' => 3 ), array( 'owner_id' => $to_user, 'friend_id' => $from ), array( '%d', '%d', '%s', '%d' ), array( '%d', '%d' )
    );

    frnd_del_message( $from, $to_user );

    // опция "Подписывать при отказе в дружбе"
    if ( rcl_get_option( 'frnd_rej_subs', 'yes' ) === 'yes' && function_exists( 'rcl_insert_feed_data' ) ) {
        frnd_sign_it_feed( $from, $to_user );
    }

    if ( $update ) {
        do_action( 'frnd_reject_request', $from, $to_user );
        return true;
    } else {
        return false;
    }
}

/**
 * Удалим из друзей
 *
 * @since 2.0
 *
 * @param int $from     id юзера.
 *
 * @param int $to_user  id друга.
 *
 * @return bool         'true' - при успешной отработке.
 *                      'false' — при ошибке.
 */
function frnd_remove_from_friends( $from, $to_user ) {
    global $wpdb;

    $del = $wpdb->query( $wpdb->prepare( "DELETE FROM " . FRND_DB . " WHERE (owner_id,friend_id) IN (('%d','%d'),('%d','%d'))", $from, $to_user, $to_user, $from ) );

    frnd_del_message( $from, $to_user );

    // опция "Подписывать при удалении из друзей"
    if ( rcl_get_option( 'frnd_del_subs', 'yes' ) === 'yes' && function_exists( 'rcl_insert_feed_data' ) ) {
        frnd_sign_it_feed( $from, $to_user );
    }

    if ( $del ) {
        do_action( 'frnd_delete_friend', $from, $to_user );
        return true;
    } else {
        return false;
    }
}

/**
 * Считаем входящие запросы в друзья
 *
 * @since 2.0
 *
 * @param int $user_id  id юзера.
 *
 * @return int|null     Число входящих запросов ('null' - если нет)
 */
function frnd_count_incoming_friend_requests( $user_id ) {
    global $wpdb;

    $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(id) "
            . "FROM " . FRND_DB . " "
            . "WHERE friend_id = %d AND status = 1", $user_id
        ) );

    return $count;
}

// от меня есть запросы в друзья? - посчитаем
/**
 * Считаем исходящие запросы в друзья
 *
 * @since 2.0
 *
 * @param int $user_id  id юзера.
 *
 * @return int|null     Число исходящих запросов ('null' - если нет)
 */
function frnd_count_outgoing_friend_requests( $user_id ) {
    global $wpdb;

    $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(id) "
            . "FROM " . FRND_DB . " "
            . "WHERE owner_id = %d AND status = 1", $user_id
        ) );

    return $count;
}

/**
 * Посчитаем сколько друзей по ID
 *
 * @since 2.0
 *
 * @param int $user_id  id юзера.
 *
 * @return int          Число друзей (0 - если нет)
 */
function frnd_count_user_friends( $user_id ) {
    global $wpdb;

    $count = $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(id) "
            . "FROM " . FRND_DB . " "
            . "WHERE owner_id = %d AND status = 2", $user_id
        ) );

    if ( ! $count ) {
        return 0;
    }

    return $count;
}

// Друзья по ID
// Используй вместо неё функцию frnd_get_friends_by_id()
function frnd_friend_by_id_db( $user_id, $limit, $offset ) {
    global $wpdb;

    $ids = $wpdb->get_col( $wpdb->prepare(
            "SELECT owner_id "
            . "FROM " . FRND_DB . " "
            . "WHERE friend_id = %d AND status = 2 "
            . "LIMIT %d, %d", $user_id, $offset, $limit
        ) );

    return $ids;
}

// подписан ли юзер (доп FEED)
function frnd_is_feed( $from, $to_user ) {
    global $wpdb;

    $count = $wpdb->get_var( "SELECT COUNT(feed_id) FROM " . RCL_PREF . "feeds WHERE user_id={$to_user} AND object_id={$from}" );

    return $count;
}

// кто онлайн
/*
  Array
  (
  [0] => Array
  (
  [ID] => 1
  [display_name] => Анжелика
  [user_nicename] => otshelnik-fm
  [user_email] => otshelnik-fm@yandex.ru
  [meta_value] => http://test-recall.otshelnik-fm.ru/wp-content/uploads/rcl-uploads/avatars/1.jpg
  [time_action] => 2019-07-10 13:44:56
  )

  )
 */
function frnd_get_online_friends( $limit ) {
    global $wpdb, $user_ID;

    $ids = frnd_get_friends_by_id( $user_ID, 'list' );

    if ( ! $ids )
        return;

    $datas = $wpdb->get_results( "
            SELECT wp_users.ID,wp_users.display_name,wp_users.user_nicename,user_email,meta_value,actions.time_action
            FROM " . $wpdb->users . " AS wp_users
            LEFT JOIN " . $wpdb->prefix . "rcl_user_action AS actions
            ON wp_users.ID = actions.user
            LEFT JOIN " . $wpdb->usermeta . " AS t_meta
            ON wp_users.ID=t_meta.user_id
            AND meta_key IN ('rcl_avatar', 'ulogin_photo')
            WHERE actions.time_action > date_sub('" . current_time( 'mysql' ) . "', interval 10 minute)
            AND wp_users.ID IN (" . $ids . ")
            ORDER BY actions.time_action DESC
            LIMIT 0," . $limit . "
        ", ARRAY_A );

    return $datas;
}

/**
 * удалим все сообщения по id пользователя
 *
 * @since 2.0
 *
 * @param int $user_id  id пользователя.
 *
 * @return int|bool     'число' - кол-во строк удалено.
 *                      'false' — если ошибка.
 */
function frnd_del_all_messages_by_user_id( $user_id ) {
    if ( ! $user_id || $user_id == 0 )
        return;

    $user_deleted = ( int ) $user_id;

    global $wpdb;

    $del = $wpdb->query( "DELETE FROM " . FRND_MESS_DB . " WHERE from_id = '$user_deleted' OR to_id = '$user_deleted'" );

    return $del;
}

/**
 * удалим все связи по id пользователя
 *
 * @since 2.0
 *
 * @param int $user_id  id пользователя.
 *
 * @return int|bool     'число' - кол-во строк удалено.
 *                      'false' — если ошибка.
 */
function frnd_del_all_friendships_by_user_id( $user_id ) {
    if ( ! $user_id || $user_id == 0 )
        return;

    $user_deleted = ( int ) $user_id;

    global $wpdb;

    $del = $wpdb->query( "DELETE FROM " . FRND_DB . " WHERE owner_id = '$user_deleted' OR friend_id = '$user_deleted'" );

    return $del;
}
