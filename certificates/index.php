<?php
$page_info['section'] = 'certificates';
$page_info['page'] = 'index';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<div class="container">
    <h1><?php print $lang['certificates']['index']['page-title'] ?></h1>
    <h3><?php print $lang['certificates']['index']['heading']['general-info'] ?></h3>
    <div>
        <?php print $lang['certificates']['index']['content']['intro'] ?>
        <ul class="list-group mx-5">
            <?php echo $lang['certificates']['index']['list']['intro'] ?>
        </ul>
    </div>
    <h3 class="mt-3"><?php print $lang['certificates']['index']['heading']['scoring'] ?></h3>
    <div class="mb-5">
        <?php print $lang['certificates']['index']['content']['scoring'] ?>
    </div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
