<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'partner-reports';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
$output = '';
/* year, month, day arrays */
for ($i = 2000; $i <= 2020; $i++) $years_array[$i] = $i;
for ($i = 1; $i <= 12; $i++) $months_array[$i] = date('F', mktime(0,0,0,$i,1,0));
for ($i = 1; $i <= 31; $i++) $days_array[$i] = $i;
if (isset($_GET['partner'])) $partner_id = $_GET['partner'];
if (isset($_GET['format'])) $output_format = $_GET['format'];
if (isset($_GET['min_year']) and isset($_GET['min_month']) and isset($_GET['min_day'])) $min_date = $_GET['min_year'].'-'.$_GET['min_month'].'-'.$_GET['min_day'];
if (isset($_GET['max_year']) and isset($_GET['max_month']) and isset($_GET['max_day'])) $max_date = $_GET['max_year'].'-'.$_GET['max_month'].'-'.$_GET['max_day'];
if (isset($partner_id) and isset($output_format) and isset($min_date) and isset($max_date))
{
  /* get partner information */
  $partner_query = <<< END_QUERY
    SELECT description
    FROM partners
    WHERE partner_id = $partner_id
END_QUERY;
  $result = mysql_query($partner_query, $site_info['db_conn']);
  $partner_details = mysql_fetch_array($result);
  /* get participant information */
  $participant_query = <<< END_QUERY
    SELECT u.user_id, u.last_name, u.first_name, i.primary_email,
      i.city, t.code AS state_code, n.description AS country,
      DATE_FORMAT(y.cycle_start, '%c/%e/%Y') AS cycle_start,
      c.code AS course_code, c.description AS course,
      s.description AS course_status, m.description AS payment_status,
      IF (s.course_status_id = 7 AND m.payment_status_id = 4, '5.00', '0.00') AS partner_stipend
    FROM users AS u,
      user_info AS i LEFT JOIN states AS t ON i.state_id = t.state_id,
      countries AS n, users_courses AS uc, courses AS c, cycles AS y,
      orders AS o, course_status AS s, payment_status AS m
    WHERE u.user_id = i.user_id
      AND u.user_id = uc.user_id
      AND u.user_id = o.customer_id
      AND i.country_id = n.country_id
      AND uc.course_id = c.course_id
      AND uc.user_course_id = o.product_id
      AND uc.user_role_id = 5
      AND uc.course_status_id IN (2, 3, 4, 5, 6, 7)
      AND uc.course_status_id = s.course_status_id
      AND c.cycle_id = y.cycle_id
      AND o.customer_type_id = 1
      AND o.product_type_id = 1
      AND o.payment_status_id = m.payment_status_id
      AND (i.partner_id = $partner_id OR i.diocese_id = $partner_id OR (o.discount_type_id IN (2,3) AND o.discount_id = $partner_id))
      AND y.cycle_start >= '$min_date'
      AND y.cycle_start <= '$max_date'
    ORDER BY u.last_name, u.first_name, u.user_id, y.cycle_start, c.code
END_QUERY;
  $result = mysql_query($participant_query, $site_info['db_conn']);
  if (in_array($output_format, array(1, 2, 3)))
  {
    $export_array = array();
    $export_array[] = array('User ID', 'Last Name', 'First Name', 'E-Mail Address', 'City', 'State', 'Country', 'Course Date', 'Course Code', 'Course', 'Course Status', 'Payment Status', 'Partner Rebate');
    while ($record = mysql_fetch_row($result)) $export_array[] = $record;
    switch ($output_format)
    {
      case 1:
        vlc_export_data($export_array, 'partner-reports', 1);
        break;
      case 2:
        vlc_export_data($export_array, 'partner-reports', 2, 'P');
        break;
      case 3:
        vlc_export_data($export_array, 'partner-reports', 2, 'L');
        break;
    }
  }
  else
  {
    $output .= '<h3 align="center">'.$partner_details['description'].'</h3>';
    $output .= '<p align="center">(Date Range: '.$months_array[$_GET['min_month']].' '.$_GET['min_day'].', '.$_GET['min_year'].' to '.$months_array[$_GET['max_month']].' '.$_GET['max_day'].', '.$_GET['max_year'].')</p>';
    if ($num_registrations = mysql_num_rows($result))
    {
      $output .= '<p align="center">({NUM_STUDENTS} Participants, {NUM_REGISTRATIONS} Registrations)</p>';
      $output .= '<table border="1" cellpadding="5" cellspacing="0">';
      $output .= '<tr><th>Participant</th><th>Course Date</th><th>Course Code</th><th>Course</th><th>Course Status</th><th>Payment Status</th><th>Partner Rebate</th></tr>';
      $prev_student_id = 0;
      $student_id_array = array();
      while ($record = mysql_fetch_array($result))
      {
        $student_id_array[] = $curr_student_id = $record['user_id'];
        if ($curr_student_id != $prev_student_id)
        {
          if (isset($record['state_code'])) $state_country = $record['state_code'];
          else $state_country = $record['country'];
          $output .= '<tr><td colspan="7"><b>'.ucwords($record['last_name']).', '.ucwords($record['first_name']).'</b> ('.$record['primary_email'].', '.$record['city'].', '.$state_country.')</td></tr>';
        }
        $output .= '<tr><td>&nbsp;</td><td>'.$record['cycle_start'].'</td><td>'.$record['course_code'].'</td><td>'.$record['course'].'</td><td>'.$record['course_status'].'</td><td>'.$record['payment_status'].'</td><td>'.$record['partner_stipend'].'</td></tr>';
        $prev_student_id = $curr_student_id;
      }
      $output .= '</table>';
      $num_students = count(array_unique($student_id_array));
      $output = str_replace(array('{NUM_STUDENTS}', '{NUM_REGISTRATIONS}'), array($num_students, $num_registrations), $output);
    }
    else $output .= '<p>No Participants Found.</p>';
  }
}
else
{
  /* build array for "partner" select box */
  $partner_options_array = array();
  $partner_query = <<< END_QUERY
    SELECT p.partner_id, p.description, IFNULL(s.code, c.description) AS state_country
    FROM partners AS p LEFT JOIN states AS s USING (state_id), countries AS c
    WHERE p.is_partner = 1
    AND p.country_id = c.country_id
    ORDER BY p.description
END_QUERY;
  $result = mysql_query($partner_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) $partner_options_array[$record['partner_id']] = $record['description'].' ('.$record['state_country'].')';
  /* build array for "format" select box */
  $format_options_array = array(1 => 'Export to Excel (CSV)', 'Printable Table (PDF - Portrait)', 'Printable Table (PDF - Landscape)', 'Printable Table (HTML)');
  $output .= '<form method="get" action="partner_reports.php">';
  $output .= '<p>Partner: '.vlc_select_box($partner_options_array, 'array', 'partner', -1, true).'</p>';
  $output .= '<p>Date Range: '.vlc_select_box($months_array, 'array', 'min_month', 1, true).' '.vlc_select_box($days_array, 'array', 'min_day', 1, true).' '.vlc_select_box($years_array, 'array', 'min_year', 2000, true).' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].min_year,document.forms[0].min_month,document.forms[0].min_day,false,false,this);"> to '.vlc_select_box($months_array, 'array', 'max_month', 12, true).' '.vlc_select_box($days_array, 'array', 'max_day', 31, true).' '.vlc_select_box($years_array, 'array', 'max_year', 2020, true).' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].max_year,document.forms[0].max_month,document.forms[0].max_day,false,false,this);"></p>';
  $output .= '<p>Format: '.vlc_select_box($format_options_array, 'array', 'format', 4, true).'</p>';
  $output .= '<p><input type="submit" value="Go"></p>';
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
