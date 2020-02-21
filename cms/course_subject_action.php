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
$return_url = 'cms/course_subject_details.php';
if (isset($form_fields['course_subject_id'])) $return_url .= '?subject='.$form_fields['course_subject_id'];
/* check to see if required fields were filled in */
if (!(strlen($form_fields['description'] = trim($form_fields['description'])))) $error_message .= '<li>Description is required.</li>';
if (!isset($form_fields['course_tracks'])) $form_fields['course_tracks'] = array();
if (!isset($form_fields['is_restricted'])) $form_fields['is_restricted'] = 0;
if (!isset($form_fields['is_active'])) $form_fields['is_active'] = 0;
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
if (isset($form_fields['course_subject_id']))
{
  /* update course subject details */
  $update_course_subject_query = <<< END_QUERY
    UPDATE course_subjects
    SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']}, description = '{$form_fields['description']}', amazon_link = NULLIF('{$form_fields['amazon_link']}', ''), course_type_id = {$form_fields['course_type_id']}, course_level_id = {$form_fields['course_level_id']}, language_id = {$form_fields['language_id']}, is_restricted = {$form_fields['is_restricted']}, is_active = {$form_fields['is_active']}
    WHERE course_subject_id = {$form_fields['course_subject_id']}
END_QUERY;
  $result = mysql_query($update_course_subject_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "course_subjects"');
  /* delete old course track(s) */
  $delete_course_tracks_query = 'DELETE FROM courses_tracks WHERE course_subject_id = '.$form_fields['course_subject_id'];
  $result = mysql_query($delete_course_tracks_query, $site_info['db_conn']);
  /* insert new course track(s) */
  if (count($form_fields['course_tracks']))
  {
    foreach ($form_fields['course_tracks'] as $course_track_id) $insert_course_tracks_array[] = '('.$form_fields['course_subject_id'].', '.$course_track_id.', NULL, NULL, NULL, '.$user_info['user_id'].')';
    $insert_course_tracks_query = 'INSERT INTO courses_tracks VALUES '.join(', ', $insert_course_tracks_array);
    $result = mysql_query($insert_course_tracks_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "courses_tracks"');
  }
  $success_message = '<p>Course Subject Details Updated.</p>';
  $db_events_array[] = array(COURSE_SUBJECTS_UPDATE, $form_fields['course_subject_id']);
}
else
{
  /* insert new course subject */
  $insert_course_subject_query = <<< END_QUERY
    INSERT INTO course_subjects
    SET CREATED = NULL, CREATEDBY = {$user_info['user_id']}, description = '{$form_fields['description']}', amazon_link = '{$form_fields['amazon_link']}', course_type_id = {$form_fields['course_type_id']}, course_level_id = {$form_fields['course_level_id']}, language_id = {$form_fields['language_id']}, is_restricted = {$form_fields['is_restricted']}, is_active = {$form_fields['is_active']}
END_QUERY;
  $result = mysql_query($insert_course_subject_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "course_subjects"');
  $course_subject_id = mysql_insert_id();
  $return_url .= '?subject='.$course_subject_id;
  /* insert new course track(s) */
  if (count($form_fields['course_tracks']))
  {
    foreach ($form_fields['course_tracks'] as $course_track_id) $insert_course_tracks_array[] = '('.$course_subject_id.', '.$course_track_id.', NULL, NULL, NULL, '.$user_info['user_id'].')';
    $insert_course_tracks_query = 'INSERT INTO courses_tracks VALUES '.join(', ', $insert_course_tracks_array);
    $result = mysql_query($insert_course_tracks_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "courses_tracks"');
  }
  $success_message = '<p>New Course Subject Added.</p>';
  $db_events_array[] = array(COURSE_SUBJECTS_CREATE, $course_subject_id);
}
vlc_insert_events($db_events_array);
/* return to course subject details page */
vlc_exit_page($success_message, 'success', $return_url);
?>
