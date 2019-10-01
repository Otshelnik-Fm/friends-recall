<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Уведомление через Rcl-Notification
add_action( 'frnd_send_request', 'frnd_support_rcl_notifications', 10, 2 );
function frnd_support_rcl_notifications( $user_id, $friend ) {
    if ( ! rcl_exist_addon( 'notification' ) )
        return;

    // не нужны уведомления на сайте
    if ( rcl_get_option( 'frnd_notify', 'yes' ) === 'no' )
        return;

    $from_userdata = get_userdata( $user_id );
    $from_name     = $from_userdata->get( 'display_name' );
    if ( ! $from_name ) {
        $from_name = $from_userdata->get( 'user_login' );
    }

    $cabinet = rcl_format_url( get_author_posts_url( $friend ), 'friends', 'incoming-friends' );

    $text = $from_name . ' хочет добавить вас в друзья.<br>';
    $text .= 'Чтобы принять этот запрос и управлять списком всех ожидающих запросов, посетите <a href="' . $cabinet . '">свой личный кабинет</a>';

    $args = array(
        'user_id'        => $friend,
        'notice_subject' => 'Новый запрос дружбы',
        'notice_content' => $text
    );

    rcl_add_notification( $args );
}

// Уведомление через rcl_notice
add_action( 'wp_footer', 'frnd_rcl_notice' );
function frnd_rcl_notice() {
    if ( ! is_user_logged_in() )
        return;

    // не нужны уведомления на сайте
    if ( rcl_get_option( 'frnd_notify', 'yes' ) === 'no' )
        return;

    // доп уведомлений включен - значит не в работе нотисы
    if ( rcl_exist_addon( 'notification' ) )
        return;

    global $user_ID;

    // в своем ЛК не нужен. Там и так есть над кабинетом уведомление
    if ( rcl_is_office( $user_ID ) )
        return;

    $offer = frnd_count_incoming_friend_requests( $user_ID );

    if ( ! $offer )
        return;

    $cabinet = rcl_format_url( get_author_posts_url( $user_ID ), 'friends', 'incoming-friends' );
    $url     = '<br><a href=\"' . $cabinet . '\" title=\"Перейти в Личный кабинет\">посмотреть</a>';

    echo '<script>rcl_notice("Запрос дружбы: ' . $offer . $url . '","success",15000);</script>';
}
