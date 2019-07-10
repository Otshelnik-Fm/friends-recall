<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// кто онлайн
add_shortcode( 'frnd_online', 'frnd_box_online_users' );
function frnd_box_online_users() {
    if ( ! is_user_logged_in() )
        return;

    $datas = frnd_online_friends_db();

    if ( ! $datas )
        return false; // нет никого

    $out = '<h3>Друзья на сайте:</h3>';
    $out .= '<div class="userlist mini-list">';
    foreach ( $datas as $data ) {
        $out .= '<div class="user-single">';
        $out .= '<div class="thumb-user">';
        $out .= '<a title="' . $data['display_name'] . '" href="' . get_author_posts_url( $data['ID'], $data['user_nicename'] ) . '">';
        if ( $data['meta_value'] ) {
            $out  .= '<img class="avatar" src="' . rcl_get_url_avatar( $data['meta_value'], $data['ID'], $size = 50 ) . '?ver=' . tag_escape( $data['time_action'] ) . '" alt="" width="50" height="50">';
        } else {
            $out .= get_avatar( $data['user_email'], 50 );
        }
        $out .= '<span class="status_user online"><i class="rcli fa-circle"></i></span>';
        $out .= '</a>';
        $out .= '</div>';
        $out .= '</div>';
    }
    $out .= '</div>';

    return $out;
}
