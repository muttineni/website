<?php
$page_info['section'] = 'cms';
$page_info['login_required'] = 1;
$user_info = vlc_get_user_info($page_info['login_required']);
$lang = vlc_get_language();
/* get form fields from posted variables */
$form_fields = $_POST;
$error_message = '';
$db_events_array = array();
/* create return url */
$return_url = 'cms/cert_prog_details.php';
if (isset($form_fields['cert_prog_id'])) $return_url .= '?cert_prog='.$form_fields['cert_prog_id'];
if (isset($form_fields['course_subjects']))
{
  $num_added = $num_updated = $num_removed = 0;
  $insert_query_array = $update_query_array = $delete_query_array = array();
  /* get existing course subjects */
  $course_subject_query = 'SELECT course_subject_id, cert_cat_id, display_order FROM certs_courses WHERE cert_prog_id = '.$form_fields['cert_prog_id'];
  $result = mysql_query($course_subject_query, $site_info['db_conn']);
  $course_subject_array = $course_subject_id_array = array();
  while ($record = mysql_fetch_array($result))
  {
    $course_subject_array[$record['course_subject_id']] = $record;
    $course_subject_id_array[] = $record['course_subject_id'];
  }
  foreach ($form_fields['course_subjects'] as $key => $course_subject)
  {
    if (is_numeric($key))
    {
      if (isset($course_subject['remove']))
      {
        $delete_query_array[] = 'DELETE FROM certs_courses WHERE cert_prog_id = '.$form_fields['cert_prog_id'].' AND course_subject_id = '.$key;
        $db_events_array[] = array(CERT_PROGS_REMOVE_COURSE, $form_fields['cert_prog_id']);
        $num_removed++;
      }
      elseif ($course_subject['cert_cat_id'] != $course_subject_array[$key]['cert_cat_id'] or $course_subject['display_order'] != $course_subject_array[$key]['display_order'])
      {
        $update_query_array[] = 'UPDATE certs_courses SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', cert_cat_id = '.$course_subject['cert_cat_id'].', display_order = '.$course_subject['display_order'].' WHERE cert_prog_id = '.$form_fields['cert_prog_id'].' AND course_subject_id = '.$key;
        $num_updated++;
      }
    }
    elseif (is_numeric($course_subject['course_subject_id']) and !in_array($course_subject['course_subject_id'], $course_subject_id_array))
    {
      $course_subject_id_array[] = $course_subject['course_subject_id'];
      if (!is_numeric($course_subject['display_order'])) $error_message .= '<li>Display Order is required.</li>';
      if (strlen($error_message) > 0)
      {
        $_SESSION['form_fields'] = $form_fields;
        vlc_exit_page($error_message, 'error', $return_url);
      }
      /* link course subject query */
      $insert_query_array[] = 'INSERT INTO certs_courses VALUES ('.$form_fields['cert_prog_id'].', '.$course_subject['course_subject_id'].', '.$course_subject['cert_cat_id'].', '.$course_subject['display_order'].', NULL, NULL, NULL, '.$user_info['user_id'].')';
      $db_events_array[] = array(CERT_PROGS_ADD_COURSE, $form_fields['cert_prog_id']);
      $num_added++;
    }
  }
  if (count($insert_query_array) or count($update_query_array) or count($delete_query_array))
  {
    foreach ($insert_query_array as $insert_query)
    {
      $result = mysql_query($insert_query, $site_info['db_conn']);
      if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "certs_courses"');
    }
    foreach ($update_query_array as $update_query)
    {
      $result = mysql_query($update_query, $site_info['db_conn']);
      if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "certs_courses"');
    }
    foreach ($delete_query_array as $delete_query)
    {
      $result = mysql_query($delete_query, $site_info['db_conn']);
      if (mysql_affected_rows() < 1) trigger_error('DELETE FAILED: "certs_courses"');
    }
    vlc_insert_events($db_events_array);
    vlc_exit_page($num_added.' Course Subject(s) Added, '.$num_updated.' Course Subject(s) Updated, '.$num_removed.' Course Subject(s) Removed.', 'success', $return_url);
  }
  else
  {
    $_SESSION['form_fields'] = $form_fields;
    vlc_exit_page('<li>No Changes Made or Duplicate Course Subject(s) Selected.</li>', 'error', $return_url);
  }
}
elseif (isset($form_fields['students']))
{
  /* get existing users */
  $user_query = 'SELECT cert_user_id, user_id, cert_status_id FROM certs_users WHERE cert_prog_id = '.$form_fields['cert_prog_id'];
  $result = mysql_query($user_query, $site_info['db_conn']);
  $user_array = $user_id_array = array();
  while ($record = mysql_fetch_array($result))
  {
    $user_array[$record['cert_user_id']] = $record;
    $user_id_array[] = $record['user_id'];
  }
  /* get application fee */
  $app_fee_query = <<< END_QUERY
    SELECT partner_cost, non_partner_cost
    FROM cert_progs
    WHERE cert_prog_id = {$form_fields['cert_prog_id']}
END_QUERY;
  $result = mysql_query($app_fee_query, $site_info['db_conn']);
  $cert_prog_details = mysql_fetch_array($result);
  $num_users_inserted = $num_users_updated = 0;
  $insert_query_array = $update_query_array = array();
  foreach ($form_fields['students'] as $key => $student)
  {
    if (is_numeric($key))
    {
      if ($student['cert_status_id'] != $user_array[$key]['cert_status_id'])
      {
        $update_query_array[] = 'UPDATE certs_users SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', cert_status_id = '.$student['cert_status_id'].' WHERE cert_user_id = '.$key;
        $db_events_array[] = array(CERTS_USERS_UPDATE, $key);
        $num_users_updated++;
      }
    }
    elseif (is_numeric($student['user_id']) and !in_array($student['user_id'], $user_id_array))
    {
      $user_id_array[] = $student['user_id'];
      $product_id = 'LAST_INSERT_ID()';
      $customer_type_id = 1;
      $product_type_id = 6;
      if (is_numeric($student['discount_type_id']))
      {
        $discount_type_id = floor($student['discount_type_id'] / 10000);
        $discount_id = $student['discount_type_id'] % 10000;
        $order_cost = $cert_prog_details['partner_cost'];
      }
      else
      {
        $discount_type_id = $discount_id = 'NULL';
        $order_cost = $cert_prog_details['non_partner_cost'];
      }
      if ($order_cost == 0) $payment_status_id = 1;
      else $payment_status_id = 2;
      $insert_query_array[] = 'INSERT INTO certs_users (cert_user_id, cert_prog_id, user_id, cert_status_id, score_level_id, certificate_date, notes, application_notes, UPDATED, UPDATEDBY, CREATED, CREATEDBY) VALUES (NULL, '.$form_fields['cert_prog_id'].', '.$student['user_id'].', '.$student['cert_status_id'].', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '.$user_info['user_id'].')';
      $insert_query_array[] = 'SELECT '.CERTS_USERS_CREATE.' AS event_type_id, @new_product_id := LAST_INSERT_ID() AS new_id';
      $insert_query_array[] = 'INSERT INTO orders VALUES (NULL, 1, 1, '.$payment_status_id.', CURDATE(), '.$product_type_id.', '.$product_id.', '.$customer_type_id.', '.$student['user_id'].', '.$discount_type_id.', '.$discount_id.', '.$order_cost.', 0, '.$order_cost.', NULL, NULL, NULL, '.$user_info['user_id'].')';
      $insert_query_array[] = 'SELECT '.ORDERS_CREATE.' AS event_type_id, LAST_INSERT_ID() AS new_id';
      $db_events_array[] = array(CERT_PROGS_ADD_USER, $form_fields['cert_prog_id']);
      $db_events_array[] = array(USERS_ADD_CERT_PROG, $student['user_id']);
      $num_users_inserted++;
    }
  }
  if ($num_users_updated or $num_users_inserted)
  {
    foreach ($insert_query_array as $insert_query)
    {
      $result = mysql_query($insert_query, $site_info['db_conn']);
      if (is_numeric(strpos($insert_query, 'INSERT INTO')) and mysql_affected_rows() < 1) trigger_error('INSERT FAILED');
      if (is_numeric(strpos($insert_query, 'SELECT')) and mysql_num_rows($result) == 1)
      {
        $record = mysql_fetch_array($result);
        $db_events_array[] = array($record['event_type_id'], $record['new_id']);
      }
    }
    foreach ($update_query_array as $update_query) $result = mysql_query($update_query, $site_info['db_conn']);
    vlc_insert_events($db_events_array);
    vlc_exit_page($num_users_inserted.' User(s) Added, '.$num_users_updated.' User(s) Updated.', 'success', $return_url);
  }
  else vlc_exit_page('<li>No User ID(s) Entered or Duplicate User ID(s) Entered.</li><li>No Changes Saved.</li>', 'error', $return_url);
}
/* check to see if required fields were filled in */
if (!(strlen($form_fields['description'] = trim($form_fields['description'])))) $error_message .= '<li>Description is required.</li>';
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
if (isset($form_fields['cert_prog_id']))
{
  /* update cert prog details */
  $update_cert_prog_query = <<< END_QUERY
    UPDATE cert_progs
    SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']}, cert_level_id = {$form_fields['cert_level_id']}, description = '{$form_fields['description']}', display_order = {$form_fields['display_order']}
    WHERE cert_prog_id = {$form_fields['cert_prog_id']}
END_QUERY;
  $result = mysql_query($update_cert_prog_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "cert_progs"');
  $success_message = '<p>Certificate Program Details Updated.</p>';
  $db_events_array[] = array(CERT_PROGS_UPDATE, $form_fields['cert_prog_id']);
}
else
{
  /* insert new cert prog */
  $insert_cert_prog_query = <<< END_QUERY
    INSERT INTO cert_progs
    SET CREATED = NULL, CREATEDBY = {$user_info['user_id']}, cert_level_id = {$form_fields['cert_level_id']}, description = '{$form_fields['description']}', display_order = {$form_fields['display_order']}
END_QUERY;
  $result = mysql_query($insert_cert_prog_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "cert_progs"');
  $cert_prog_id = mysql_insert_id();
  $return_url .= '?cert_prog='.$cert_prog_id;
  $success_message = '<p>New Certificate Program Added.</p>';
  $db_events_array[] = array(CERT_PROGS_CREATE, $cert_prog_id);
}
vlc_insert_events($db_events_array);
/* return to cert prog details page */
vlc_exit_page($success_message, 'success', $return_url);
?>
