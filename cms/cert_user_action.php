<?php
$page_info['section'] = 'cms';
$page_info['login_required'] = 1;
$user_info = vlc_get_user_info($page_info['login_required']);
$lang = vlc_get_language();
/* get form fields from posted variables */
$form_fields = $_POST;
/* initialize success message array */
$success_message_array = array();
$db_events_array = array();
/*******************************************************************************
** editing multiple records (coming from certs_users.php)
*/
/* build update queries */
if (isset($form_fields['cert_user_id_array']))
{
  if (is_array($form_fields['cert_user_id_array']) and count($form_fields['cert_user_id_array']))
  {
    $update_query_array = array();
    foreach ($form_fields['cert_user_array'] as $cert_user_id => $cert_user_record)
    {
      /* check to see if user selected "update all checked records" */
      if (is_numeric($form_fields['update_all_cert_status_id'])) $cert_user_record['cert_status_id'] = $form_fields['update_all_cert_status_id'];
      /* update cert status */
      if ($cert_user_record['cert_status_id'] != $cert_user_record['previous_cert_status_id'])
      {
        $update_query_array[] = 'UPDATE certs_users SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', cert_status_id = '.$cert_user_record['cert_status_id'].' WHERE cert_user_id = '.$cert_user_id;
        /* add record to success message */
        $success_message_array['certs_users'][$cert_user_id] = '<a href="#cert-user-'.$cert_user_id.'">'.$cert_user_id.'</a>';
        $db_events_array[] = array(CERTS_USERS_UPDATE, $cert_user_id);
      }
    }
    /* execute update queries */
    foreach ($update_query_array as $update_query)
    {
      $result = mysql_query($update_query, $site_info['db_conn']);
      if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "certs_users"');
    }
  }
  /* success message */
  if (count($success_message_array))
  {
    $success_message = '<p>The following changes were saved:</p>';
    $success_message .= '<ul>';
    if (isset($success_message_array['certs_users'])) $success_message .= '<li>Update Certificate Registration Status ('.count($success_message_array['certs_users']).'): '.join(', ', $success_message_array['certs_users']).'</li>';
    $success_message .= '</ul>';
  }
  else $success_message = '<p>No changes were saved.</p>';
  vlc_insert_events($db_events_array);
  /* return to search results */
  vlc_exit_page($success_message, 'success', 'cms/certs_users.php?'.$_SERVER['QUERY_STRING']);
}
/*******************************************************************************
** adding / updating / removing requirements completed outside of vlcff
*/
elseif (isset($form_fields['cert_prog_reqs']))
{
  /* return url */
  $return_url = 'cms/cert_user_details.php?cert_user='.$form_fields['cert_user_id'];
  /* query arrays */
  $update_query_array = $delete_query_array = $insert_query_array = array();
  foreach ($form_fields['cert_prog_reqs'] as $course_subject_id => $cert_prog_req_details)
  {
    foreach ($cert_prog_req_details as $key => $value)
    {
      if (is_string($value)) $cert_prog_req_details[$key] = addslashes($value);
    }
    /* add */
    if (isset($cert_prog_req_details['add']))
    {
      $insert_query_array[] = 'INSERT INTO cert_prog_reqs SET CREATED = NULL, CREATEDBY = '.$user_info['user_id'].', cert_user_id = '.$form_fields['cert_user_id'].', course_subject_id = '.$course_subject_id.', description = NULLIF(\''.trim($cert_prog_req_details['description']).'\', \'\'), notes = NULLIF(\''.trim($cert_prog_req_details['notes']).'\', \'\')';
      $db_events_array[] = array(CERT_PROG_REQS_ADD_REQ, $form_fields['cert_user_id']);
    }
    /* remove */
    elseif (isset($cert_prog_req_details['remove']))
    {
      $insert_query_array[] = 'DELETE FROM cert_prog_reqs WHERE cert_prog_req_id = '.$cert_prog_req_details['cert_prog_req_id'];
      $db_events_array[] = array(CERT_PROG_REQS_REMOVE_REQ, $form_fields['cert_user_id']);
    }
    /* update */
    elseif (isset($cert_prog_req_details['cert_prog_req_id']))
    {
      $update_query_array[] = 'UPDATE cert_prog_reqs SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', description = NULLIF(\''.trim($cert_prog_req_details['description']).'\', \'\'), notes = NULLIF(\''.trim($cert_prog_req_details['notes']).'\', \'\') WHERE cert_prog_req_id = '.$cert_prog_req_details['cert_prog_req_id'];
      $db_events_array[] = array(CERT_PROG_REQS_UPDATE_REQ, $form_fields['cert_user_id']);
    }
  }
  foreach ($update_query_array as $update_query)
  {
    $result = mysql_query($update_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED');
  }
  foreach ($delete_query_array as $delete_query)
  {
    $result = mysql_query($delete_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('DELETE FAILED');
  }
  foreach ($insert_query_array as $insert_query)
  {
    $result = mysql_query($insert_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED');
  }
  /* create success message */
  $success_message = '<p>Certificate Program Course Requirement Details Updated.</p>';
  vlc_insert_events($db_events_array);
  /* return to order details page */
  vlc_exit_page($success_message, 'success', $return_url);
}
/*******************************************************************************
** editing a single record (coming from cert_user_details.php)
*/
/* return url */
$return_url = 'cms/cert_user_details.php?cert_user='.$form_fields['cert_user_id'];
/* query arrays */
$update_query_array = $insert_query_array = array();
/* process updates */
if (is_numeric($form_fields['certificate_date_year']) and is_numeric($form_fields['certificate_date_month']) and is_numeric($form_fields['certificate_date_day'])) $certificate_date = $form_fields['certificate_date_year'].'-'.$form_fields['certificate_date_month'].'-'.$form_fields['certificate_date_day'];
else $certificate_date = '';
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = addslashes($value);
}
$update_query_array[] = 'UPDATE certs_users SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', cert_status_id = '.$form_fields['cert_status_id'].', certificate_date = NULLIF(\''.$certificate_date.'\', \'\'), notes = NULLIF(\''.trim($form_fields['notes']).'\', \'\'), score_level_id = '.$form_fields['score_level_id'].', application_notes = NULLIF(\''.trim($form_fields['application_notes']).'\', \'\') WHERE cert_user_id = '.$form_fields['cert_user_id'];
foreach ($update_query_array as $update_query)
{
  $result = mysql_query($update_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED');
}
/* create success message */
$success_message = '<p>Certificate Program Registration Details Updated.</p>';
$db_events_array[] = array(CERTS_USERS_UPDATE, $form_fields['cert_user_id']);
vlc_insert_events($db_events_array);
/* return to order details page */
vlc_exit_page($success_message, 'success', $return_url);
?>
