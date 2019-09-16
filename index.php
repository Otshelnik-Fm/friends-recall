<?php

/*

  ╔═╗╔╦╗╔═╗╔╦╗
  ║ ║ ║ ╠╣ ║║║ https://otshelnik-fm.ru
  ╚═╝ ╩ ╚  ╩ ╩

 */

/** @todo

  Возможности:

  +  Добавить в друзья
  +  Комментарий к заявке в друзья
  +  Удалить из друзей
  +  Забанить
  +  Список заявок в друзья
  +  Список друзей и кто онлайн
  +  Интеграция с FEED

 */
/* БД */
add_action( 'init', 'frnd_define_constant', 5 );
function frnd_define_constant() {
    if ( defined( 'FRND_DB' ) )
        return false;

    global $wpdb;

    define( 'FRND_DB', $wpdb->base_prefix . 'otfm_friends' );
    define( 'FRND_MESS_DB', $wpdb->base_prefix . 'otfm_friends_messages' );
}

//
require_once 'inc/db.php';
require_once 'inc/tabs.php';
require_once 'inc/feed.php';
require_once 'inc/mails.php';
require_once 'inc/settings.php';
require_once 'inc/functions.php';
require_once 'inc/shortcodes.php';
require_once 'inc/top-messages.php';
require_once 'inc/ajax-actions.php';
require_once 'inc/notifications.php';

/**/
function frnd_load_script() {
    rcl_enqueue_script( 'frnd_script', rcl_addon_url( 'assets/js/friends.js', __FILE__ ), false, true );
}

function frnd_load_style() {
    rcl_enqueue_style( 'frnd_style', rcl_addon_url( 'assets/css/friends.css', __FILE__ ) );
}

add_action( 'rcl_enqueue_scripts', 'frnd_load_style_card' );
function frnd_load_style_card() {
    if ( ! rcl_is_office() )
        return;

    if ( rcl_get_option( 'frnd_type', 'rows' ) === 'frnd-card' ) {
        rcl_enqueue_style( 'frnd_card', rcl_addon_url( 'assets/css/friends-card.css', __FILE__ ) );
    } else if ( rcl_get_option( 'frnd_type', 'rows' ) === 'frnd-mini-card' ) {
        rcl_enqueue_style( 'frnd_mini_card', rcl_addon_url( 'assets/css/friends-mini-card.css', __FILE__ ) );
    } else if ( rcl_get_option( 'frnd_type', 'rows' ) === 'frnd-ava' ) {
        rcl_enqueue_style( 'frnd_ava', rcl_addon_url( 'assets/css/friends-ava.css', __FILE__ ) );
    }
}

// вкладка Добавить в друзья сверху ЛК
add_action( 'rcl_area_actions', 'frnd_get_actions_cabinet', 51 );
function frnd_get_actions_cabinet() {
    if ( ! is_user_logged_in() )
        return;

    global $user_ID;

    // если в своем ЛК - проверим и выведем счетчик - кто подал ко мне заявку в друзья
    if ( rcl_is_office( $user_ID ) ) {
        echo frnd_incoming_friend_count_box( $user_ID );
    }

    global $user_LK;

    frnd_manager_friend( $user_ID, $user_LK );
}

//
//
//
//
//      основной вызов
function frnd_manager_friend( $user_id, $to_user ) {
    // ресурсы
    rcl_dialog_scripts();
    frnd_load_script();
    frnd_load_style();

    // получим статус
    global $frnd_status;

    if ( ! $frnd_status ) {
        $frnd_status = frnd_get_friendship_status_code( $user_id, $to_user );
    }

    if ( ! isset( $frnd_status ) && ! rcl_is_office( $user_id ) ) {
        echo frnd_offer_friendship_button( $user_id, $to_user );
    }

    switch ( $frnd_status ) {
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

// Кнопка - запрос дружбы + модалка для сообщения
function frnd_offer_friendship_button( $user_id, $to_user ) {
    $data = rcl_encode_post( [
        'user_id' => $user_id,
        'to_user' => $to_user
        ] );

    return '<div class="frnd_offer">'
        . rcl_get_button( 'Добавить в друзья', '#', array( 'icon' => 'fa-user-plus', 'attr' => 'data-frnd_request=' . $data . ' onclick="frnd_offer(this);return false;" ' ) )
        . '</div>';
}

// Кнопка - подтверждаю дружбу
function frnd_confirm_offer_friendship_button( $user_id, $to_user ) {
    $data = rcl_encode_post( [
        'user_id' => $user_id,
        'to_user' => $to_user,
        'type'    => 'confirm'
        ] );

    $args = [
        'icon' => 'fa-user-plus',
        'attr' => 'data-frnd_data=' . $data . ' data-frnd_type="confirm" onclick="frnd_operations(this);return false;"'
    ];

    return '<span class="frnd_actions_bttn frnd_offer_confirm">' . rcl_get_button( 'Добавить в друзья', '#', $args ) . '</span>';
}

// Кнопка - отклоняю дружбу
function frnd_reject_offer_friendship_button( $user_id, $to_user ) {
    $data = rcl_encode_post( [
        'user_id' => $user_id,
        'to_user' => $to_user,
        'type'    => 'reject'
        ] );

    $args = [
        'icon' => 'fa-user-times',
        'attr' => 'data-frnd_data=' . $data . ' data-frnd_type="reject" onclick="frnd_operations(this);return false;"'
    ];

    return '<span class="frnd_actions_bttn frnd_offer_reject">' . rcl_get_button( 'Отклонить', '#', $args ) . '</span>';
}

// Кнопка - удаляю из друзей
function frnd_delete_friendship_button( $user_id, $to_user ) {
    $data = rcl_encode_post( [
        'user_id' => $user_id,
        'to_user' => $to_user,
        'type'    => 'delete'
        ] );

    $args = [
        'icon' => 'fa-user-times',
        'attr' => 'data-frnd_data=' . $data . ' data-frnd_type="reject" onclick="frnd_operations(this);return false;"'
    ];

    return '<span class="frnd_actions_bttn frnd_delete">' . rcl_get_button( 'Убрать из друзей', '#', $args ) . '</span>';
}

// ожидаем подтверждения. заявка подана
function frnd_pending_friendship() {
    echo '<span class="frnd_pending"><a href="#" class="recall-button frnd_disabled"><i class="rcli fa-clock-o"></i><span>Заявка ожидает рассмотрения</span></a></span>';
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
    global $frnd_offer_in;

    if ( ! $frnd_offer_in )
        return;

    return '<div class="frnd_count">'
        . '<a class="recall-button rcl-ajax" data-post="' . frnd_ajax_data( 'friends', 'incoming-friends' ) . '" href="?tab=friends&subtab=incoming-friends"><span>Запросы в друзья: ' . $frnd_offer_in . '</span></a>'
        . '</div>';
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

// склонения "друг, друга, друзей"
function frnd_decline_friend( $n, $w = array( '', '', '' ) ) {
    $x  = ($xx = abs( $n ) % 100) % 10;
    return $w[($xx > 10 AND $xx < 15 OR ! $x OR $x > 4 AND $x < 10) ? 2 : ($x == 1 ? 0 : 1)];
}

// future core in 17.0 wp-recall
function frnd_notice( $params ) {
    $defaults = [
        'header'  => '',
        'text'    => '',
        'type'    => 'info', // info, success, warning, simple
        'is_icon' => true,
        'icon'    => '',
        'class'   => '',
        'border'  => false
    ];
    $args     = wp_parse_args( $params, $defaults );

    $icon = '';

    if ( ! empty( $args['is_icon'] ) && empty( $args['icon'] ) ) {
        switch ( $args['type'] ) {
            case 'success':
                $icon = 'fa-check-circle';
                break;
            case 'warning':
                $icon = 'fa-exclamation-circle';
                break;
            case 'info':
                $icon = 'fa-info-circle';
                break;
        }
    } else if ( ! empty( $args['is_icon'] ) && isset( $args['icon'] ) ) {
        $icon = $args['icon'];
    }

    $border = ! empty( $args['border'] ) ? 'frnd_border' : '';

    $notice_block = '<div class="frnd_notify frnd_' . $args['type'] . ' ' . $args['class'] . ' ' . $border . '">';
    if ( ! empty( $args['is_icon'] ) && ! empty( $icon ) )
        $notice_block .= '<i class="rcli ' . $icon . '" aria-hidden="true"></i>';

    if ( ! empty( $args['header'] ) )
        $notice_block .= '<div class="frnd_notify_header">' . $args['header'] . '</div>';

    $notice_block .= '<div class="frnd_notify_text">' . $args['text'] . '</div>';
    $notice_block .= '</div>';

    return $notice_block;
}

// добавим к блоку автора и к списку пользователей (rows)
add_action( 'rcl_user_description', 'frnd_add_friends_author_publications', 40 );
function frnd_add_friends_author_publications() {
    if ( rcl_is_office() || is_singular( 'page' ) )
        return;

    global $user_ID, $rcl_user;

    if ( ( int ) $rcl_user->ID === ( int ) $user_ID )
        return;

    frnd_manager_friend( $user_ID, $rcl_user->ID );
}
