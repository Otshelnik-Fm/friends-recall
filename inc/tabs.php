<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'rcl_tabs', 'frnd_decline_tab' );
function frnd_decline_tab( $tabs ) {
    if ( rcl_get_option( 'frnd_place', 'counters' ) === 'menu' )
        return $tabs;

    // если это F-Chat - не меняем ничего
    if ( isset( $tabs['friends'] ) && $tabs['friends']['name'] === 'Друзья' ) {
        $tabs['friends']['name'] = frnd_decline_friend( $tabs['friends']['counter'], [ 'Друг', 'Друга', 'Друзей' ] );
    }

    return $tabs;
}

// вкладка Друзья
add_action( 'init', 'frnd_friends_tab' );
function frnd_friends_tab() {
    global $rcl_office, $user_ID;

    $place = rcl_get_option( 'frnd_place', 'counters' );

    $count = 0;

    if ( $rcl_office > 0 ) {
        // если это кабинет залогиненого
        if ( $rcl_office == $user_ID ) {
            $count = frnd_count_current_user_friends();
        } else {
            $count = frnd_count_lk_friends();
        }
    }

    $tab_data = array(
        'id'       => 'friends',
        'name'     => 'Друзья',
        'supports' => array( 'ajax' ),
        'public'   => 1,
        'output'   => $place,
        'counter'  => $count,
        'icon'     => 'fa-handshake-o',
        'content'  => array(
            array(
                'id'       => 'all-friends',
                'name'     => 'Все друзья',
                'callback' => array(
                    'name' => 'frnd_all_friends_tab',
                    'args' => array( $count )
                )
            ),
        )
    );

    rcl_tab( $tab_data );
}

// подвкладки "Входящие запросы в друзья"
add_action( 'rcl_setup_tabs', 'frnd_add_incoming_subtab', 10 );
function frnd_add_incoming_subtab() {
    global $user_ID;

    if ( ! rcl_is_office( $user_ID ) )
        return;

    $offer_in = frnd_count_incoming_friend_requests_lk();

    $counter = ($offer_in) ? ': ' . $offer_in : '';

    $subtab = array(
        'id'       => 'incoming-friends',
        'name'     => 'Входящие запросы в друзья' . $counter,
        'callback' => array(
            'name' => 'frnd_inc_friends_tab'
        )
    );

    rcl_add_sub_tab( 'friends', $subtab );

    // Заявки в друзья: исходящие
    $subtab_out = array(
        'id'       => 'outcoming-friends',
        'name'     => 'Заявки в друзья: исходящие',
        'callback' => array(
            'name' => 'frnd_out_friends_tab'
        )
    );

    rcl_add_sub_tab( 'friends', $subtab_out );
}

// коллбек вкладки Друзья
function frnd_all_friends_tab( $count ) {
    if ( ! rcl_is_office() )
        return;

    $content = '<h3>Список друзей:</h3>';

    global $user_ID;

    if ( $count && $count > 0 ) {
        // шаблон вывода списка друзей
        $type = rcl_get_option( 'frnd_type', 'frnd-mini-card' );
        // кнопка "Убрать из друзей"
        if ( rcl_is_office( $user_ID ) ) {
            if ( $type === 'rows' ) {
                add_action( 'rcl_user_description', 'frnd_get_delete_friend', 90 );
            } else if ( $type === 'frnd-card' || $type === 'frnd-mini-card' || $type === 'frnd-ava' ) {
                add_action( 'frnd_button', 'frnd_get_delete_friend', 90 );
            }
        }
        // дополним запрос
        add_filter( 'rcl_users_query', 'frnd_query_friend_userlist', 10 );
        if ( $type === 'rows' ) {
            $content .= rcl_get_userlist( array(
                'template'    => 'rows',
                'per_page'    => 20,
                'orderby'     => 'time_action',
                'filters'     => 0,
                'search_form' => 0,
                'data'        => 'rating_total,description,posts_count,comments_count',
                'add_uri'     => array( 'tab' => 'friends' )
                ) );
        } else if ( $type === 'frnd-card' ) {
            $content .= rcl_get_userlist( array(
                'template'    => 'frnd-card',
                'per_page'    => 20,
                'orderby'     => 'time_action',
                'filters'     => 0,
                'search_form' => 0,
                'data'        => 'rating_total,posts_count,comments_count',
                'add_uri'     => array( 'tab' => 'friends' )
                ) );
        } else if ( $type === 'frnd-mini-card' ) {
            $content .= rcl_get_userlist( array(
                'template'    => 'frnd-mini-card',
                'per_page'    => 20,
                'orderby'     => 'time_action',
                'filters'     => 0,
                'search_form' => 0,
                'data'        => 'rating_total,posts_count,comments_count',
                'add_uri'     => array( 'tab' => 'friends' )
                ) );
        } else if ( $type === 'frnd-ava' ) {
            $content .= rcl_get_userlist( array(
                'template'    => 'frnd-ava',
                'per_page'    => 20,
                'orderby'     => 'time_action',
                'filters'     => 0,
                'search_form' => 0,
                'data'        => 'rating_total',
                'add_uri'     => array( 'tab' => 'friends' )
                ) );
        }
    } else {
        $datas = '';
        if ( rcl_is_office( $user_ID ) ) {
            $data = [
                'type'   => 'info',
                'border' => true,
                'text'   => 'У вас пока нет друзей',
                'icon'   => 'fa-info-circle',
            ];

            $datas = apply_filters( 'frnd_you_not_friends', $data );
        } else {
            $data = [
                'text' => 'Пока нет друзей',
                'icon' => 'fa-info-circle',
            ];

            $datas = apply_filters( 'frnd_not_friends', $data );
        }
        $content .= frnd_notice( $datas );
    }

    return $content;
}

// дополним запрос для вкладки друзей
function frnd_query_friend_userlist( $query ) {
    global $user_LK;

    $query['join'][]  = "INNER JOIN " . FRND_DB . " AS friend ON wp_users.ID = friend.friend_id";
    $query['where'][] = "friend.owner_id='$user_LK'";
    $query['where'][] = "friend.status='2'";

    return $query;
}

// переназначим шаблон списка карточкой
add_filter( 'rcl_template_path', 'frnd_replace_template_card', 10, 2 );
function frnd_replace_template_card( $path, $templateName ) {
    if ( $templateName != 'user-frnd-card.php' )
        return $path;

    if ( file_exists( RCL_TAKEPATH . 'templates/user-frnd-card.php' ) )
        return RCL_TAKEPATH . 'templates/user-frnd-card.php';

    return rcl_addon_path( __FILE__ ) . 'templates/user-frnd-card.php';
}

// переназначим шаблон списка мини карточкой
add_filter( 'rcl_template_path', 'frnd_replace_template_mini_card', 10, 2 );
function frnd_replace_template_mini_card( $path, $templateName ) {
    if ( $templateName != 'user-frnd-mini-card.php' )
        return $path;

    if ( file_exists( RCL_TAKEPATH . 'templates/user-frnd-mini-card.php' ) )
        return RCL_TAKEPATH . 'templates/user-frnd-mini-card.php';

    return rcl_addon_path( __FILE__ ) . 'templates/user-frnd-mini-card.php';
}

// переназначим шаблон списка аватарками
add_filter( 'rcl_template_path', 'frnd_replace_template_ava', 10, 2 );
function frnd_replace_template_ava( $path, $templateName ) {
    if ( $templateName != 'user-frnd-ava.php' )
        return $path;

    if ( file_exists( RCL_TAKEPATH . 'templates/user-frnd-ava.php' ) )
        return RCL_TAKEPATH . 'templates/user-frnd-ava.php';

    return rcl_addon_path( __FILE__ ) . 'templates/user-frnd-ava.php';
}

// коллбек вкладки "Входящие запросы в друзья"
function frnd_inc_friends_tab() {
    $content = '<h3>Входящие запросы в друзья:</h3>';

    $offer_in = frnd_count_incoming_friend_requests_lk();

    if ( $offer_in ) {
        // текст сообщения к дружбе
        add_action( 'rcl_user_description', 'frnd_messages', 90 );

        // кнопки "Подтвердить" и "Отклонить"
        add_action( 'rcl_user_description', 'frnd_get_confirm_offer_friends', 90 );
        // дополним запрос
        add_filter( 'rcl_users_query', 'frnd_query_inc_friend_userlist', 10 );
        $content .= rcl_get_userlist( array(
            'template'    => 'rows',
            'per_page'    => 20,
            'orderby'     => 'time_action',
            'filters'     => 0,
            'search_form' => 0,
            'data'        => '',
            'add_uri'     => array( 'tab' => 'friends' )
            ) );
    } else {
        $data = [
            'text' => 'Запросов нет',
            'icon' => 'fa-info-circle',
        ];

        $datas = apply_filters( 'frnd_not_inc_friends', $data );

        $content .= frnd_notice( $datas );
    }


    return $content;
}

// дополним запрос для вкладки друзей
function frnd_query_inc_friend_userlist( $query ) {
    global $user_LK;

    $query['join'][]  = "INNER JOIN " . FRND_DB . " AS friend ON wp_users.ID = friend.owner_id";
    $query['where'][] = "friend.friend_id='$user_LK'";
    $query['where'][] = "friend.status='1'";

    return $query;
}

// кнопки: подтверждаю/отклоняю
function frnd_get_confirm_offer_friends() {
    global $rcl_user, $user_ID;

    echo frnd_confirm_offer_friendship_button( $user_ID, $rcl_user->ID );

    echo frnd_reject_offer_friendship_button( $user_ID, $rcl_user->ID );
}

function frnd_get_delete_friend() {
    global $rcl_user, $user_ID;

    echo frnd_delete_friendship_button( $user_ID, $rcl_user->ID );
}

function frnd_get_messages() {
    global $rcl_user, $user_ID;

    $messages = frnd_get_friend_request_message( $rcl_user->ID, $user_ID );

    if ( ! $messages )
        return;

    $title   = '<div class="frnd_title">Сообщение к заявке:</div>';
    $message = '<div class="frnd_mess">' . $messages . '</div>';

    return '<div id="frnd_mess_box" class="frnd_mess_block"><div>' . $title . $message . '</div></div>';
}

// получаю текст сообщения
function frnd_messages() {
    echo frnd_get_messages();
}

// вкладка "Входящие запросы в друзья"
function frnd_out_friends_tab() {
    $content = '<h3>Вы подали заявки:</h3>';

    global $user_LK;

    $frnd_offer_out = frnd_count_outgoing_friend_requests( $user_LK );

    if ( $frnd_offer_out ) {
        // текст сообщения к дружбе
        add_action( 'rcl_user_description', 'frnd_messages', 90 );
        // кнопки "Подтвердить" и "Отклонить"
        //add_action( 'rcl_user_description', 'frnd_get_confirm_offer_friends', 90 );
        // дополним запрос
        add_filter( 'rcl_users_query', 'frnd_query_out_friend_userlist', 10 );
        $content .= rcl_get_userlist( array(
            'template'    => 'rows',
            'per_page'    => 20,
            'orderby'     => 'time_action',
            'filters'     => 0,
            'search_form' => 0,
            'data'        => '',
            'add_uri'     => array( 'tab' => 'friends' )
            ) );
    } else {
        $data = [
            'text' => 'Заявок нет',
            'icon' => 'fa-info-circle',
        ];

        $datas = apply_filters( 'frnd_not_out_friends', $data );

        $content .= frnd_notice( $datas );
    }

    return $content;
}

// дополним запрос для вкладки друзей
function frnd_query_out_friend_userlist( $query ) {
    global $user_LK;

    $query['join'][]  = "INNER JOIN " . FRND_DB . " AS friend ON wp_users.ID = friend.friend_id";
    $query['where'][] = "friend.owner_id='$user_LK'";
    $query['where'][] = "friend.status='1'";

    return $query;
}
