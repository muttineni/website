<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'evaluations';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* build array for "format" select box */
$format_options_array = array(1 => 'Grouped by Question (HTML)', 'Grouped by Student (HTML)', 'Export to Excel (CSV)', 'Printable Table (PDF - Portrait)', 'Printable Table (PDF - Landscape)');
$output = '';
if (isset($_GET['cycle']))
{
  $cycle_id = $_GET['cycle'];
  $where_clause = 'AND c.cycle_id = '.$cycle_id;
  $hidden_field = '<input type="hidden" name="cycle" value="'.$cycle_id.'">';
  $output .= '<h3 align="center">(Cycle ID: '.vlc_internal_link($cycle_id, 'cms/cycle_details.php?cycle='.$cycle_id).')</h3>';
}
if (isset($_GET['course']))
{
  $course_id = $_GET['course'];
  $where_clause = 'AND c.course_id = '.$course_id;
  $hidden_field = '<input type="hidden" name="course" value="'.$course_id.'">';
  $output .= '<h3 align="center">(Course ID: '.vlc_internal_link($course_id, 'cms/course_details.php?course='.$course_id).')</h3>';
}
if (isset($_GET['user']))
{
  $user_id = $_GET['user'];
  $where_clause = 'AND u.user_id = '.$user_id;
  $hidden_field = '<input type="hidden" name="user" value="'.$user_id.'">';
  $output .= '<h3 align="center">(User ID: '.vlc_internal_link($user_id, 'cms/user_details.php?user='.$user_id).')</h3>';
}
if (isset($_GET['format'])) $output_format = $_GET['format'];
if (isset($where_clause) and isset($output_format))
{
  $output .= '<form method="get" action="evaluations.php">';
  $output .= $hidden_field;
  $output .= '<p align="center">Select a Different Format: '.vlc_select_box($format_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  /* get course information */
  $course_query = <<< END_QUERY
    SELECT c.course_id, c.description AS course_name, f.user_id AS facilitator_id, f.first_name AS facilitator_first_name, f.last_name AS facilitator_last_name
    FROM users_courses AS u, courses AS c, users_courses AS fc, users AS f
    WHERE u.course_id = c.course_id
    AND c.course_id = fc.course_id
    AND fc.user_id = f.user_id
    AND fc.user_role_id = 4
    $where_clause
    GROUP BY c.course_id
END_QUERY;
  $result = mysql_query($course_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result))
  {
    $courses[$record['course_id']]['course_details'] = $record;
    $courses[$record['course_id']]['facilitators'][] = $record['facilitator_first_name'].' '.$record['facilitator_last_name'].' ('.vlc_internal_link($record['facilitator_id'], 'cms/user_details.php?user='.$record['facilitator_id']).')';
  }
  /* questions */
  $evaluation_questions[1] = 'What is your general attitude of on-line courses after completing this course?';
  $evaluation_questions[] = 'What were the advantages to an on-line course especially in relationship to other courses you have taken?';
  $evaluation_questions[] = 'What was your significant learning experience with the on-line course?';
  $evaluation_questions[] = 'What were your biggest challenges during the on-line course?';
  $evaluation_questions[] = 'Were you able to overcome these? If yes, how. If no, why.';
  $evaluation_questions[] = 'What recommendations would you make for either facilitators or participants for the on-line course?';
  $evaluation_questions[] = 'How does the content of this course measure up to other adult religious formation courses you have taken?';
  $evaluation_questions[] = 'How did you feel about the process for the on-line course?';
  $evaluation_questions[] = 'What modifications would you like to see within the on-line course you participated in?';
  $evaluation_questions[] = 'Other comments:';
  switch ($output_format)
  {
    /***************************************************************************
    ** get evaluations - grouped by question
    */
    case 1:
      $evaluation_query = <<< END_QUERY
        SELECT c.course_id, q.question_id, q.display_order AS question_number, a.answer, u.user_id AS student_id, u.first_name AS student_first_name, u.last_name AS student_last_name
        FROM courses AS c, course_subjects AS s, resources AS r, questions AS q, responses AS a, users AS u
        WHERE c.course_subject_id = s.course_subject_id
          AND s.course_subject_id = r.course_subject_id
          AND r.resource_id = q.test_id
          AND q.question_id = a.question_id
          AND a.user_id = u.user_id
          AND r.resource_type_id = 44
          AND c.course_id = a.course_id
          $where_clause
        ORDER BY c.description, c.course_id, q.display_order, u.last_name, u.first_name
END_QUERY;
      $result = mysql_query($evaluation_query, $site_info['db_conn']);
      if (mysql_num_rows($result))
      {
        $course_list_open = $question_list_open = $prev_course_id = $prev_question_id = 0;
        while ($record = mysql_fetch_array($result))
        {
          $curr_course_id = $record['course_id'];
          if ($curr_course_id != $prev_course_id)
          {
            if ($question_list_open)
            {
              $output .= '</ul></li>';
              $question_list_open = 0;
            }
            if ($course_list_open) $output .= '</ol>';
            $output .= '<h3>'.$courses[$curr_course_id]['course_details']['course_name'].' ('.vlc_internal_link($curr_course_id, 'cms/course_details.php?course='.$curr_course_id).')<br>'.join('<br>', $courses[$curr_course_id]['facilitators']).'</h3>';
            $output .= '<ol>';
            $course_list_open = 1;
            $prev_question_id = 0;
          }
          $curr_question_id = $record['question_id'];
          if ($curr_question_id != $prev_question_id)
          {
            if ($question_list_open) $output .= '</ul></li>';
            $output .= '<li><b>'.$evaluation_questions[$record['question_number']].'</b><ul>';
            $question_list_open = 1;
          }
          $output .= '<li><b>'.$record['student_first_name'].' '.$record['student_last_name'].' ('.vlc_internal_link($record['student_id'], 'cms/user_details.php?user='.$record['student_id']).'):</b> '.$record['answer'].'</li>';
          $prev_course_id = $curr_course_id;
          $prev_question_id = $curr_question_id;
        }
      }
      else $output .= '<p>No Evaluations Found.</p>';
      break;
    /***************************************************************************
    ** get evaluations - grouped by student
    */
    case 2:
      $output .= '<p><b>Evaluation Questions:</b></p><ol>';
      foreach ($evaluation_questions as $question)
      {
        $output .= '<li>'.$question.'</li>';
      }
      $output .= '</ol>';
      $evaluation_query = <<< END_QUERY
        SELECT c.course_id, q.display_order AS question_number, a.answer, u.user_id AS student_id, u.first_name AS student_first_name, u.last_name AS student_last_name
        FROM courses AS c, course_subjects AS s, resources AS r, questions AS q, responses AS a, users AS u
        WHERE c.course_subject_id = s.course_subject_id
          AND s.course_subject_id = r.course_subject_id
          AND r.resource_id = q.test_id
          AND q.question_id = a.question_id
          AND a.user_id = u.user_id
          AND r.resource_type_id = 44
          AND c.course_id = a.course_id
          $where_clause
        ORDER BY c.description, c.course_id, u.last_name, u.first_name, q.display_order
END_QUERY;
      $result = mysql_query($evaluation_query, $site_info['db_conn']);
      if (mysql_num_rows($result))
      {
        $course_list_open = $student_list_open = $prev_course_id = $prev_student_id = 0;
        while ($record = mysql_fetch_array($result))
        {
          $curr_course_id = $record['course_id'];
          if ($curr_course_id != $prev_course_id)
          {
            if ($student_list_open)
            {
              $output .= '</ol></li>';
              $student_list_open = 0;
            }
            if ($course_list_open) $output .= '</ul>';
            $output .= '<h3>'.$courses[$curr_course_id]['course_details']['course_name'].' ('.vlc_internal_link($curr_course_id, 'cms/course_details.php?course='.$curr_course_id).')<br>'.join('<br>', $courses[$curr_course_id]['facilitators']).'</h3>';
            $output .= '<ul>';
            $course_list_open = 1;
            $prev_student_id = 0;
          }
          $curr_student_id = $record['student_id'];
          if ($curr_student_id != $prev_student_id)
          {
            if ($student_list_open) $output .= '</ol></li>';
            $output .= '<li><b>'.$record['student_first_name'].' '.$record['student_last_name'].'</b> ('.vlc_internal_link($record['student_id'], 'cms/user_details.php?user='.$record['student_id']).')<ol>';
            $student_list_open = 1;
          }
          $output .= '<li>'.$record['answer'].'</li>';
          $prev_course_id = $curr_course_id;
          $prev_student_id = $curr_student_id;
        }
      }
      else $output .= '<p>No Evaluations Found.</p>';
      break;
    /***************************************************************************
    ** get evaluations - csv format
    */
    case 3:
    case 4:
    case 5:
      $evaluation_query = <<< END_QUERY
        SELECT c.course_id, q.display_order AS question_number, a.answer, u.user_id AS student_id, u.first_name AS student_first_name, u.last_name AS student_last_name
        FROM courses AS c, course_subjects AS s, resources AS r, questions AS q, responses AS a, users AS u
        WHERE c.course_subject_id = s.course_subject_id
          AND s.course_subject_id = r.course_subject_id
          AND r.resource_id = q.test_id
          AND q.question_id = a.question_id
          AND a.user_id = u.user_id
          AND r.resource_type_id = 44
          AND c.course_id = a.course_id
          $where_clause
        ORDER BY c.course_id, u.user_id, q.question_id
END_QUERY;
      $result = mysql_query($evaluation_query, $site_info['db_conn']);
      $evaluation_array = array();
      $evaluation_array[] = array('Course ID', 'Course', 'Facilitator ID', 'Facilitator First Name', 'Facilitator Last Name', 'Student ID', 'Student First Name', 'Student Last Name', 'Question Number', 'Question', 'Answer');
      while ($record = mysql_fetch_array($result))
      {
        $record = array_merge($courses[$record['course_id']]['course_details'], $record);
        $record['question'] = $evaluation_questions[$record['question_number']];
        $record['answer'] = str_replace(array('"', "\n", "\r"), array('""', ' ', ' '), $record['answer']);
        $evaluation_array[] = array($record['course_id'], $record['course_name'], $record['facilitator_id'], $record['facilitator_first_name'], $record['facilitator_last_name'], $record['student_id'], $record['student_first_name'], $record['student_last_name'], $record['question_number'], $record['question'], $record['answer']);
      }
      switch ($output_format)
      {
        case 3:
          vlc_export_data($evaluation_array, 'evaluations', 1);
          break;
        case 4:
          vlc_export_data($evaluation_array, 'evaluations', 2, 'P');
          break;
        case 5:
          vlc_export_data($evaluation_array, 'evaluations', 2, 'L');
          break;
      }
      break;
    default:
      $output .= '<p>Invalid Format.</p>';
  }
}
else
{
  /* build array for "cycle" select box */
  $cycle_options_array = array();
  $cycle_options_query = <<< END_QUERY
    SELECT cycle_id, code, UNIX_TIMESTAMP(cycle_start) AS cycle_start_timestamp
    FROM cycles
    ORDER BY YEAR(cycle_start) DESC, cycle_start
END_QUERY;
  $result = mysql_query($cycle_options_query, $site_info['db_conn']);
  $previous_year = 0;
  while ($record = mysql_fetch_array($result))
  {
    $current_year = date('Y', $record['cycle_start_timestamp']);
    if ($current_year != $previous_year)
    {
      $previous_year = $current_year;
      $cycle_options_array[$current_year]['label'] = $current_year;
    }
    $cycle_options_array[$current_year]['options'][$record['cycle_id']] = $record['code'].' ('.date('M. Y', $record['cycle_start_timestamp']).')';
  }
  /* build array for "course" select box */
  $course_options_array = array();
  $course_options_query = <<< END_QUERY
    SELECT c.course_id, c.code AS course_code, c.description, y.cycle_id, y.code AS cycle_code, UNIX_TIMESTAMP(cycle_start) AS cycle_start_timestamp
    FROM courses AS c, cycles AS y
    WHERE c.cycle_id = y.cycle_id
    ORDER BY y.cycle_start DESC, c.code
END_QUERY;
  $result = mysql_query($course_options_query, $site_info['db_conn']);
  $previous_cycle = 0;
  while ($record = mysql_fetch_array($result))
  {
    $current_cycle = $record['cycle_id'];
    if ($current_cycle != $previous_cycle)
    {
      $previous_cycle = $current_cycle;
      $course_options_array[$current_cycle]['label'] = $record['cycle_code'].' ('.date('M. Y', $record['cycle_start_timestamp']).')';
    }
    $course_options_array[$current_cycle]['options'][$record['course_id']] = $record['course_code'].' - '.$record['description'];
  }
  $output .= '<form method="get" action="evaluations.php">';
  $output .= '<p>Select a Cycle: '.vlc_select_box($cycle_options_array, 'array', 'cycle', -1, true).' '.vlc_select_box($format_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  $output .= '<form method="get" action="evaluations.php">';
  $output .= '<p>Select a Course: '.vlc_select_box($course_options_array, 'array', 'course', -1, true).' '.vlc_select_box($format_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
}
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
