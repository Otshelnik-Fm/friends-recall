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

/* Сообщения вверху ЛК */
add_action( 'rcl_area_before', 'frnd_top_messages', 200 );
function frnd_top_messages() {
    if ( ! is_user_logged_in() )
        return;

    global $user_ID;

    // если в своем ЛК - проверим и выведем счетчик - кто подал ко мне заявку в друзья
    if ( rcl_is_office( $user_ID ) ) {
        echo frnd_incoming_top_mess();

        return;
    }

    global $user_LK, $frnd_status;

    if ( ! $frnd_status ) {
        $frnd_status = frnd_get_friendship_status_code( $user_ID, $user_LK );

        if ( ! $frnd_status )
            return;
    }

    // заявка подана
    if ( ( int ) $frnd_status === 1 ) {
        echo frnd_top_mess_pending();
    } else if ( ( int ) $frnd_status === 4 ) {
        // заблокирован. бан
        echo frnd_top_mess_blocked();
    }
}

function frnd_top_mess_pending() {
    $data = [
        'type'   => 'success',
        'border' => true,
        'text'   => 'Заявка в друзья ожидает рассмотрения',
        'icon'   => 'fa-handshake-o',
    ];

    return frnd_notice( $data );
}

function frnd_top_mess_blocked() {
    $data = [
        'type'   => 'warning',
        'border' => true,
        'text'   => 'Забанен',
        'icon'   => 'fa-gift',
    ];

    return frnd_notice( $data );
}

function frnd_incoming_top_mess() {
    global $frnd_offer_in;

    if ( ! $frnd_offer_in )
        return;

    $data = [
        'type'   => 'success',
        'border' => true,
        'text'   => 'У вас: <a class="frnd_link rcl-ajax" data-post="' . frnd_ajax_data( 'friends', 'incoming-friends' ) . '" href="?tab=friends&subtab=incoming-friends">' . $frnd_offer_in . ' ' . frnd_decline_friend( $frnd_offer_in, [ 'запрос', 'запроса', 'запросов' ] ) . ' в друзья!</a>',
        'icon'   => 'fa-bell-o',
    ];

    return frnd_notice( $data );
}
