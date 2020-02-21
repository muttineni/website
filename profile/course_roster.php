<?php
$page_info['section'] = 'profile';
$page_info['page'] = 'course-roster';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;

/* define course status array */
$course_status_array = array(7 => $lang['database']['course-status'][7], 6 => $lang['database']['course-status'][6]);

/* define score level array */
$score_level_array = $lang['database']['score-levels'];

/* get course details */
$course_id = $_GET['course'];

$course_query = <<< END_QUERY
  SELECT c.code, c.description, UNIX_TIMESTAMP(y.cycle_start) AS cycle_start, UNIX_TIMESTAMP(y.cycle_end) AS cycle_end
  FROM courses AS c, cycles AS y
  WHERE c.cycle_id = y.cycle_id
  AND c.course_id = $course_id
END_QUERY;

$result = mysql_query($course_query, $site_info['db_conn']);

if (mysql_num_rows($result)) {

  $record = mysql_fetch_array($result);
  
  /* get course start date */
  $start_date = date('j|n|Y', $record['cycle_start']);
  $start_date_array = explode('|', $start_date);
  $start_date_array[1] = $lang['common']['months']['abbr'][$start_date_array[1]];
  array_unshift($start_date_array, $lang['common']['misc']['short-date-format']);
  $start_date = call_user_func_array('sprintf', $start_date_array);
  
  // get course end date - bob
  $end_date = date('j|n|Y', $record['cycle_end']);
  $end_date_array = explode('|', $end_date);
  $end_date_array[1] = $lang['common']['months']['abbr'][$end_date_array[1]];
  array_unshift($end_date_array, $lang['common']['misc']['short-date-format']);
  $end_date = call_user_func_array('sprintf', $end_date_array);
  
  // Prepare Course Info for display - bob
  $course_info = '<div style="float:left;">
  					<p><b>'.
  						$lang['profile']['course-roster']['misc']['course-code'].':</b> '.$record['code'].'</p><p><b>'.
  						$lang['profile']['course-roster']['misc']['course-name'].':</b> '.$record['description'].'</p><p><b>'.
						$lang['profile']['course-roster']['misc']['start-date'].':</b> '.$start_date.
					'</p>
				  </div>
				  <div style="float:right; margin:0px 100px 0 0;">
				     <a href="checklist.php" target="_blank"><strong>Create Checklist</strong></a>
				  </div>
				  <p style="clear:both;"><br /></p>';
}
else 
	$course_info = '<p>'.$lang['profile']['course-roster']['status']['invalid-course'].'</p>';

	

// SET SESSION VARIABLES FOR CHECKLIST - bob
$_SESSION['course_name'] = $record['description'];
$_SESSION['course_num'] = $course_id;
$_SESSION['course_code'] = $record['code'];
$_SESSION['course_start_date'] = $start_date;
$_SESSION['course_end_date'] = $end_date;
$_SESSION['facilitator'] = $_SESSION['user_info']['full_name'];	



/* get course roster */
$roster_query = <<< END_QUERY
  SELECT 
	  sc.course_status_id, 
	  sc.is_scored,
	  sc.registration_type_id,
	  IFNULL(sc.score_level_id, -1) AS score_level_id, 
	  sc.user_course_id, 
	  s.user_id, 
	  s.first_name, 
	  s.last_name, 
	  i.primary_email, 
	  i.city,
	  IFNULL(i.state_id, -1) AS state_id, 
	  IFNULL(i.country_id, -1) AS country_id,
	  IFNULL(i.partner_id, -1) AS partner_id, 
	  IFNULL(i.diocese_id, -1) AS diocese_id,
	  '' AS cert_prog
  FROM 
	  users AS f, 
	  users_courses AS fc, 
	  courses AS c,
	  users_courses AS sc, 
	  users AS s, user_info AS i
  WHERE 
  	  f.user_id = fc.user_id
     AND fc.course_id = c.course_id
     AND c.course_id = sc.course_id
     AND sc.user_id = s.user_id
     AND s.user_id = i.user_id
     AND f.user_id = {$user_info['user_id']}
     AND c.course_id = $course_id
     AND sc.user_role_id = 5
     AND sc.course_status_id NOT IN (1, 4, 5)
  ORDER BY 
     s.last_name, 
	  s.first_name
END_QUERY;

$result = mysql_query($roster_query, $site_info['db_conn']);

if (mysql_num_rows($result)) {

  $course_roster_array = array();
  
  while ($record = mysql_fetch_array($result)) 
  	$course_roster_array[$record['user_id']] = $record;
  
  $user_id_list = join(', ', array_keys($course_roster_array));
	
  /* get related certificate programs */
  $cert_prog_query = <<< END_QUERY
    SELECT tu.user_id, t.description
    FROM courses AS c, cert_progs AS t, certs_courses AS tc, certs_users AS tu
    WHERE c.course_subject_id = tc.course_subject_id
    AND t.cert_prog_id = tc.cert_prog_id
    AND tc.cert_prog_id = tu.cert_prog_id
    AND c.course_id = $course_id
    AND tu.user_id IN ($user_id_list)
	 AND tu.cert_status_id = 2
END_QUERY;
	
  $result = mysql_query($cert_prog_query, $site_info['db_conn']);
  
  while ($record = mysql_fetch_array($result)) 
  	$course_roster_array[$record['user_id']]['cert_prog'] .= '<br>* '.$record['description'];
	
  $i = 0;
  
  $roster = $lang['profile']['course-roster']['content']['attention'];
  $roster .= '<p>&nbsp;</p>';
  
  // Create form. Use post method to collect data
  $roster .= '<form method="post" action="course_roster_action.php">';
  $roster .= '<input type="hidden" name="course_id" value="'.$course_id.'">';
  
  // Create table and make table headings
  $roster .= '<table border="0" cellpadding="5" cellspacing="0" width="100%">';
  $roster .= '<tr bgcolor="#eeeeee"><th>'.
  				 $lang['profile']['course-roster']['misc']['name'].' / '.
  				 $lang['profile']['course-roster']['misc']['email'].'</th><th>'.
				 $lang['profile']['course-roster']['misc']['diocese'].' / '.
				 $lang['profile']['course-roster']['misc']['location'].'</th><th>'.
				 $lang['profile']['course-roster']['misc']['course-status'].'</th><th>'.
				 $lang['profile']['course-roster']['misc']['score'].'</th></tr>';
				 
				 
  // clear student session variables befor starting foreach loop
  unset ($_SESSION['student']); 			
				 
  foreach ($course_roster_array as $user_id => $user_details) {
  
    /* alternate row colors */
    if ($i % 2 == 0) 
	 	$row_background = '';
    else 
	 	$row_background = ' bgcolor="#eeeeee"';
		
    /* student name */
    $student_name = vlc_internal_link(
	 						$user_details['last_name'].', '.
							$user_details['first_name'], 'profile/student_history.php?course='.
							$course_id.'&student='.
							$user_details['user_id']);
							
    /* course status */
    $course_status_options = vlc_select_box(
	 									$course_status_array, 'array', 'users_courses['.
										$user_details['user_course_id'].'][course_status_id]', 
										$user_details['course_status_id'], false, 'form-field');
										
    $course_status_options .= '<input type="hidden" name="users_courses['.
	 									$user_details['user_course_id'].'][previous_course_status_id]" value="'.
										$user_details['course_status_id'].'">';
										
    
	 /* score level */
    if ($user_details['is_scored']) 
	 	$score_level_options = vlc_select_box($score_level_array, 'array', 
									  'users_courses['.$user_details['user_course_id'].'][score_level_id]', 
									  $user_details['score_level_id'], false, 'form-field');
    else 
	 	$score_level_options = vlc_select_box(array('NULL' => $lang['profile']['course-roster']['misc']['not-scored']), 
									 'array', 'users_courses['.$user_details['user_course_id'].'][score_level_id]', 
									 $user_details['score_level_id'], true, 'form-field');
									 
	 $score_level_options .= '<input type="hidden" name="users_courses['.$user_details['user_course_id'].'][previous_score_level_id]" value="'.
									 $user_details['score_level_id'].'">';
									
	 $score_level_options .= '<input type="hidden" name="users_courses['.$user_details['user_course_id'].'][is_scored]" value="'.
									 $user_details['is_scored'].'">';
									
										
    /* e-mail address link */
    $email_link = vlc_mailto_link($user_details['primary_email'], $user_details['primary_email'], $lang['common']['misc']['vlcff']);
	 
    /* location */
    $location = $user_details['city'];
    if (isset($lang['database']['states'][$user_details['state_id']])) 
	 	$location .= ', '.$lang['database']['states'][$user_details['state_id']];
    if (isset($lang['database']['countries'][$user_details['country_id']])) 
	 	$location .= ', '.$lang['database']['countries'][$user_details['country_id']];
		
    /* partner */
    if (isset($lang['database']['partners'][$user_details['partner_id']])) 
	 	$partner = $lang['database']['partners'][$user_details['partner_id']];
    elseif (isset($lang['database']['partners'][$user_details['diocese_id']])) 
	 	$partner = $lang['database']['partners'][$user_details['diocese_id']];
    else 
	 	$partner = '--';
		
    /* add table row to roster output */
    $roster .= '<tr'.$row_background.'><td>'.
	 				$student_name.'<br>'.
					$email_link.
					$user_details['cert_prog'].'</td><td>'.
					$partner.'<br>'.
					$location.'</td><td>'.
					$course_status_options.'</td><td>'.
					$score_level_options.'</td></tr>';
					
    $roster .= '<tr'.$row_background.'><td align="right" valign="top"><b>'.
	 				$lang['profile']['course-roster']['misc']['comments'].
					':</b></td><td colspan="3">
					<textarea cols="60" rows="5" name="users_courses['.$user_details['user_course_id'].'][facilitator_notes]">
					
					</textarea></td></tr>';
						
	// GET ROSTER VARIABLES FOR CHECKLIST - bob					
	$student_num = $i+1;				
	$_SESSION['student'][$student_num]['last_name'] = $user_details['last_name'];
	$_SESSION['student'][$student_num]['first_name'] = $user_details['first_name'];
	$_SESSION['student'][$student_num]['email'] = $user_details['primary_email'];
	
	if ($user_details['cert_prog'] > '0')
		$_SESSION['student'][$student_num]['certificate_status'] = 'Y';
	else
		$_SESSION['student'][$student_num]['certificate_status'] = 'N';
		
	if (abs($user_details['is_scored']) >= '1')
		$_SESSION['student'][$student_num]['scoring_status'] = 'Y';
	else
		$_SESSION['student'][$student_num]['scoring_status'] = 'N';
	
	switch ($user_details['registration_type_id']) {
		case 1:
			$_SESSION['student'][$student_num]['registration_type'] = 'CEU';
			break;
		case 2:
			$_SESSION['student'][$student_num]['registration_type'] = 'UGC';
			break;
		case 3:
			$_SESSION['student'][$student_num]['registration_type'] = 'GC';
			break;
		default:
			$_SESSION['student'][$student_num]['registration_type'] = 'None';
	}
					
					
    $i++;
  } //end foreach
  
  $roster .= '</table>';
    
  $roster .= '<p>'.$lang['profile']['course-roster']['content']['comments'].'</p>';
  $roster .= '<p>
  					<textarea cols="80" rows="10" name="comments">
					
					</textarea>
				 </p>';
				 
  $roster .= '<p>'.$lang['profile']['course-roster']['content']['comments_send'].'</p>';
  
  $roster .= '<p class="center"><input type="submit" value="'.
  				 $lang['profile']['course-roster']['form-fields']['save-changes-button'].'" class="submit-button"></p>';
  $roster .= '</form>';
  
} //end if (mysql_num_rows($result))

else 
	$roster = '<p class="center">'.$lang['profile']['course-roster']['content']['no-students'].'</p>';
?>

<?php

//GET COURSE INFO FOR CHECKLIST - bob

// COURSE QUERY
$course_query = <<< END_QUERY
SELECT 
s.session_id,
s.description as session_description,
CONCAT(IF(r.resource_type_id = 4, 'DB', 'Ex'), ': ', LEFT(r.title, 12), '...') AS short_title
FROM courses AS c
JOIN course_subjects AS j ON c.course_subject_id = j.course_subject_id
JOIN sessions AS s ON j.course_subject_id = s.course_subject_id
JOIN resources AS r ON j.course_subject_id = r.course_subject_id
AND s.session_id = r.session_id
WHERE r.resource_type_id IN (4,26)
AND c.course_id = $course_id
ORDER BY j.course_subject_id, c.course_id, s.display_order, r.display_order
END_QUERY;
$result = mysql_query($course_query, $site_info['db_conn']);

// initialize variables - bob
$course_session_num = 1; // this is a number we will use to for the week number of the class. We initialize it to 1.
$course_session_id_prev = 'first'; // this holds the previous value of the $course_session_id variable, used for comparison.

// clear course seesion variables befor starting loop - bob
unset($_SESSION['course']);
$task_num = 1; // initialize
while ($record = mysql_fetch_assoc($result)) {
		$course_session_id = $record['session_id'];
		if (($course_session_id_prev == 'first') || ($course_session_id_prev == $course_session_id)) {
			$_SESSION['course'][$course_session_num]['session_name'] = $record['session_description'];
			$_SESSION['course'][$course_session_num][$task_num]['task_name'] = $record['short_title'];
			$task_num++;
			$course_session_id_prev = $course_session_id;
		} else {
			$course_session_num++; // increment course session number
			$task_num = 1; // initialize
			$_SESSION['course'][$course_session_num]['session_name'] = $record['session_description'];
			$_SESSION['course'][$course_session_num][$task_num]['task_name'] = $record['short_title'];
			$task_num++;
			$course_session_id_prev = $course_session_id;
		}
}
//pass number of course sessions to a session variable

$_SESSION['course']['qty'] = $course_session_num;


?>


<!-- begin page content -->
<h1>
	<?php print $lang['profile']['course-roster']['heading']['course-roster'] ?>
</h1>
<p>
	<?php print vlc_internal_link($lang['profile']['course-roster']['misc']['return-link'], 'profile/course_history.php') ?>
</p>

<?php print $course_info ?>
<?php print $roster ?>
<!-- end page content -->

<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;

?>
