<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'cycle-details';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get url variables */
if (isset($_GET['cycle'])) $form_fields['cycle_id'] = intval($_GET['cycle']);
if (isset($_GET['num'])) $num = $_GET['num'];
else $num = 1;
/* get form fields */
if (strlen($status_message) > 0 and isset($_SESSION['form_fields']))
{
  $form_fields = $_SESSION['form_fields'];
  $_SESSION['form_fields'] = null;
}
if (isset($form_fields['cycle_id']))
{
  if (!isset($form_fields['code']))
  {
    /* get cycle details */
    $cycle_query = <<< END_QUERY
      SELECT cycle_id, IFNULL(code, '') AS code, IFNULL(description, '') AS description,
        IFNULL(MONTH(cycle_start), -1) AS cycle_start_month,
        IFNULL(DAYOFMONTH(cycle_start), -1) AS cycle_start_day,
        IFNULL(YEAR(cycle_start), -1) AS cycle_start_year,
        IFNULL(MONTH(cycle_end), -1) AS cycle_end_month,
        IFNULL(DAYOFMONTH(cycle_end), -1) AS cycle_end_day,
        IFNULL(YEAR(cycle_end), -1) AS cycle_end_year,
        IFNULL(MONTH(registration_start), -1) AS registration_start_month,
        IFNULL(DAYOFMONTH(registration_start), -1) AS registration_start_day,
        IFNULL(YEAR(registration_start), -1) AS registration_start_year,
        IFNULL(MONTH(registration_end), -1) AS registration_end_month,
        IFNULL(DAYOFMONTH(registration_end), -1) AS registration_end_day,
        IFNULL(YEAR(registration_end), -1) AS registration_end_year
      FROM cycles
      WHERE cycle_id = {$form_fields['cycle_id']}
END_QUERY;
    $result = mysql_query($cycle_query, $site_info['db_conn']);
    $cycle_details = mysql_fetch_array($result);
    $form_fields = array_merge($form_fields, $cycle_details);
  }
  if (!isset($form_fields['courses']))
  {
    $form_fields['courses'] = $course_codes = $course_dates = $date_fields = array();
    /* get course codes for cycle */
    $course_code_query = <<< END_QUERY
      SELECT IFNULL(code, '') AS code
      FROM courses
      WHERE cycle_id = {$form_fields['cycle_id']}
      ORDER BY code
END_QUERY;
    $result = mysql_query($course_code_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result)) $course_codes[] = $record['code'];
    /* calculate course dates */
    $course_dates['facilitator_start'] = mktime(0, 0, 0, $form_fields['cycle_start_month'], $form_fields['cycle_start_day'] - 2, $form_fields['cycle_start_year']);
    $course_dates['facilitator_end'] = mktime(0, 0, 0, $form_fields['cycle_end_month'], $form_fields['cycle_end_day'] + 4, $form_fields['cycle_end_year']);
    $course_dates['student_start'] = mktime(0, 0, 0, $form_fields['cycle_start_month'], $form_fields['cycle_start_day'] - 1, $form_fields['cycle_start_year']);
    $course_dates['student_end'] = mktime(0, 0, 0, $form_fields['cycle_end_month'], $form_fields['cycle_end_day'] + 3, $form_fields['cycle_end_year']);
    foreach ($course_dates as $key => $value)
    {
      $date_fields[$key.'_month'] = date('n', $value);
      $date_fields[$key.'_day'] = date('j', $value);
      $date_fields[$key.'_year'] = date('Y', $value);
    }
    $course_fields = array('course_subject_id' => -1, 'section_id' => -1, 'facilitator_id' => -1, 'description' => '', 'course_email' => '', 'is_restricted' => 0, 'is_sample' => 0, 'is_active' => -1, 'registration_type_id' => -1);
    $form_fields_template = array_merge($date_fields, $course_fields);
    $j = 0;
    for ($i = 0; $i < $num; $i++)
    {
      do {
          $code = $form_fields['code'].str_pad(++$j, 2, '0', STR_PAD_LEFT);
      } while (in_array($code, $course_codes));
      $course_codes[] = $form_fields_template['code'] = $code;
      $form_fields['courses'][] = $form_fields_template;
    }
  }
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
    AND (uc.user_role_id = 4 OR ur.user_role_id = 4 OR uc.user_role_id = 8 OR ur.user_role_id = 8 OR uc.user_role_id = 11 OR ur.user_role_id = 11 OR uc.user_role_id = 12 OR ur.user_role_id = 12)
    GROUP BY u.user_id
    ORDER BY u.last_name, u.first_name
END_QUERY;
  $result = mysql_query($facilitator_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) $facilitator_options_array[$record['facilitator_id']] = $record['last_name'].', '.$record['first_name'];
  /* get registration type options */
  $registration_type_query = <<< END_QUERY
    SELECT registration_type_id, IFNULL(description, registration_type_id) AS description
    FROM registration_types
    ORDER BY registration_type_id
END_QUERY;
  $result = mysql_query($registration_type_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) $registration_type_options_array[$record['registration_type_id']] = $record['description'];
  /* course status values */
  $is_active_options_array = array(1 => 'Active', 0 => 'Inactive');
}
elseif (!isset($form_fields['code']))
{
  $form_fields = array
  (
    'code' => '', 'description' => '',
    'cycle_start_month' => -1, 'cycle_start_day' => -1, 'cycle_start_year' => -1,
    'cycle_end_month' => -1, 'cycle_end_day' => -1, 'cycle_end_year' => -1,
    'registration_start_month' => -1, 'registration_start_day' => -1, 'registration_start_year' => -1,
    'registration_end_month' => -1, 'registration_end_day' => -1, 'registration_end_year' => -1
  );
}
if (isset($form_fields['cycle_id']))
{
  /* get courses linked to this cycle */
  $course_query = <<< END_QUERY
    SELECT c.course_id, IFNULL(c.code, '') AS code, IFNULL(c.description, '') AS description, c.course_email, c.is_active, c.is_restricted, u.primary_email, u.secondary_email
    FROM courses AS c, users_courses AS uc, user_info AS u
    WHERE c.course_id = uc.course_id
    AND uc.user_id = u.user_id
    AND c.cycle_id = {$form_fields['cycle_id']}
    AND uc.user_role_id = 4
    ORDER BY c.code
END_QUERY;
  $result = mysql_query($course_query, $site_info['db_conn']);
  $course_email_array = $facilitator_email_array = array();
  while ($record = mysql_fetch_array($result))
  {
    if (!isset($course_details[$record['course_id']])) $course_details[$record['course_id']] = $record;
    if ($record['is_active'])
    {
      if (isset($record['course_email']) and strlen($record['course_email'])) $course_email_array[] = $record['course_email'].'@lists.udayton.edu';
      if (isset($record['primary_email']) and strlen($record['primary_email'])) $facilitator_email_array[] = strtolower($record['primary_email']);
      if (isset($record['secondary_email']) and strlen($record['secondary_email'])) $facilitator_email_array[] = strtolower($record['secondary_email']);
    }
  }
  if (count($course_email_array))
  {
    sort($course_email_array);
    $course_email_array = array_unique($course_email_array);
    $course_email_list = join(', ', $course_email_array);
  }
  else $course_email_list = '';
  if (count($facilitator_email_array))
  {
    sort($facilitator_email_array);
    $facilitator_email_array = array_unique($facilitator_email_array);
    $facilitator_email_list = join(', ', $facilitator_email_array);
  }
  else $facilitator_email_list = '';
  /* get event details */
  $entity_id = $form_fields['cycle_id'];
  $event_type_array = array(
    CYCLES_CREATE,
    CYCLES_UPDATE,
    CYCLES_ADD_COURSE
  );
  $event_history = vlc_get_event_history($event_type_array, $entity_id);
}
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = htmlspecialchars($value);
}
/* begin output variable */
$output = '';
/* return to previous page link */
if (isset($_SERVER['HTTP_REFERER']) and $return_url = strstr($_SERVER['HTTP_REFERER'], 'cms/')) $output .= '<p>'.vlc_internal_link('Return to Previous Page', $return_url).'</p>';
/* month, day, year arrays */
for ($i = 1; $i <= 12; $i++) $months_array[$i] = date('F', mktime(0,0,0,$i,1,0));
for ($i = 1; $i <= 31; $i++) $days_array[$i] = $i;
for ($i = 2000; $i <= 2020; $i++) $years_array[$i] = $i;
/* select boxes for cycle dates */
$cycle_start_month = vlc_select_box($months_array, 'array', 'cycle_start_month', $form_fields['cycle_start_month'], true);
$cycle_start_day = vlc_select_box($days_array, 'array', 'cycle_start_day', $form_fields['cycle_start_day'], true);
$cycle_start_year = vlc_select_box($years_array, 'array', 'cycle_start_year', $form_fields['cycle_start_year'], true);
$cycle_end_month = vlc_select_box($months_array, 'array', 'cycle_end_month', $form_fields['cycle_end_month'], true);
$cycle_end_day = vlc_select_box($days_array, 'array', 'cycle_end_day', $form_fields['cycle_end_day'], true);
$cycle_end_year = vlc_select_box($years_array, 'array', 'cycle_end_year', $form_fields['cycle_end_year'], true);
$registration_start_month = vlc_select_box($months_array, 'array', 'registration_start_month', $form_fields['registration_start_month'], true);
$registration_start_day = vlc_select_box($days_array, 'array', 'registration_start_day', $form_fields['registration_start_day'], true);
$registration_start_year = vlc_select_box($years_array, 'array', 'registration_start_year', $form_fields['registration_start_year'], true);
$registration_end_month = vlc_select_box($months_array, 'array', 'registration_end_month', $form_fields['registration_end_month'], true);
$registration_end_day = vlc_select_box($days_array, 'array', 'registration_end_day', $form_fields['registration_end_day'], true);
$registration_end_year = vlc_select_box($years_array, 'array', 'registration_end_year', $form_fields['registration_end_year'], true);
/* num courses array */
for ($i = 5; $i <= 50; $i += 5) $num_courses_array[$i] = $i;
/* add cycle details to output */
$output .= '<form method="post" action="cycle_action.php">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
if (isset($form_fields['cycle_id'])) $output .= '<tr><td><b>Cycle ID:</b></td><td colspan="3">'.$form_fields['cycle_id'].'<input type="hidden" name="cycle_id" value="'.$form_fields['cycle_id'].'"></td></tr>';
$output .= '<tr>';
$output .= '<td><b>Code:</b></td><td><input type="text" size="10" name="code" value="'.$form_fields['code'].'"></td>';
$output .= '<td><b>Description:</b></td><td><input type="text" size="30" name="description" value="'.$form_fields['description'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Cycle Start Date:</b></td><td>'.$cycle_start_month.'&nbsp;'.$cycle_start_day.'&nbsp;'.$cycle_start_year.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].cycle_start_year,document.forms[0].cycle_start_month,document.forms[0].cycle_start_day,false,false,this);"></td>';
$output .= '<td><b>Cycle End Date:</b></td><td>'.$cycle_end_month.'&nbsp;'.$cycle_end_day.'&nbsp;'.$cycle_end_year.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].cycle_end_year,document.forms[0].cycle_end_month,document.forms[0].cycle_end_day,false,false,this);"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Registration Start Date:</b></td><td>'.$registration_start_month.'&nbsp;'.$registration_start_day.'&nbsp;'.$registration_start_year.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].registration_start_year,document.forms[0].registration_start_month,document.forms[0].registration_start_day,false,false,this);"></td>';
$output .= '<td><b>Registration End Date:</b></td><td>'.$registration_end_month.'&nbsp;'.$registration_end_day.'&nbsp;'.$registration_end_year.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].registration_end_year,document.forms[0].registration_end_month,document.forms[0].registration_end_day,false,false,this);"></td>';
$output .= '</tr>';
$output .= '<tr><td colspan="4" align="center"><input type="submit" value="Submit"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
if (isset($form_fields['cycle_id']))
{
  $output .= '<h3>View Related Records:</h3>';
  $output .= '<ul>';
  $output .= '<li>'.vlc_internal_link('Courses', 'cms/courses.php?cycle_id='.$form_fields['cycle_id']).'</li>';
  $output .= '<li>'.vlc_internal_link('Course Registrations', 'cms/users_courses.php?cycle_id='.$form_fields['cycle_id']).'</li>';
  $output .= '<li>'.vlc_internal_link('Student Registration Orders', 'cms/student_orders.php?cycle_id='.$form_fields['cycle_id']).'</li>';
  $output .= '</ul>';
  $output .= '<h3>Update Student Course Status:</h3>';
  $output .= '<form method="post" action="cycle_action.php">';
  $output .= '<input type="hidden" name="cycle_id" value="'.$form_fields['cycle_id'].'">';
  $output .= '<input type="hidden" name="update_course_status" value="1">';
  $output .= '<p>To be used at the beginning of a cycle.</p>';
  $output .= '<ul><li>Click <b>&quot;Submit&quot;</b> to change Student Course Status from <b>&quot;Next Cycle&quot;</b> to <b>&quot;In Progress&quot;</b>: <input type="submit" value="Submit"></li></ul>';
  $output .= '</form>';
  $output .= '<h3>Update Facilitator Course Status:</h3>';
  $output .= '<form method="post" action="cycle_action.php">';
  $output .= '<input type="hidden" name="cycle_id" value="'.$form_fields['cycle_id'].'">';
  $output .= '<input type="hidden" name="update_course_status" value="2">';
  $output .= '<p>To be used at the end of a cycle.</p>';
  $output .= '<ul><li>Click <b>&quot;Submit&quot;</b> to change Facilitator Course Status from <b>&quot;In Progress&quot;</b> to <b>&quot;Complete&quot;</b>: <input type="submit" value="Submit"></li></ul>';
  $output .= '</form>';
  $output .= '<h3>Sympa E-Mail Addresses:</h3>';
  $output .= '<form onsubmit="return false">';
  $output .= '<textarea cols="80" rows="3" onfocus="select_all(this, false)">'.$course_email_list.'</textarea>';
  $output .= '</form>';
  $output .= '<h3>Facilitator E-Mail Addresses:</h3>';
  $output .= '<form onsubmit="return false">';
  $output .= '<textarea cols="80" rows="3" onfocus="select_all(this, false)">'.$facilitator_email_list.'</textarea>';
  $output .= '</form>';
  $evaluation_options_array = array(1 => 'Grouped by Question (HTML)', 'Grouped by Student (HTML)', 'Export to Excel (CSV)', 'Printable Table (PDF - Portrait)', 'Printable Table (PDF - Landscape)');
  $output .= '<h3>Course Evaluations:</h3>';
  $output .= '<form method="get" action="evaluations.php">';
  $output .= '<input type="hidden" name="cycle" value="'.$form_fields['cycle_id'].'">';
  $output .= '<p>Select a Format: '.vlc_select_box($evaluation_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  $format_options_array = array(1 => 'Printable Table (HTML)', 'Export to Excel (CSV)', 'Printable Table (PDF - Portrait)', 'Printable Table (PDF - Landscape)');
  $output .= '<h3>Course Roster:</h3>';
  $output .= '<form method="get" action="roster.php">';
  $output .= '<input type="hidden" name="cycle" value="'.$form_fields['cycle_id'].'">';
  $output .= '<p>Select a Format: '.vlc_select_box($format_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  $output .= '<h3>Certificate Data:</h3>';
  $output .= '<form method="get" action="certificate.php">';
  $output .= '<input type="hidden" name="cycle" value="'.$form_fields['cycle_id'].'">';
  $output .= '<p>Select a Format: '.vlc_select_box($format_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  $output .= '<h2>Courses:</h2>';
  $output .= '<form method="get" action="cycle_details.php#add-courses">';
  $output .= '<input type="hidden" name="cycle" value="'.$form_fields['cycle_id'].'">';
  $output .= '<p>The following courses are linked to this cycle.</p>';
  $output .= '<ul>';
  $output .= '<li>To view additional course details, click the <b>&quot;Course ID&quot;</b> link.</li>';
  $output .= '<li>To add courses to this cycle, select the number of courses to add and click <b>&quot;Submit&quot;</b>: '.vlc_select_box($num_courses_array, 'array', 'num', -1, true).' <input type="submit" value="Submit"></li>';
  $output .= '</ul>';
  $output .= '</form>';
  $output .= '<table border="1" cellpadding="5" cellspacing="0">';
  $output .= '<tr><th>&nbsp;</th><th>Course ID</th><th>Code</th><th>Description</th><th>Course Status</th><th>Restricted</th></tr>';
  if (isset($course_details) and is_array($course_details) and count($course_details))
  {
    $i = 1;
    foreach ($course_details as $course)
    {
      $output .= '<tr><td>'.$i++.'.</td><td align="center">'.vlc_internal_link($course['course_id'], 'cms/course_details.php?course='.$course['course_id']).'</td><td align="center">'.$course['code'].'</td><td>'.$course['description'].'</td><td>'.($course['is_active'] ? 'Active' : 'Inactive').'</td><td>'.($course['is_restricted'] ? 'Yes' : 'No').'</td></tr>';
    }
  }
  else $output .= '<tr><td colspan="6" align="center">No Courses Found.</td></tr>';
  $output .= '<tr><td colspan="6">&nbsp;</td></tr>';
  $output .= '</table>';
  $output .= '<a name="add-courses"></a><h3>Add Courses:</h3>';
  $output .= '<form method="post" action="cycle_action.php">';
  $output .= '<input type="hidden" name="cycle_id" value="'.$form_fields['cycle_id'].'">';
  $output .= '<table border="1" cellpadding="5" cellspacing="0">';
  $i = 1;
  foreach ($form_fields['courses'] as $course)
  {
    $output .= '<tr>';
    $output .= '<td rowspan="8" valign="top">'.$i.'.</td>';
    $output .= '<td><b>Facilitator:</b></td>';
    $output .= '<td colspan="3">'.vlc_select_box($facilitator_options_array, 'array', 'courses['.$i.'][facilitator_id]', $course['facilitator_id'], false).'</td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><b>Cycle:</b></td>';
    $output .= '<td>'.$form_fields['code'].'</td>';
    $output .= '<td><b>Course Status:</b></td>';
    $output .= '<td>'.vlc_select_box($is_active_options_array, 'array', 'courses['.$i.'][is_active]', $course['is_active'], true).'</td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><b>Subject:</b></td>';
    $output .= '<td>'.vlc_select_box($course_subject_options_array, 'array', 'courses['.$i.'][course_subject_id]', $course['course_subject_id'], false).'</td>';
    $output .= '<td><b>Section:</b></td>';
    $output .= '<td>'.vlc_select_box($course_section_options_array, 'array', 'courses['.$i.'][section_id]', $course['section_id'], false).'</td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><b>Code:</b></td>';
    $output .= '<td><input type="text" size="10" name="courses['.$i.'][code]" value="'.$course['code'].'"></td>';
    $output .= '<td><b>Description:</b></td>';
    $output .= '<td><input type="text" size="30" name="courses['.$i.'][description]" value="'.$course['description'].'"></td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><b>Sympa:</b></td>';
    $output .= '<td><input type="text" size="30" name="courses['.$i.'][course_email]" value="'.$course['course_email'].'"></td>';
    $output .= '<td><b>List Course As:</b></td>';
    $output .= '<td>'.vlc_select_box($registration_type_options_array, 'array', 'courses['.$i.'][registration_type_id]', $course['registration_type_id'], true).'</td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><b>Restricted:</b></td>';
    $output .= '<td><input type="checkbox" name="courses['.$i.'][is_restricted]" value="1"'.((isset($course['is_restricted']) and $course['is_restricted']) ? ' checked="checked"' : '').'></td>';
    $output .= '<td><b>Sample:</b></td>';
    $output .= '<td><input type="checkbox" name="courses['.$i.'][is_sample]" value="1"'.((isset($course['is_sample']) and $course['is_sample']) ? ' checked="checked"' : '').'></td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><b>Facilitator Start:</b></td>';
    $output .= '<td><nobr>'.vlc_select_box($months_array, 'array', 'courses['.$i.'][facilitator_start_month]', $course['facilitator_start_month'], true).' '.vlc_select_box($days_array, 'array', 'courses['.$i.'][facilitator_start_day]', $course['facilitator_start_day'], true).' '.vlc_select_box($years_array, 'array', 'courses['.$i.'][facilitator_start_year]', $course['facilitator_start_year'], true).'</nobr></td>';
    $output .= '<td><b>Facilitator End:</b></td>';
    $output .= '<td><nobr>'.vlc_select_box($months_array, 'array', 'courses['.$i.'][facilitator_end_month]', $course['facilitator_end_month'], true).' '.vlc_select_box($days_array, 'array', 'courses['.$i.'][facilitator_end_day]', $course['facilitator_end_day'], true).' '.vlc_select_box($years_array, 'array', 'courses['.$i.'][facilitator_end_year]', $course['facilitator_end_year'], true).'</nobr></td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><b>Student Start:</b></td>';
    $output .= '<td><nobr>'.vlc_select_box($months_array, 'array', 'courses['.$i.'][student_start_month]', $course['student_start_month'], true).' '.vlc_select_box($days_array, 'array', 'courses['.$i.'][student_start_day]', $course['student_start_day'], true).' '.vlc_select_box($years_array, 'array', 'courses['.$i.'][student_start_year]', $course['student_start_year'], true).'</nobr></td>';
    $output .= '<td><b>Student End:</b></td>';
    $output .= '<td><nobr>'.vlc_select_box($months_array, 'array', 'courses['.$i.'][student_end_month]', $course['student_end_month'], true).' '.vlc_select_box($days_array, 'array', 'courses['.$i.'][student_end_day]', $course['student_end_day'], true).' '.vlc_select_box($years_array, 'array', 'courses['.$i.'][student_end_year]', $course['student_end_year'], true).'</nobr></td>';
    $output .= '</tr>';
    $i++;
  }
  $output .= '<tr><td colspan="5" align="center"><input type="submit" value="Submit"></td></tr>';
  $output .= '</table>';
  $output .= '</form>';
  $output .= $event_history;
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
