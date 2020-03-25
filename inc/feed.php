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

    $friends_id = frnd_get_friends_by_id( $user_ID );

    if ( ! $friends_id )
        return $args;

    $merge   = array_merge( $args['post_author__in'], $friends_id );
    $unique  = array_unique( $merge );
    $authors = array_values( $unique );

    $args['post_author__in'] = $authors;

    return $args;
}

// Если есть связи:
// 1    - заявка
// 2    - дружит
// 4    - заблокирован. Бан
// - уберем кнопку фида "Подписаться"
// если 3 - подписчик - оставляем кнопку
add_action( 'init', 'frnd_del_feed_bttn', 9 );
function frnd_del_feed_bttn() {
    if ( ! is_user_logged_in() )
        return;

    global $user_ID;

    if ( rcl_is_office( $user_ID ) )
        return;

    $relations = frnd_get_relation_friendship_current_user_to_lk();

    if ( ! isset( $relations ) || empty( $relations ) )
        return;

    if ( in_array( $relations[0]['status'], [ 1, 2, 4 ] ) ) {
        remove_action( 'init', 'rcl_add_block_feed_button' );

        // в ЛК theme-control на другом хуке висит
        if ( rcl_exist_addon( 'theme-control' ) ) {
            remove_action( 'tcl_after_actions', 'tcl_feed_actions_button', 200 );
        }
    }
}

// тоже что выше только из одиночной записи
add_action( 'rcl_user_description', 'frnd_del_feed_bttn_in_post', 80 );
function frnd_del_feed_bttn_in_post() {
    if ( ! is_user_logged_in() )
        return;

    // это не одиночная запись
    if ( rcl_is_office() || is_singular( 'page' ) || is_front_page() )
        return;

    global $user_ID, $post;

    // юзер = автор публикации
    if ( $user_ID == $post->post_author )
        return;

    $relations = frnd_get_relation_friendship_current_user_to_author();
    if ( ! isset( $relations ) || empty( $relations ) )
        return;

    if ( in_array( $relations[0]['status'], [ 1, 2, 4 ] ) ) {
        remove_filter( 'rcl_user_description', 'rcl_add_userlist_follow_button', 90 );
    }
}
