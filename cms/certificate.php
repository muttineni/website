<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'certificate';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* build array for "format" select box */
$format_options_array = array(1 => 'Printable Table (HTML)', 'Export to Excel (CSV)', 'Printable Table (PDF - Portrait)', 'Printable Table (PDF - Landscape)');
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
if (isset($where_clause) and isset($output_format))
{
  $output .= '<form method="get" action="certificate.php">';
  $output .= $hidden_field;
  $output .= '<p align="center">Select a Different Format: '.vlc_select_box($format_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  /* get certificate data */
  $certificate_query = <<< END_QUERY
    SELECT IFNULL(d.is_partner, '') AS diocese_status, IFNULL(d.description, '') AS diocese_description,
      IFNULL(p.is_partner, '') AS special_partner_status, IFNULL(p.description, '') AS special_partner_description,
      j.course_subject_id, j.description, c.code, y.cycle_start, FORMAT(t.ceu, 1) AS ceu,
      IFNULL(u.first_name, '') AS first_name, IFNULL(u.middle_name, '') AS middle_name, IFNULL(u.last_name, '') AS last_name,
      CONCAT(IFNULL(i.address_1, ''), IF(IFNULL(i.address_2, '') = '', '', CONCAT(', ', i.address_2))) AS address,
      i.city, s.code, i.zip, n.description, o.payment_status_id
    FROM users_courses AS uc, users AS u, courses AS c,
      cycles AS y, course_subjects AS j, course_types AS t, countries AS n,
      user_info AS i
        LEFT JOIN states AS s ON i.state_id = s.state_id
        LEFT JOIN partners AS d ON i.diocese_id = d.partner_id
        LEFT JOIN partners AS p ON i.partner_id = p.partner_id,
      orders AS o
    WHERE uc.user_id = u.user_id
    AND u.user_id = i.user_id
    AND i.country_id = n.country_id
    AND uc.course_id = c.course_id
    AND c.cycle_id = y.cycle_id
    AND c.course_subject_id = j.course_subject_id
    AND j.course_type_id = t.course_type_id
    AND uc.user_course_id = o.product_id
    AND o.product_type_id = 1
    AND o.customer_type_id = 1
    $where_clause
    AND c.is_active = 1
    AND uc.user_role_id = 5
    AND uc.course_status_id = 7
    AND (uc.score_level_id IS NULL OR uc.score_level_id > 1)
    ORDER BY d.is_partner DESC, d.description, p.is_partner DESC, p.description, j.description, u.last_name, u.first_name
END_QUERY;
  $result = mysql_query($certificate_query, $site_info['db_conn']);
  $field_name_array = array('Diocese Status', 'Diocese', 'Special Partner Status', 'Special Partner', 'Course Subject ID', 'Course Name', 'Course Code', 'Course Date', 'CEU', 'First Name', 'Middle Name', 'Last Name', 'Address', 'City', 'State', 'Zip', 'Country', 'Payment Status');
  $partner_status_array = array('Non-Partner', 'Partner');
  $payment_status_array = array(1 => 'No Charge', 'Not Paid', 'Partial Payment', 'Paid in Full', 'Over Payment');
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
        $record[9] = ucwords($record[9]);
        $record[10] = ucwords($record[10]);
        $record[11] = ucwords($record[11]);
        $record[12] = ucwords($record[12]);
        $record[13] = ucwords($record[13]);
        if (strlen($record[1])) $record[0] = $partner_status_array[$record[0]];
        else $record[0] = '';
        if (strlen($record[3])) $record[2] = $partner_status_array[$record[2]];
        else $record[2] = '';
        $record[17] = $payment_status_array[$record[17]];
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
    ** csv/pdf export
    */
    case 2:
    case 3:
    case 4:
      $certificate_array = array();
      $certificate_array[] = $field_name_array;
      while ($record = mysql_fetch_row($result))
      {
        $record[9] = ucwords($record[9]);
        $record[10] = ucwords($record[10]);
        $record[11] = ucwords($record[11]);
        $record[12] = ucwords($record[12]);
        $record[13] = ucwords($record[13]);
        if (strlen($record[1])) $record[0] = $partner_status_array[$record[0]];
        else $record[0] = '';
        if (strlen($record[3])) $record[2] = $partner_status_array[$record[2]];
        else $record[2] = '';
        $record[17] = $payment_status_array[$record[17]];
        $certificate_array[] = $record;
      }
      switch ($output_format)
      {
        case 2:
          vlc_export_data($certificate_array, 'certificate', 1);
          break;
        case 3:
          vlc_export_data($certificate_array, 'certificate', 2, 'P');
          break;
        case 4:
          vlc_export_data($certificate_array, 'certificate', 2, 'L');
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
  $output .= '<form method="get" action="certificate.php">';
  $output .= '<p>Select a Cycle: '.vlc_select_box($cycle_options_array, 'array', 'cycle', -1, true).' '.vlc_select_box($format_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  $output .= '<form method="get" action="certificate.php">';
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
