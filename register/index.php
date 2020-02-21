<?php
$page_info['section'] = 'register';
$page_info['page'] = 'index';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
if ($user_info['logged_in'] == true)
{
  /* if the user is a student, list courses for registration */
  if (in_array(5, $user_info['user_roles']) or in_array(7, $user_info['user_roles']))
  {
    /* get course details */
    $course_query = <<< END_QUERY
      SELECT c.course_id, c.course_subject_id, c.code, c.description, c.registration_type_id,
        ct.duration, ct.ceu, ct.credit, UNIX_TIMESTAMP(y.cycle_start) AS cycle_start,
        f.first_name, f.last_name, f.user_id, 0 AS num_students
      FROM courses AS c, cycles AS y, course_subjects AS cs, course_types AS ct,
        users_courses AS fc, users AS f
      WHERE c.cycle_id = y.cycle_id
      AND CURDATE() >= y.registration_start
      AND CURDATE() <= y.registration_end
      AND c.course_subject_id = cs.course_subject_id
      AND cs.course_type_id = ct.course_type_id
      AND cs.language_id = {$lang['common']['misc']['current-language-id']}
      AND c.course_id = fc.course_id
      AND fc.user_id = f.user_id
      AND fc.user_role_id = 4
      AND c.is_restricted = 0
      AND c.is_sample = 0
      AND c.is_active = 1
      ORDER BY c.description
END_QUERY;
    $result = mysql_query($course_query, $site_info['db_conn']);
    if (mysql_num_rows($result))
    {
      $page_content = '';
      /* get course details */
      while ($record = mysql_fetch_array($result)) $course_array[$record['course_id']] = $record;
      /* get course id list */
      $course_id_array = array_keys($course_array);
      $course_id_list = implode(', ', $course_id_array);
      /* get cycle start date */
      $start_date = date('w|n|j|Y', $course_array[$course_id_array[0]]['cycle_start']);
      $start_date_array = explode('|', $start_date);
      $start_date_array[0] = $lang['common']['days']['full'][$start_date_array[0]+1];
      $start_date_array[1] = $lang['common']['months']['full'][$start_date_array[1]];
      array_unshift($start_date_array, $lang['common']['misc']['long-date-format']);
      $start_date = call_user_func_array('sprintf', $start_date_array);
      /* get the number of students registered for each course whose course status is "next cycle" */
      $num_students_query = <<< END_QUERY
        SELECT course_id, COUNT(*) AS num_students
        FROM users_courses
        WHERE course_id IN ($course_id_list)
        AND user_role_id = 5
        AND course_status_id = 2
        GROUP BY course_id
END_QUERY;
      $result = mysql_query($num_students_query, $site_info['db_conn']);
      while ($record = mysql_fetch_array($result)) $course_array[$record['course_id']]['num_students'] = $record['num_students'];
      /* see which courses and how many courses the user has registered for as a student in the current registration period (not including waiting list records) */
      $student_course_query = <<< END_QUERY
        SELECT uc.course_id, uc.course_status_id
        FROM users_courses AS uc, courses AS c, cycles AS y
        WHERE uc.course_id = c.course_id
        AND c.cycle_id = y.cycle_id
        AND CURDATE() >= y.registration_start
        AND CURDATE() <= y.registration_end
        AND uc.user_id = {$user_info['user_id']}
        AND uc.user_role_id = 5
        AND uc.course_status_id IN (1, 2)
END_QUERY;
      $result = mysql_query($student_course_query, $site_info['db_conn']);
      $num_courses = mysql_num_rows($result);
      $student_course_array = array();
      while ($record = mysql_fetch_array($result)) $student_course_array[] = $record['course_id'];
      if ($num_courses >= 2) $page_content .= '<div class="container"><p class="container"><b>'.$lang['register']['status']['course-limit'].'</b></p></div>';
      $page_content .= '<div class="alert alert-info">'.sprintf($lang['register']['index']['intro'], $start_date).'</div>';
      $page_content .= '<table class="table table-striped" width="100%">';
      $page_content .= '<thead><tr><th scope="col">'.$lang['register']['index']['misc']['course-code'].'</th><th scope="col">'.$lang['register']['index']['misc']['course-name'].'</th><th scope="col">'.$lang['register']['index']['misc']['ceu'].'/'.$lang['register']['index']['misc']['credit'].'</th><th scope="col">'.$lang['register']['index']['misc']['course-length'].'</th><th scope="col">'.$lang['register']['index']['misc']['facilitator'].'</th><th scope="col">'.$lang['register']['index']['misc']['register-label'].'</th></tr></thead>';
      $i = 0;
      foreach ($course_array as $course)
      {
        $course_id = $course['course_id'];
        $course_subject_id = $course['course_subject_id'];
        $course_code = $course['code'];
        $course_name = $course['description'];
        $course_duration = $course['duration'];
        if ($course['registration_type_id'] == 1)
        {
          $credit_string = $course['ceu'].' '.$lang['register']['index']['misc']['ceu'];
          $credit_note = '';
        }
        else
        {
          $credit_string = '<b>'.$course['credit'].' '.$lang['register']['index']['misc']['credit'].'</b>';
          $credit_note = ' <b>'.$lang['register']['common']['misc']['undergraduate-credit'].'</b>';
          $course_code = '<b>'.$course_code.'</b>';
        }
        $facilitator_name = $course['first_name'].' '.$course['last_name'];
        $facilitator_id = $course['user_id'];
        $num_students = $course['num_students'];
        $page_content .= '<tr>';
        /* if the student is already registered for the course or if the student is already registered for 2 courses, show a disabled register button */
        if (in_array($course_id, $student_course_array) or $num_courses >= 2)
        {
          $register_form = '<form method="post" action="register_action.php" onsubmit="return false;">';
          $register_form .= '<input type="submit" name="register" value="'.$lang['register']['index']['form-fields']['register-button'].'" class="submit-button-disabled btn btn-default" disabled>';
          $register_form .= '</form>';
        }
        /* if the course is full, show a waiting list button */
        elseif ($num_students >= 15)
        {
          $register_form = '<form method="post" action="register_action.php">';
          $register_form .= '<input type="hidden" name="action_id" value="2">';
          $register_form .= '<input type="hidden" name="course_id" value="'.$course_id.'">';
          $register_form .= '<input type="hidden" name="registration_type_id" value="'.$course['registration_type_id'].'">';
          $register_form .= '<input type="submit" name="register" value="'.$lang['register']['index']['form-fields']['waiting-list-button'].'" class="submit-button btn btn-warning">';
          $register_form .= '</form>';
        }
        /* otherwise, show a register button */
        else
        {
          $register_form = '<form method="post" action="register_action.php">';
          $register_form .= '<input type="hidden" name="action_id" value="1">';
          $register_form .= '<input type="hidden" name="course_id" value="'.$course_id.'">';
          $register_form .= '<input type="hidden" name="registration_type_id" value="'.$course['registration_type_id'].'">';
          $register_form .= '<input type="submit" name="register" value="'.$lang['register']['index']['form-fields']['register-button'].'" class="submit-button btn btn-vlc">';
          $register_form .= '</form>';
        }
        $page_content .= '<td>'.$course_code.'</td>';
        $page_content .= '<td><a href="course.php?course='.$course_subject_id.'" target="_blank" onclick="window.open(\'course.php?course='.$course_subject_id.'\', \'course\', \'width=640,height=480,top=0,left=0,status=1,scrollbars=1,resizable=1,toolbar=0,location=0,directories=0,menubar=0\'); return false;">'.$course_name.'</a>'.$credit_note.'</td>';
        $page_content .= '<td><nobr>'.$credit_string.'</nobr></td>';
        $page_content .= '<td>'.$course_duration.' '.$lang['register']['index']['misc']['weeks'].'</td>';
        $page_content .= '<td><a href="facilitator.php?facilitator='.$facilitator_id.'" target="_blank" onclick="window.open(\'facilitator.php?facilitator='.$facilitator_id.'\', \'facilitator\', \'width=640,height=480,top=0,left=0,status=1,scrollbars=1,resizable=1,toolbar=0,location=0,directories=0,menubar=0\'); return false;">'.$facilitator_name.'</a></td>';
        $page_content .= '<td align="center">'.$register_form.'</td>';
        $page_content .= '</tr>';
        $i++;
      }
      $page_content .= '</table>';
    }
    else $page_content = '<div>'.$lang['register']['index']['content']['registration-closed'].'</div>';
  }
  /* if the user is not a student, display error message */
  else $page_content =  '<div>'.$lang['register']['index']['content']['students-only'].'</div>';
}
/* else the user is not logged in */
else $page_content =  '<div>'.$lang['register']['index']['content']['not-logged-in'].'</div>';
print $header;
?>
<!-- begin page content -->
  <div class="container">
    <h1><?php echo $lang['register']['index']['page-title'] ?></h1>
    <?php print $page_content ?>
  </div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
