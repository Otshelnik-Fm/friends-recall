<?php

/*

  ╔═╗╔╦╗╔═╗╔╦╗
  ║ ║ ║ ╠╣ ║║║ https://otshelnik-fm.ru
  ╚═╝ ╩ ╚  ╩ ╩

 */

/** @todo
  -  Функционал блокирования - бана. Кнопка "Черный список"
  -  кнопка "блок действий" - как в Group Theme Replace. Чтобы видеть инфу и в выпадающем меню были кнопки

 */
/**/

// Константа FRND_ADDON_FILE.
if ( ! defined( 'FRND_ADDON_FILE' ) ) {
    define( 'FRND_ADDON_FILE', __FILE__ );
}

// Подключим FriendsRecall класс.
if ( ! class_exists( 'FriendsRecall' ) ) {
    include_once dirname( __FILE__ ) . '/classes/class-friends-recall.php';
}

/**/
/**
 * Экземпляр класса FriendsRecall
 *
 * @since  2.0
 * @return FriendsRecall
 */
function frnd_base() {
    return FriendsRecall::instance();
}

add_action( 'init', 'frnd_init', 1 );
function frnd_init() {
    return frnd_base();
}

/**
 * Экземпляр класса FriendsTopMessages
 *
 * @since  2.0
 * @return FriendsTopMessages
 */
add_action( 'rcl_area_before', 'frnd_top_messages', 200 );
function frnd_top_messages() {
    if ( ! rcl_is_office() )
        return;

    if ( ! class_exists( 'FriendsTopMessages' ) ) {
        include_once FRND_ADDON_ABSPATH . 'classes/class-friends-top-messages.php';
    }

    return FriendsTopMessages::instance();
}

//
require_once 'inc/db.php';
require_once 'inc/tabs.php';
require_once 'inc/mails.php';
require_once 'inc/functions.php';
require_once 'inc/shortcodes.php';
require_once 'inc/ajax-actions.php';
require_once 'inc/notifications.php';
require_once 'inc/future-release.php';

if ( function_exists( 'rcl_insert_feed_data' ) ) {
    require_once 'inc/feed.php';
}

/**/

// вкладка Добавить в друзья сверху ЛК
add_action( 'webx_area_center', 'frnd_get_actions_cabinet', 51 );
add_action( 'rcl_area_actions', 'frnd_get_actions_cabinet', 51 );
function frnd_get_actions_cabinet() {
    if ( ! is_user_logged_in() )
        return;

    global $user_ID, $user_LK;

    frnd_manager_friend( $user_ID, $user_LK );
}

// связи лк или одниночной записи
function frnd_get_all_site_relations() {
    $relations = false;
    // в кабинете
    if ( rcl_is_office() ) {
        $relations = frnd_get_relation_friendship_current_user_to_lk();
    }
    // одиночная запись и не в друзьях
    else if ( ( ! rcl_is_office() && ! is_singular( 'page' ) && ! is_front_page() ) || ! frnd_is_friend_post() ) {
        $relations = frnd_get_relation_friendship_current_user_to_author();
    }

    return $relations;
}

// статус лк или одниночной записи
function frnd_get_all_site_statuses() {
    $status = false;
    // в кабинете
    if ( rcl_is_office() ) {
        $status = frnd_get_status_friendship_lk_to_current_user();
    }
    // одиночная запись и не в друзьях
    else if ( ( ! rcl_is_office() && ! is_singular( 'page' ) && ! is_front_page() ) || ! frnd_is_friend_post() ) {
        $status = frnd_get_status_friendship_author_to_current_user();
    }

    return $status;
}

//
//
//
//      основной вызов
function frnd_manager_friend( $user_id, $to_user ) {
    // у друга нечего делать
    if ( ! $user_id || ! $to_user || frnd_is_friend_post() || frnd_is_friend_office() )
        return;

    $status = frnd_get_all_site_statuses();

    // $user_id подписчик
    //if ( isset( $status ) && $status == 3 )
    //    return;

    $relations = frnd_get_all_site_relations();

    frnd_base()->load_logged_in_style();

    // нет статуса или есть и это не запрос в друзья - нужен скрипт
    if ( ! $status || $status != 1 ) {
        frnd_base()->load_logged_in_script();
    }

    // в своем ЛК
    if ( rcl_is_office( $user_id ) ) {
        echo frnd_incoming_friend_count_box( $user_id );
        return;
    }

    if ( ! $status || $status != 1 ) {
        // модалка
        rcl_dialog_scripts();
    }

    // он уже подал к $user_id
    if ( isset( $relations[0] ) && $relations[0]['friend_id'] == $user_id && $relations[0]['status'] == 1 ) {
        if ( ! rcl_is_office() ) {
            echo frnd_button_in_notice_box( $user_id, $to_user );

            return;
        } else {
            // в кабинете. Стопим. т.к. top-message выведет вверху кнопки и сообщение
            return;
        }
    }

    if ( isset( $relations[0] ) && $relations[0]['status'] == 3 && isset( $relations[1] ) && $relations[1]['friend_id'] == $user_id && $relations[1]['status'] == 1 ) {
        echo frnd_reject_offer_friendship_button( $user_id, $to_user );
        echo frnd_confirm_offer_friendship_button( $user_id, $to_user );

        return;
    }

    // запрос дружбы
    if ( $status == 0 && ! rcl_is_office( $user_id ) ) {
        echo frnd_offer_friendship_button( $user_id, $to_user );
        return;
    }


    return frnd_get_buttons( $status, $user_id, $to_user );
}

function frnd_get_buttons( $status, $user_id, $to_user ) {
    switch ( $status ) {
        // заявка подана
        case 1:
            frnd_pending_friendship( $user_id, $to_user );
            break;

        // в друзьях
        case 2:
            frnd_accepted_friendship( $user_id, $to_user );
            break;

        // отклонено. подписчик
        case 3:
            frnd_declined_friendship( $user_id, $to_user );
            break;

        // заблокирован. бан
        case 4:
            frnd_blocked_friendship( $user_id, $to_user );
            break;
    }
}

// в блок автора
function frnd_button_in_notice_box( $user_id, $to_user ) {
    // в theme-control не требуется
    if ( ! rcl_exist_addon( 'theme-control' ) ) {
        frnd_base()->load_core_style();
    }

    $text = frnd_get_friend_request_message( $user_id, $to_user );

    $mess = '<div class="frnd_auth_mess">';
    $mess .= '<div class="frnd_auth_title">' . frnd_get_author_name( $to_user ) . ' хочет добавить вас в друзья.<br>'
        . 'Вы можете принять запрос или отклонить его, кнопками ниже:</div>';

    if ( $text ) {
        $conf = [
            'type'  => 'simple', // info,success,warning,error,simple
            'class' => 'frnd_mess',
            'text'  => $text,
            'icon'  => 'fa-bullhorn',
        ];

        $mess .= '<div id="frnd_mess_box" class="frnd_mess_block">'
            . '<div class="frnd_title">Он оставил вам сообщение:</div>'
            . rcl_get_notice( $conf )
            . '</div>';
    }

    $mess .= frnd_reject_offer_friendship_button( $user_id, $to_user );
    $mess .= frnd_confirm_offer_friendship_button( $user_id, $to_user );
    $mess .= '</div>';

    $data         = [
        'type'  => 'success',
        'text'  => $mess,
        'class' => 'frnd_incoming_mess',
    ];
    $data['icon'] = ( ! $text ) ? 'fa-handshake-o' : '';


    return rcl_get_notice( $data );
}

// Кнопка - запрос дружбы + модалка для сообщения
function frnd_offer_friendship_button( $user_id, $to_user ) {
    $data = rcl_encode_post( [
        'user_id' => $user_id,
        'to_user' => $to_user
        ] );

    $args = [
        'label'   => 'Добавить в друзья',
        'icon'    => 'fa-user-plus',
        'href'    => '#',
        'onclick' => 'frnd_offer(this);return false;',
        'data'    => [ 'frnd_request' => $data ]
    ];

    return rcl_get_button( $args );
}

// Кнопка - подтверждаю дружбу
function frnd_confirm_offer_friendship_button( $user_id, $to_user ) {
    $data = rcl_encode_post( [
        'user_id' => $user_id,
        'to_user' => $to_user,
        'type'    => 'confirm'
        ] );

    $args = [
        'label'   => 'Принять запрос в друзья',
        'icon'    => 'fa-user-plus',
        'href'    => '#',
        'class'   => 'frnd_actions_bttn frnd_offer_confirm',
        'onclick' => 'frnd_operations(this);return false;',
        'data'    => [ 'frnd_data' => $data, 'frnd_type' => "confirm" ]
    ];

    return rcl_get_button( $args );
}

// Кнопка - отклоняю дружбу
function frnd_reject_offer_friendship_button( $user_id, $to_user ) {
    $data = rcl_encode_post( [
        'user_id' => $user_id,
        'to_user' => $to_user,
        'type'    => 'reject'
        ] );

    $args = [
        'label'   => 'Отклонить запрос в друзья',
        'icon'    => 'fa-user-times',
        'href'    => '#',
        'class'   => 'frnd_actions_bttn frnd_offer_reject',
        'onclick' => 'frnd_operations(this);return false;',
        'data'    => [ 'frnd_data' => $data, 'frnd_type' => "reject" ]
    ];

    return rcl_get_button( $args );
}

// Кнопка - удаляю из друзей
function frnd_delete_friendship_button( $user_id, $to_user ) {
    $data = rcl_encode_post( [
        'user_id' => $user_id,
        'to_user' => $to_user,
        'type'    => 'delete'
        ] );

    $args = [
        'label'     => 'Убрать из друзей',
        'icon'      => 'fa-user-times',
        'href'      => '#',
        'fullwidth' => 1,
        'class'     => 'frnd_actions_bttn frnd_delete',
        'onclick'   => 'frnd_operations(this);return false;',
        'data'      => [ 'frnd_data' => $data, 'frnd_type' => "reject" ]
    ];

    return rcl_get_button( $args );
}

// ожидаем подтверждения. заявка подана
function frnd_pending_friendship() {
    $args = [
        'label'      => 'Заявка ожидает рассмотрения',
        'icon'       => 'fa-clock-o',
        'icon_align' => 'right',
        'icon_mask'  => '1',
        'status'     => 'disabled',
        'class'      => 'frnd_pending',
        'style'      => 'background-color:#d5ca78;opacity:.85;color:#000 !important;'
    ];

    echo rcl_get_button( $args );
}

// в друзьях - убрать из друзей
function frnd_accepted_friendship( $user_id, $to_user ) {

}

// отклонён. подписчик
function frnd_declined_friendship( $user_id, $to_user ) {
    echo frnd_offer_friendship_button( $user_id, $to_user );
}

// заблокирован. бан
function frnd_blocked_friendship( $user_id, $to_user ) {

}

// Кнопка "Запросы в друзья: 1"
function frnd_incoming_friend_count_box( $user_id ) {
    $offer_in = frnd_count_incoming_friend_requests_lk();

    if ( ! $offer_in )
        return;

    $args = [
        'label'   => 'Запросы в друзья',
        'href'    => '?tab=friends&subtab=incoming-friends',
        'counter' => $offer_in,
        'class'   => 'frnd_incoming rcl-ajax',
        'attr'    => 'data-post=' . frnd_ajax_data( 'friends', 'incoming-friends' )
    ];

    return rcl_get_button( $args );
}

// формируем для ajax строку в data-post атрибут
function frnd_ajax_data( $tab_id, $subtab ) {
    global $user_LK;

    $datapost = array(
        'tab_id'    => $tab_id,
        'subtab_id' => $subtab,
        'master_id' => $user_LK
    );

    return rcl_encode_post( $datapost );
}

// добавим к блоку автора и к списку пользователей (rows)
add_action( 'rcl_user_description', 'frnd_add_friends_author_publications', 40 );
function frnd_add_friends_author_publications() {
    if ( ! is_user_logged_in() )
        return;

    if ( rcl_is_office() || is_singular( 'page' ) )
        return;

    // В группе кнопка "в друзья" в заявках и списке юзеров не нужна
    global $rcl_group;
    if ( $rcl_group )
        return;

    global $user_ID, $rcl_user;

    if ( ( int ) $rcl_user->ID === ( int ) $user_ID )
        return;

    frnd_manager_friend( $user_ID, $rcl_user->ID );
}

// при удалении пользователя - очистим
add_action( 'delete_user', 'frnd_delete_user' );
function frnd_delete_user( $user_id ) {
    frnd_del_all_messages_by_user_id( $user_id );

    frnd_del_all_friendships_by_user_id( $user_id );
}

// если это друг - в его ЛК добавим в body доп класс
add_filter( 'body_class', 'frnd_add_body_class_friend' );
function frnd_add_body_class_friend( $classes ) {
    if ( ! is_user_logged_in() && ! rcl_is_office() )
        return $classes;

    global $user_ID;

    // в чужом ЛК
    if ( ! rcl_is_office( $user_ID ) && frnd_is_friend_office() ) {
        $classes[] = 'frnd_is_friend';
    }

    return $classes;
}
