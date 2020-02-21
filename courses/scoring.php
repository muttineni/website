<?php
$page_info['section'] = 'courses';
$page_info['page'] = 'scoring';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<div class="container">
    <h1><?php print $lang['courses']['scoring']['page-title'] ?></h1>
    <div class="return-link">
        <i class="fa fa-arrow-left"></i> <?php print vlc_internal_link($lang['courses']['shared']['return-link'], 'courses/') ?>
    </div>
    <?php print $lang['courses']['scoring']['content']['scoring'] ?>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

