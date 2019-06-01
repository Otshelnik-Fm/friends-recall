<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'frnd_offer', 'frnd_offer_mail', 10, 2 );
function frnd_offer_mail( $user_id, $friend ) {
    $from_userdata = get_userdata( $user_id );
    $to_userdata   = get_userdata( $friend );

    $to_name = $to_userdata->get( 'display_name' );
    if ( ! $to_name ) {
        $to_name = $to_userdata->get( 'user_login' );
    }

    $email = $to_userdata->get( 'user_email' );

    $from_name = $from_userdata->get( 'display_name' );
    if ( ! $from_name ) {
        $from_name = $from_userdata->get( 'user_login' );
    }

    $title = 'Новый запрос дружбы';

    $user_link = '<a href="' . get_author_posts_url( $user_id ) . '">' . $from_name . '</a>';

    $text = '<span style="font-size:19px;font-weight:bold">Привет, ' . $to_name . '.</span><br><br>';
    $text .= 'Пользователь ' . $user_link . ' хочет добавить вас в друзья.<br><br>';
    $text .= 'Чтобы принять этот запрос и управлять списком всех ожидающих запросов, перейдите: ';
    $text .= rcl_format_url( get_author_posts_url( $friend ), 'friends', 'incoming-friends' );

    rcl_mail( $email, $title, $text );
}
