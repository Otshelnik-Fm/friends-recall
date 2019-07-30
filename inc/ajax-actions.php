<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**/
// форма запроса в друзья
rcl_ajax_action( 'frnd_send_offer' );
function frnd_send_offer() {
    rcl_verify_ajax_nonce();

    global $user_ID;

    $datas = rcl_decode_post( $_POST['frnd_datas'] );

    $from = $datas->user_id;

    if ( ( int ) $user_ID === ( int ) $from ) {
        wp_send_json( array(
            'content' => frnd_offer_form( $_POST['frnd_datas'] )
        ) );
    } else {
        wp_send_json( array( 'error' => 'Ошибка frnd_101' ) );
    }
}

// сама форма
function frnd_offer_form( $datas ) {
    $fields = array(
        [
            'type'        => 'textarea',
            'slug'        => 'frnd_message',
            'placeholder' => 'Можете написать, как вы познакомились'
        ],
        [
            'type'  => 'hidden',
            'slug'  => 'frnd_from_to',
            'value' => $datas
        ],
    );

    $form = rcl_get_form( [
        'onclick' => 'rcl_send_form_data("frnd_save_offer_form",this);return false;',
        'submit'  => 'Отправить запрос в друзья',
        'fields'  => $fields
        ] );

    return $form;
}

// сохраняем с нее данные
rcl_ajax_action( 'frnd_save_offer_form' );
function frnd_save_offer_form() {
    rcl_verify_ajax_nonce();

    global $user_ID;

    $datas = rcl_decode_post( $_POST['frnd_from_to'] );

    $from = ( int ) $datas->user_id;
    $to   = ( int ) $datas->to_user;
    $mess = sanitize_text_field( $_POST['frnd_message'] );

    if ( ( int ) $user_ID === $from ) {
        // получим еще раз статус, возможно race condition
        $status = frnd_get_friend_by_id( $from, $to );

        // всё еще нет никаких связей
        if ( ! isset( $status ) ) {
            // впишем в БД запрос
            frnd_insert_offer_db( $from, $to );

            if ( isset( $mess ) && ! empty( $mess ) ) {
                frnd_insert_offer_message_db( $from, $to, $mess );
            }

            wp_send_json( [
                'success' => 'Запрос отправлен',
                'reload'  => true,
                'dialog'  => [ 'close' => true ],
            ] );
        }
        // В подписчиках но просит снова дружбу
        else if ( isset( $status ) && $status == 3 ) {
            // впишем в БД запрос
            frnd_update_offer_db( $from, $to );

            if ( isset( $mess ) && ! empty( $mess ) ) {
                frnd_insert_offer_message_db( $from, $to, $mess );
            }

            wp_send_json( [
                'success' => 'Запрос отправлен',
                'reload'  => true,
                'dialog'  => [ 'close' => true ],
            ] );
        }
        // появилась какая-то связь. Обновим страницу
        else {
            wp_send_json( [
                'error'  => 'Ошибка frnd_201',
                'reload' => true,
                'dialog' => [ 'close' => true ],
            ] );
        }
    }
    // пользователь не тот кто есть
    else {
        wp_send_json( array( 'error' => 'Ошибка frnd_202' ) );
    }
}

// операции с друзьями
rcl_ajax_action( 'frnd_send_operations' );
function frnd_send_operations() {
    rcl_verify_ajax_nonce();

    global $user_ID;

    $datas = rcl_decode_post( $_POST['frnd_datas'] );

    $from    = ( int ) $datas->user_id;
    $to_user = ( int ) $datas->to_user;

    // пользователь не тот за кого себя выдает
    if ( ( int ) $user_ID !== $from ) {
        wp_send_json( array( 'error' => 'Ошибка frnd_301' ) );
    }

    // подтверждаем дружбу
    if ( $datas->type === 'confirm' ) {
        $result = frnd_confirm_offer_db( $from, $to_user );

        if ( ! empty( $result ) ) {
            wp_send_json( array(
                'status'  => 'ok',
                'content' => 'Вы подтвердили дружбу'
            ) );
        } else {
            wp_send_json( array( 'error' => 'Ошибка frnd_302' ) );
        }
    }

    // оставим в подписчиках
    else if ( $datas->type === 'reject' ) {
        $result = frnd_reject_offer_db( $from, $to_user );

        if ( ! empty( $result ) ) {
            wp_send_json( array(
                'status'  => 'ok',
                'content' => 'Вы отклонили дружбу'
            ) );
        } else {
            wp_send_json( array( 'error' => 'Ошибка frnd_303' ) );
        }
    }

    // удаляем друга
    else if ( $datas->type === 'delete' ) {
        $result = frnd_delete_friend_db( $from, $to_user );

        if ( ! empty( $result ) ) {
            wp_send_json( array(
                'status'  => 'ok',
                'content' => 'Вы успешно удалили из друзей'
            ) );
        } else {
            wp_send_json( array( 'error' => 'Ошибка frnd_304' ) );
        }
    }
}
