<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**/
/**
 * Получим массив/список друзей по id
 *
 * @since 2.0
 *
 * @param int $user_id  id юзера.
 *
 * @param bool $list    как возвращать результат:
 *                      'true' - списком через запятую.
 *                      'false' - вернет массив.
 *                      Default 'false'.
 *
 * @param int $limit    максимум к выборке. По умолчанию 1000
 *
 * @param int $offset   отступ в выборке. По умолчанию 0
 *
 * @return string|array|bool    массив или список пользователей разделенных запятой: 1,2,3 и т.д.
 *                              'false' - если нет
 */
function frnd_get_friends_by_id( $user_id, $list = false, $limit = 1000, $offset = 0 ) {
    $friends = frnd_friend_by_id_db( $user_id, $limit, $offset );

    if ( ! $friends ) {
        return false;
    }

    if ( $list ) {
        return implode( ",", $friends );
    } else {
        return $friends;
    }
}

/**
 * Эти пользователи друзья?
 *
 * @since 2.0
 *
 * @param int $user_id  id юзера.
 *
 * @param int $friend   id друга.
 *
 * @return bool         'true' - друзья.
 *                      'false' - не друзья.
 */
function frnd_check_friendship( $user_id, $friend ) {
    $status = frnd_get_status_friendship( $user_id, $friend );

    if ( $status && $status == 2 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Это кабинет друга?
 *
 * @since 2.0
 *
 * @return bool 'true' - Да.
 *              'false' - Нет.
 */
function frnd_is_friend_office() {
    $status = frnd_get_status_friendship_lk_to_current_user();

    if ( $status == 2 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Это публикация друга?
 *
 * @since 2.0
 *
 * @return bool 'true' - Да.
 *              'false' - Нет.
 */
function frnd_is_friend_post() {
    $status = frnd_get_status_friendship_author_to_current_user();

    if ( $status == 2 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Получим статус дружбы 2-х пользователей (текстом)
 *
 * @since 2.0
 *
 * @param int $user_id      id юзера.
 *
 * @param int $friend       id друга.
 *
 * @param string $type      Тип вывода:
 *                          'code' - вернет числовое значение (по умолчанию)
 *                          'text' - текстовое значение
 *
 * @return string           '0' или 'not_friend' - Не друзья.
 *                          '1' или'pending' - Заявка в друзья подана. Ожидаем подтверждения.
 *                          '2' или 'is_friend' - Друзья.
 *                          '3' или 'subscriber' - Оставлен в подписчиках.
 *                          '4' или 'ban' - Заблокирован. Бан.
 */
function frnd_get_status_friendship( $user_id, $friend, $type = 'code' ) {
    $status = frnd_get_friendship_status_code( $user_id, $friend );

    if ( $type == 'code' ) {
        return $status;
    }

    // вернуть текстовое значение
    else if ( $type == 'text' ) {
        return frnd_convert_code_to_text( $status );
    }
}

function frnd_convert_code_to_text( $status ) {
    switch ( $status ) {
        // заявка подана
        case 0:
            return 'not_friend';

        // заявка подана
        case 1:
            return 'pending';

        // в друзьях
        case 2:
            return 'is_friend';

        // отклонено. подписчик
        case 3:
            return 'subscriber';

        // заблокирован. бан
        case 4:
            return 'ban';
    }
}

/**
 * Есть у пользователя друзья?
 *
 * @since 2.0
 *
 * @param int $user_id  id юзера.
 *
 * @return bool         'true'  - есть друзья.
 *                      'false' - нет друзей.
 */
function frnd_check_user_has_friends( $user_id ) {
    $count = frnd_count_user_friends( $user_id );

    if ( $count && $count > 0 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Есть у текущего пользователя друзья?
 *
 * @since 2.0
 *
 * @return bool     'true'  - есть друзья.
 *                  'false' - нет друзей.
 */
function frnd_check_current_user_has_friends() {
    $count_current_user_friends = frnd_base()->count_current_user_friends;

    if ( $count_current_user_friends && $count_current_user_friends > 0 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Считаем друзей текущего пользователя
 *
 * @since 2.0
 *
 * @return int  Число друзей ('0' - нет друзей)
 */
function frnd_count_current_user_friends() {
    return frnd_base()->count_current_user_friends;
}

/**
 * Считаем друзей ЛК
 *
 * @since 2.0
 *
 * @return int  Число друзей ('0' - нет друзей)
 */
function frnd_count_lk_friends() {
    return frnd_base()->count_lk_friends;
}

/**
 * Есть у текущего ЛК друзья?
 *
 * @since 2.0
 *
 * @return bool     'true'  - есть друзья.
 *                  'false' - нет друзей.
 */
function frnd_check_lk_has_friends() {
    $count = frnd_count_lk_friends();

    if ( $count && $count > 0 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Считаем входящие заявки в кабинет
 *
 * @since 2.0
 *
 * @return int  Число друзей ('0' - нет друзей)
 */
function frnd_count_incoming_friend_requests_lk() {
    return frnd_base()->count_incoming_lk_requests;
}

/**
 * Получим связи дружбы чужого ЛК к залогиненному и обратно
 *
 * @since 2.0
 *
 * @return array связи 1=>2 -> status, 2=>1 -> status
 */
function frnd_get_relation_friendship_current_user_to_lk() {
    return frnd_base()->users_relations_to_lk;
}

/**
 * Получим связи дружбы автора записи к залогиненному и обратно
 *
 * @since 2.0
 *
 * @return array связи 1=>2 -> status, 2=>1 -> status
 */
function frnd_get_relation_friendship_current_user_to_author() {
    return frnd_base()->users_relations_to_author;
}

/**
 * Получим статус дружбы чужого ЛК к залогиненному (владелец ЛК - друг?)
 *
 * @since 2.0
 *
 * @param string $type      Тип вывода:
 *                          'code' - вернет числовое значение (по умолчанию)
 *                          'text' - текстовое значение
 *
 * @return string           '0' или 'not_friend' - Не друзья.
 *                          '1' или'pending' - Заявка в друзья подана. Ожидаем подтверждения.
 *                          '2' или 'is_friend' - Друзья.
 *                          '3' или 'subscriber' - Оставлен в подписчиках.
 *                          '4' или 'ban' - Заблокирован. Бан.
 */
function frnd_get_status_friendship_lk_to_current_user( $type = 'code' ) {
    $status = frnd_base()->status_lk_to_current_user;

    if ( $type == 'code' ) {
        return $status;
    }

    // вернуть текстовое значение
    else if ( $type == 'text' ) {
        return frnd_convert_code_to_text( $status );
    }
}

/**
 * Получим статус дружбы автора записи к залогиненному (Автор записи - друг?)
 *
 * @since 2.0
 *
 * @param string $type      Тип вывода:
 *                          'code' - вернет числовое значение (по умолчанию)
 *                          'text' - текстовое значение
 *
 * @return string           '0' или 'not_friend' - Не друзья.
 *                          '1' или'pending' - Заявка в друзья подана. Ожидаем подтверждения.
 *                          '2' или 'is_friend' - Друзья.
 *                          '3' или 'subscriber' - Оставлен в подписчиках.
 *                          '4' или 'ban' - Заблокирован. Бан.
 */
function frnd_get_status_friendship_author_to_current_user( $type = 'code' ) {
    $status = frnd_base()->status_author_to_current_user;

    if ( $type == 'code' ) {
        return $status;
    }

    // вернуть текстовое значение
    else if ( $type == 'text' ) {
        return frnd_convert_code_to_text( $status );
    }
}

// при удалении пользователя - очистим
add_action( 'delete_user', 'frnd_delete_user' );
function frnd_delete_user( $user_id ) {
    frnd_del_all_messages_by_user_id( $user_id );

    frnd_del_all_friendships_by_user_id( $user_id );
}

/**
 * склонения "друг, друга, друзей"
 *
 * @since 1.0
 *
 * @param int $n    Передаем из счетчика число.
 *
 * @param array $w  [ 'Друг', 'Друга', 'Друзей' ]
 *
 * @return string   e.g. ($n = 5) 'Друзей'
 */
function frnd_decline_friend( $n, $w = array( '', '', '' ) ) {
    $x  = ($xx = abs( $n ) % 100) % 10;
    return $w[($xx > 10 AND $xx < 15 OR ! $x OR $x > 4 AND $x < 10) ? 2 : ($x == 1 ? 0 : 1)];
}

/**
 * склоняем по полу
 *
 * @since 2.0.0
 *
 * @param int $user_id      id user ('-1' reserved for wp-cron).
 *
 * @param array $data       ['опубликовал','опубликовала']
 *
 * @return string           e.g. 'опубликовал'
 */
function frnd_decline_by_sex( $user_id, $data ) {
    if ( $user_id == '-1' )
        return $data[0];

    $sex = get_user_meta( $user_id, 'rcl_sex', true );

    $out = $data[0];

    if ( $sex ) {
        $out = ($sex === 'Женский') ? $data[1] : $data[0];
    }

    return $out;
}

/**
 * получим имя автора
 *
 * @since 2.0
 *
 * @param int $user_id      id user.
 *
 * @return string           имя владельца кабинета или автора записи
 */
function frnd_get_author_name( $user_id ) {
    $userdatas = get_userdata( $user_id );
    $name      = $userdatas->get( 'display_name' );
    if ( ! $name ) {
        $name = $userdatas->get( 'user_login' );
    }

    return $name;
}

/**
 * если это друг - в его ЛК добавим в body доп класс
 *
 * @since 2.0
 *
 * @return string   body class 'frnd_is_friend'.
 */
add_filter( 'body_class', 'frnd_add_body_class_friend' );
function frnd_add_body_class_friend( $classes ) {
    if ( ! is_user_logged_in() && ! rcl_is_office() )
        return $classes;

    global $user_ID;

    // в чужом ЛК
    if ( ! rcl_is_office( $user_ID ) && frnd_is_friend_office() ) {
        $classes[] = 'frnd_is_friend';
    }

    return $classes;
}
