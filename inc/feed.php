<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'rcl_feed_posts_args', 'frnd_add_feed' );
function frnd_add_feed( $args ) {
    if ( ! is_user_logged_in() )
        return $args;

    global $user_ID;

    $friends_id = frnd_friend_by_id_db( $user_ID );

    if ( ! $friends_id )
        return $args;

    $merge   = array_merge( $args['post_author__in'], $friends_id );
    $unique  = array_unique( $merge );
    $authors = array_values( $unique );

    $args['post_author__in'] = $authors;

    return $args;
}
