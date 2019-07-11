<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_action( 'frnd_offer', 'frnd_offer_mail', 10, 2 );
function frnd_offer_mail( $user_id, $friend ) {
    $from_userdata = get_userdata( $user_id );
    $to_userdata   = get_userdata( $friend );

    $data = array();

    $to_name = $to_userdata->get( 'display_name' );
    if ( ! $to_name ) {
        $to_name = $to_userdata->get( 'user_login' );
    }
    $data['to_name'] = $to_name;

    $email = $to_userdata->get( 'user_email' );

    $from_name = $from_userdata->get( 'display_name' );
    if ( ! $from_name ) {
        $from_name = $from_userdata->get( 'user_login' );
    }

    $title = 'Новый запрос дружбы';

    $data['user_link'] = '<a href="' . get_author_posts_url( $user_id ) . '" style="color:#a52a2a" target="_blank" rel="noopener noreferrer">' . $from_name . '</a>';
    $data['friend']    = $friend;
    $data['cabinet']   = rcl_format_url( get_author_posts_url( $friend ), 'friends', 'incoming-friends' );

    $content = rcl_get_include_template( 'mail-friend-offer.php', RCL_TAKEPATH . 'add-on/friends-recall/templates/', $data );

    rcl_mail( $email, $title, $content );
}
