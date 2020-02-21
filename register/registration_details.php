<?php
$page_info['section'] = 'register';
$page_info['page'] = 'registration-details';
$login_required = 1;
$user_info = vlc_get_user_info($login_required);
$lang = vlc_get_language();
if (isset($_SESSION['course_details']) and isset($_SESSION['form_fields']))
{
  /* get course details from session variable */
  $course_details = $_SESSION['course_details'];
  /* get form field values from session variable */
  $form_fields = $_SESSION['form_fields'];
}
else vlc_redirect('register/');
$output = '<h1 align="center">'.$lang['register']['registration-details']['heading']['registration-details'].'</h1>';
/* create success message based on action (register = 1, waiting list = 2) */
if ($form_fields['action_id'] == 1)
{
  $output .= sprintf($lang['register']['success']['content']['register-message'], $course_details['description'], $course_details['code']);
  if ($form_fields['registration_type_id'] == 1) $output .= '<div class="container"><p>'.$lang['register']['success']['content']['junk-mail-notice'].'</p></div>';
  else $output .= $lang['register']['success']['content']['credit-message'];
}
elseif ($form_fields['action_id'] == 2) $output .= sprintf($lang['register']['success']['content']['wait-message'], $course_details['description'], $course_details['code']);
if ($form_fields['registration_type_id'] == 1) $credit_string = $course_details['ceu'].' '.$lang['register']['index']['misc']['ceu'];
else $credit_string = $course_details['credit'].' '.$lang['register']['index']['misc']['credit'];
$output .= '<div align="center">';
$output .= '<table border="0" cellpadding="2" cellspacing="0">';
$output .= '<tr bgcolor="#eeeeee"><td><b>'.$lang['register']['common']['misc']['course-label'].':</b></td><td>'.$course_details['description'].' ('.$course_details['code'].')</td></tr>';
$output .= '<tr><td><b>'.$lang['register']['common']['misc']['start-date-label'].':</b></td><td>'.$course_details['cycle_start'].'</td></tr>';
$output .= '<tr bgcolor="#eeeeee"><td><b>'.$lang['register']['common']['misc']['course-details-label'].':</b></td><td>'.$lang['database']['course-levels'][$course_details['course_level_id']].' / '.$lang['database']['course-types'][$course_details['course_type_id']].' / '.$credit_string.'</td></tr>';
$output .= '<tr><td><b>'.$lang['register']['common']['misc']['facilitator-label'].':</b></td><td>'.$course_details['first_name'].' '.$course_details['last_name'].'</td></tr>';
if ($form_fields['registration_type_id'] == 1)
{
  $output .= '<tr bgcolor="#eeeeee"><td><b>'.$lang['register']['common']['misc']['discount-type-label'].':</b></td><td>'.$course_details['discount_type_description'].'</td></tr>';
  $output .= '<tr><td><b>'.$lang['register']['common']['misc']['discount-label'].':</b></td><td>'.$course_details['discount_description'].'</td></tr>';
  $output .= '<tr bgcolor="#eeeeee"><td><b>'.$lang['register']['common']['misc']['course-cost-label'].':</b></td><td>'.$course_details['student_cost'].'</td></tr>';
}
if ($form_fields['is_scored']) $scoring = $lang['register']['confirm']['form-fields']['yes'];
else $scoring = $lang['register']['confirm']['form-fields']['no'];
$output .= '<tr><td><b>'.$lang['register']['common']['misc']['scoring-label'].':</b></td><td>'.$scoring.'</td></tr>';
$output .= '</table>';
$output .= '</div>';
if ($form_fields['action_id'] == 1)
{
  $output .= '<h2>'.$lang['register']['course-details']['heading']['course-materials'].'</h2>';
  $output .= '<ul>';
  if ($course_details['num_required'] == 0) $output .= '<li><b>'.$lang['register']['course-details']['misc']['all-materials-online'].'</b></li>';
  if (isset($course_details['materials']) and count($course_details['materials']))
  {
    foreach ($course_details['materials'] as $material) $output .= '<li>'.$material.'</li>';
  }
  $output .= '</ul>';
  if ($course_details['num_required'] > 0) $output .= '<h2>'.$lang['register']['success']['heading']['where-to-buy'].'</h2>'.$lang['register']['success']['content']['where-to-buy'];
  $output .= '<h2>'.$lang['register']['success']['heading']['course-access'].'</h2>'.$lang['register']['success']['content']['course-access'];
  $output .= '<h2>'.$lang['register']['success']['heading']['username-password'].'</h2>'.$lang['register']['success']['content']['username-password'];
  $output .= '<h2>'.$lang['register']['success']['heading']['additional-materials'].'</h2>'.$lang['register']['success']['content']['additional-materials'];
  $output .= '<h2>'.$lang['register']['success']['heading']['technical-support'].'</h2>'.$lang['register']['success']['content']['technical-support'];
}
$output .= '<p style="text-align: center;">'.$lang['register']['course-details']['content']['print-link'].' - '.$lang['register']['course-details']['content']['close-window-link'].'</p>';
?>
<!--
  Virtual Learning Community for Faith Formation (VLCFF)
  Institute for Pastoral Initiatives (IPI)
  University of Dayton
  vlcff@udayton.edu
  http://vlc.udayton.edu/
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $lang['common']['misc']['current-language-code'] ?>" lang="<?php print $lang['common']['misc']['current-language-code'] ?>">
<head>
<title><?php print $lang['common']['misc']['vlcff'] ?> @ UD &gt; <?php print $lang[$page_info['section']][$page_info['page']]['page-title'] ?></title>
<link rel="stylesheet" type="text/css" href="<?php print $site_info['css_url'] ?>style.css">
<link rel="icon" href="{$site_info['images_url']}favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="{$site_info['images_url']}favicon.ico" type="image/x-icon">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Language" content="<?php print $lang['common']['misc']['current-language-code'] ?>">
<meta name="Keywords" content="<?php print $lang['common']['misc']['meta-keywords'] ?>">
<meta name="Description" content="<?php print $lang['common']['misc']['meta-description'] ?>">
<meta name="Author" content="<?php print $lang['common']['misc']['meta-author'] ?>">
</head>
<body onload="self.focus();">
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
</body>
</html>
