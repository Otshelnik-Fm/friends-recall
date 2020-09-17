<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
// кто онлайн
add_shortcode( 'frnd_online', 'frnd_box_online_users' );
function frnd_box_online_users( $attr ) {
    $out = '';

    $atts = shortcode_atts( array(
        'title'       => 'Друзья на сайте:',
        'not-friends' => '',
        'not-online'  => '',
        'guest-text'  => '',
        'number'      => 10,
        ), $attr, 'frnd_online' );

    if ( ! is_user_logged_in() ) {
        if ( ! empty( $atts['guest-text'] ) ) {
            $out = '<h3>' . $atts['title'] . '</h3>';
            $out .= $atts['guest-text'];
        }

        return $out;
    }

    // нет друзей вообще
    if ( ! frnd_check_current_user_has_friends() ) {
        // нет друзей на сайте
        if ( ! empty( $atts['not-friends'] ) ) {
            $out .= '<h3>' . $atts['title'] . '</h3>';
            $out .= $atts['not-friends'];
        }

        return $out;
    }

    $datas = frnd_get_online_friends( $atts['number'] );

    // нет друзей на сайте
    if ( ! $datas ) {
        if ( ! empty( $atts['not-online'] ) ) {
            $out .= '<h3>' . $atts['title'] . '</h3>';
            $out .= $atts['not-online'];
        }

        return $out;
    }

    $out .= '<h3>' . $atts['title'] . '</h3>';
    $out .= '<div class="userlist mini-list">';
    foreach ( $datas as $data ) {
        $out .= '<div class="user-single">';
        $out .= '<div class="thumb-user">';
        $out .= '<a title="' . $data['display_name'] . '" href="' . get_author_posts_url( $data['ID'], $data['user_nicename'] ) . '">';
        $out .= frnd_get_avatar( $data, 50 );
        $out .= '<span class="status_user online"><i class="rcli fa-circle"></i></span>';
        $out .= '</a>';
        $out .= '</div>';
        $out .= '</div>';
    }
    $out .= '</div>';

    return $out;
}
