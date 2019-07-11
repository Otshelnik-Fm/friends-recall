<?php
/*  Шаблон дополнения Friends Recall https://codeseller.ru/products/friends-recall/
  Версия шаблона: v1.0
  Шаблон отправки письма о новом запросе в друзья
  Этот шаблон можно скопировать в папку WP-Recall шаблонов по пути: ваш-сайт/wp-content/wp-recall/templates/
  - сделать нужные вам правки и изменения и он будет подключаться оттуда
  Работа с шаблонами описана тут: https://codeseller.ru/?p=11632
 */
?>

<?php $site_name = get_bloginfo( 'name' ); ?>

<table style="border-collapse:collapse !important;border-spacing:0 !important;margin-bottom:0 !important;margin-left:auto !important;margin-right:auto !important;margin-top:0 !important;table-layout:fixed !important" cellspacing="0" cellpadding="0" bgcolor="#F7F3F0" height="100%" border="0" width="100%">
    <tbody>
        <tr>
            <td valign="top">
    <center style="text-align:left;width:100%">
        <div style="display:none;font:1px / 1px sans-serif;overflow:hidden"></div>
        <div style="margin:auto;max-width:600px">
            <table style="border-collapse:collapse !important;border-spacing:0 !important;border-top-color:#90ee90;border-top-style:solid;border-top-width:7px;margin-bottom:0 !important;margin-left:auto !important;margin-right:auto !important;margin-top:0 !important;max-width:600px;table-layout:fixed !important" cellspacing="0" cellpadding="0" bgcolor="#F7F3F0" align="center" border="0" width="100%">
                <tbody>
                    <tr>
                        <td style="color:#000000;font:bold 30px sans-serif;padding:15px 0 15px 0;text-align:center"><?php echo $site_name; ?></td>
                    </tr>
                </tbody>
            </table>
            <table style="border-collapse:collapse !important;border-radius:5px;border-spacing:0 !important;margin-bottom:0 !important;margin-left:auto !important;margin-right:auto !important;margin-top:0 !important;max-width:600px;table-layout:fixed !important" cellspacing="0" cellpadding="0" bgcolor="#FFFFFF" align="center" border="0" width="100%">
                <tbody>
                    <tr>
                        <td>
                            <table style="border-collapse:collapse !important;border-spacing:0 !important;margin-bottom:0 !important;margin-left:auto !important;margin-right:auto !important;margin-top:0 !important;table-layout:fixed !important" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tbody>
                                    <tr>
                                        <td style="color:#555555;font:15px / 24px sans-serif;padding:20px">
                                            <span style="font-size:20px;font-weight:bold">Привет, <?php echo $data['to_name']; ?>.</span>
                                            <hr style="margin: 15px 0 0;" color="#F7F3F0">
                                            <p>Пользователь <?php echo $data['user_link']; ?> хочет добавить вас в друзья.</p>
                                            <p>Чтобы принять этот запрос и управлять списком всех ожидающих запросов, посетите: <a href="<?php echo $data['cabinet']; ?>" style="color:#a52a2a" target="_blank" rel="noopener noreferrer"><?php echo $data['cabinet']; ?></a></p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <table style="border-collapse:collapse !important;border-radius:5px;border-spacing:0 !important;margin-bottom:0 !important;margin-left:auto !important;margin-right:auto !important;margin-top:0 !important;max-width:600px;table-layout:fixed !important" cellspacing="0" cellpadding="0" bgcolor="#F7F3F0" align="left" border="0" width="100%">
                <tbody>
                    <tr>
                        <td style="color:#525252;font:12px / 19px sans-serif;padding:0 20px 20px;text-align:left;width:100%">
                            <span>© <?php echo date( 'Y' ); ?> <?php echo $site_name; ?></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </center>
</td>
</tr>
</tbody>
</table>