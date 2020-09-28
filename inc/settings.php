<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'rcl_options', 'frnd_addon_options' );
function frnd_addon_options( $options ) {
    // создаем блок
    $options->add_box( 'frnd_box_id', array(
        'title' => 'Настройки Friends Recall',
        'icon'  => 'fa-handshake-o'
    ) );

    // создаем группу 1
    $options->box( 'frnd_box_id' )->add_group( 'frnd_group_1', array(
        'title' => '<span class="dashicons dashicons-images-alt2"></span> Вывод списка друзей:'
    ) )->add_options( array(
        [
            'title'   => 'Вкладку "Друзья" выводить:',
            'type'    => 'radio',
            'slug'    => 'frnd_place',
            'values'  => [ 'counters' => 'В области "Counters"', 'menu' => 'В области "Menu"' ],
            'default' => 'counters',
            'help'    => 'Здесь вы можете задать место, где будет выводиться вкладка.<br>',
            'notice'  => 'По умолчанию: "В области "Counters""<hr>',
        ],
        [
            'title'   => 'Вариант вывода списка друзей в ЛК',
            'type'    => 'radio',
            'slug'    => 'frnd_type',
            'values'  => [ 'frnd-mini-card' => 'Мини карточкой', 'frnd-card' => 'Карточкой', 'rows' => 'Списком', 'frnd-ava' => 'Аватаркой' ],
            'default' => 'frnd-mini-card',
            'help'    => 'В личном кабинете будет выводить список друзей выбранным шаблоном в вкладке "Все друзья".<br><br>По умолчанию: "Мини карточкой"',
        ],
    ) );

    // создаем группу 2
    $options->box( 'frnd_box_id' )->add_group( 'frnd_group_2', array(
        'title' => '<span class="dashicons dashicons-table-col-delete"></span> Подписки:'
    ) )->add_options( array(
        [
            'title'  => 'Подписывать при отказе в дружбе?',
            'type'   => 'select',
            'slug'   => 'frnd_rej_subs',
            'values' => [ 'yes' => 'Да', 'no' => 'Нет' ],
            'help'   => 'Функционал дополнения FEED<br><br>'
            . 'Если выбрали "Да" и активировано дополнение FEED - пользователь при отказе в дружбе подпишется на того с кем он хотел дружить.<br>'
            . '<br>Пример: Вася подал заявку Маше в друзья. Маша Васе отказала. Вася станет её подписчиком.<br><br>'
            . '<strong>ВАЖНО!</strong> Если дополнение FEED будет отключено - никакой подписки не будет. Заявка в дружбу просто отклонится.',
            'notice' => 'По умолчанию: "Да"<hr>',
        ],
        [
            'title'  => 'Подписывать при удалении из друзей?',
            'type'   => 'select',
            'slug'   => 'frnd_del_subs',
            'values' => [ 'yes' => 'Да', 'no' => 'Нет' ],
            'help'   => 'Функционал дополнения FEED<br><br>'
            . 'Если выбрали "Да" и активировано дополнение FEED - пользователь при удалении друга переведёт его в подписчики.<br>'
            . '<br>Пример: если Маша удалит Васю из друзей - Васю автоматически подпишет на Машу, а из друзей удалит.<br><br>'
            . '<strong>ВАЖНО!</strong> Если дополнение FEED будет отключено - то произойдет взаимное исключение из друзей.',
            'notice' => 'По умолчанию: "Да"',
        ],
    ) );

    // создаем группу 3
    $options->box( 'frnd_box_id' )->add_group( 'frnd_group_3', array(
        'title' => '<span class="dashicons dashicons-testimonial"></span> Уведомления на сайте:'
    ) )->add_options( array(
        [
            'title'   => 'Включаем уведомления на сайте?',
            'type'    => 'radio',
            'slug'    => 'frnd_notify',
            'values'  => [ 'yes' => 'Да', 'no' => 'Нет' ],
            'default' => 'yes',
            'help'    => 'Если у вас включено дополнение <a href="https://codeseller.ru/products/rcl-notification-spisok-uvedomlenij-polzovatelya-v-lichnom-kabinete/" target="_blank">Rcl-Notification</a> - '
            . 'то о новом запросе в друзья пользователь узнает через его сообщение на сайте. Если же данный доп у вас на сайте не активирован - то при каждой загрузке страницы, '
            . 'если есть не принятые запросы в друзья, слева вверху будет всплывать нотис. Это более назойливое сообщение - поэтому пользователю, чтоб скрыть его, придется все '
            . 'входящие сообщения о дружбе обрабатывать (отказать или принять дружбу).'
            . '<br><br>p.s. Письмо-уведомление о новом предложении дружбы отправляется на почту пользователя всегда, вне зависимости от этой настройки.',
            'notice'  => 'По умолчанию: "Да"<hr>',
        ],
        [
            'title'   => 'В ЛК незалогиненому покажем сообщение?',
            'type'    => 'radio',
            'slug'    => 'frnd_guest_mess',
            'values'  => [ 'yes' => 'Да', 'no' => 'Нет' ],
            'default' => 'yes',
            'help'    => 'Над личным кабинетом незалогиненный увидит сообщение: "Анжелика знакома вам? Войдите на сайт и вы сможете добавить её в друзья"',
        ],
    ) );



    $text   = 'Перейдите на CodeSeller.ru, в товарную метку <strong><a href="https://codeseller.ru/product_tag/druzya/" title="Перейти в каталог к товарной метке" target="_blank">"Друзья"</a></strong>'
        . ' <small>(ссылка откроется в новом окне)</small>'
        . ' -&nbsp;там представлены дополнения, которые расширяют функционал основного дополнения "Друзья" и работают с ним.'
        . '<br><br>Заходите почаще - там будет много нового!';
    $text   .= '<style>#options-group-frnd_group_4 .rcl-notice__text{text-align:left;margin-left:18px;}</style>';
    $args   = [
        'type'  => 'success', // info,success,warning,error,simple
        'icon'  => 'fa-exclamation-triangle',
        'title' => 'Расширьте функционал "Friends Recall"',
        'text'  => $text,
    ];
    $my_adv = rcl_get_notice( $args );

    // создаем группу 4
    $options->box( 'frnd_box_id' )->add_group( 'frnd_group_4', array(
        'title' => '<span class="dashicons dashicons-money-alt"></span> Расширить функционал "Друзей":'
    ) )->add_options( array(
        [
            'type'    => 'custom',
            'content' => $my_adv
        ],
    ) );


    return $options;
}
