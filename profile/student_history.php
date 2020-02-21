<?php
$page_info['section'] = 'profile';
$page_info['page'] = 'student-history';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
$course_id = $_GET['course'];
$student_id = $_GET['student'];
?>
<!-- begin page content -->
<h1><?php print $lang['profile']['student-history']['heading']['student-course-history'] ?></h1>
<p><?php print vlc_internal_link($lang['profile']['student-history']['misc']['return-link'], 'profile/course_roster.php?course='.$course_id) ?></p>
<?php
/* get student details */
$student_details_query = <<< END_QUERY
  SELECT s.first_name, s.last_name, i.primary_email, i.city,
    IFNULL(i.state_id, -1) AS state_id, IFNULL(i.country_id, -1) AS country_id,
    IFNULL(i.partner_id, -1) AS partner_id, IFNULL(i.diocese_id, -1) AS diocese_id
  FROM user_info AS i, users AS s, users_courses AS sc, users_courses AS fc
  WHERE i.user_id = s.user_id
  AND s.user_id = sc.user_id
  AND sc.course_id = fc.course_id
  AND sc.user_id = $student_id
  AND sc.user_role_id = 5
  AND sc.course_id = $course_id
  AND fc.user_id = {$user_info['user_id']}
  AND fc.user_role_id = 4
END_QUERY;
$result = mysql_query($student_details_query, $site_info['db_conn']);
if (mysql_num_rows($result))
{
  $record = mysql_fetch_array($result);
  /* location */
  $location = $record['city'];
  if (isset($lang['database']['states'][$record['state_id']])) $location .= ', '.$lang['database']['states'][$record['state_id']];
  if (isset($lang['database']['countries'][$record['country_id']])) $location .= ', '.$lang['database']['countries'][$record['country_id']];
  /* partner */
  if (isset($lang['database']['partners'][$record['partner_id']])) $diocese = $lang['database']['partners'][$record['partner_id']];
  elseif (isset($lang['database']['partners'][$record['diocese_id']])) $diocese = $lang['database']['partners'][$record['diocese_id']];
  else $diocese = '--';
  print '<p><b>'.$lang['profile']['student-history']['misc']['name'].':</b> '.$record['first_name'].' '.$record['last_name'].'</p><p><b>'.$lang['profile']['student-history']['misc']['email'].':</b> '.vlc_mailto_link($record['primary_email'], $record['primary_email'], $lang['common']['misc']['vlcff']).'</p><p><b>'.$lang['profile']['student-history']['misc']['location'].':</b> '.$location.'</p><p><b>'.$lang['profile']['student-history']['misc']['diocese'].':</b> '.$diocese.'</p>';
  /* initialize student_record variable */
  $student_record = '';
  /* get all courses except for those where course status is "cancel" */
  $course_record_query = <<< END_QUERY
    SELECT c.course_id, c.code, c.description, t.ceu, uc.course_status_id,
      UNIX_TIMESTAMP(y.cycle_start) AS cycle_start,
      f.user_id, f.first_name, f.last_name
    FROM courses AS c, users_courses AS uc, users AS u, cycles AS y,
      course_subjects AS s, course_types AS t, users_courses AS fc, users AS f
    WHERE c.course_id = uc.course_id
      AND uc.user_id = u.user_id
      AND u.user_id = $student_id
      AND uc.user_role_id = 5
      AND c.cycle_id = y.cycle_id
      AND c.course_subject_id = s.course_subject_id
      AND s.course_type_id = t.course_type_id
      AND c.course_id = fc.course_id
      AND fc.user_role_id = 4
      AND fc.user_id = f.user_id
      AND uc.course_status_id IN (1, 2, 3, 5, 6, 7)
    GROUP BY c.course_id
    ORDER BY y.cycle_start DESC
END_QUERY;
  $result = mysql_query($course_record_query, $site_info['db_conn']);
  $i = 0;
  while ($record = mysql_fetch_array($result))
  {
    $course_status = $lang['database']['course-status'][$record['course_status_id']];
    $facilitator_name = $record['first_name'].' '.$record['last_name'];
    /* get cycle start date */
    $start_date = date('j|n|Y', $record['cycle_start']);
    $start_date_array = explode('|', $start_date);
    $start_date_array[1] = $lang['common']['months']['abbr'][$start_date_array[1]];
    array_unshift($start_date_array, $lang['common']['misc']['short-date-format']);
    $start_date = call_user_func_array('sprintf', $start_date_array);
    if ($i % 2 == 0) $student_record .= '<tr>';
    else $student_record .= '<tr bgcolor="#eeeeee">';
    $facilitator_link = vlc_internal_link($facilitator_name, 'courses/facilitator_details.php?facilitator='.$record['user_id']);
    $student_record .= '<td>'.$record['code'].'</td><td>'.$record['description'].'</td><td>'.$start_date.'</td><td>'.$record['ceu'].'</td><td>'.$course_status.'</td><td>'.$facilitator_link.'</td></tr>';
    $i++;
  }
  if (strlen($student_record))
  {
    print <<< END_TEXT
      <p>&nbsp;</p>
      <table border="0" cellpadding="2" cellspacing="0" width="100%">
      <tr bgcolor="#eeeeee"><th>{$lang['profile']['student-history']['misc']['course-code']}</th><th>{$lang['profile']['student-history']['misc']['course-name']}</th><th>{$lang['profile']['student-history']['misc']['course-date']}</th><th>{$lang['profile']['student-history']['misc']['ceu']}</th><th>{$lang['profile']['student-history']['misc']['course-status']}</th><th>{$lang['profile']['student-history']['misc']['facilitator']}</th></tr>
      $student_record
      </table>
END_TEXT;
  }
  else print '<p class="center">'.$lang['profile']['student-history']['content']['no-courses'].'</p>';
}
else print '<p>'.$lang['profile']['student-history']['status']['invalid-student'].'</p>';
?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
