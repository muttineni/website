<?php
$page_info['section'] = 'cms';
$page_info['login_required'] = 1;
$user_info = vlc_get_user_info($page_info['login_required']);
$lang = vlc_get_language();
/* get form fields from posted variables */
$form_fields = $_POST;
/* create return url */
$return_url = 'cms/partner_details.php';
$error_message = '';
$db_events_array = array();
/* remove diocesan partner representatives */
if (isset($form_fields['representatives']))
{
  $return_url .= '?partner='.$form_fields['partner_id'];
  $num_users_deleted = 0;
  foreach ($form_fields['representatives'] as $user_id => $representative)
  {
    if (isset($representative['remove']))
    {
      $delete_query = 'DELETE FROM users_partners WHERE partner_id = '.$form_fields['partner_id'].' AND user_id = '.$user_id;
      $result = mysql_query($delete_query, $site_info['db_conn']);
      $db_events_array[] = array(PARTNERS_REMOVE_REP, $form_fields['partner_id']);
      $db_events_array[] = array(USERS_REMOVE_FROM_PARTNER, $user_id);
      $num_users_deleted++;
    }
  }
  vlc_insert_events($db_events_array);
  if ($num_users_deleted) vlc_exit_page($num_users_deleted.' Diocesan Partner Representative(s) Removed.', 'success', $return_url);
  else vlc_exit_page('<li>No Changes Saved.</li>', 'error', $return_url);
}
/* add diocesan partner representative */
elseif (isset($form_fields['user_id']))
{
  $return_url .= '?partner='.$form_fields['partner_id'];
  if (is_numeric($form_fields['user_id']) and $form_fields['user_id'] > 0)
  {
    $insert_query = 'INSERT INTO users_partners SET partner_id = '.$form_fields['partner_id'].', user_id = '.$form_fields['user_id'];
    $result = @mysql_query($insert_query, $site_info['db_conn']);
    $mysql_error_num = mysql_errno();
    if ($mysql_error_num == 1062) vlc_exit_page('<li>Duplicate User ID Entered.</li><li>No Changes Saved.</li>', 'error', $return_url);
    elseif ($mysql_error_num > 0) trigger_error('INSERT FAILED: users_partners');
    $db_events_array[] = array(PARTNERS_ADD_REP, $form_fields['partner_id']);
    $db_events_array[] = array(USERS_ADD_TO_PARTNER, $form_fields['user_id']);
    vlc_insert_events($db_events_array);
    vlc_exit_page('Diocesan Partner Representative Added.', 'success', $return_url);
  }
  else vlc_exit_page('<li>No User ID Entered or User ID is Not Valid.</li><li>No Changes Saved.</li>', 'error', $return_url);
}
/* check to see if required fields were filled in */
if (!(strlen($form_fields['description'] = trim($form_fields['description'])))) $error_message .= '<li>Description is required.</li>';
if (strlen($form_fields['student_seminar_cost']))
{
  if (!is_numeric($form_fields['student_seminar_cost'])) $error_message .= '<li>Student Seminar Cost must be numeric.</li>';
  if (!strlen($form_fields['partner_seminar_cost']) or !strlen($form_fields['student_course_cost']) or !strlen($form_fields['partner_course_cost'])) $error_message .= '<li>If one <b>&quot;Cost&quot;</b> field is filled in, then all <b>&quot;Cost&quot;</b> fields must be filled in.</li>';
}
if (strlen($form_fields['partner_seminar_cost']))
{
  if (!is_numeric($form_fields['partner_seminar_cost'])) $error_message .= '<li>Partner Seminar Cost must be numeric.</li>';
  if (!strlen($form_fields['student_seminar_cost']) or !strlen($form_fields['student_course_cost']) or !strlen($form_fields['partner_course_cost'])) $error_message .= '<li>If one <b>&quot;Cost&quot;</b> field is filled in, then all <b>&quot;Cost&quot;</b> fields must be filled in.</li>';
}
if (strlen($form_fields['student_course_cost']))
{
  if (!is_numeric($form_fields['student_course_cost'])) $error_message .= '<li>Student Course Cost must be numeric.</li>';
  if (!strlen($form_fields['student_seminar_cost']) or !strlen($form_fields['partner_seminar_cost']) or !strlen($form_fields['partner_course_cost'])) $error_message .= '<li>If one <b>&quot;Cost&quot;</b> field is filled in, then all <b>&quot;Cost&quot;</b> fields must be filled in.</li>';
}
if (strlen($form_fields['partner_course_cost']))
{
  if (!is_numeric($form_fields['partner_course_cost'])) $error_message .= '<li>Partner Course Cost must be numeric.</li>';
  if (!strlen($form_fields['student_seminar_cost']) or !strlen($form_fields['partner_seminar_cost']) or !strlen($form_fields['student_course_cost'])) $error_message .= '<li>If one <b>&quot;Cost&quot;</b> field is filled in, then all <b>&quot;Cost&quot;</b> fields must be filled in.</li>';
}
if (!isset($form_fields['is_partner'])) $form_fields['is_partner'] = 0;
if (!isset($form_fields['is_diocese'])) $form_fields['is_diocese'] = 0;
/* if errors have occurred, go back to form */
if (strlen($error_message) > 0)
{
  $_SESSION['form_fields'] = $form_fields;
  vlc_exit_page($error_message, 'error', $return_url);
}
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = addslashes($value);
}
if (is_numeric($form_fields['student_seminar_cost'])) $form_fields['student_seminar_cost'] *= 100;
else $form_fields['student_seminar_cost'] = 'NULL';
if (is_numeric($form_fields['partner_seminar_cost'])) $form_fields['partner_seminar_cost'] *= 100;
else $form_fields['partner_seminar_cost'] = 'NULL';
if (is_numeric($form_fields['student_course_cost'])) $form_fields['student_course_cost'] *= 100;
else $form_fields['student_course_cost'] = 'NULL';
if (is_numeric($form_fields['partner_course_cost'])) $form_fields['partner_course_cost'] *= 100;
else $form_fields['partner_course_cost'] = 'NULL';
/* update partner details */
if (isset($form_fields['partner_id']))
{
  /* add partner id to return url */
  $return_url .= '?partner='.$form_fields['partner_id'];
  /* update partners table */
  $update_partner_query = <<< END_QUERY
    UPDATE partners
    SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']},
      description = '{$form_fields['description']}', alternate_description = NULLIF('{$form_fields['alternate_description']}', ''),
      student_seminar_cost = {$form_fields['student_seminar_cost']}, partner_seminar_cost = {$form_fields['partner_seminar_cost']},
      student_course_cost = {$form_fields['student_course_cost']}, partner_course_cost = {$form_fields['partner_course_cost']},
      is_partner = {$form_fields['is_partner']}, is_diocese = {$form_fields['is_diocese']},
      address_1 = NULLIF('{$form_fields['address_1']}', ''), address_2 = NULLIF('{$form_fields['address_2']}', ''),
      city = NULLIF('{$form_fields['city']}', ''), state_id = {$form_fields['state_id']}, zip = NULLIF('{$form_fields['zip']}', ''),
      country_id = {$form_fields['country_id']},
      notes = NULLIF('{$form_fields['notes']}', ''), url = NULLIF('{$form_fields['url']}', ''), bishop = NULLIF('{$form_fields['bishop']}', '')
    WHERE partner_id = {$form_fields['partner_id']}
END_QUERY;
  $result = mysql_query($update_partner_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "partners"');
  /* create success message */
  $success_message = '<p>Partner Details Updated.</p>';
  $db_events_array[] = array(PARTNERS_UPDATE, $form_fields['partner_id']);
}
/* insert new partner */
else
{
  /* insert into partners table */
  $insert_partner_query = <<< END_QUERY
    INSERT INTO partners
    SET CREATED = NULL, CREATEDBY = {$user_info['user_id']},
      description = '{$form_fields['description']}', alternate_description = NULLIF('{$form_fields['alternate_description']}', ''),
      student_seminar_cost = NULLIF('{$form_fields['student_seminar_cost']}', ''), partner_seminar_cost = NULLIF('{$form_fields['partner_seminar_cost']}', ''),
      student_course_cost = NULLIF('{$form_fields['student_course_cost']}', ''), partner_course_cost = NULLIF('{$form_fields['partner_course_cost']}', ''),
      is_partner = {$form_fields['is_partner']}, is_diocese = {$form_fields['is_diocese']},
      address_1 = NULLIF('{$form_fields['address_1']}', ''), address_2 = NULLIF('{$form_fields['address_2']}', ''),
      city = NULLIF('{$form_fields['city']}', ''), state_id = {$form_fields['state_id']}, zip = NULLIF('{$form_fields['zip']}', ''),
      country_id = {$form_fields['country_id']},
      notes = NULLIF('{$form_fields['notes']}', ''), url = NULLIF('{$form_fields['url']}', ''), bishop = NULLIF('{$form_fields['bishop']}', '')
END_QUERY;
  $result = mysql_query($insert_partner_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "partners"');
  $new_partner_id = mysql_insert_id();
  /* add new partner id to return url */
  $return_url .= '?partner='.$new_partner_id;
  /* create success message */
  $success_message = '<p>Partner Successfully Created.</p>';
  $db_events_array[] = array(PARTNERS_CREATE, $new_partner_id);
}
vlc_insert_events($db_events_array);
/* return to partner details page */
vlc_exit_page($success_message, 'success', $return_url);
?>
