<?php
$page_info['section'] = 'profile';
$page_info['page'] = 'cert-prog-details';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<div class="container mb-5">
  <h1><?php print $lang['profile']['cert-prog-details']['heading']['cert-prog-details'] ?></h1>
  <div class="return-link">
    <i class="fa fa-arrow-left"></i>
    <?php echo vlc_internal_link($lang['profile']['cert-prog-history']['heading']['cert-prog-history'], 'profile/cert_prog_history.php') ?>
  </div>
<?php
/* get cert user record id */
$cert_user_id = $_GET['id'];
/* initialize variables */
$cert_prog_details = $courses = $categories = $course_subjects = $course_subject_id_array = $student_course_history = array();
/* get cert prog details */
$cert_prog_details_query = <<< END_QUERY
  SELECT p.cert_prog_id, p.description, cu.cert_status_id, UNIX_TIMESTAMP(cu.CREATED) AS app_date
  FROM certs_users AS cu, cert_progs AS p
  WHERE cu.cert_prog_id = p.cert_prog_id
  AND cu.cert_user_id = $cert_user_id
  AND cu.user_id = {$user_info['user_id']}
END_QUERY;
$result = mysql_query($cert_prog_details_query, $site_info['db_conn']);
$cert_prog_details = mysql_fetch_array($result);
$cert_prog_status = $lang['database']['cert-status'][$cert_prog_details['cert_status_id']];
/* get application date */
$app_date = date('j|n|Y', $cert_prog_details['app_date']);
$app_date_array = explode('|', $app_date);
$app_date_array[1] = $lang['common']['months']['abbr'][$app_date_array[1]];
array_unshift($app_date_array, $lang['common']['misc']['short-date-format']);
$app_date = call_user_func_array('sprintf', $app_date_array);
/* get cert prog courses */
$cert_prog_courses_query = <<< END_QUERY
  SELECT IFNULL(r.cert_cat_id, 0) AS cert_cat_id, r.description AS category, s.course_subject_id, s.description AS course_subject
  FROM certs_courses AS cc LEFT JOIN cert_cats AS r ON cc.cert_cat_id = r.cert_cat_id, course_subjects AS s
  WHERE cc.course_subject_id = s.course_subject_id
  AND cc.cert_prog_id = {$cert_prog_details['cert_prog_id']}
  ORDER BY r.display_order, cc.display_order
END_QUERY;
$result = mysql_query($cert_prog_courses_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result))
{
  $course_subject_id_array[] = $record['course_subject_id'];
  $categories[$record['cert_cat_id']] = $record['category'];
  $course_subjects[$record['cert_cat_id']][$record['course_subject_id']] = $record['course_subject'];
}
if (count($categories) == 0) $categories[] = '';
if (count($course_subject_id_array))
{
  $course_subject_id_list = join(', ', $course_subject_id_array);
  /* get student course record */
  $student_course_query = <<< END_QUERY
    SELECT c.course_subject_id, c.course_id, y.cycle_start, uc.course_status_id, uc.is_scored, uc.score_level_id
    FROM users_courses AS uc, courses AS c, cycles AS y
    WHERE c.cycle_id = y.cycle_id
    AND c.course_id = uc.course_id
    AND c.course_subject_id IN ($course_subject_id_list)
    AND uc.course_status_id IN (2, 3, 6, 7)
    AND uc.user_id = {$user_info['user_id']}
    ORDER BY c.course_subject_id, uc.course_status_id
END_QUERY;
  $result = mysql_query($student_course_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) $student_course_history[$record['course_subject_id']] = $record;
  /* get student course requirements completed outside of vlcff */
  $cert_prog_req_query = <<< END_QUERY
    SELECT r.course_subject_id, DATE(r.CREATED) AS cycle_start, '7' AS course_status_id, '0' AS is_scored
    FROM cert_prog_reqs AS r
    WHERE r.course_subject_id IN ($course_subject_id_list)
    AND r.cert_user_id = $cert_user_id
    ORDER BY r.course_subject_id
END_QUERY;
  $result = mysql_query($cert_prog_req_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) $student_course_history[$record['course_subject_id']] = $record;
}
/* cert prog record */
$cert_prog_record = '<h2>'.$cert_prog_details['description'].'</h2>';
$cert_prog_record .= '<p><b>'.$lang['profile']['cert-prog-details']['misc']['app-date'].':</b> '.$app_date.'</p>';
$cert_prog_record .= '<p><b>'.$lang['profile']['cert-prog-details']['misc']['cert-prog-status'].':</b> '.$cert_prog_status.'</p>';
$cert_prog_record .= '<div class="alert alert-info">'.$lang['profile']['cert-prog-details']['content']['cert-prog-status-info'].'</div>';
/* course list */
foreach ($categories as $cert_cat_id => $category)
{
  if ($cert_cat_id > 0) $cert_prog_record .= '<h3>'.$category.'</h3>';
  $cert_prog_record .= '<ol>';
  foreach ($course_subjects[$cert_cat_id] as $course_subject_id => $course_subject)
  {
    if (isset($student_course_history[$course_subject_id]))
    {
      $student_details = $student_course_history[$course_subject_id];
      if ($student_details['is_scored'])
      {
        if (isset($student_details['score_level_id'])) $score = $lang['database']['score-levels'][$student_details['score_level_id']];
        else $score = $lang['profile']['cert-prog-details']['misc']['not-available'];
        $scoring = $lang['profile']['cert-prog-details']['misc']['score'].': '.$score;
      }
      else $scoring = $lang['profile']['cert-prog-details']['misc']['not-scored'];
      $course_details = '<b>'.$lang['database']['course-status'][$student_details['course_status_id']].' ('.$student_details['cycle_start'].') - '.$scoring.'</b>';
    }
    else $course_details = '<b>'.$lang['profile']['cert-prog-details']['misc']['not-registered'].'</b>';
    $cert_prog_record .= '<li>'.$course_subject.' - '.$course_details.'</li>';
  }
  $cert_prog_record .= '</ol>';
}
print $cert_prog_record;
?>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

