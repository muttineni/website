<?php
$page_info['section'] = 'courses';
$page_info['page'] = 'participant-guidelines';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<div class="container">
    <h1><?php print $lang['courses']['participant-guidelines']['heading']['participant-guidelines'] ?></h1>
    <div class="return-link">
        <i class="fa fa-arrow-left"></i> <?php print vlc_internal_link($lang['courses']['shared']['return-link'], 'courses/') ?>
    </div>
    <h3><?php print $lang['courses']['participant-guidelines']['heading']['ensuring-quality'] ?></h3>
    <div class="course-detail">
        <?php print $lang['courses']['participant-guidelines']['content']['ensuring-quality'] ?>
    </div>
    <h3><?php print $lang['courses']['participant-guidelines']['heading']['setting-expectations'] ?></h3>
    <div class="course-detail">
        <?php print $lang['courses']['participant-guidelines']['content']['setting-expectations'] ?>
    </div>
    <h3><?php print $lang['courses']['participant-guidelines']['heading']['self-direction'] ?></h3>
    <div class="course-detail">
        <?php print $lang['courses']['participant-guidelines']['content']['self-direction'] ?>
    </div>
    <h3><?php print $lang['courses']['participant-guidelines']['heading']['insights'] ?></h3>
    <div class="course-detail">
        <?php print $lang['courses']['participant-guidelines']['content']['insights'] ?>
    </div>    
    <h3><?php print $lang['courses']['participant-guidelines']['heading']['facilitator'] ?></h3>
    <div class="course-detail">
        <?php print $lang['courses']['participant-guidelines']['content']['facilitator'] ?>
    </div>    
    <h3><?php print $lang['courses']['participant-guidelines']['heading']['ceu'] ?></h3>
    <div class="course-detail">
        <?php print $lang['courses']['participant-guidelines']['content']['ceu'] ?>
    </div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

