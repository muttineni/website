<?php
$page_info['section'] = 'profile';
$login_required = 1;
$lang = vlc_get_language();
$user_info = vlc_get_user_info($login_required, 1);

/* get form fields */
$form_fields = $_POST;

/* update course status */
$db_events_array = array();
$course_record_updates_array = array();

foreach ($form_fields['users_courses'] as $user_course_id => $user_course_details){

  $update_fields = '';
  
  if (is_numeric($user_course_details['course_status_id']) and $user_course_details['course_status_id'] != 
  $user_course_details['previous_course_status_id']) {
    $update_fields .= ', course_status_id = '.$user_course_details['course_status_id'];
  }
  
  if (is_numeric($user_course_details['score_level_id']) and $user_course_details['score_level_id'] != 
  $user_course_details['previous_score_level_id']) {
    $update_fields .= ', score_level_id = '.$user_course_details['score_level_id'];
  }
  
  if (strlen($user_course_details['facilitator_notes'])) {
    $facilitator_notes_array[$user_course_id] = "\n\n".$user_course_details['facilitator_notes']."\n";
    $user_course_details['facilitator_notes'] = "\n\n====== Added ".date('F j, Y, g:i a')." ======\n\n".$user_course_details['facilitator_notes'];
    $update_fields .= ', facilitator_notes = CONCAT(IFNULL(facilitator_notes, \'\'), \''.
	 							addslashes($user_course_details['facilitator_notes']).'\')';
  }
  
  if (strlen($update_fields)) {
    $update_course_status_query = 
	 'UPDATE users_courses SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].$update_fields.' WHERE user_course_id = '.$user_course_id;
    $result = mysql_query($update_course_status_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "users_courses"');
    $course_record_updates_array[] = $user_course_id;
    $db_events_array[] = array(USERS_COURSES_UPDATE, $user_course_id);
  }
} //end foreach

/* exit and show error message if no records were updated */
if (count($course_record_updates_array) == 0) 
	vlc_exit_page('<li>'.$lang['profile']['course-roster']['status']['course-status-error'] .
	'</li>', 'error', 'profile/course_roster.php?course='.$form_fields['course_id']);
	
/* get student details */
$user_course_id_list = join(', ', array_keys($form_fields['users_courses']));

$student_details_query = <<< END_QUERY
  SELECT u.first_name, u.last_name, uc.user_course_id, uc.course_status_id, uc.is_scored, IFNULL(s.description, '') AS score_level
  FROM users AS u, users_courses AS uc LEFT JOIN score_levels AS s ON uc.score_level_id = s.score_level_id
  WHERE u.user_id = uc.user_id
  AND uc.user_course_id IN ($user_course_id_list)
  ORDER BY u.last_name, u.first_name
END_QUERY;

$result = mysql_query($student_details_query, $site_info['db_conn']);
$complete_array = $incomplete_array = $static_array = array();

while ($record = mysql_fetch_array($result)) {

  $name_string = ' > '.$record['first_name'].' '.$record['last_name'];
  
  if (in_array($record['user_course_id'], $course_record_updates_array)) {
  
    if (isset($facilitator_notes_array[$record['user_course_id']])) 
	 	$name_string .= $facilitator_notes_array[$record['user_course_id']];
	 
    if ($record['course_status_id'] == 7) 
	 	$complete_array[] = $name_string;
    elseif ($record['course_status_id'] == 6) 
	 	$incomplete_array[] = $name_string;
		
  }else{
  
    if (isset($lang['database']['course-status'][$record['course_status_id']])) 
	 	$name_string .= ' ('.$lang['database']['course-status'][$record['course_status_id']].')';
		
    $static_array[] = $name_string;
  } //end if
} //end while

if (count($complete_array)) 
	$complete_list = join("\n", $complete_array);
else 
	$complete_list = ' > None.';
	
if (count($incomplete_array)) 
	$incomplete_list = join("\n", $incomplete_array);
else 
	$incomplete_list = ' > None.';
	
if (count($static_array)) 
	$static_list = join("\n", $static_array);
else
	$static_list = ' > None.';
	
/* get course details */
$course_details_query = <<< END_QUERY
  SELECT c.description, c.code, u.first_name, u.last_name, cs.language_id
  FROM courses AS c, users_courses AS uc, users AS u, course_subjects AS cs
  WHERE c.course_id = uc.course_id
  AND c.course_subject_id = cs.course_subject_id      
  AND uc.user_id = u.user_id
  AND uc.course_id = {$form_fields['course_id']}
  AND uc.user_id = {$user_info['user_id']}
  AND uc.user_role_id = 4
END_QUERY;

$result = mysql_query($course_details_query, $site_info['db_conn']);
$course_details = mysql_fetch_array($result);

/* mail subject and mail message */
$form_fields['comments'] = preg_replace('/^/m', ' > ', $form_fields['comments']);
$subject = $lang['profile']['email']['course-roster']['subject'];

$message = sprintf($lang['profile']['email']['course-roster']['message'], $course_details['description'].
			  ' ('.$course_details['code'].')', $course_details['first_name'].' '.
			  $course_details['last_name'], $complete_list, $incomplete_list, $static_list, $form_fields['comments'], $site_info['vlcff_email']);
			  
/* send message to user from administrator */
$from = 'From: "'.$lang['common']['misc']['vlcff-admin'].'" <'.$site_info['vlcff_email'].'>';
$to = $user_info['email'];
mail($to, $subject, $message, $from);

/* add cms link to message */
$cms_url = 'http://'.$_SERVER['HTTP_HOST'].$site_info['home_url'].'cms/orders.php?course_id='.$form_fields['course_id'];
$message .= "\n\nCMS Link: $cms_url";

/* send additional message to administrators from user */
$from = 'From: "'.$user_info['full_name'].'" <'.$user_info['email'].'>';
if ($course_details['language_id'] == 2) {
    /* address to Spanish admin */
    $to = $site_info['webmaster_email'].', '.$site_info['support_email'].', '.$site_info['billing_email'].', '.$site_info['spanish_curriculum_email'];
} else {
    /* address to English admin */
    $to = $site_info['webmaster_email'].', '.$site_info['support_email'].', '.$site_info['billing_email'].', '.$site_info['curriculum_email'];
}
/* send mail */
mail($to, $subject, $message, $from);    

/* exit and show success message */
vlc_insert_events($db_events_array);

vlc_exit_page($lang['profile']['course-roster']['status']['course-status-success'], 'success', 'profile/course_roster.php?course='.
              $form_fields['course_id']);
