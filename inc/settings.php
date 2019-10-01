<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

add_filter( 'admin_options_wprecall', 'frnd_admin_settings' );
function frnd_admin_settings( $content ) {
    $inline_style = '
<style>
#options-friends-recall .rcl-custom-fields-box:nth-child(4) {
    border-color: #b6dbd5;
    background: linear-gradient(180deg, #f8ffd9 0%,#ffe3e3 100%);
    box-shadow: 6px 6px 12px -6px #aaa;
    margin-top: 18px;
}
#options-friends-recall .rcl-custom-fields-box:nth-child(4) h3 {
    color: #2c4e79;
}
</style>
';
    $my_adv       = '<div style="font-size:16px;margin:15px 0 0;line-height: normal;">'
        . 'Расширьте функционал дополнения "Friends Recall"<br><br>'
        . 'Перейдите на CodeSeller.ru, в товарную метку <strong><a href="https://codeseller.ru/product_tag/druzya/" title="Перейти в каталог к товарной метке" target="_blank">"Друзья"</a></strong>'
        . ' <small>(ссылка откроется в новом окне)</small>'
        . ' -&nbsp;там представлены дополнения, которые расширяют функционал основного дополнения "Друзья" и работают с ним.'
        . '<br><br>Заходите почаще - там будет много нового!'
        . '</div>';

    $opt = new Rcl_Options( __FILE__ );

    $content .= $opt->options( 'Настройки Friends Recall', array(
        $opt->options_box( 'Вывод списка друзей', array(
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
            )
        ),
        $opt->options_box( 'Подписки', array(
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
            )
        ),
        $opt->options_box( 'Уведомления на сайте', array(
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
            )
        ),
        $opt->options_box( 'Расширить функционал "Друзей"', array(
            [
                'type'    => 'custom',
                'content' => $inline_style . $my_adv
            ]
            )
        ),
        ) );

    return $content;
}
