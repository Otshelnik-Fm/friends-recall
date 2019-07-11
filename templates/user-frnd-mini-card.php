<?php
global $rcl_user, $rcl_users_set;

// если есть вызов в data атрибута comments_count
$uc_count = '';
if ( in_array( 'comments_count', $rcl_users_set->data ) ) {
    $uc_count = $rcl_user->comments_count;
    if ( ! isset( $uc_count ) ) {
        $uc_count = '0';
    }
}
// если есть вызов в data атрибута posts_count
$up_count = '';
if ( in_array( 'posts_count', $rcl_users_set->data ) ) {
    $up_count = $rcl_user->posts_count;
    if ( ! isset( $up_count ) ) {
        $up_count = '0';
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
    </div>
    <?php do_action( 'frnd_button' ); ?>
</div>