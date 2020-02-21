<?php
$page_info['section'] = 'courses';
$page_info['page'] = 'ug-credit';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<div class="container">
    <h1><?php print $lang['courses']['ug-credit']['page-title'] ?></h1>
    <div class="return-link">
        <i class="fa fa-arrow-left"></i> <?php print vlc_internal_link($lang['courses']['shared']['return-link'], 'courses/') ?>
    </div>
    <h3><?php print $lang['courses']['ug-credit']['heading']['grading'] ?></h3>
    <div class="course-detail">
        <?php print $lang['courses']['ug-credit']['content']['grading'] ?>  
        <?php print $lang['courses']['ug-credit']['content']['grading-scale'] ?>
    </div>
    <h3><?php print $lang['courses']['ug-credit']['heading']['cost'] ?></h3>
    <div class="course-detail">
        <?php print $lang['courses']['ug-credit']['content']['cost'] ?>
    </div>
    <h3><?php print $lang['courses']['ug-credit']['heading']['reqts'] ?></h3>
    <div class="course-detail">
        <?php print $lang['courses']['ug-credit']['content']['reqts'] ?>
    </div>
    <h3><?php print $lang['courses']['ug-credit']['heading']['contact'] ?></h3>
    <div class="course-detail">
        <?php print $lang['courses']['ug-credit']['content']['contact_info'] ?>
        <?php print $lang['courses']['ug-credit']['content']['contact_setup'] ?>
    </div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>