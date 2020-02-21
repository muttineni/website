<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'course-details';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get url variables */
if (isset($_GET['course'])) $form_fields['course_id'] = intval($_GET['course']);
if (isset($_GET['num'])) $num = $_GET['num'];
else $num = 1;
/* get form fields */
if (strlen($status_message) > 0 and isset($_SESSION['form_fields']))
{
  $form_fields = $_SESSION['form_fields'];
  $_SESSION['form_fields'] = null;
}
if (isset($form_fields['course_id']))
{
  $select_box_required = 1;
  if (!isset($form_fields['cycle_id']))
  {
    /* get course details */
    $course_details_query = <<< END_QUERY
      SELECT course_id, cycle_id, course_subject_id, section_id, is_restricted, is_sample, is_active, registration_type_id,
        IFNULL(code, '') AS code, IFNULL(description, '') AS description, IFNULL(course_email, '') AS course_email,
        IFNULL(MONTH(facilitator_start), -1) AS facilitator_start_month,
        IFNULL(DAYOFMONTH(facilitator_start), -1) AS facilitator_start_day,
        IFNULL(YEAR(facilitator_start), -1) AS facilitator_start_year,
        IFNULL(MONTH(facilitator_end), -1) AS facilitator_end_month,
        IFNULL(DAYOFMONTH(facilitator_end), -1) AS facilitator_end_day,
        IFNULL(YEAR(facilitator_end), -1) AS facilitator_end_year,
        IFNULL(MONTH(student_start), -1) AS student_start_month,
        IFNULL(DAYOFMONTH(student_start), -1) AS student_start_day,
        IFNULL(YEAR(student_start), -1) AS student_start_year,
        IFNULL(MONTH(student_end), -1) AS student_end_month,
        IFNULL(DAYOFMONTH(student_end), -1) AS student_end_day,
        IFNULL(YEAR(student_end), -1) AS student_end_year
      FROM courses
      WHERE course_id = {$form_fields['course_id']}
END_QUERY;
    $result = mysql_query($course_details_query, $site_info['db_conn']);
    $form_fields = mysql_fetch_array($result);
  }
  if (!isset($form_fields['facilitators']))
  {
    $form_fields['facilitators'] = array();
    /* get facilitator(s) */
    $facilitator_query = <<< END_QUERY
      SELECT uc.user_course_id, uc.user_id, uc.course_status_id, u.last_name, u.first_name,
        o.order_id, o.is_active, o.payment_status_id, o.order_cost
      FROM users_courses AS uc, users AS u, orders AS o
      WHERE uc.user_id = u.user_id
      AND uc.user_id = o.customer_id
      AND uc.course_id = o.product_id
      AND o.product_type_id = 3
      AND o.customer_type_id = 3
      AND uc.user_role_id = 4
      AND uc.course_id = {$form_fields['course_id']}
      ORDER BY u.last_name, u.first_name
END_QUERY;
    $result = mysql_query($facilitator_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result)) $form_fields['facilitators'][] = $record;
  }
  if (!isset($form_fields['students']))
  {
    $form_fields['students'] = array();
    /* get students */
    $student_query = <<< END_QUERY
      SELECT uc.user_course_id, uc.user_id, r.description AS user_role, p.description AS payment_status, t.description AS registration_type,
        uc.course_status_id, u.last_name, u.first_name, o.order_id,
        CASE o.discount_type_id
          WHEN 1 THEN CONCAT(b.code, ' - ', b.description)
          WHEN 2 THEN a.description
          WHEN 3 THEN a.description
          ELSE '&nbsp;'
        END AS discount
      FROM users_courses AS uc, users AS u, user_roles AS r, payment_status AS p, registration_types AS t,
        orders AS o
          LEFT JOIN partners AS a ON o.discount_id = a.partner_id
          LEFT JOIN payment_codes AS b ON o.discount_id = b.payment_code_id
          LEFT JOIN discount_types AS d ON o.discount_type_id = d.discount_type_id
      WHERE uc.user_id = u.user_id
      AND uc.user_id = o.customer_id
      AND uc.user_role_id = r.user_role_id
      AND uc.user_course_id = o.product_id
      AND uc.registration_type_id = t.registration_type_id
      AND o.payment_status_id = p.payment_status_id
      AND o.product_type_id = 1
      AND o.customer_type_id = 1
      AND uc.user_role_id = 5
      AND uc.course_id = {$form_fields['course_id']}
      ORDER BY u.last_name, u.first_name
END_QUERY;
    $result = mysql_query($student_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result)) $form_fields['students'][] = $record;
  }
  /* get event details */
  $entity_id = $form_fields['course_id'];
  $event_type_array = array(
    COURSES_CREATE,
    COURSES_UPDATE,
    COURSES_ADD_USER
  );
  $event_history = vlc_get_event_history($event_type_array, $entity_id);
}
else
{
  $select_box_required = 0;
  if (!isset($form_fields['cycle_id']))
  {
    $form_fields = array
    (
      'cycle_id' => -1,
      'course_subject_id' => -1,
      'section_id' => -1,
      'facilitator_id' => -1,
      'is_restricted' => 0,
      'is_sample' => 0,
      'is_active' => -1,
      'registration_type_id' => -1,
      'code' => '',
      'description' => '',
      'course_email' => '',
      'facilitator_start_month' => -1,
      'facilitator_start_day' => -1,
      'facilitator_start_year' => -1,
      'facilitator_end_month' => -1,
      'facilitator_end_day' => -1,
      'facilitator_end_year' => -1,
      'student_start_month' => -1,
      'student_start_day' => -1,
      'student_start_year' => -1,
      'student_end_month' => -1,
      'student_end_day' => -1,
      'student_end_year' => -1
    );
  }
}
foreach ($form_fields as $key => $value)
{
  //if (is_string($value)) $form_fields[$key] = htmlspecialchars($value); Commented out to properly display foreign characters - 2015-10-06 Bob
}
/* get cycles */
$cycle_query = <<< END_QUERY
  SELECT cycle_id, IFNULL(code, cycle_id) AS code,
    UNIX_TIMESTAMP(cycle_start) AS cycle_start_timestamp,
    UNIX_TIMESTAMP(cycle_end) AS cycle_end_timestamp
  FROM cycles
  ORDER BY cycle_start
END_QUERY;
$result = mysql_query($cycle_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $cycle_options_array[$record['cycle_id']] = $record['code'].' ('.date('M. j', $record['cycle_start_timestamp']).' - '.date('M. j', $record['cycle_end_timestamp']).')';
/* get course subjects */
$course_subject_query = <<< END_QUERY
  SELECT course_subject_id, IFNULL(description, course_subject_id) AS description
  FROM course_subjects
  ORDER BY description
END_QUERY;
$result = mysql_query($course_subject_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $course_subject_options_array[$record['course_subject_id']] = $record['description'];
/* get sections */
$section_query = <<< END_QUERY
  SELECT section_id, IFNULL(code, section_id) AS code
  FROM sections
  ORDER BY code
END_QUERY;
$result = mysql_query($section_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $course_section_options_array[$record['section_id']] = $record['code'];
/* get facilitators */
$facilitator_query = <<< END_QUERY
  SELECT u.user_id AS facilitator_id, u.first_name, u.last_name
  FROM users AS u, users_courses AS uc, users_roles AS ur
  WHERE u.user_id = uc.user_id
  AND u.user_id = ur.user_id
  AND (uc.user_role_id = 4 OR ur.user_role_id = 4)
  GROUP BY u.user_id
  ORDER BY u.last_name, u.first_name
END_QUERY;
$result = mysql_query($facilitator_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $facilitator_options_array[$record['facilitator_id']] = $record['last_name'].', '.$record['first_name'];
/* get course status options */
$course_status_query = <<< END_QUERY
  SELECT course_status_id, IFNULL(description, course_status_id) AS description
  FROM course_status
  ORDER BY course_status_id
END_QUERY;
$result = mysql_query($course_status_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $course_status_options_array[$record['course_status_id']] = $record['description'];
/* get registration type options */
$registration_type_query = <<< END_QUERY
  SELECT registration_type_id, IFNULL(description, registration_type_id) AS description
  FROM registration_types
  ORDER BY registration_type_id
END_QUERY;
$result = mysql_query($registration_type_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $registration_type_options_array[$record['registration_type_id']] = $record['description'];
/* build array for "discount type" select box */
$discount_type_options_array = array();
$discount_type_options_array[1]['label'] = 'Payment Codes';
$discount_type_options_array[2]['label'] = 'Partnering Organizations';
$discount_type_options_array[3]['label'] = 'Partnering Dioceses';
/* get payment codes */
$payment_codes_query = <<< END_QUERY
  SELECT payment_code_id, code AS payment_code, description
  FROM payment_codes
  ORDER BY code
END_QUERY;
$result = mysql_query($payment_codes_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $discount_type_options_array[1]['options'][10000 + $record['payment_code_id']] = $record['payment_code'].' - '.$record['description'];
/* get dioceses and partners */
$partner_query = <<< END_QUERY
  SELECT p.partner_id, p.description, p.is_diocese, IFNULL(s.code, c.description) AS state_country
  FROM partners AS p LEFT JOIN states AS s USING (state_id), countries AS c
  WHERE p.is_partner = 1
  AND p.country_id = c.country_id
  ORDER BY p.description
END_QUERY;
$result = mysql_query($partner_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result))
{
  if ($record['is_diocese']) $discount_type_options_array[3]['options'][30000 + $record['partner_id']] = $record['description'].' ('.$record['state_country'].')';
  else $discount_type_options_array[2]['options'][20000 + $record['partner_id']] = $record['description'];
}
/* build array for "user role" select box */
$user_role_options_array = array(4 => 'Facilitator', 5 => 'Student');
/* course status values */
$is_active_options_array = array(1 => 'Active', 0 => 'Inactive');
/* begin output variable */
$output = '';
/* return to previous page link */
if (isset($_SERVER['HTTP_REFERER']) and $return_url = strstr($_SERVER['HTTP_REFERER'], 'cms/')) $output .= '<p>'.vlc_internal_link('Return to Previous Page', $return_url).'</p>';
/* month, day, year arrays */
for ($i = 1; $i <= 12; $i++) $months_array[$i] = date('F', mktime(0,0,0,$i,1,0));
for ($i = 1; $i <= 31; $i++) $days_array[$i] = $i;
for ($i = 2000; $i <= 2020; $i++) $years_array[$i] = $i;
/* num users array */
for ($i = 5; $i <= 50; $i += 5) $num_users_array[$i] = $i;
/* output */
$output .= '<form method="post" action="course_action.php">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
if (isset($form_fields['course_id']))
{
  $output .= '<tr><td><nobr><b>Course Link:</b></nobr></td><td colspan="3">'.vlc_internal_link('Click here to see this course in the classes user interface.', 'classes/?course='.$form_fields['course_id'], '', '', 0, 1).'</td></tr>';
  $output .= '<tr><td><nobr><b>Course ID:</b></nobr></td><td colspan="3">'.$form_fields['course_id'].'<input type="hidden" name="course_id" value="'.$form_fields['course_id'].'"></td></tr>';
}
else $output .= '<tr><td><nobr><b>Facilitator:</b></nobr></td><td colspan="3">'.vlc_select_box($facilitator_options_array, 'array', 'facilitator_id', $form_fields['facilitator_id'], $select_box_required).'</td></tr>';
$output .= '<tr>';
$output .= '<td><nobr><b>Cycle:</b></nobr></td>';
$output .= '<td>'.vlc_select_box($cycle_options_array, 'array', 'cycle_id', $form_fields['cycle_id'], $select_box_required).($form_fields['cycle_id'] > 0 ? ' ('.vlc_internal_link($form_fields['cycle_id'], 'cms/cycle_details.php?cycle='.$form_fields['cycle_id']).')' : '').'</td>';
$output .= '<td><nobr><b>Course Status:</b></nobr></td>';
$output .= '<td>'.vlc_select_box($is_active_options_array, 'array', 'is_active', $form_fields['is_active'], $select_box_required).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><nobr><b>Subject:</b></nobr></td>';
$output .= '<td>'.vlc_select_box($course_subject_options_array, 'array', 'course_subject_id', $form_fields['course_subject_id'], $select_box_required).($form_fields['course_subject_id'] > 0 ? ' ('.vlc_internal_link($form_fields['course_subject_id'], 'cms/course_subject_details.php?subject='.$form_fields['course_subject_id']).')' : '').'</td>';
$output .= '<td><nobr><b>Section:</b></nobr></td>';
$output .= '<td>'.vlc_select_box($course_section_options_array, 'array', 'section_id', $form_fields['section_id'], $select_box_required).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><nobr><b>Code:</b></nobr></td>';
$output .= '<td><input type="text" size="10" name="code" value="'.$form_fields['code'].'"></td>';
$output .= '<td><nobr><b>Description:</b></nobr></td>';
$output .= '<td><input type="text" size="30" name="description" value="'.$form_fields['description'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><nobr><b>Sympa:</b></nobr></td>';
$output .= '<td><input type="text" size="30" name="course_email" value="'.$form_fields['course_email'].'"></td>';
$output .= '<td><nobr><b>List Course As:</b></nobr></td>';
$output .= '<td>'.vlc_select_box($registration_type_options_array, 'array', 'registration_type_id', $form_fields['registration_type_id'], $select_box_required).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><nobr><b>Restricted:</b></nobr></td>';
$output .= '<td><input type="checkbox" name="is_restricted" value="1"'.($form_fields['is_restricted'] ? ' checked="checked"' : '').'></td>';
$output .= '<td><nobr><b>Sample:</b></nobr></td>';
$output .= '<td><input type="checkbox" name="is_sample" value="1"'.($form_fields['is_sample'] ? ' checked="checked"' : '').'></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><nobr><b>Facilitator Start:</b></nobr></td>';
$output .= '<td><nobr>'.vlc_select_box($months_array, 'array', 'facilitator_start_month', $form_fields['facilitator_start_month'], true).' '.vlc_select_box($days_array, 'array', 'facilitator_start_day', $form_fields['facilitator_start_day'], true).' '.vlc_select_box($years_array, 'array', 'facilitator_start_year', $form_fields['facilitator_start_year'], true).' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].facilitator_start_year,document.forms[0].facilitator_start_month,document.forms[0].facilitator_start_day,false,false,this);"></nobr></td>';
$output .= '<td><nobr><b>Facilitator End:</b></nobr></td>';
$output .= '<td><nobr>'.vlc_select_box($months_array, 'array', 'facilitator_end_month', $form_fields['facilitator_end_month'], true).' '.vlc_select_box($days_array, 'array', 'facilitator_end_day', $form_fields['facilitator_end_day'], true).' '.vlc_select_box($years_array, 'array', 'facilitator_end_year', $form_fields['facilitator_end_year'], true).' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].facilitator_end_year,document.forms[0].facilitator_end_month,document.forms[0].facilitator_end_day,false,false,this);"></nobr></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><nobr><b>Student Start:</b></nobr></td>';
$output .= '<td><nobr>'.vlc_select_box($months_array, 'array', 'student_start_month', $form_fields['student_start_month'], true).' '.vlc_select_box($days_array, 'array', 'student_start_day', $form_fields['student_start_day'], true).' '.vlc_select_box($years_array, 'array', 'student_start_year', $form_fields['student_start_year'], true).' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].student_start_year,document.forms[0].student_start_month,document.forms[0].student_start_day,false,false,this);"></nobr></td>';
$output .= '<td><nobr><b>Student End:</b></nobr></td>';
$output .= '<td><nobr>'.vlc_select_box($months_array, 'array', 'student_end_month', $form_fields['student_end_month'], true).' '.vlc_select_box($days_array, 'array', 'student_end_day', $form_fields['student_end_day'], true).' '.vlc_select_box($years_array, 'array', 'student_end_year', $form_fields['student_end_year'], true).' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].student_end_year,document.forms[0].student_end_month,document.forms[0].student_end_day,false,false,this);"></nobr></td>';
$output .= '</tr>';
$output .= '<tr><td colspan="4" align="center"><input type="submit" value="Submit"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
$css_output = $js_output = '';
if (isset($form_fields['course_id']))
{
  $output .= '<h3>View Related Records:</h3>';
  $output .= '<ul>';
  $output .= '<li>'.vlc_internal_link('Course Registrations', 'cms/users_courses.php?course_id='.$form_fields['course_id']).'</li>';
  $output .= '<li>'.vlc_internal_link('Student Registration Orders', 'cms/student_orders.php?course_id='.$form_fields['course_id']).'</li>';
  $output .= '</ul>';
  $evaluation_options_array = array(1 => 'Grouped by Question (HTML)', 'Grouped by Student (HTML)', 'Export to Excel (CSV)', 'Printable Table (PDF - Portrait)', 'Printable Table (PDF - Landscape)');
  $output .= '<h3>Course Evaluations:</h3>';
  $output .= '<form method="get" action="evaluations.php">';
  $output .= '<input type="hidden" name="course" value="'.$form_fields['course_id'].'">';
  $output .= '<p>Select a Format: '.vlc_select_box($evaluation_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  $format_options_array = array(1 => 'Printable Table (HTML)', 'Export to Excel (CSV)', 'Printable Table (PDF - Portrait)', 'Printable Table (PDF - Landscape)');
  $output .= '<h3>Course Roster:</h3>';
  $output .= '<form method="get" action="roster.php">';
  $output .= '<input type="hidden" name="course" value="'.$form_fields['course_id'].'">';
  $output .= '<p>Select a Format: '.vlc_select_box($format_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  $output .= '<h3>Certificate Data:</h3>';
  $output .= '<form method="get" action="certificate.php">';
  $output .= '<input type="hidden" name="course" value="'.$form_fields['course_id'].'">';
  $output .= '<p>Select a Format: '.vlc_select_box($format_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  $output .= '<h3>Facilitators:</h3>';
  $output .= '<form method="post" action="course_action.php">';
  $output .= '<input type="hidden" name="course_id" value="'.$form_fields['course_id'].'">';
  $output .= '<table border="1" cellpadding="5" cellspacing="0">';
  $output .= '<tr><th>&nbsp;</th><th>Order ID</th><th>Course Registration ID</th><th>User ID</th><th>Name</th><th>Stipend Amount</th><th>Course Status</th><th>Remove</th></tr>';
  if (count($form_fields['facilitators']))
  {
    $i = 1;
    $stipend_total = 0;
    foreach ($form_fields['facilitators'] as $facilitator)
    {
      $stipend_total += $facilitator['order_cost'];
      $output .= '<tr>';
      $output .= '<td>'.$i++.'.</td>';
      $output .= '<td align="center">'.vlc_internal_link($facilitator['order_id'], 'cms/order_details.php?order='.$facilitator['order_id']).'</td>';
      $output .= '<td align="center">'.vlc_internal_link($facilitator['user_course_id'], 'cms/user_course_details.php?user_course='.$facilitator['user_course_id']).'</td>';
      $output .= '<td align="center">'.vlc_internal_link($facilitator['user_id'], 'cms/user_details.php?user='.$facilitator['user_id']).'</td>';
      $output .= '<td>'.$facilitator['last_name'].', '.$facilitator['first_name'].'</td>';
      $output .= '<td align="right">$ <input type="text" size="10" name="facilitators['.$facilitator['user_course_id'].'][order_cost]" value="'.number_format($facilitator['order_cost'] / 100, 2).'" style="text-align:right"></td>';
      $output .= '<td align="center">'.vlc_select_box($course_status_options_array, 'array', 'facilitators['.$facilitator['user_course_id'].'][course_status_id]', $facilitator['course_status_id'], true).'</td>';
      $output .= '<td align="center"><input type="checkbox" name="facilitators['.$facilitator['user_course_id'].'][remove]" value="1"></td>';
      $output .= '</tr>';
    }
    $output .= '<tr><td colspan="5" align="right"><nobr><b>Total Stipend Amount:</b></nobr></td><td align="right">$'.number_format($stipend_total / 100, 2).'</td><td colspan="2">&nbsp;</td></tr>';
    $output .= '<tr><td colspan="8" align="center"><input type="submit" value="Submit"> <input type="checkbox" name="update_facilitator_stipend" value="1" id="update_stipend_checkbox"> <label for="update_stipend_checkbox">Update Stipend Amount Automatically</label></td></tr>';
  }
  else $output .= '<tr><td colspan="7" align="center">No Facilitators Found.</td></tr>';
  $output .= '</table>';
  $output .= '</form>';
  $output .= '<a name="students"></a><h3>Students:</h3>';
  $output .= '<p>The following students are linked to this course.</p>';
  $output .= '<form method="get" action="course_details.php#students">';
  $output .= '<input type="hidden" name="course" value="'.$form_fields['course_id'].'">';
  $output .= '<ul>';
  $output .= '<li>To view additional order details, click the <b>&quot;Order ID&quot;</b> link.</li>';
  $output .= '<li>To view additional course registration details, click the <b>&quot;Course Registration ID&quot;</b> link.</li>';
  $output .= '<li>To view additional user details, click the <b>&quot;User ID&quot;</b> link.</li>';
  $output .= '<li>To add users to this course, select the number of users to add and click <b>&quot;Submit&quot;</b>: '.vlc_select_box($num_users_array, 'array', 'num', -1, true).' <input type="submit" value="Submit"></li>';
  $output .= '</ul>';
  $output .= '</form>';
  $output .= '<form method="post" action="course_action.php">';
  $output .= '<input type="hidden" name="course_id" value="'.$form_fields['course_id'].'">';
  $output .= '<table border="1" cellpadding="5" cellspacing="0">';
  $output .= '<tr><th><nobr>&nbsp;</nobr></th><th><nobr>Order ID</nobr></th><th><nobr>Course Registration ID</nobr></th><th><nobr>User ID</nobr></th><th><nobr>Name</nobr></th><th><nobr>Course Status</nobr></th><th><nobr>Partner Discount</nobr></th><th><nobr>Registration Type</nobr></th><th><nobr>Payment Status</nobr></th></tr>';
  $i = 1;
  if (count($form_fields['students']))
  {
    foreach ($form_fields['students'] as $student)
    {
      $output .= '<tr>';
      $output .= '<td>'.$i++.'.</td>';
      $output .= '<td align="center">'.vlc_internal_link($student['order_id'], 'cms/order_details.php?order='.$student['order_id']).'</td>';
      $output .= '<td align="center">'.vlc_internal_link($student['user_course_id'], 'cms/user_course_details.php?user_course='.$student['user_course_id']).'</td>';
      $output .= '<td align="center">'.vlc_internal_link($student['user_id'], 'cms/user_details.php?user='.$student['user_id']).'</td>';
      $output .= '<td>'.$student['last_name'].', '.$student['first_name'].'</td>';
      $output .= '<td align="center">'.vlc_select_box($course_status_options_array, 'array', 'students['.$student['user_course_id'].'][course_status_id]', $student['course_status_id'], true).'</td>';
      $output .= '<td>'.$student['discount'].'</td>';
      $output .= '<td align="center">'.$student['registration_type'].'</td>';
      $output .= '<td align="center">'.$student['payment_status'].'</td>';
      $output .= '</tr>';
    }
  }
  else $output .= '<tr><td colspan="8" align="center">No Students Found.</td></tr>';
  $az_array = array();
  for ($j = 97; $j < 123; $j++) $az_array[] = chr($j);
  for ($j = 65; $j < 91; $j++) $az_array[] = chr($j);
  for ($j = 0; $j < $num; $j++)
  {
    $output .= '<tr>';
    $output .= '<td>'.$i++.'.</td>';
    $output .= '<td colspan="3"><div class="yui-skin-sam"><div id="ac_user_id_'.$az_array[$j].'" class="autocomplete"><input type="text" name="students['.$az_array[$j].'][user_id]" id="ac_user_id_'.$az_array[$j].'_field" value=""><div id="ac_user_id_'.$az_array[$j].'_container"></div></div></div></td>';
    $output .= '<td align="center">'.vlc_select_box($user_role_options_array, 'array', 'students['.$az_array[$j].'][user_role_id]', 5, true).'</td>';
    $output .= '<td align="center">'.vlc_select_box($course_status_options_array, 'array', 'students['.$az_array[$j].'][course_status_id]', 2, true).'</td>';
    $output .= '<td align="center">'.vlc_select_box($discount_type_options_array, 'array', 'students['.$az_array[$j].'][discount_type_id]', -1, false, '', '', 'discount_type_'.$az_array[$j]).'</td>';
    $output .= '<td align="center">'.vlc_select_box($registration_type_options_array, 'array', 'students['.$az_array[$j].'][registration_type_id]', 1, true).'</td>';
    $output .= '<td>&nbsp;</td>';
    $output .= '</tr>';
    $css_output .= '#ac_user_id_'.$az_array[$j].' { z-index:'.(9000 - $j).'; }'."\n";
    $js_output .= 'this.ac_user_id_'.$az_array[$j].'_data_source = new YAHOO.widget.DS_XHR(\'xhr.php\', ["\n", "\t"]); this.ac_user_id_'.$az_array[$j].'_data_source.responseType = YAHOO.widget.DS_XHR.TYPE_FLAT; this.ac_user_id_'.$az_array[$j].'_data_source.scriptQueryAppend = \'field=user_id_with_diocese_id\'; this.ac_user_id_'.$az_array[$j].'_widget = new YAHOO.widget.AutoComplete(\'ac_user_id_'.$az_array[$j].'_field\', \'ac_user_id_'.$az_array[$j].'_container\', this.ac_user_id_'.$az_array[$j].'_data_source); this.ac_user_id_'.$az_array[$j].'_widget.related_dropdown = \'discount_type_'.$az_array[$j].'\'; this.ac_user_id_'.$az_array[$j].'_widget.itemSelectEvent.subscribe(select_handler); this.ac_user_id_'.$az_array[$j].'_widget.formatResult = function(result_array, query_string) { var result_id = result_array[0]; var result_string = result_array[1]; return format_result(result_id, result_string, query_string); };'."\n";
  }
  $output .= '<tr><td colspan="9" align="center"><input type="submit" value="Submit"></td></tr>';
  $output .= '</table>';
  $output .= '</form>';
  $output .= $event_history;
}
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<style type="text/css">
<?php print $css_output ?>
</style>
<script type="text/javascript">
YAHOO.example.ACFlatData = new function() {
    function format_result(result_id, result_string, query_string) {
      var result_id_offset = result_id.indexOf(query_string);
      if (result_id_offset != -1) {
        var result_id_begin = result_id.substr(0, result_id_offset);
        var result_id_match = result_id.substr(result_id_offset, query_string.length);
        var result_id_end = result_id.substr(result_id_offset + query_string.length);
        result_id = result_id_begin + '<span class="result-match">' + result_id_match + '</span>' + result_id_end;
      }
      var result_string_offset = result_string.toLowerCase().indexOf(query_string.toLowerCase());
      if (result_string_offset != -1) {
        var result_string_begin = result_string.substr(0, result_string_offset);
        var result_string_match = result_string.substr(result_string_offset, query_string.length);
        var result_string_end = result_string.substr(result_string_offset + query_string.length);
        result_string = result_string_begin + '<span class="result-match">' + result_string_match + '</span>' + result_string_end;
      }
      var html_string = '<div class="sample-result"><div class="result-id">' + result_id + '</div>' + result_string + '</div>';
      return html_string;
    };
    function select_handler(event_type, arg_array) {
      var data_array = arg_array[2];
      var discount_type_id = 30000 + parseInt(data_array[2]);
      var select_box = document.getElementById(arg_array[0].related_dropdown);
      select_box.options[0].selected = true;
      for (var i = 0; i < select_box.length; i++) {
        if (select_box.options[i].value == discount_type_id) {
          select_box.options[i].selected = true;
          return;
        }
      }
    };
<?php print $js_output ?>
};
</script>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
