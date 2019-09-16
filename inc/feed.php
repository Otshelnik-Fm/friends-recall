<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* проверим подписку и подпишем */
function frnd_sign_it_feed( $from, $to_user ) {
    $subs = frnd_is_feed( $from, $to_user );

    if ( ! $subs || $subs == 0 ) {

        $args = array(
            'user_id'     => $to_user,
            'object_id'   => $from,
            'feed_type'   => 'author',
            'feed_status' => 1
        );

        rcl_insert_feed_data( $args );
    }
}

// Дополним ленту feed друзьями
add_filter( 'rcl_feed_posts_args', 'frnd_add_feed' );
function frnd_add_feed( $args ) {
    if ( ! is_user_logged_in() )
        return $args;

    global $user_ID;

    $friends_id = frnd_get_friend_user_ids( $user_ID );

    if ( ! $friends_id )
        return $args;

    $merge   = array_merge( $args['post_author__in'], $friends_id );
    $unique  = array_unique( $merge );
    $authors = array_values( $unique );

    $args['post_author__in'] = $authors;

    return $args;
}
