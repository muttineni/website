<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'checklist';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* build array for "format" select box */
$format_options_array = array(1 => 'Printable Table (HTML)', 'Export to Excel (CSV)', 'Printable Table (PDF - Portrait)', 'Printable Table (PDF - Landscape)');
/* build array for "query" select box */
$query_options_array = array(1 => 'Facilitators', 'Courses', 'Students');
$output = '';
if (isset($_GET['cycle']))
{
  $cycle_id = $_GET['cycle'];
  $where_clause = 'AND c.cycle_id = '.$cycle_id;
  $hidden_field = '<input type="hidden" name="cycle" value="'.$cycle_id.'">';
  $output .= '<h3 align="center">(Cycle ID: '.vlc_internal_link($cycle_id, 'cms/cycle_details.php?cycle='.$cycle_id).')</h3>';
}
elseif (isset($_GET['course']))
{
  $course_id = $_GET['course'];
  $where_clause = 'AND c.course_id = '.$course_id;
  $hidden_field = '<input type="hidden" name="course" value="'.$course_id.'">';
  $output .= '<h3 align="center">(Course ID: '.vlc_internal_link($course_id, 'cms/course_details.php?course='.$course_id).')</h3>';
}
if (isset($_GET['format'])) $output_format = $_GET['format'];
if (isset($_GET['query']))
{
  $query = $_GET['query'];
  $hidden_field .= '<input type="hidden" name="query" value="'.$query.'">';
}
if (isset($where_clause) and isset($output_format) and isset($query))
{
  $output .= '<form method="get" action="checklist.php">';
  $output .= $hidden_field;
  $output .= '<p align="center">Select a Different Format: '.vlc_select_box($format_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  /* get checklist data */
  switch ($query)
  {
    /***************************************************************************
    ** facilitators
    */
    case 1:
      $facilitator_query = <<< END_QUERY
        SELECT c.course_id, c.description, u.first_name, u.last_name, i.primary_email
        FROM courses AS c
          JOIN users_courses AS uc ON c.course_id = uc.course_id
          JOIN users AS u ON uc.user_id = u.user_id
          JOIN user_info AS i ON u.user_id = i.user_id
        WHERE uc.user_role_id = 4
        $where_clause
        GROUP BY c.course_id
        ORDER BY c.course_id
END_QUERY;
      $result = mysql_query($facilitator_query, $site_info['db_conn']);
      $keyword = 'facilitators';
      break;
    /***************************************************************************
    ** courses
    */
    case 2:
      $course_query = <<< END_QUERY
        SELECT c.course_id, c.description, s.session_id, s.display_order, s.description, r.resource_id, r.resource_type_id, r.display_order, r.title,
          CONCAT(IF(r.resource_type_id = 4, 'DB', 'Ex'), ': ', LEFT(r.title, 12), '...') AS short_title
        FROM courses AS c
          JOIN course_subjects AS j ON c.course_subject_id = j.course_subject_id
          JOIN sessions AS s ON j.course_subject_id = s.course_subject_id
          JOIN resources AS r ON j.course_subject_id = r.course_subject_id
            AND s.session_id = r.session_id
        WHERE r.resource_type_id IN (4,26)
        $where_clause
        ORDER BY j.course_subject_id, c.course_id, s.display_order, r.display_order
END_QUERY;
      $result = mysql_query($course_query, $site_info['db_conn']);
      $keyword = 'courses';
      break;
    /***************************************************************************
    ** students
    */
    case 3:
      $student_query = <<< END_QUERY
        SELECT uc.course_id, u.first_name, u.last_name, i.primary_email,
          CASE IFNULL(uc.is_scored, 0) WHEN 0 THEN 'N' ELSE 'Y' END AS is_scored,
          r.registration_type_id, r.description,
          CASE MAX(IFNULL(cu.cert_user_id, 0)) WHEN 0 THEN 'N' ELSE 'Y' END AS cert_status
        FROM users AS u
          JOIN user_info AS i ON u.user_id = i.user_id
          JOIN users_courses AS uc ON i.user_id = uc.user_id
          JOIN courses AS c ON uc.course_id = c.course_id
          JOIN course_subjects AS s ON c.course_subject_id = s.course_subject_id
          JOIN registration_types AS r ON uc.registration_type_id = r.registration_type_id
          LEFT JOIN certs_courses AS cc ON s.course_subject_id = cc.course_subject_id
          LEFT JOIN certs_users AS cu ON cc.cert_prog_id = cu.cert_prog_id
            AND u.user_id = cu.user_id
            AND cu.cert_status_id != 4
        WHERE uc.user_role_id = 5
        AND uc.course_status_id IN (2,3)
        $where_clause
        GROUP BY uc.user_id
        ORDER BY uc.course_id, u.last_name, u.first_name
END_QUERY;
      $result = mysql_query($student_query, $site_info['db_conn']);
      $keyword = 'students';
      break;
    default:
      $output .= '<p>Invalid Query.</p>';
  }
  $num_fields = mysql_num_fields($result);
  $field_name_array = array();
  for ($i = 0; $i < $num_fields; $i++)
  {
    $field_name_array[] = str_replace('_', ' ', mysql_field_table($result, $i).' . '.mysql_field_name($result, $i));
  }
  switch ($output_format)
  {
    /***************************************************************************
    ** printable table (html)
    */
    case 1:
      $output .= '<table border="1" cellpadding="5" cellspacing="0">';
      $output .= '<tr><th>'.join('</th><th>', $field_name_array).'</th></tr>';
      while ($record = mysql_fetch_row($result))
      {
        foreach ($record as $key => $value)
        {
          if (!isset($record[$key]))
          {
            $record[$key] .= '[NULL]';
          }
          elseif (strlen(trim($record[$key])) == 0)
          {
            $record[$key] .= '&nbsp;';
          }
        }
        $output .= '<tr><td><pre>'.join('</pre></td><td><pre>', $record).'</pre></td></tr>';
      }
      $output .= '</table>';
      break;
    /***************************************************************************
    ** csv/pdf export
    */
    case 2:
    case 3:
    case 4:
      $output_array = array();
      $output_array[] = $field_name_array;
      while ($record = mysql_fetch_row($result)) $output_array[] = $record;
      switch ($output_format)
      {
        case 2:
          vlc_export_data($output_array, $keyword.'-checklist-data', 1);
          break;
        case 3:
          vlc_export_data($output_array, $keyword.'-checklist-data', 2, 'P');
          break;
        case 4:
          vlc_export_data($output_array, $keyword.'-checklist-data', 2, 'L');
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
  $output .= '<form method="get" action="checklist.php">';
  $output .= '<p>Select a Cycle: '.vlc_select_box($cycle_options_array, 'array', 'cycle', -1, true).' '.vlc_select_box($query_options_array, 'array', 'query', -1, true).' '.vlc_select_box($format_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  $output .= '<form method="get" action="checklist.php">';
  $output .= '<p>Select a Course: '.vlc_select_box($course_options_array, 'array', 'course', -1, true).' '.vlc_select_box($query_options_array, 'array', 'query', -1, true).' '.vlc_select_box($format_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
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
