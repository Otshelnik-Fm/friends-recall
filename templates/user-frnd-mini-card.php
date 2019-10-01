<?php
/*  Шаблон дополнения Friends Recall https://codeseller.ru/products/friends-recall/
  Версия шаблона: v1.1
  Шаблон вывода списка друзей в вкладке ЛК - "Мини карточкой"
  Этот шаблон можно скопировать в папку WP-Recall шаблонов по пути: ваш-сайт/wp-content/wp-recall/templates/
  - сделать нужные вам правки и изменения и он будет подключаться оттуда
  Работа с шаблонами описана тут: https://codeseller.ru/?p=11632
 */
?>
<?php
global $rcl_user, $rcl_users_set;

// если есть вызов в data атрибута comments_count
$uc_count = '0';
if ( in_array( 'comments_count', $rcl_users_set->data ) ) {
    if ( isset( $rcl_user->comments_count ) ) {
        $uc_count = $rcl_user->comments_count;
    }
}
// если есть вызов в data атрибута posts_count
$up_count = '0';
if ( in_array( 'posts_count', $rcl_users_set->data ) ) {
    if ( isset( $rcl_user->posts_count ) ) {
        $up_count = $rcl_user->posts_count;
    }
}
?>

<div class="user-single" data-user-id="<?php echo $rcl_user->ID; ?>">
    <div class="thumb-user">
        <a title="<?php rcl_user_name(); ?>" href="<?php rcl_user_url(); ?>">
            <?php rcl_user_avatar( 150 ); ?>
            <?php rcl_user_action(); ?>
        </a>
        <?php rcl_user_rayting(); ?>
    </div>
    <div class="frnd_bottom">
        <div class="frnd_n_publications"><span>Публикаций:</span><span><?php echo $up_count; ?></span></div>
        <div class="frnd_n_comments"><span>Комментариев:</span><span><?php echo $uc_count; ?></span></div>
        <?php do_action( 'frnd_bottom', $rcl_user ); ?>
    </div>
    <?php do_action( 'frnd_button', $rcl_user ); ?>
</div>
