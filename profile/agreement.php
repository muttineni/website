<?php
$page_info['section'] = 'profile';
$page_info['page'] = 'agreement';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<div class="page-content__main">
    <h1><?php echo $lang['profile']['agreement']['heading']['agreement'] ?></h1>
    <div class="card">
        <div class="card-body">
            <?php echo $lang['profile']['agreement']['content']['terms-conditions'] ?>
            <div>
                <?php echo vlc_internal_link($lang['profile']['agreement']['misc']['agree-link'], 'profile/profile.php', 'btn btn-vlc') ?>
            </div>
        </div>  
    </div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

