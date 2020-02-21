<?php
$page_info['section'] = 'courses';
$page_info['page'] = 'payment-policy';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<div class="container">
    <h1><?php print $lang['courses']['payment-policy']['page-title'] ?></h1>
    <div class="return-link">
        <i class="fa fa-arrow-left"></i> <?php print vlc_internal_link($lang['courses']['shared']['return-link'], 'courses/') ?>
    </div>
    <h3><?php print $lang['courses']['payment-policy']['heading']['course-cost'] ?></h3>
    <div class="course-detail">
        <?php print $lang['courses']['payment-policy']['content']['course-cost'] ?>
    </div>
    <h3><?php print $lang['courses']['payment-policy']['heading']['seminar-cost'] ?></h3>
    <div class="course-detail">
        <?php print $lang['courses']['payment-policy']['content']['seminar-cost'] ?>
    </div>
    <h3><?php print $lang['courses']['payment-policy']['heading']['book-requirements'] ?></h3>
    <div class="course-detail">
        <?php print $lang['courses']['payment-policy']['content']['book-requirements'] ?>
    </div>
    <h3><?php print $lang['courses']['payment-policy']['heading']['payment-policy'] ?></h3>
    <div class="course-detail">
        <?php print $lang['courses']['payment-policy']['content']['payment-policy'] ?>
    </div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

