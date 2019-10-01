<?php
/*  Шаблон дополнения Friends Recall https://codeseller.ru/products/friends-recall/
  Версия шаблона: v1.1
  Шаблон вывода списка друзей в вкладке ЛК - "Аватарками"
  Этот шаблон можно скопировать в папку WP-Recall шаблонов по пути: ваш-сайт/wp-content/wp-recall/templates/
  - сделать нужные вам правки и изменения и он будет подключаться оттуда
  Работа с шаблонами описана тут: https://codeseller.ru/?p=11632
 */
?>
<?php global $rcl_user, $rcl_users_set; ?>
<div class="user-single" data-user-id="<?php echo $rcl_user->ID; ?>">
    <div class="thumb-user">
        <a title="<?php rcl_user_name(); ?>" href="<?php rcl_user_url(); ?>">
            <?php rcl_user_avatar( 150 ); ?>
            <?php rcl_user_action(); ?>
        </a>
        <?php rcl_user_rayting(); ?>
        <?php do_action( 'frnd_bottom', $rcl_user ); ?>
    </div>
    <?php do_action( 'frnd_button', $rcl_user ); ?>
</div>