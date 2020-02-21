<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'courses';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* initialize form fields variable */
$form_fields = array
(
  'start' => 0,
  'num' => 10,
  'course_id' => '',
  'code' => '',
  'description' => '',
  'cycle_id' => 'NULL',
  'course_subject_id' => 'NULL',
  'section_id' => 'NULL',
  'is_restricted' => -1,
  'is_sample' => -1,
  'is_active' => -1,
  'facilitator_id' => 'NULL',
  'facilitator_start_min_year' => 2000,
  'facilitator_start_min_month' => 1,
  'facilitator_start_min_day' => 1,
  'facilitator_start_max_year' => 2020,
  'facilitator_start_max_month' => 12,
  'facilitator_start_max_day' => 31,
  'facilitator_end_min_year' => 2000,
  'facilitator_end_min_month' => 1,
  'facilitator_end_min_day' => 1,
  'facilitator_end_max_year' => 2020,
  'facilitator_end_max_month' => 12,
  'facilitator_end_max_day' => 31,
  'student_start_min_year' => 2000,
  'student_start_min_month' => 1,
  'student_start_min_day' => 1,
  'student_start_max_year' => 2020,
  'student_start_max_month' => 12,
  'student_start_max_day' => 31,
  'student_end_min_year' => 2000,
  'student_end_min_month' => 1,
  'student_end_min_day' => 1,
  'student_end_max_year' => 2020,
  'student_end_max_month' => 12,
  'student_end_max_day' => 31,
  'sort_by' => array('NULL', 'NULL', 'NULL'),
  'sort_dir' => array('NULL', 'NULL', 'NULL')
);
/* merge url variables into form fields variable */
if (isset($_GET)) $form_fields = array_merge($form_fields, $_GET);
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = addslashes($value);
}
/* query filter parameters */
if (!is_numeric($form_fields['facilitator_start_min_year'])) $form_fields['facilitator_start_min_year'] = 2000;
if (!is_numeric($form_fields['facilitator_start_min_month'])) $form_fields['facilitator_start_min_month'] = 1;
if (!is_numeric($form_fields['facilitator_start_min_day'])) $form_fields['facilitator_start_min_day'] = 1;
if (!is_numeric($form_fields['facilitator_start_max_year'])) $form_fields['facilitator_start_max_year'] = 2020;
if (!is_numeric($form_fields['facilitator_start_max_month'])) $form_fields['facilitator_start_max_month'] = 12;
if (!is_numeric($form_fields['facilitator_start_max_day'])) $form_fields['facilitator_start_max_day'] = 31;
if (!is_numeric($form_fields['facilitator_end_min_year'])) $form_fields['facilitator_end_min_year'] = 2000;
if (!is_numeric($form_fields['facilitator_end_min_month'])) $form_fields['facilitator_end_min_month'] = 1;
if (!is_numeric($form_fields['facilitator_end_min_day'])) $form_fields['facilitator_end_min_day'] = 1;
if (!is_numeric($form_fields['facilitator_end_max_year'])) $form_fields['facilitator_end_max_year'] = 2020;
if (!is_numeric($form_fields['facilitator_end_max_month'])) $form_fields['facilitator_end_max_month'] = 12;
if (!is_numeric($form_fields['facilitator_end_max_day'])) $form_fields['facilitator_end_max_day'] = 31;
if (!is_numeric($form_fields['student_start_min_year'])) $form_fields['student_start_min_year'] = 2000;
if (!is_numeric($form_fields['student_start_min_month'])) $form_fields['student_start_min_month'] = 1;
if (!is_numeric($form_fields['student_start_min_day'])) $form_fields['student_start_min_day'] = 1;
if (!is_numeric($form_fields['student_start_max_year'])) $form_fields['student_start_max_year'] = 2020;
if (!is_numeric($form_fields['student_start_max_month'])) $form_fields['student_start_max_month'] = 12;
if (!is_numeric($form_fields['student_start_max_day'])) $form_fields['student_start_max_day'] = 31;
if (!is_numeric($form_fields['student_end_min_year'])) $form_fields['student_end_min_year'] = 2000;
if (!is_numeric($form_fields['student_end_min_month'])) $form_fields['student_end_min_month'] = 1;
if (!is_numeric($form_fields['student_end_min_day'])) $form_fields['student_end_min_day'] = 1;
if (!is_numeric($form_fields['student_end_max_year'])) $form_fields['student_end_max_year'] = 2020;
if (!is_numeric($form_fields['student_end_max_month'])) $form_fields['student_end_max_month'] = 12;
if (!is_numeric($form_fields['student_end_max_day'])) $form_fields['student_end_max_day'] = 31;
$facilitator_start_min = $form_fields['facilitator_start_min_year'].'-'.$form_fields['facilitator_start_min_month'].'-'.$form_fields['facilitator_start_min_day'];
$facilitator_start_max = $form_fields['facilitator_start_max_year'].'-'.$form_fields['facilitator_start_max_month'].'-'.$form_fields['facilitator_start_max_day'];
$facilitator_end_min = $form_fields['facilitator_end_min_year'].'-'.$form_fields['facilitator_end_min_month'].'-'.$form_fields['facilitator_end_min_day'];
$facilitator_end_max = $form_fields['facilitator_end_max_year'].'-'.$form_fields['facilitator_end_max_month'].'-'.$form_fields['facilitator_end_max_day'];
$student_start_min = $form_fields['student_start_min_year'].'-'.$form_fields['student_start_min_month'].'-'.$form_fields['student_start_min_day'];
$student_start_max = $form_fields['student_start_max_year'].'-'.$form_fields['student_start_max_month'].'-'.$form_fields['student_start_max_day'];
$student_end_min = $form_fields['student_end_min_year'].'-'.$form_fields['student_end_min_month'].'-'.$form_fields['student_end_min_day'];
$student_end_max = $form_fields['student_end_max_year'].'-'.$form_fields['student_end_max_month'].'-'.$form_fields['student_end_max_day'];
$where_clause = '';
$where_clause .= " AND c.facilitator_start >= '$facilitator_start_min'";
$where_clause .= " AND c.facilitator_start <= '$facilitator_start_max'";
$where_clause .= " AND c.facilitator_end >= '$facilitator_end_min'";
$where_clause .= " AND c.facilitator_end <= '$facilitator_end_max'";
$where_clause .= " AND c.student_start >= '$student_start_min'";
$where_clause .= " AND c.student_start <= '$student_start_max'";
$where_clause .= " AND c.student_end >= '$student_end_min'";
$where_clause .= " AND c.student_end <= '$student_end_max'";
if (is_numeric($form_fields['course_id']) and $form_fields['course_id'] > 0) $where_clause .= ' AND c.course_id = '.$form_fields['course_id'];
if (strlen($form_fields['code']) > 0) $where_clause .= " AND c.code LIKE '".$form_fields['code']."'";
if (strlen($form_fields['description']) > 0) $where_clause .= " AND c.description LIKE '".$form_fields['description']."'";
if (is_numeric($form_fields['cycle_id']) and $form_fields['cycle_id'] > 0) $where_clause .= ' AND c.cycle_id = '.$form_fields['cycle_id'];
if (is_numeric($form_fields['course_subject_id']) and $form_fields['course_subject_id'] > 0) $where_clause .= ' AND c.course_subject_id = '.$form_fields['course_subject_id'];
if (is_numeric($form_fields['section_id']) and $form_fields['section_id'] > 0) $where_clause .= ' AND c.section_id = '.$form_fields['section_id'];
if (is_numeric($form_fields['is_restricted']) and $form_fields['is_restricted'] != -1) $where_clause .= ' AND c.is_restricted = '.$form_fields['is_restricted'];
else $form_fields['is_restricted'] = -1;
if (is_numeric($form_fields['is_sample']) and $form_fields['is_sample'] != -1) $where_clause .= ' AND c.is_sample = '.$form_fields['is_sample'];
else $form_fields['is_sample'] = -1;
if (is_numeric($form_fields['is_active']) and $form_fields['is_active'] != -1) $where_clause .= ' AND c.is_active = '.$form_fields['is_active'];
else $form_fields['is_active'] = -1;
if (is_numeric($form_fields['facilitator_id']) and $form_fields['facilitator_id'] > 0) $where_clause .= ' AND u.user_id = '.$form_fields['facilitator_id'];
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = stripslashes($value);
}
/* query sorting parameters */
$sort_field_array = array(1 => 'c.course_id', 'c.code', 'c.description', 'y.code', 's.description', 'n.code', 'c.is_active', 'c.is_restricted', 'u.last_name'); /* , 'c.facilitator_start', 'c.facilitator_end', 'c.student_start', 'c.student_end' */
$sort_options_array = array(1 => 'Course ID', 'Code', 'Description', 'Cycle', 'Course Subject', 'Section', 'Course Status', 'Restricted', 'Facilitator'); /* , 'Facilitator Start', 'Facilitator End', 'Student Start', 'Student End' */
$sort_dir_array = array(1 => 'ASC', 2 => 'DESC');
$order_by_array = array();
foreach ($form_fields['sort_by'] as $key => $value)
{
  if (isset($sort_field_array[$value]))
  {
    $temp_order_by = $sort_field_array[$value];
    if (isset($sort_dir_array[$form_fields['sort_dir'][$key]])) $temp_order_by .= ' '.$sort_dir_array[$form_fields['sort_dir'][$key]];
    $order_by_array[] = $temp_order_by;
  }
  $sort_post_vars_array[] = vlc_select_box($sort_options_array, 'array', 'sort_by[]', $value, false).' '.vlc_select_box($sort_dir_array, 'array', 'sort_dir[]', $form_fields['sort_dir'][$key], false);
  $sort_get_vars_array[] = 'sort_by[]='.$value.'&sort_dir[]='.$form_fields['sort_dir'][$key];
}
if (count($order_by_array)) $order_clause = ' ORDER BY '.join(', ', $order_by_array);
else $order_clause = '';
$sort_post_vars_string = join('</td></tr><tr><td colspan="3">then by ', $sort_post_vars_array);
$sort_get_vars_string = join('&', $sort_get_vars_array);
/* get total number of rows (without limit clause) */
$total_rows_query = <<< END_QUERY
  SELECT c.course_id
  FROM courses AS c
    LEFT JOIN users_courses AS uc ON c.course_id = uc.course_id AND uc.user_role_id = 4
    LEFT JOIN users AS u ON uc.user_id = u.user_id,
    cycles AS y, course_subjects AS s, sections AS n
  WHERE c.cycle_id = y.cycle_id
  AND c.course_subject_id = s.course_subject_id
  AND c.section_id = n.section_id
  $where_clause
  GROUP BY c.course_id
END_QUERY;
$result = mysql_query($total_rows_query, $site_info['db_conn']);
$total_rows = mysql_num_rows($result);
/* query limit parameters */
$default_start = 0;
$default_num = 10;
if (!is_numeric($form_fields['start']) or $form_fields['start'] < 0) $form_fields['start'] = $default_start;
if (!is_numeric($form_fields['num']) or $form_fields['num'] <= 0) $form_fields['num'] = $default_num;
$limit_clause = ' LIMIT '.$form_fields['start'].', '.$form_fields['num'];
/*******************************************************************************
** export data
*/
if (isset($form_fields['export_data']) and isset($form_fields['export_format']))
{
  /* get export data */
  $export_query = <<< END_QUERY
    SELECT c.course_id, c.code, c.description, y.code, s.description, n.code, IF(c.is_active, 'Active', 'Inactive') AS is_active, IF(c.is_restricted, 'Yes', 'No') AS is_restricted, u.first_name, u.last_name
    FROM courses AS c
      LEFT JOIN users_courses AS uc ON c.course_id = uc.course_id AND uc.user_role_id = 4
      LEFT JOIN users AS u ON uc.user_id = u.user_id,
      cycles AS y, course_subjects AS s, sections AS n
    WHERE c.cycle_id = y.cycle_id
    AND c.course_subject_id = s.course_subject_id
    AND c.section_id = n.section_id
END_QUERY;
  if (in_array($form_fields['export_data'], array(1, 2))) $export_query .= $where_clause;
  $export_query .= ' GROUP BY c.course_id';
  $export_query .= $order_clause;
  if ($form_fields['export_data'] == 1) $export_query .= $limit_clause;
  $result = mysql_query($export_query, $site_info['db_conn']);
  $export_array = array();
  $export_array[] = array('Course ID', 'Code', 'Description', 'Cycle', 'Course Subject', 'Section', 'Course Status', 'Restricted', 'Facilitator First Name', 'Facilitator Last Name');
  while ($record = mysql_fetch_row($result)) $export_array[] = $record;
  switch ($form_fields['export_format'])
  {
    case 1:
      vlc_export_data($export_array, 'courses', 1);
      break;
    case 2:
      vlc_export_data($export_array, 'courses', 2, 'P');
      break;
    case 3:
      vlc_export_data($export_array, 'courses', 2, 'L');
      break;
  }
}
/* url parameters */
$url_parameters = '';
foreach ($form_fields as $key => $value)
{
  if (!in_array($key, array('start', 'num', 'sort_by', 'sort_dir')))
  {
    $value = urlencode($value);
    $url_parameters .= "$key=$value&";
  }
}
$url_parameters_without_sort = $url_parameters;
$url_parameters .= $sort_get_vars_string;
$url_parameters .= '&num='.$form_fields['num'];
/* "first" link and "previous" link */
$first_start = 0;
if ($form_fields['start'] == 0)
{
  $first_link = '&laquo; First';
  $prev_link = '&lsaquo; Prev';
}
else
{
  $first_url = 'cms/courses.php?'.$url_parameters.'&start='.$first_start;
  $first_link = vlc_internal_link('&laquo; First', $first_url);
  $prev_start = $form_fields['start'] - $form_fields['num'];
  if ($prev_start < 0) $prev_start = 0;
  $prev_url = 'cms/courses.php?'.$url_parameters.'&start='.$prev_start;
  $prev_link = vlc_internal_link('&lsaquo; Prev', $prev_url);
}
/* "next" link and "last" link */
$next_start = $form_fields['start'] + $form_fields['num'];
if ($next_start >= $total_rows)
{
  $next_link = 'Next &rsaquo;';
  $last_link = 'Last &raquo;';
  $last_start = $form_fields['start'];
}
else
{
  $next_url = 'cms/courses.php?'.$url_parameters.'&start='.$next_start;
  $next_link = vlc_internal_link('Next &rsaquo;', $next_url);
  /* determine the index of the last record of the search results (the index of the first record is "0") */
  $last_index = $total_rows - 1;
  /* determine the index of the first record of the last page of search results */
  $last_start = $last_index - ($last_index % $form_fields['num']);
  $last_url = 'cms/courses.php?'.$url_parameters.'&start='.$last_start;
  $last_link = vlc_internal_link('Last &raquo;', $last_url);
}
/* search results page numbers */
$page_links_array = array();
$last_page = 0;
if ($total_rows > 0)
{
  $page_nav_array = array();
  /* determine first page, current page, and last page */
  $first_page = $first_start / $form_fields['num'] + 1;
  $current_page = $form_fields['start'] / $form_fields['num'] + 1;
  $last_page = $last_start / $form_fields['num'] + 1;
  /* add the first 3 pages to the page navigation array */
  for ($page_num = $first_page; $page_num <= 3 and $page_num <= $last_page; $page_num++)
  {
    $page_nav_array[$page_num] = ($page_num - 1) * $form_fields['num'];
  }
  /* if the current page is not the first page, add the current page and the pages immediately before and after the current page to the page navigation array */
  if ($current_page > 2)
  {
    for ($page_num = $current_page - 2; $page_num <= $current_page + 2 and $page_num <= $last_page; $page_num++)
    {
      $page_nav_array[$page_num] = ($page_num - 1) * $form_fields['num'];
    }
  }
  /* if there are more than 3 pages of results, add the last 3 pages to the page navigation array */
  if ($last_page > 3)
  {
    for ($page_num = $last_page - 2; $page_num <= $last_page; $page_num++)
    {
      $page_nav_array[$page_num] = ($page_num - 1) * $form_fields['num'];
    }
  }
  /* compile the page links array */
  $prev_page_num = 0;
  foreach ($page_nav_array as $page_num => $page_start)
  {
    if ($page_num != $prev_page_num + 1) $page_links_array[] = '...';
    if ($page_num == $current_page) $page_links_array[] = '<b>'.$page_num.'</b>';
    else
    {
      $page_link_url = 'cms/courses.php?'.$url_parameters.'&start='.$page_start;
      $page_links_array[] = vlc_internal_link($page_num, $page_link_url);
    }
    $prev_page_num = $page_num;
  }
}
$page_num_links = join(' ', $page_links_array);
/* search results navigation */
$nav_links = "<p align=\"center\">$first_link $prev_link $page_num_links $next_link $last_link</p>";
/* search results start and end */
$list_start = $form_fields['start'] + 1;
$list_end = $form_fields['start'] + $form_fields['num'];
if ($list_end > $total_rows) $list_end = $total_rows;
/* year, month, day arrays */
for ($i = 2000; $i <= 2020; $i++) $years_array[$i] = $i;
for ($i = 1; $i <= 12; $i++) $months_array[$i] = date('F', mktime(0,0,0,$i,1,0));
for ($i = 1; $i <= 31; $i++) $days_array[$i] = $i;
/* select boxes for course dates */
$facilitator_start_min_year_dropdown = vlc_select_box($years_array, 'array', 'facilitator_start_min_year', $form_fields['facilitator_start_min_year'], true);
$facilitator_start_min_month_dropdown = vlc_select_box($months_array, 'array', 'facilitator_start_min_month', $form_fields['facilitator_start_min_month'], true);
$facilitator_start_min_day_dropdown = vlc_select_box($days_array, 'array', 'facilitator_start_min_day', $form_fields['facilitator_start_min_day'], true);
$facilitator_start_max_year_dropdown = vlc_select_box($years_array, 'array', 'facilitator_start_max_year', $form_fields['facilitator_start_max_year'], true);
$facilitator_start_max_month_dropdown = vlc_select_box($months_array, 'array', 'facilitator_start_max_month', $form_fields['facilitator_start_max_month'], true);
$facilitator_start_max_day_dropdown = vlc_select_box($days_array, 'array', 'facilitator_start_max_day', $form_fields['facilitator_start_max_day'], true);
$facilitator_end_min_year_dropdown = vlc_select_box($years_array, 'array', 'facilitator_end_min_year', $form_fields['facilitator_end_min_year'], true);
$facilitator_end_min_month_dropdown = vlc_select_box($months_array, 'array', 'facilitator_end_min_month', $form_fields['facilitator_end_min_month'], true);
$facilitator_end_min_day_dropdown = vlc_select_box($days_array, 'array', 'facilitator_end_min_day', $form_fields['facilitator_end_min_day'], true);
$facilitator_end_max_year_dropdown = vlc_select_box($years_array, 'array', 'facilitator_end_max_year', $form_fields['facilitator_end_max_year'], true);
$facilitator_end_max_month_dropdown = vlc_select_box($months_array, 'array', 'facilitator_end_max_month', $form_fields['facilitator_end_max_month'], true);
$facilitator_end_max_day_dropdown = vlc_select_box($days_array, 'array', 'facilitator_end_max_day', $form_fields['facilitator_end_max_day'], true);
$student_start_min_year_dropdown = vlc_select_box($years_array, 'array', 'student_start_min_year', $form_fields['student_start_min_year'], true);
$student_start_min_month_dropdown = vlc_select_box($months_array, 'array', 'student_start_min_month', $form_fields['student_start_min_month'], true);
$student_start_min_day_dropdown = vlc_select_box($days_array, 'array', 'student_start_min_day', $form_fields['student_start_min_day'], true);
$student_start_max_year_dropdown = vlc_select_box($years_array, 'array', 'student_start_max_year', $form_fields['student_start_max_year'], true);
$student_start_max_month_dropdown = vlc_select_box($months_array, 'array', 'student_start_max_month', $form_fields['student_start_max_month'], true);
$student_start_max_day_dropdown = vlc_select_box($days_array, 'array', 'student_start_max_day', $form_fields['student_start_max_day'], true);
$student_end_min_year_dropdown = vlc_select_box($years_array, 'array', 'student_end_min_year', $form_fields['student_end_min_year'], true);
$student_end_min_month_dropdown = vlc_select_box($months_array, 'array', 'student_end_min_month', $form_fields['student_end_min_month'], true);
$student_end_min_day_dropdown = vlc_select_box($days_array, 'array', 'student_end_min_day', $form_fields['student_end_min_day'], true);
$student_end_max_year_dropdown = vlc_select_box($years_array, 'array', 'student_end_max_year', $form_fields['student_end_max_year'], true);
$student_end_max_month_dropdown = vlc_select_box($months_array, 'array', 'student_end_max_month', $form_fields['student_end_max_month'], true);
$student_end_max_day_dropdown = vlc_select_box($days_array, 'array', 'student_end_max_day', $form_fields['student_end_max_day'], true);
/* get cycles */
$cycle_query = <<< END_QUERY
  SELECT cycle_id, IFNULL(code, cycle_id) AS code
  FROM cycles
  ORDER BY cycle_start DESC
END_QUERY;
$result = mysql_query($cycle_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $cycle_options_array[$record['cycle_id']] = $record['code'];
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
/* restricted options */
$restricted_options_array = array('No', 'Yes');
/* sample options */
$sample_options_array = array('No', 'Yes');
/* course status values */
$is_active_options_array = array(1 => 'Active', 0 => 'Inactive');
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
/* build array for "export data" select box */
$export_data_array = array(1 => 'this page of search results', 'all search results', 'all records');
/* build array for "export format" select box */
$export_format_array = array(1 => 'CSV', 'PDF (Portrait)', 'PDF (Landscape)');
/* build hidden fields for export form */
$export_hidden_fields = vlc_create_hidden_fields($form_fields);
/* build array for "num" select box */
$num_results_array = array();
for ($i = 10; $i <= 100; $i+=10) $num_results_array[$i] = $i;
/* num courses array */
for ($i = 5; $i <= 50; $i += 5) $num_courses_array[$i] = $i;
/* results/pages found labels (singular/plural) */
$results_label = ($total_rows == 1) ? 'Result' : 'Results';
$pages_label = ($last_page == 1) ? 'Page' : 'Pages';
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = htmlspecialchars($value);
}
/* begin building page output */
$output = '';
$output .= '<p align="center">'.vlc_internal_link('Click here to create a new course.', 'cms/course_details.php').'</p>';
$output .= '<form method="get" action="courses.php">';
$output .= $export_hidden_fields;
$output .= '<p align="center">Export '.vlc_select_box($export_data_array, 'array', 'export_data', -1, true).' to '.vlc_select_box($export_format_array, 'array', 'export_format', -1, true).' <input type="submit" value="Go"></p>';
$output .= '</form>';
$output .= '<p align="center"><b>'.$total_rows.'</b> '.$results_label.' Found (<b>'.$last_page.'</b> '.$pages_label.')</p>';
$output .= '<form method="get" action="courses.php">';
$output .= '<input type="hidden" name="start" value="0">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Course ID:</b></nobr></td>';
$output .= '<td><div class="yui-skin-sam"><div id="ac_course_id" class="autocomplete"><input type="text" name="course_id" id="ac_course_id_field" value="'.$form_fields['course_id'].'"><div id="ac_course_id_container"></div></div></div></td>';
$output .= '<td align="right"><nobr><b>Course Status:</b></nobr></td>';
$output .= '<td>'.vlc_select_box($is_active_options_array, 'array', 'is_active', $form_fields['is_active'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Code:</b></nobr></td>';
$output .= '<td><div class="yui-skin-sam"><div id="ac_code" class="autocomplete"><input type="text" name="code" id="ac_code_field" value="'.$form_fields['code'].'"><div id="ac_code_container"></div></div></div></td>';
$output .= '<td align="right"><nobr><b>Description:</b></nobr></td>';
$output .= '<td><div class="yui-skin-sam"><div id="ac_description" class="autocomplete"><input type="text" name="description" id="ac_description_field" value="'.$form_fields['description'].'"><div id="ac_description_container"></div></div></div></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Cycle:</b></nobr></td>';
$output .= '<td>'.vlc_select_box($cycle_options_array, 'array', 'cycle_id', $form_fields['cycle_id'], false).'</td>';
$output .= '<td align="right"><nobr><b>Subject:</b></nobr></td>';
$output .= '<td>'.vlc_select_box($course_subject_options_array, 'array', 'course_subject_id', $form_fields['course_subject_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Facilitator:</b></nobr></td>';
$output .= '<td>'.vlc_select_box($facilitator_options_array, 'array', 'facilitator_id', $form_fields['facilitator_id'], false).'</td>';
$output .= '<td align="right"><nobr><b>Section:</b></nobr></td>';
$output .= '<td>'.vlc_select_box($course_section_options_array, 'array', 'section_id', $form_fields['section_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Restricted:</b></nobr></td>';
$output .= '<td>'.vlc_select_box($restricted_options_array, 'array', 'is_restricted', $form_fields['is_restricted'], false).'</td>';
$output .= '<td align="right"><nobr><b>Sample:</b></nobr></td>';
$output .= '<td>'.vlc_select_box($sample_options_array, 'array', 'is_sample', $form_fields['is_sample'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Facilitator Start:</b></nobr></td>';
$output .= '<td colspan="3">Between '.$facilitator_start_min_month_dropdown.' '.$facilitator_start_min_day_dropdown.' '.$facilitator_start_min_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].facilitator_start_min_year,document.forms[1].facilitator_start_min_month,document.forms[1].facilitator_start_min_day,false,false,this);"> and '.$facilitator_start_max_month_dropdown.' '.$facilitator_start_max_day_dropdown.' '.$facilitator_start_max_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].facilitator_start_max_year,document.forms[1].facilitator_start_max_month,document.forms[1].facilitator_start_max_day,false,false,this);"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Facilitator End:</b></nobr></td>';
$output .= '<td colspan="3">Between '.$facilitator_end_min_month_dropdown.' '.$facilitator_end_min_day_dropdown.' '.$facilitator_end_min_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].facilitator_end_min_year,document.forms[1].facilitator_end_min_month,document.forms[1].facilitator_end_min_day,false,false,this);"> and '.$facilitator_end_max_month_dropdown.' '.$facilitator_end_max_day_dropdown.' '.$facilitator_end_max_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].facilitator_end_max_year,document.forms[1].facilitator_end_max_month,document.forms[1].facilitator_end_max_day,false,false,this);"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Student Start:</b></nobr></td>';
$output .= '<td colspan="3">Between '.$student_start_min_month_dropdown.' '.$student_start_min_day_dropdown.' '.$student_start_min_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].student_start_min_year,document.forms[1].student_start_min_month,document.forms[1].student_start_min_day,false,false,this);"> and '.$student_start_max_month_dropdown.' '.$student_start_max_day_dropdown.' '.$student_start_max_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].student_start_max_year,document.forms[1].student_start_max_month,document.forms[1].student_start_max_day,false,false,this);"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Student End:</b></nobr></td>';
$output .= '<td colspan="3">Between '.$student_end_min_month_dropdown.' '.$student_end_min_day_dropdown.' '.$student_end_min_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].student_end_min_year,document.forms[1].student_end_min_month,document.forms[1].student_end_min_day,false,false,this);"> and '.$student_end_max_month_dropdown.' '.$student_end_max_day_dropdown.' '.$student_end_max_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].student_end_max_year,document.forms[1].student_end_max_month,document.forms[1].student_end_max_day,false,false,this);"></td>';
$output .= '</tr>';
$output .= '<tr><td align="right" valign="top" rowspan="3"><nobr><b>Sort By:</b></nobr></td><td colspan="3">'.$sort_post_vars_string.'</td></tr>';
$output .= '<tr><td colspan="4" align="center"><nobr><b>Results / Page:</b></nobr> '.vlc_select_box($num_results_array, 'array', 'num', $form_fields['num'], true).' <input type="submit" value="Submit"> <input type="button" value="Clear Search Fields" onclick="clear_fields(this.form); this.form.num.selectedIndex = 0;"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
/* search results navigation */
if ($total_rows > 0) $output .= "<p align=\"right\"><b>Showing $list_start - $list_end of $total_rows (Page $current_page of $last_page)</b></p>";
$output .= $nav_links;
/* get courses */
$course_query = <<< END_QUERY
  SELECT c.course_id, IFNULL(c.code, '--') AS code, IFNULL(c.description, '--') AS description,
    c.cycle_id, c.course_subject_id, c.section_id, c.is_active, c.is_restricted, u.user_id AS facilitator_id
  FROM courses AS c
    LEFT JOIN users_courses AS uc ON c.course_id = uc.course_id AND uc.user_role_id = 4
    LEFT JOIN users AS u ON uc.user_id = u.user_id,
    cycles AS y, course_subjects AS s, sections AS n
  WHERE c.cycle_id = y.cycle_id
  AND c.course_subject_id = s.course_subject_id
  AND c.section_id = n.section_id
  $where_clause
  GROUP BY c.course_id
END_QUERY;
$course_query .= $order_clause;
$course_query .= $limit_clause;
$result = mysql_query($course_query, $site_info['db_conn']);
/* compile search results */
if (mysql_num_rows($result) > 0)
{
  /* build results table */
  $result_num = $list_start;
  $output .= '<table border="1" cellpadding="5" cellspacing="0">';
  $output .= '<tr>';
  $output .= '<th>&nbsp;</th>';
  /* build column headers with sort links */
  foreach ($sort_options_array as $sort_by_key => $sort_by_value)
  {
    $sort_asc_url = 'cms/courses.php?'.$url_parameters_without_sort.'sort_by[]='.$sort_by_key.'&sort_dir[]=1&sort_by[]=NULL&sort_dir[]=NULL&sort_by[]=NULL&sort_dir[]=NULL&num='.$form_fields['num'].'&start=0';
    $sort_asc_link = vlc_internal_link('<img border="0" src="'.$site_info['images_url'].'sort-asc.png">', $sort_asc_url);
    $sort_desc_url = 'cms/courses.php?'.$url_parameters_without_sort.'sort_by[]='.$sort_by_key.'&sort_dir[]=2&sort_by[]=NULL&sort_dir[]=NULL&sort_by[]=NULL&sort_dir[]=NULL&num='.$form_fields['num'].'&start=0';
    $sort_desc_link = vlc_internal_link('<img border="0" src="'.$site_info['images_url'].'sort-desc.png">', $sort_desc_url);
    $output .= "<th><nobr>$sort_by_value $sort_asc_link $sort_desc_link</nobr></th>";
  }
  $output .= '</tr>';
  while ($record = mysql_fetch_array($result))
  {
    if (isset($record['facilitator_id'])) $facilitator_name = $facilitator_options_array[$record['facilitator_id']];
    else $facilitator_name = '&nbsp;';
    $output .= '<tr>';
    $output .= '<td>'.$result_num.'.</td>';
    $output .= '<td align="center">'.vlc_internal_link($record['course_id'], 'cms/course_details.php?course='.$record['course_id']).'</td>';
    $output .= '<td align="center">'.$record['code'].'</td>';
    $output .= '<td>'.$record['description'].'</td>';
    $output .= '<td align="center">'.$cycle_options_array[$record['cycle_id']].'</td>';
    $output .= '<td>'.$course_subject_options_array[$record['course_subject_id']].'</td>';
    $output .= '<td align="center">'.$course_section_options_array[$record['section_id']].'</td>';
    $output .= '<td align="center">'.$is_active_options_array[$record['is_active']].'</td>';
    $output .= '<td align="center">'.$restricted_options_array[$record['is_restricted']].'</td>';
    $output .= '<td>'.$facilitator_name.'</td>';
    $output .= '</tr>';
    $result_num++;
  }
  $output .= '</table>';
  $output .= '</form>';
}
else $output .= '<p align="center"><b>No results found.</b></p>';
/* search results navigation */
$output .= $nav_links;
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<style type="text/css">
#ac_course_id { z-index:9001; }
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
    /* course_id */
    this.ac_course_id_data_source = new YAHOO.widget.DS_XHR('xhr.php', ["\n", "\t"]);
    this.ac_course_id_data_source.responseType = YAHOO.widget.DS_XHR.TYPE_FLAT;
    this.ac_course_id_data_source.scriptQueryAppend = 'field=course_id';
    this.ac_course_id_widget = new YAHOO.widget.AutoComplete('ac_course_id_field', 'ac_course_id_container', this.ac_course_id_data_source);
    this.ac_course_id_widget.autoHighlight = false;
    this.ac_course_id_widget.formatResult = function(result_array, query_string) {
      var result_id = result_array[0];
      var result_string = result_array[1];
      return format_result(result_id, result_string, query_string);
    };
    /* code */
    this.ac_code_data_source = new YAHOO.widget.DS_XHR("xhr.php", ["\n", "\t"]);
    this.ac_code_data_source.responseType = YAHOO.widget.DS_XHR.TYPE_FLAT;
    this.ac_code_data_source.scriptQueryAppend = 'field=course_code';
    this.ac_code_widget = new YAHOO.widget.AutoComplete('ac_code_field', 'ac_code_container', this.ac_code_data_source);
    this.ac_code_widget.autoHighlight = false;
    this.ac_code_widget.formatResult = function(result_array, query_string) {
      var result_id = result_array[1];
      var result_string = result_array[0];
      return format_result(result_id, result_string, query_string);
    };
    /* description */
    this.ac_description_data_source = new YAHOO.widget.DS_XHR("xhr.php", ["\n", "\t"]);
    this.ac_description_data_source.responseType = YAHOO.widget.DS_XHR.TYPE_FLAT;
    this.ac_description_data_source.scriptQueryAppend = 'field=course_desc';
    this.ac_description_widget = new YAHOO.widget.AutoComplete('ac_description_field', 'ac_description_container', this.ac_description_data_source);
    this.ac_description_widget.autoHighlight = false;
    this.ac_description_widget.formatResult = function(result_array, query_string) {
      var result_id = result_array[1];
      var result_string = result_array[0];
      return format_result(result_id, result_string, query_string);
    };
};
</script>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
