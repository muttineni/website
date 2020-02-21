<?php
$page_info['section'] = 'profile';
$page_info['page'] = 'course-history';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>

<?php
/* initialize student_record and facilitator_record variables */
$student_record = '';
$facilitator_record = '';
/* get all courses except for those where course status is "cancel" */
$course_record_query = <<< END_QUERY
  SELECT c.course_id, c.code, c.description,
    uc.user_role_id, t.ceu, uc.course_status_id,
    UNIX_TIMESTAMP(y.cycle_start) AS cycle_start,
    f.user_id, f.first_name, f.last_name, u.first_name AS user_first_name, u.last_name AS user_last_name
  FROM courses AS c, users_courses AS uc, users AS u, cycles AS y,
    course_subjects AS s, course_types AS t, users_courses AS fc, users AS f
  WHERE c.course_id = uc.course_id
  AND uc.user_id = u.user_id
  AND u.user_id = {$user_info['user_id']}
  AND c.cycle_id = y.cycle_id
  AND c.course_subject_id = s.course_subject_id
  AND s.course_type_id = t.course_type_id
  AND c.course_id = fc.course_id
  AND fc.user_role_id = 4
  AND fc.course_status_id != 4
  AND fc.user_id = f.user_id
  AND uc.course_status_id IN (1, 2, 3, 5, 6, 7)
  AND c.is_active = 1
  GROUP BY c.course_id
  ORDER BY uc.user_role_id DESC, y.cycle_start DESC
END_QUERY;
$result = mysql_query($course_record_query, $site_info['db_conn']);

$i = 0;
$j = 0;
$k = 0;

while ($record = mysql_fetch_array($result))
{ 
  if ($k==0)
  {
   $my_profile_link = vlc_internal_link($lang['profile']['shared']['return-link'], 'profile/');  
   $student_name = $record['user_first_name'].' '.$record['user_last_name'];
   print <<< BEGIN_TEXT
   <!-- begin page content -->
    <div class="container mb-5">
    <h1>{$lang['profile']['course-history']['heading']['course-history']} {$student_name} </h1>
    <div class="return-link">
      <i class="fa fa-arrow-left"></i>
    {$my_profile_link}
    </div>
BEGIN_TEXT;
   $k++;
  }
  
  $course_status = $lang['database']['course-status'][$record['course_status_id']];
  $facilitator_name = $record['first_name'].' '.$record['last_name'];
  
  /* get cycle start date */
  $start_date = date('j|n|Y', $record['cycle_start']);
  $start_date_array = explode('|', $start_date);
  $start_date_array[1] = $lang['common']['months']['abbr'][$start_date_array[1]];
  array_unshift($start_date_array, $lang['common']['misc']['short-date-format']);
  $start_date = call_user_func_array('sprintf', $start_date_array);
  if ($record['user_role_id'] == 5)
  {
    $student_record .= '<tr>';
    $facilitator_link = vlc_internal_link($facilitator_name, 'courses/facilitator_details.php?facilitator='.$record['user_id']);
    $student_record .= '<td>'.$record['code'].'</td><td>'.$record['description'].'</td><td>'.$start_date.'</td><td>'.$record['ceu'].'</td><td>'.$course_status.'</td><td>'.$facilitator_link.'</td></tr>';
    $i++;
  }
  if ($record['user_role_id'] == 4)
  {
    $facilitator_record .= '<tr>';
    $roster_link = vlc_internal_link($lang['profile']['course-history']['misc']['course-roster'], 'profile/course_roster.php?course='.$record['course_id']);
    $facilitator_record .= '<td>'.$record['code'].'</td><td>'.$record['description'].'</td><td>'.$start_date.'</td><td>'.$course_status.'</td><td>'.$roster_link.'</td></tr>';
    $j++;
  }
}
/* if the user has not taken any courses as a student and has not facilitated any courses, show this message */
if (strlen($student_record) == 0 and strlen($facilitator_record) == 0) print '<div">'.$lang['profile']['course-history']['content']['no-courses'].'</div>';
else
{
  print '<div class="alert alert-info">'.$lang['profile']['course-history']['content']['course-status-info'].'</div>';
  /* if the user has taken courses as a student, list them here */
  if (strlen($student_record))
  {
    print <<< END_TEXT
      <table class="table table-striped w-100">
      <thead><tr><th>{$lang['profile']['course-history']['misc']['course-code']}</th><th>{$lang['profile']['course-history']['misc']['course-name']}</th><th>{$lang['profile']['course-history']['misc']['course-date']}</th><th>{$lang['profile']['course-history']['misc']['ceu']}</th><th>{$lang['profile']['course-history']['misc']['course-status']}</th><th>{$lang['profile']['course-history']['misc']['facilitator']}</th></tr></thead>
      $student_record
      </table>
END_TEXT;
  }
  /* if the user has facilitated courses, list them first */
  if (strlen($facilitator_record))
  {
    print <<< END_TEXT
      <h2>{$lang['profile']['course-history']['heading']['facilitator-courses']}</h2>
      <table class="table table-striped w-100">
      <thead><tr><th>{$lang['profile']['course-history']['misc']['course-code']}</th><th>{$lang['profile']['course-history']['misc']['course-name']}</th><th>{$lang['profile']['course-history']['misc']['course-date']}</th><th>{$lang['profile']['course-history']['misc']['course-status']}</th><th>{$lang['profile']['course-history']['misc']['course-roster']}</th></tr></thead>
      $facilitator_record
      </table>
END_TEXT;
  }
}
?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

