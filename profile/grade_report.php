<?php
$page_info['section'] = 'profile';
$login_required = 1;
$lang = vlc_get_language();
$user_info = vlc_get_user_info($login_required, 1);

// GET CYCLE (in letter year forma)
?>

<form action="" method="post">
Cycle Code: <input type="text" name="cycle_code"><br>
<input type="submit">
</form>

<?php

if (empty($_POST["cycle_code"])) {
 echo "Please enter cycle code";
}else{
	
$cycle_code = $_POST["cycle_code"];

echo $cycle_code;

// PULL GRADE DATA FOR GIVEN CYCLE FROM DATABASE
$grades_query = <<< END_QUERY
SELECT
users_courses.user_role_id AS 'Role'
,users.user_id AS 'User ID'
,users.last_name AS 'Last Name'
,users.first_name AS 'First Name'
,user_info.primary_email AS 'Email'
,courses.code AS 'Course Code'
,courses.description AS 'Course Name'
,IF (users_courses.is_scored = 1,'yes','no') AS 'Scored?'
,course_status.description AS 'Student Status'
,IFNULL (score_levels.description,'') AS 'Score'
,users_courses.facilitator_notes AS 'Facilitator Notes'

FROM

users
INNER JOIN
user_info
ON (users.user_id =  user_info.user_id)

INNER JOIN
users_courses
ON (users.user_id =  users_courses.user_id)

INNER JOIN
courses
ON (courses.course_id = users_courses.course_id)

INNER JOIN
course_status
ON (course_status.course_status_id = users_courses.course_status_id)

LEFT JOIN
score_levels
ON (score_levels.score_level_id = users_courses.score_level_id)

INNER JOIN
cycles
ON (cycles.cycle_id = courses.cycle_id)

WHERE
cycles.code = '$cycle_code'
AND
course_status.description <> 'Cancelled'
AND
course_status.description <> 'Dropped'

ORDER BY
courses.description
,users.last_name
,users.first_name
END_QUERY;

$result = mysql_query($grades_query, $site_info['db_conn']) or die(mysql_error());

while($row = mysql_fetch_array($result)) {

	if ($row['Role'] == 4) {
		$course['course_code'] = $row['Course Code'];
		$course['course_code']['course_name'] = $row['Course Name'];
		$course['course_code']['facilitator_name'] = $row['First Name'] . ' ' .$row['Last Name'];
		$course['course_code']['facilitator_email'] = $row['Email'];
	}
	if (($row['Role'] == 5) && ($row['Student Status'] == 'Completed')){
		$course['course_code']['completed']['student_name'] = $row['First Name'] . ' '. $row['Last Name'];
		$course['course_code']['completed']['facilitator_notes'] = $row['Facilitator Notes'];
	}elseif (($row['Role'] == 5) && ($row['Student Status'] == 'Did Not Complete')){
		$course['course_code']['not_completed']['student_name'] = $row['First Name'] . ' '. $row['Last Name'];
		$course['course_code']['not_completed']['facilitator_notes'] = $row['Facilitator Notes'];
	}

}

} // end else

// FORMAT DATA FOR EMAIL

echo ("<br>Count = " . count($course));


// EMAIL EACH FACILITATOR'S COURSE GRADE DATA BACK TO THEM



/* get form fields */

$_POST = array();

?>
