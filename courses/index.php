<?php
$page_info['section'] = 'courses';
$page_info['page'] = 'index';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<div class="container">
  <h1><?php print $lang['courses']['index']['heading']['course-info'] ?></h1>
  <div>
    <?php print $lang['courses']['index']['content']['course-info'] ?>
    <?php print $lang['courses']['index']['content']['certificate-info'] ?>
  </div>
  <h3 class="mt-4"><?php print $lang['courses']['index']['content']['sample-course-link'] ?></h3>
  <div>
    <?php print $lang['courses']['sample-course']['content']['sample-course'] ?>
  </div>
  <div class="card list mt-5">
    <div class="card-header">
      <h3><?php print $lang['courses']['index']['heading']['info-links'] ?></h3>
    </div>
    <ul class="list-group list-group-flush">
      <li class="list-group-item">
        <?php print vlc_internal_link($lang['courses']['index']['content']['payment-policy-link'], 'courses/payment_policy.php') ?>
      </li>
      <li class="list-group-item">
        <?php print vlc_internal_link($lang['courses']['index']['content']['course-catalog-link'], 'courses/courses.php?group_by_track=1')   ?>
      </li>
      <li class="list-group-item">
        <?php print vlc_internal_link($lang['courses']['index']['content']['facilitator-list-link'], 'courses/facilitators.php') ?>
      </li>
      <li class="list-group-item">
        <?php print vlc_internal_link($lang['courses']['index']['content']['participant-guidelines-link'], 'courses/guidelines.php') ?>
      </li>
      <li class="list-group-item">
        <?php print vlc_internal_link($lang['courses']['index']['content']['scoring-rubric-link'], 'courses/scoring.php') ?>
      </li>
      <li class="list-group-item">
        <?php print vlc_internal_link($lang['courses']['index']['content']['ug-credit-link'], 'courses/undergraduate_credit.php') ?>
      </li>
      <li class="list-group-item">
        <?php print vlc_internal_link($lang['courses']['index']['content']['ceu-link'], 'courses/ceu.php').' '.$lang['courses']['index']['content']['ceu-info'] ?>
      </li
    </ul>
  </div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
