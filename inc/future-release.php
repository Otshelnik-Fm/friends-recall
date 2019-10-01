<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


// на будущее - для кеширования основных данных
add_action( 'wp_login', 'frnd_add_count_usermeta', 10, 2 );
function frnd_add_count_usermeta( $user_login, $user ) {
    // общее кол-во друзей
    //if ( empty( get_user_meta( $user->ID, 'frnd_total_friends', true ) ) ) {
    $total_friends = frnd_count_user_friends( $user->ID );
    update_user_meta( $user->ID, 'frnd_total_friends', ( int ) $total_friends );
    //}
    //
    // кол-во исходящих запросов в друзья
    //if ( empty( get_user_meta( $user->ID, 'frnd_incoming_call', true ) ) ) {
    $incoming_call = frnd_count_incoming_friend_requests( $user->ID );
    update_user_meta( $user->ID, 'frnd_incoming_call', ( int ) $incoming_call );
    //}
    //
    // кол-во входящих запросв в друзья
    //if ( empty( get_user_meta( $user->ID, 'frnd_outgoing_call', true ) ) ) {
    /**
     *   @todo Ещё нигде не используется
     *
     *    */
    $outgoing_call = frnd_count_outgoing_friend_requests( $user->ID );
    update_user_meta( $user->ID, 'frnd_outgoing_call', ( int ) $outgoing_call );
    //}
}

// добавили/удалили из друзей. Обновим у 2-х юзеров
add_action( 'frnd_confirm_request', 'frnd_add_total_friends', 10, 2 );
add_action( 'frnd_delete_friend', 'frnd_add_total_friends', 10, 2 );
function frnd_add_total_friends( $from, $to_user ) {
    $total_friends_from = frnd_count_user_friends( $from );
    update_user_meta( $from, 'frnd_total_friends', ( int ) $total_friends_from );

    $total_friends_to_user = frnd_count_user_friends( $to_user );
    update_user_meta( $to_user, 'frnd_total_friends', ( int ) $total_friends_to_user );
}

// добавил/подтвердил/отклонил запрос в друзья
add_action( 'frnd_send_request', 'frnd_change_incoming_outgoing_call', 10, 2 );
add_action( 'frnd_confirm_request', 'frnd_change_incoming_outgoing_call', 10, 2 );
add_action( 'frnd_reject_request', 'frnd_change_incoming_outgoing_call', 10, 2 );
function frnd_change_incoming_outgoing_call( $from, $to_user ) {
    /**
     *   @todo Ещё нигде не используется
     *
     *    */
    $count_outgoing = frnd_count_outgoing_friend_requests( $from );
    update_user_meta( $from, 'frnd_outgoing_call', ( int ) $count_outgoing );

    $count_incoming_from = frnd_count_incoming_friend_requests( $from );
    update_user_meta( $from, 'frnd_incoming_call', ( int ) $count_incoming_from );

    $count_incoming = frnd_count_incoming_friend_requests( $to_user );
    update_user_meta( $to_user, 'frnd_incoming_call', ( int ) $count_incoming );

    /**
     *   @todo Ещё нигде не используется
     *
     *    */
    $count_outgoing_to = frnd_count_outgoing_friend_requests( $to_user );
    update_user_meta( $to_user, 'frnd_outgoing_call', ( int ) $count_outgoing_to );
}
