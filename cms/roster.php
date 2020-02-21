<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'roster';
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
  $output .= '<form method="get" action="roster.php">';
  $output .= $hidden_field;
  $output .= '<p align="center">Select a Different Format: '.vlc_select_box($format_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  /* get course information */
  $course_query = <<< END_COURSE_QUERY
    SELECT c.course_id, c.description AS course_name, c.code AS course_code,
      f.user_id AS facilitator_id, f.username AS facilitator_username, f.password AS facilitator_password,
      f.first_name AS facilitator_first_name, f.last_name AS facilitator_last_name
    FROM courses AS c, users_courses AS uc, users AS f
    WHERE c.course_id = uc.course_id
    AND uc.user_id = f.user_id
    AND uc.user_role_id = 4
    AND uc.course_status_id NOT IN (1, 4, 5)
    AND c.is_active = 1
    $where_clause
    ORDER BY c.course_id
END_COURSE_QUERY;
  $result = mysql_query($course_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result))
  {
    $courses[$record['course_id']]['course_details'] = $record;
    $courses[$record['course_id']]['facilitators'][] = 'Facilitator: '.$record['facilitator_first_name'].' '.$record['facilitator_last_name'].' (<code>User ID: '.vlc_internal_link($record['facilitator_id'], 'cms/user_details.php?user='.$record['facilitator_id']).', Username: '.$record['facilitator_username'].', Password: '.$record['facilitator_password'].'</code>)';
  }
  /* get student information */
  $student_query = <<< END_QUERY
    SELECT uc.user_course_id, o.order_id, c.course_id, u.user_id AS student_id, u.username, u.password, i.primary_email, IFNULL(i.secondary_email, '') AS secondary_email,
      IFNULL(i.primary_phone, '') AS primary_phone, IFNULL(i.secondary_phone, '') AS secondary_phone,
      u.first_name AS student_first_name, u.last_name AS student_last_name,
      IFNULL(d.description, '') AS diocese, IF(i.diocese_id IS NULL, '', IF(d.is_partner, 'Partner', 'Non-Partner')) AS diocese_partner_status,
      IFNULL(p.description, '') AS partner, IF(i.partner_id IS NULL, '', IF(p.is_partner, 'Partner', 'Non-Partner')) AS partner_partner_status,
      IFNULL(i.address_1, '') AS address_1, IFNULL(i.address_2, '') AS address_2,
      IFNULL(i.city, '') AS city, IFNULL(t.code, '--') AS state, IFNULL(i.zip, '') AS zip, IFNULL(y.description, '') AS country,
      o.payment_status_id, m.description AS payment_status, s.description AS course_status, uc.registration_type_id
    FROM courses AS c, users_courses AS uc, users AS u,
      user_info AS i
        LEFT JOIN partners AS d ON i.diocese_id = d.partner_id
        LEFT JOIN partners AS p ON i.partner_id = p.partner_id
        LEFT JOIN countries AS y ON i.country_id = y.country_id
        LEFT JOIN states AS t ON i.state_id = t.state_id,
      course_status AS s, orders AS o, payment_status AS m
    WHERE c.course_id = uc.course_id
      AND uc.course_status_id = s.course_status_id
      AND uc.user_course_id = o.product_id
      AND o.payment_status_id = m.payment_status_id
      AND uc.user_id = u.user_id
      AND u.user_id = i.user_id
      AND uc.user_role_id = 5
      AND o.product_type_id = 1
      AND o.customer_type_id = 1
      AND uc.course_status_id NOT IN (1, 4, 5)
      AND c.is_active = 1
      $where_clause
    ORDER BY c.description, c.section_id, u.last_name, u.first_name
END_QUERY;
  $result = mysql_query($student_query, $site_info['db_conn']);
  switch ($output_format)
  {
    /***************************************************************************
    ** printable table (html)
    */
    case 1:
      $num_students = mysql_num_rows($result);
      $prev_course_id = 0;
      $output .= '<h3 align="center">Total Number of Students: '.$num_students.'</h3>';
      $output .= '<table border="1" cellpadding="5" cellspacing="0">';
      while ($record = mysql_fetch_array($result))
      {
        $curr_course_id = $record['course_id'];
        if ($curr_course_id != $prev_course_id)
        {
          $output .= '<tr><th>'.$courses[$curr_course_id]['course_details']['course_name'].' (Course Code: '.$courses[$curr_course_id]['course_details']['course_code'].', Course ID: '.vlc_internal_link($curr_course_id, 'cms/course_details.php?course='.$curr_course_id).')<br>'.join('<br>', $courses[$curr_course_id]['facilitators']).'</th></tr>';
        }
        $output .= '<tr>';
        if (in_array($record['payment_status_id'], array(1, 4)))
        {
          $output .= '<td style="background: #fff;">';
          $payment_icon = '';
        }
        else
        {
          $output .= '<td>';
          $payment_icon = ' <img border="0" src="'.$site_info['images_url'].'alert.jpg">';
        }
        if ($record['registration_type_id'] == 2) $reg_type = ' <b>* Undergraduate Credit *</b>';
        else $reg_type = '';
        $output .= '<b>'.$record['student_last_name'].', '.$record['student_first_name'].'</b> (<code>User ID: '.vlc_internal_link($record['student_id'], 'cms/user_details.php?user='.$record['student_id']).', Username: '.$record['username'].', Password: '.$record['password'].'</code>)'.$reg_type.'<br>';
        $output .= '&nbsp;&nbsp;&nbsp;&nbsp;Payment Status: <b>'.$record['payment_status'].'</b> ('.vlc_internal_link($record['order_id'], 'cms/order_details.php?order='.$record['order_id']).') '.$payment_icon.'<br>';
        $output .= '&nbsp;&nbsp;&nbsp;&nbsp;Course Status: <b>'.$record['course_status'].'</b> ('.vlc_internal_link($record['user_course_id'], 'cms/user_course_details.php?user_course='.$record['user_course_id']).')<br>';
        $output .= '&nbsp;&nbsp;&nbsp;&nbsp;Diocese: '.$record['diocese'].' <b>'.$record['diocese_partner_status'].'</b><br>';
        $output .= '&nbsp;&nbsp;&nbsp;&nbsp;Non-Diocesan Organization: '.$record['partner'].' <b>'.$record['partner_partner_status'].'</b><br>';
        $output .= '&nbsp;&nbsp;&nbsp;&nbsp;Address: '.$record['address_1'].', '.$record['address_2'].', '.$record['city'].', '.$record['state'].', '.$record['zip'].' '.$record['country'].'<br>';
        $output .= '&nbsp;&nbsp;&nbsp;&nbsp;Phone: '.$record['primary_phone'].', E-Mail: <a href="mailto:'.$record['primary_email'].'">'.$record['primary_email'].'</a>';
        $output .= '</td>';
        $output .= '</tr>';
        $prev_course_id = $curr_course_id;
      }
      $output .= '</table>';
      break;
    /***************************************************************************
    ** csv export
    */
    case 2:
    case 3:
    case 4:
      $roster_array = array();
      $roster_array[] = array('Course ID', 'Course Code', 'Course', 'Facilitator ID', 'Facilitator First Name', 'Facilitator Last Name', 'Facilitator Username', 'Facilitator Password', 'Student ID', 'Student First Name', 'Student Last Name', 'Student Username', 'Student Password', 'Payment Status', 'Course Status', 'Diocese', 'Diocese Partner Status', 'Non-Diocese', 'Non-Diocese Partner Status', 'Address 1', 'Address 2', 'City', 'State', 'Zip Code', 'Country', 'Primary Phone', 'Secondary Phone', 'Primary E-Mail', 'Secondary E-Mail');
      while ($record = mysql_fetch_array($result))
      {
        $record = array_merge($courses[$record['course_id']]['course_details'], $record);
        $roster_array[] = array($record['course_id'], $record['course_code'], $record['course_name'], $record['facilitator_id'], $record['facilitator_first_name'], $record['facilitator_last_name'], $record['facilitator_username'], $record['facilitator_password'], $record['student_id'], $record['student_first_name'], $record['student_last_name'], $record['username'], $record['password'], $record['payment_status'], $record['course_status'], $record['diocese'], $record['diocese_partner_status'], $record['partner'], $record['partner_partner_status'], $record['address_1'], $record['address_2'], $record['city'], $record['state'], $record['zip'], $record['country'], $record['primary_phone'], $record['secondary_phone'], $record['primary_email'], $record['secondary_email']);
      }
      switch ($output_format)
      {
        case 2:
          vlc_export_data($roster_array, 'roster', 1);
          break;
        case 3:
          vlc_export_data($roster_array, 'roster', 2, 'P');
          break;
        case 4:
          vlc_export_data($roster_array, 'roster', 2, 'L');
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
  $output .= '<form method="get" action="roster.php">';
  $output .= '<p>Select a Cycle: '.vlc_select_box($cycle_options_array, 'array', 'cycle', -1, true).' '.vlc_select_box($format_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  $output .= '<form method="get" action="roster.php">';
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
