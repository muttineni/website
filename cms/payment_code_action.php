<?php
$page_info['section'] = 'cms';
$page_info['login_required'] = 1;
$user_info = vlc_get_user_info($page_info['login_required']);
$lang = vlc_get_language();
/* get form fields from posted variables */
$form_fields = $_POST;
/* create return url */
$return_url = 'cms/payment_code_details.php';
$error_message = '';
$db_events_array = array();
/* check to see if required fields were filled in */
if (!(strlen($form_fields['code'] = trim($form_fields['code'])))) $error_message .= '<li>Code is required.</li>';
if (!(strlen($form_fields['description'] = trim($form_fields['description'])))) $error_message .= '<li>Description is required.</li>';
if (strlen($form_fields['student_seminar_cost']) == 0 or !is_numeric($form_fields['student_seminar_cost'])) $error_message .= '<li>Student Seminar Cost is required and must be numeric.</li>';
if (strlen($form_fields['student_course_cost']) == 0 or !is_numeric($form_fields['student_course_cost'])) $error_message .= '<li>Student Course Cost is required and must be numeric.</li>';
if (isset($form_fields['partner_id']) and is_numeric($form_fields['partner_id']))
{
  if (strlen($form_fields['partner_seminar_cost']) == 0 or !is_numeric($form_fields['partner_seminar_cost'])) $error_message .= '<li>Partner Seminar Cost is required and must be numeric when a partner is selected from the drop-down.</li>';
  if (strlen($form_fields['partner_course_cost']) == 0 or !is_numeric($form_fields['partner_course_cost'])) $error_message .= '<li>Partner Course Cost is required and must be numeric when a partner is selected from the drop-down.</li>';
}
elseif (strlen($form_fields['partner_seminar_cost']) or strlen($form_fields['partner_course_cost'])) $error_message .= '<li>Partner Seminar Cost and Partner Course Cost should be blank when no partner is selected from the drop-down.</li>';
/* if errors have occurred, go back to form */
if (strlen($error_message) > 0)
{
  $_SESSION['form_fields'] = $form_fields;
  vlc_exit_page($error_message, 'error', $return_url);
}
$form_fields['student_seminar_cost'] = $form_fields['student_seminar_cost'] * 100;
$form_fields['student_course_cost'] = $form_fields['student_course_cost'] * 100;
if (is_numeric($form_fields['partner_seminar_cost'])) $form_fields['partner_seminar_cost'] = $form_fields['partner_seminar_cost'] * 100;
else $form_fields['partner_seminar_cost'] = 'NULL';
if (is_numeric($form_fields['partner_course_cost'])) $form_fields['partner_course_cost'] = $form_fields['partner_course_cost'] * 100;
else $form_fields['partner_course_cost'] = 'NULL';
$form_fields['active_start'] = $form_fields['active_start_year'].'-'.$form_fields['active_start_month'].'-'.$form_fields['active_start_day'];
$form_fields['active_end'] = $form_fields['active_end_year'].'-'.$form_fields['active_end_month'].'-'.$form_fields['active_end_day'];
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = addslashes($value);
}
/* update payment code details */
if (isset($form_fields['payment_code_id']))
{
  /* add payment code id to return url */
  $return_url .= '?code='.$form_fields['payment_code_id'];
  /* update payment_codes table */
  $update_payment_code_query = <<< END_QUERY
    UPDATE payment_codes
    SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']}, code = '{$form_fields['code']}', description = '{$form_fields['description']}', partner_id = {$form_fields['partner_id']},
      student_seminar_cost = {$form_fields['student_seminar_cost']}, partner_seminar_cost = {$form_fields['partner_seminar_cost']},
      student_course_cost = {$form_fields['student_course_cost']}, partner_course_cost = {$form_fields['partner_course_cost']},
      active_start = '{$form_fields['active_start']}', active_end = '{$form_fields['active_end']}'
    WHERE payment_code_id = {$form_fields['payment_code_id']}
END_QUERY;
  $result = mysql_query($update_payment_code_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "payment_codes"');
  /* create success message */
  $success_message = '<p>Payment Code Details Updated.</p>';
  $db_events_array[] = array(PAYMENT_CODES_UPDATE, $form_fields['payment_code_id']);
}
/* insert new payment code */
else
{
  /* insert into payment_codes table */
  $insert_payment_code_query = <<< END_QUERY
    INSERT INTO payment_codes
    SET CREATED = NULL, CREATEDBY = {$user_info['user_id']},
      code = '{$form_fields['code']}', description = '{$form_fields['description']}', partner_id = {$form_fields['partner_id']},
      student_seminar_cost = {$form_fields['student_seminar_cost']}, partner_seminar_cost = {$form_fields['partner_seminar_cost']},
      student_course_cost = {$form_fields['student_course_cost']}, partner_course_cost = {$form_fields['partner_course_cost']},
      active_start = '{$form_fields['active_start']}', active_end = '{$form_fields['active_end']}'
END_QUERY;
  $result = mysql_query($insert_payment_code_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "payment_codes"');
  $new_payment_code_id = mysql_insert_id();
  /* add new payment code id to return url */
  $return_url .= '?code='.$new_payment_code_id;
  /* create success message */
  $success_message = '<p>Payment Code Successfully Created.</p>';
  $db_events_array[] = array(PAYMENT_CODES_CREATE, $new_payment_code_id);
}
vlc_insert_events($db_events_array);
/* return to payment code details page */
vlc_exit_page($success_message, 'success', $return_url);
?>
