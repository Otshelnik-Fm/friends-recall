<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/** СООБЩЕНИЯ:
 * 1. Гостю в ЛК (если вкл в опциях):   "Штучка знакома вам? Войдите на сайт и вы сможете добавить её в друзья"
 * 2. В своём ЛК:                       "У вас: 2 запроса в друзья! Посмотреть"
 * 3. В чужом ЛК, к вам в друзья:       "Matroskin хочет добавить вас в друзья. Вы можете принять запрос или отклонить его кнопками ниже"
 * 4. В чужом ЛК, я к нему в друзья     "Вы уже отправили запрос в друзья этому пользователю"
 * 5. В чужом ЛК - он меня забанил      "Пользователь вас забанил"
 *
 *  */
class FriendsTopMessages {

    protected static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    function __construct() {
        $this->top_messages();
    }

    private function top_messages() {
        // гость
        if ( ! is_user_logged_in() && rcl_get_option( 'frnd_guest_mess', 'yes' ) === 'yes' ) {
            echo $this->guests_top_mess();
        }

        // залогиненный
        else {
            global $user_ID;

            // если в своем ЛК - проверим и выведем счетчик - кто подал ко мне заявку в друзья
            if ( rcl_is_office( $user_ID ) ) {
                echo $this->incoming_top_mess();
            }

            // в чужом ЛК
            else {
                echo $this->not_your_lk();
            }
        }
    }

    private function not_your_lk() {
        $relations = frnd_get_relation_friendship_current_user_to_lk();

        if ( ! $relations ) {
            return;
        }

        global $user_ID;

        // это обратный запрос
        if ( isset( $relations[0] ) && $relations[0]['friend_id'] == $user_ID && $relations[0]['status'] == 1 ) {
            return $this->top_mess_pending_incoming();
        }
        // это я в кабинет подал запрос
        else if ( isset( $relations[0] ) && $relations[0]['owner_id'] == $user_ID && $relations[0]['status'] == 1 ) {
            return $this->top_mess_pending_outgoing();
        }
        // заблокирован. бан
        else if ( isset( $relations[0] ) && $relations[0]['friend_id'] == $user_ID && $relations[0]['status'] == 4 ) {
            return $this->top_mess_ban();
        }
    }

    // 1. Гостю в ЛК (если вкл в опциях)
    private function guests_top_mess() {
        global $rcl_office;

        $login = 'Войдите';
        if ( rcl_get_option( 'login_form_recall', 'yes' ) == 0 ) {
            $login = '<a href="#" class="rcl-login"><span>Войдите</span></a>';
        }

        $data = [
            'type' => 'success',
            'text' => frnd_get_author_name( $rcl_office ) . ' ' . frnd_decline_by_sex( $rcl_office, [ 'знаком', 'знакома' ] ) . ' вам? '
            . $login . ' на сайт и вы сможете добавить ' . frnd_decline_by_sex( $rcl_office, [ 'его', 'её' ] ) . ' в друзья',
            'icon' => 'fa-hand-pointer-o',
        ];

        return rcl_get_notice( $data );
    }

    // 2. В своём ЛК
    private function incoming_top_mess() {
        $offer_in = frnd_count_incoming_friend_requests_lk();

        if ( ! $offer_in )
            return;

        $data = [
            'type' => 'success',
            'text' => 'У вас: <a class="frnd_link rcl-ajax" data-post="' . frnd_ajax_data( 'friends', 'incoming-friends' ) . '" href="?tab=friends&subtab=incoming-friends">' . $offer_in . ' ' . frnd_decline_friend( $offer_in, [ 'запрос', 'запроса', 'запросов' ] ) . ' в друзья! Посмотреть</a>',
            'icon' => 'fa-bell-o',
        ];

        return rcl_get_notice( $data );
    }

    // 3. В чужом ЛК, к вам в друзья
    private function top_mess_pending_incoming() {
        global $rcl_office, $user_ID;

        return frnd_button_in_notice_box( $user_ID, $rcl_office );
    }

    // 4. В чужом ЛК, я к нему в друзья
    private function top_mess_pending_outgoing() {
        $data = [
            'type' => 'success',
            'text' => 'Вы уже отправили запрос в друзья этому пользователю',
            'icon' => 'fa-handshake-o',
        ];

        return rcl_get_notice( $data );
    }

    // 5. В чужом ЛК - он меня забанил
    private function top_mess_ban() {
        $data = [
            'type' => 'error',
            'text' => 'Пользователь вас забанил',
            'icon' => 'fa-ban',
        ];

        return rcl_get_notice( $data );
    }

}
