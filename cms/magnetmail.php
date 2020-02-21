<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'magnetmail';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* build array for "format" select box */
$format_options_array = array(1 => 'Printable Table (HTML)', 'Export to Excel (CSV)', 'Printable Table (PDF - Portrait)', 'Printable Table (PDF - Landscape)');
$output = $where_clause = $table_string = $hidden_field = '';
if (isset($_GET['set']))
{
  switch ($_GET['set'])
  {
    case 1:
      $where_clause .= ' AND uc.user_role_id = 5';
      $output .= '<h2 align="center">Students</h2>';
      break;
    case 2:
      $table_string .= ', orders AS o';
      $where_clause .= ' AND uc.user_role_id = 5 AND uc.user_course_id = o.product_id AND o.product_type_id = 1 AND o.customer_type_id = 1 AND o.payment_status_id = 2';
      $output .= '<h2 align="center">Unpaid Student Orders</h2>';
      break;
    case 3:
      $where_clause .= ' AND uc.user_role_id = 4';
      $output .= '<h2 align="center">Facilitators</h2>';
      break;
    default:
      $output .= '<h2 align="center">Students and Facilitators</h2>';
  }
  $hidden_field .= '<input type="hidden" name="set" value="'.$_GET['set'].'">';
}
if (isset($_GET['cycle']))
{
  $cycle_id = $_GET['cycle'];
  $where_clause .= ' AND c.cycle_id = '.$cycle_id;
  $hidden_field .= '<input type="hidden" name="cycle" value="'.$cycle_id.'">';
  $output .= '<h3 align="center">(Cycle ID: '.vlc_internal_link($cycle_id, 'cms/cycle_details.php?cycle='.$cycle_id).')</h3>';
}
if (isset($_GET['course']))
{
  $course_id = $_GET['course'];
  $where_clause .= ' AND c.course_id = '.$course_id;
  $hidden_field .= '<input type="hidden" name="course" value="'.$course_id.'">';
  $output .= '<h3 align="center">(Course ID: '.vlc_internal_link($course_id, 'cms/course_details.php?course='.$course_id).')</h3>';
}
if (isset($_GET['format'])) $output_format = $_GET['format'];
if (strlen($where_clause) and isset($output_format))
{
  $output .= '<form method="get" action="magnetmail.php">';
  $output .= $hidden_field;
  $output .= '<p align="center">Select a Different Format: '.vlc_select_box($format_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  /* get certificate data */
  $magnetmail_query = <<< END_QUERY
    SELECT c.code, c.description, u.first_name, u.last_name, i.primary_email
    FROM users_courses AS uc, users AS u, user_info AS i, courses AS c$table_string
    WHERE uc.user_id = u.user_id
    AND u.user_id = i.user_id
    AND uc.course_id = c.course_id
    AND c.is_active = 1
    AND uc.course_status_id IN (2, 3, 7)
    $where_clause
    ORDER BY c.code, u.last_name, u.first_name
END_QUERY;
  $result = mysql_query($magnetmail_query, $site_info['db_conn']);
  $field_name_array = array('Course Code', 'Course Description', 'First Name', 'Last Name', 'E-Mail');
  switch ($output_format)
  {
    /***************************************************************************
    ** printable table (html)
    */
    case 1:
      $num_rows = mysql_num_rows($result);
      $output .= '<p align="center">'.$num_rows.' record(s) found.</p>';
      $output .= '<table border="1" cellpadding="5" cellspacing="0">';
      $output .= '<tr><th>'.join('</th><th>', $field_name_array).'</th></tr>';
      while ($record = mysql_fetch_row($result))
      {
        $record[2] = ucwords($record[2]);
        $record[3] = ucwords($record[3]);
        $record[4] = strtolower($record[4]);
        foreach ($record as $key => $value)
        {
          if (!isset($record[$key]) or strlen(trim($record[$key])) == 0)
          {
            $record[$key] = '&nbsp;';
          }
        }
        $output .= '<tr><td>'.join('</td><td>', $record).'</td></tr>';
      }
      $output .= '</table>';
      break;
    /***************************************************************************
    ** csv export
    */
    case 2:
    case 3:
    case 4:
      $certificate_array = array();
      $certificate_array[] = $field_name_array;
      while ($record = mysql_fetch_row($result))
      {
        $record[2] = ucwords($record[2]);
        $record[3] = ucwords($record[3]);
        $record[4] = strtolower($record[4]);
        $certificate_array[] = $record;
      }
      switch ($output_format)
      {
        case 2:
          vlc_export_data($certificate_array, 'magnetmail', 1);
          break;
        case 3:
          vlc_export_data($certificate_array, 'magnetmail', 2, 'P');
          break;
        case 4:
          vlc_export_data($certificate_array, 'magnetmail', 2, 'L');
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
  /* build array for "data set" select box */
  $data_set_options_array = array(1 => 'Students', 2 => 'Unpaid Student Orders', 3 => 'Facilitators', 4 => 'Students and Facilitators');
  $output .= '<form method="get" action="magnetmail.php">';
  $output .= '<h3>Cycle</h3><ul><li><b>Cycle:</b> '.vlc_select_box($cycle_options_array, 'array', 'cycle', -1, true).'</li><li><b>Data Set:</b> '.vlc_select_box($data_set_options_array, 'array', 'set', -1, true).'</li><li><b>Format:</b> '.vlc_select_box($format_options_array, 'array', 'format', -1, true).'</li></ul><p><input type="submit" value="Go"></p>';
  $output .= '</form>';
  $output .= '<form method="get" action="magnetmail.php">';
  $output .= '<h3>Course</h3><ul><li><b>Course:</b> '.vlc_select_box($course_options_array, 'array', 'course', -1, true).'</li><li><b>Data Set:</b> '.vlc_select_box($data_set_options_array, 'array', 'set', -1, true).'</li><li><b>Format:</b> '.vlc_select_box($format_options_array, 'array', 'format', -1, true).'</li></ul><p><input type="submit" value="Go"></p>';
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
