<?php
$page_info['section'] = 'register';
$page_info['page'] = 'credit';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get course details and form field values from session variable */
if (isset($_SESSION['course_details']) and isset($_SESSION['form_fields']))
{
  /* get course details from session variable */
  $course_details = $_SESSION['course_details'];
  /* get form field values from session variable */
  $form_fields = $_SESSION['form_fields'];
}
else vlc_redirect('register/');
/* build page content */
$page_content = '<h2>'.$lang['register']['credit']['heading']['credit'].'</h2>';
$page_content .= sprintf($lang['register']['credit']['content']['intro'], $course_details['description'], $course_details['code'], $lang['register']['common']['misc']['cancel-message']);
$page_content .= '<form method="post" action="register_action.php">';
$page_content .= '<input type="hidden" name="is_scored" value="1">';
$page_content .= '<input type="hidden" name="action_id" value="'.$form_fields['action_id'].'">';
$page_content .= '<input type="hidden" name="course_id" value="'.$form_fields['course_id'].'">';
$page_content .= '<input type="hidden" name="registration_type_id" value="'.$form_fields['registration_type_id'].'">';
$page_content .= '<p class="center">';
$page_content .= '<input type="submit" name="cancel" value="'.$lang['register']['common']['form-fields']['cancel-button'].'" class="submit-button">';
$page_content .= '&nbsp;<input type="submit" name="confirm" value="'.$lang['register']['credit']['form-fields']['confirm-button'].'" class="submit-button">';
$page_content .= '</p>';
$page_content .= '</form>';
print $header;
?>
<!-- begin page content -->
<?php print $page_content ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
