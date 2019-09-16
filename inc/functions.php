<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**/
/**
 * Получим массив/список друзей по id
 *
 * @since 1.1.0
 *
 * @param int $user_id  id юзера.
 *
 * @param bool $list    как возвращать результат:
 *                      'true' - списком через запятую.
 *                      'false' - вернет массив.
 *                      Default 'false'.
 *
 * @return string|array|bool    массив или список пользователей разделенных запятой: 1,2,3 и т.д.
 *                              'false' - если нет
 */
function frnd_get_friend_user_ids( $user_id, $list = false ) {
    $friends = frnd_friend_by_id_db( $user_id );

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
 * @since 1.1.0
 *
 * @param int $user_id  id юзера.
 *
 * @param int $friend   id друга.
 *
 * @return bool         'true' - друзья.
 *                      'false' - не друзья.
 */
function frnd_check_friendship( $user_id, $friend ) {
    $status = frnd_get_friendship_status_code( $user_id, $friend );

    if ( $status && $status == 2 ) {
        return true;
    } else {
        return false;
    }
}

/**
 * Получим статус дружбы 2-х пользователей (текстом)
 *
 * @since 1.1.0
 *
 * @param int $user_id  id юзера.
 *
 * @param int $friend   id друга.
 *
 * @return string       'not_friend' - Не друзья.
 *                      'pending' - Заявка в друзья подана. Ожидаем подтверждения.
 *                      'is_friend' - Друзья.
 *                      'subscriber' - Оставлен в подписчиках.
 *                      'ban' - Заблокирован. Бан.
 */
function frnd_get_friendship_status( $user_id, $friend ) {
    $status = frnd_get_friendship_status_code( $user_id, $friend );

    if ( ! $status ) {
        return 'not_friend';
    }

    switch ( $status ) {
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
 * @since 1.1.0
 *
 * @param int $user_id  id юзера.
 *
 * @return bool         'true' - есть друзья.
 *                      'false' — нет друзей.
 */
function frnd_check_user_has_friends( $user_id ) {
    $count = frnd_user_friend_count( $user_id );

    if ( $count && $count > 0 ) {
        return true;
    } else {
        return false;
    }
}
