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
$return_url = 'cms/cycle_details.php';
if (isset($form_fields['cycle_id'])) $return_url .= '?cycle='.$form_fields['cycle_id'];
if (isset($form_fields['courses']))
{
  $num_courses = 0;
  $insert_query_array = array();
  foreach ($form_fields['courses'] as $course)
  {
    if (is_numeric($course['course_subject_id']))
    {
      if (!is_numeric($course['section_id'])) $error_message .= '<li>Section is required.</li>';
      if (!is_numeric($course['facilitator_id'])) $error_message .= '<li>Facilitator is required.</li>';
      if (!(strlen($course['code'] = trim($course['code'])))) $error_message .= '<li>Code is required.</li>';
      if (!(strlen($course['description'] = trim($course['description'])))) $error_message .= '<li>Description is required.</li>';
      if (!isset($course['is_restricted'])) $course['is_restricted'] = 0;
      if (!isset($course['is_sample'])) $course['is_sample'] = 0;
      if (strlen($error_message) > 0)
      {
        $_SESSION['form_fields'] = $form_fields;
        vlc_exit_page($error_message, 'error', $return_url);
      }
      $course['facilitator_start'] = $course['facilitator_start_year'].'-'.$course['facilitator_start_month'].'-'.$course['facilitator_start_day'];
      $course['facilitator_end'] = $course['facilitator_end_year'].'-'.$course['facilitator_end_month'].'-'.$course['facilitator_end_day'];
      $course['student_start'] = $course['student_start_year'].'-'.$course['student_start_month'].'-'.$course['student_start_day'];
      $course['student_end'] = $course['student_end_year'].'-'.$course['student_end_month'].'-'.$course['student_end_day'];
      /* insert course query */
      $insert_query_array[] = 'INSERT INTO courses VALUES (NULL, '.$course['course_subject_id'].', '.$form_fields['cycle_id'].', '.$course['section_id'].', \''.$course['code'].'\', \''.addslashes($course['description']).'\', \''.$course['facilitator_start'].'\', \''.$course['facilitator_end'].'\', \''.$course['student_start'].'\', \''.$course['student_end'].'\', NULLIF(\''.$course['course_email'].'\', \'\'), NULL, '.$course['is_restricted'].', '.$course['is_sample'].', '.$course['is_active'].', '.$course['registration_type_id'].', NULL, NULL, NULL, '.$user_info['user_id'].')';
      /* get new course id and event type id */
      $insert_query_array[] = 'SELECT '.COURSES_CREATE.' AS event_type_id, @new_course_id := LAST_INSERT_ID() AS new_id';
      $insert_query_array[] = 'SELECT '.COURSES_ADD_USER.' AS event_type_id, @new_course_id AS new_id';
      /* link facilitator query */
      $insert_query_array[] = 'INSERT INTO users_courses (user_course_id, course_id, user_id, user_role_id, course_status_id, registration_type_id, certificate_date, notes, is_scored, score_level_id, facilitator_notes, UPDATED, UPDATEDBY, CREATED, CREATEDBY) VALUES (NULL, @new_course_id, '.$course['facilitator_id'].', 4, 2, '.$course['registration_type_id'].', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '.$user_info['user_id'].')';
      $db_events_array[] = array(USERS_ADD_COURSE, $course['facilitator_id']);
      /* get new user course id and event type id */
      $insert_query_array[] = 'SELECT '.USERS_COURSES_CREATE.' AS event_type_id, LAST_INSERT_ID() AS new_id';
      /* create facilitator stipend order */
      $insert_query_array[] = 'INSERT INTO orders VALUES (NULL, 1, 1, 1, CURDATE(), 3, @new_course_id, 3, '.$course['facilitator_id'].', NULL, NULL, 0, 0, 0, NULL, NULL, NULL, '.$user_info['user_id'].')';
      /* get new order id and event type id */
      $insert_query_array[] = 'SELECT '.ORDERS_CREATE.' AS event_type_id, LAST_INSERT_ID() AS new_id';
      $db_events_array[] = array(CYCLES_ADD_COURSE, $form_fields['cycle_id']);
      $num_courses++;
    }
  }
  if (count($insert_query_array))
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
    vlc_insert_events($db_events_array);
    vlc_exit_page($num_courses.' Course(s) Added.', 'success', $return_url);
  }
  else
  {
    $_SESSION['form_fields'] = $form_fields;
    vlc_exit_page('<li>No Course Subject Selected.</li>', 'error', $return_url);
  }
}
if (isset($form_fields['update_course_status']))
{
  /* get course_id list */
  $course_id_query = 'SELECT course_id FROM courses WHERE cycle_id = '.$form_fields['cycle_id'];
  $result = mysql_query($course_id_query, $site_info['db_conn']);
  if (mysql_num_rows($result))
  {
    $course_id_array = array();
    while ($record = mysql_fetch_array($result)) $course_id_array[] = $record['course_id'];
    $course_id_list = join(', ', $course_id_array);
    if ($form_fields['update_course_status'] == 1) $update_query = 'UPDATE users_courses SET course_status_id = 3 WHERE course_status_id = 2 AND course_id IN ('.$course_id_list.')';
    else $update_query = 'UPDATE users_courses SET course_status_id = 7 WHERE course_status_id = 3 AND user_role_id = 4 AND course_id IN ('.$course_id_list.')';
    $result = mysql_query($update_query, $site_info['db_conn']);
    $total_updates = mysql_affected_rows();
    vlc_exit_page($total_updates.' Records Updated.', 'success', $return_url);
  }
  else vlc_exit_page('<li>No Courses Found.</li>', 'error', $return_url);
}
/* check to see if required fields were filled in */
if (!(strlen($code = trim($form_fields['code'])))) $error_message .= '<li>Code is required.</li>';
if (!(strlen($description = trim($form_fields['description'])))) $error_message .= '<li>Description is required.</li>';
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
$cycle_start = $form_fields['cycle_start_year'].'-'.$form_fields['cycle_start_month'].'-'.$form_fields['cycle_start_day'];
$cycle_end = $form_fields['cycle_end_year'].'-'.$form_fields['cycle_end_month'].'-'.$form_fields['cycle_end_day'];
$registration_start = $form_fields['registration_start_year'].'-'.$form_fields['registration_start_month'].'-'.$form_fields['registration_start_day'];
$registration_end = $form_fields['registration_end_year'].'-'.$form_fields['registration_end_month'].'-'.$form_fields['registration_end_day'];
if (isset($form_fields['cycle_id']))
{
  /* update cycle details */
  $update_cycle_query = <<< END_QUERY
    UPDATE cycles
    SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']}, code = '$code', description = '$description', cycle_start = '$cycle_start', cycle_end = '$cycle_end', registration_start = '$registration_start', registration_end = '$registration_end'
    WHERE cycle_id = {$form_fields['cycle_id']}
END_QUERY;
  $result = mysql_query($update_cycle_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "cycles"');
  $success_message = '<p>Cycle Details Updated.</p>';
  $db_events_array[] = array(CYCLES_UPDATE, $form_fields['cycle_id']);
}
else
{
  /* insert new cycle */
  $insert_cycle_query = <<< END_QUERY
    INSERT INTO cycles
    SET CREATED = NULL, CREATEDBY = {$user_info['user_id']}, code = '$code', description = '$description', cycle_start = '$cycle_start', cycle_end = '$cycle_end', registration_start = '$registration_start', registration_end = '$registration_end'
END_QUERY;
  $result = mysql_query($insert_cycle_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "cycles"');
  $cycle_id = mysql_insert_id();
  $return_url .= '?cycle='.$cycle_id;
  $success_message = '<p>New Cycle Added.</p>';
  $db_events_array[] = array(CYCLES_CREATE, $cycle_id);
}
vlc_insert_events($db_events_array);
/* return to cycle details page */
vlc_exit_page($success_message, 'success', $return_url);
?>
