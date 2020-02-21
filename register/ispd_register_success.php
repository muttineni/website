<?php
$page_info['section'] = 'register';
$page_info['page'] = 'success';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
if (isset($_SESSION['course_details']) and isset($_SESSION['form_fields']))
{
  /* get course details from session variable */
  $course_details = $_SESSION['course_details'];
  /* get form field values from session variable */
  $form_fields = $_SESSION['form_fields'];
}
else vlc_redirect('register/');
/* course details */
$course_info_link = '<b><a href="https://vlcff.udayton.edu/course_info/'.$course_details['code'] .'.html" target="_BLANK">Required Course Information</a></b>';

if ($form_fields['registration_type_id'] == 1) $credit_string = $course_details['ceu'].' '.$lang['register']['index']['misc']['ceu'];
else $credit_string = $course_details['credit'].' '.$lang['register']['index']['misc']['credit'];

$course_details_table = '<table class="table table-striped w-50 mx-auto"><tbody>';
$course_details_table .= '<tr><th scope="row">'.$lang['register']['common']['misc']['course-label'].'</th><td>'.$course_details['description'].' ('.$course_details['code'].')</td></tr>';
$course_details_table .= '<tr><th scope="row">'.$lang['register']['common']['misc']['start-date-label'].'</th><td>'.$course_details['cycle_start'].'</td></tr>';
$course_details_table .= '<tr><th scope="row">'.$lang['register']['common']['misc']['course-details-label'].'</th><td>'.$lang['database']['course-levels'][$course_details['course_level_id']].' / '.$lang['database']['course-types'][$course_details['course_type_id']].' / '.$course_details['ceu'].' '.$lang['register']['index']['misc']['ceu'].'</td></tr>';
$course_details_table .= '<tr><th scope="row">'.$lang['register']['common']['misc']['facilitator-label'].'</th><td>'.$course_details['first_name'].' '.$course_details['last_name'].'</td></tr>';
if ($form_fields['registration_type_id'] == 1) {
  $course_details_table.= '<tr><th scope="row">'.$lang['register']['common']['misc']['course-cost-label'].'</th><td>'.$course_details  ['student_cost'].'</td></tr>';
}
$course_details_table .= '</tbody></table>';

/* build page content */
$output = '';
/* create success message and success form based on action (register = 1, waiting list = 2) */
if ($form_fields['action_id'] == 1)
{
  $output .= sprintf($lang['register']['success']['content']['register-message'], $course_details['description'], $course_details['code']);
  /* build register success form */
  if ($form_fields['registration_type_id'] != 1)
  {
    $output .= '<div class="alert alert-info">'.$lang['register']['success']['content']['credit-message'].'</div>';
    $output .= $course_details_table;
    $output .= '<form method="post" action="register_action.php">';
    $output .= '<div class="w-50 mx-auto"><input type="submit" name="done" value="'.$lang['register']['success']['form-fields']['done-button'].'" class="submit-button btn btn-vlc"></div>';
    $output .= '</form>';
  }
  /* show payment form*/
  elseif ($form_fields['amount_due'] > 0)
  {
    $output .= '<div class="alert alert-info">'.$lang['register']['success']['content']['junk-mail-notice'].'<hr/>';
    $output .= $lang['register']['success']['content']['payment-message'].'</div>';
    $output .= $course_details_table;
    $output .= '<form method="post" action="'.$site_info['home_url'].'payment/payment_action.php">';
    $output .= '<input type="hidden" name="order_id_array[]" value="'.$form_fields['order_id'].'">';
    $output .= '<div class="w-50 mx-auto">';
    $output .= '<input type="submit" name="pay_later" value="'.$lang['register']['success']['form-fields']['pay-later-button'].'" class="submit-button btn btn-default mr-3">';
    $output .= '<input type="submit" name="pay_now" value="'.$lang['register']['success']['form-fields']['pay-now-button'].'" class="submit-button btn btn-vlc">';
    $output .= '</div></form>';
  }
  /* no charge - do not show payment form */
  else
  {
    $output .= $course_details_table;
    $output .= '<form method="post" action="register_action.php">';
    $output .= '<div class="w-50 mx-auto"><input type="submit" name="done" value="'.$lang['register']['success']['form-fields']['done-button'].'" class="submit-button btn btn-vlc"></div>';
    $output .= '</form>';
  }
  $output .= '<h3 class="mt-4">'.$lang['register']['course-details']['heading']['course-materials'].'</h3>';
  $output .= '<ul>';
  if ($course_details['num_required'] == 0) $output .= '<li><b>'.$lang['register']['course-details']['misc']['all-materials-online'].'</b></li>';
  if (isset($course_details['materials']) and count($course_details['materials']))
  {
    foreach ($course_details['materials'] as $material) $output .= '<li>'.$material.'</li>';
  }
  $output .= '</ul>';  
  $output .= '<h3>'.$lang['register']['success']['heading']['important-course-info-ispd'].'</h3>'.$lang['register']['success']['content']['important-course-info-ispd'] . '<ul><li>'. $course_info_link .'</li></ul>';
  
  $output .= '<h3>'.$lang['register']['success']['heading']['technical-support'].'</h3>'.$lang['register']['success']['content']['technical-support-ispd'];
}
if ($form_fields['action_id'] == 2)
{
  $output .= sprintf($lang['register']['success']['content']['wait-message'], $course_details['description'], $course_details['code']);
  $output .= $course_details_table;
  /* build waiting list success form */
  $output .= '<form method="post" action="register_action.php">';
  $output .= '<div class="w-50 mx-auto mb-4"><input type="submit" name="done" value="'.$lang['register']['success']['form-fields']['done-button'].'" class="submit-button btn btn-vlc"></div>';
  $output .= '</form>';
}
print $header;
?>
<!-- begin page content -->
<div class="container">
  <h1><?php echo $lang['register']['success']['heading']['success'] ?></h1>
  <div class="print-link"><i class="fa fa-print"></i> <?php echo $lang['register']['success']['content']['pop-up-link'] ?></div>
  <?php print $output ?>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;