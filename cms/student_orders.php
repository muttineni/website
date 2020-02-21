<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'stu-reg-orders';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* initialize form fields variable */
$form_fields = array
(
  'start' => 0,
  'num' => 10,
  'order_id' => '',
  'user_course_id' => '',
  'user_id' => '',
  'first_name' => '',
  'last_name' => '',
  'username' => '',
  'primary_email' => '',
  'credit_raincheck' => -1,
  'state_id' => 'NULL',
  'country_id' => 'NULL',
  'diocese_id' => 'NULL',
  'partner_id' => 'NULL',
  'course_id' => 'NULL',
  'cycle_id' => 'NULL',
  'course_subject_id' => 'NULL',
  'order_status' => -1,
  'course_status_id' => 'NULL',
  'registration_type_id' => 'NULL',
  'payment_status_id' => 'NULL',
  'discount_type_id' => 'NULL',
  'order_date_min_year' => 2000,
  'order_date_min_month' => 1,
  'order_date_min_day' => 1,
  'order_date_max_year' => 2020,
  'order_date_max_month' => 12,
  'order_date_max_day' => 31,
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
$where_clause = '';
if (!is_numeric($form_fields['order_date_min_year'])) $form_fields['order_date_min_year'] = 2000;
if (!is_numeric($form_fields['order_date_min_month'])) $form_fields['order_date_min_month'] = 1;
if (!is_numeric($form_fields['order_date_min_day'])) $form_fields['order_date_min_day'] = 1;
if (!is_numeric($form_fields['order_date_max_year'])) $form_fields['order_date_max_year'] = 2020;
if (!is_numeric($form_fields['order_date_max_month'])) $form_fields['order_date_max_month'] = 12;
if (!is_numeric($form_fields['order_date_max_day'])) $form_fields['order_date_max_day'] = 31;
$order_date_min = $form_fields['order_date_min_year'].'-'.$form_fields['order_date_min_month'].'-'.$form_fields['order_date_min_day'];
$order_date_max = $form_fields['order_date_max_year'].'-'.$form_fields['order_date_max_month'].'-'.$form_fields['order_date_max_day'];
$where_clause .= " AND o.order_date >= '".$order_date_min."'";
$where_clause .= " AND o.order_date <= '".$order_date_max."'";
if (is_numeric($form_fields['order_id']) and $form_fields['order_id'] > 0) $where_clause .= ' AND o.order_id = '.$form_fields['order_id'];
if (is_numeric($form_fields['user_course_id']) and $form_fields['user_course_id'] > 0) $where_clause .= ' AND uc.user_course_id = '.$form_fields['user_course_id'];
if (is_numeric($form_fields['user_id']) and $form_fields['user_id'] > 0) $where_clause .= ' AND u.user_id = '.$form_fields['user_id'];
if (strlen($form_fields['first_name']) > 0) $where_clause .= " AND u.first_name LIKE '".$form_fields['first_name']."'";
if (strlen($form_fields['last_name']) > 0) $where_clause .= " AND u.last_name LIKE '".$form_fields['last_name']."'";
if (strlen($form_fields['username']) > 0) $where_clause .= " AND u.username LIKE '".$form_fields['username']."'";
if (strlen($form_fields['primary_email']) > 0) $where_clause .= " AND i.primary_email LIKE '".$form_fields['primary_email']."'";
if (is_numeric($form_fields['credit_raincheck']) and $form_fields['credit_raincheck'] != -1) $where_clause .= $form_fields['credit_raincheck'] ? ' AND r.credit_amount > 0' : ' AND r.credit_amount = 0';
else $form_fields['credit_raincheck'] = -1;
if (is_numeric($form_fields['state_id']) and $form_fields['state_id'] > 0) $where_clause .= ' AND i.state_id = '.$form_fields['state_id'];
if (is_numeric($form_fields['country_id']) and $form_fields['country_id'] > 0) $where_clause .= ' AND i.country_id = '.$form_fields['country_id'];
if (is_numeric($form_fields['diocese_id']) and $form_fields['diocese_id'] > 0) $where_clause .= ' AND i.diocese_id = '.$form_fields['diocese_id'];
if (is_numeric($form_fields['partner_id']) and $form_fields['partner_id'] > 0) $where_clause .= ' AND i.partner_id = '.$form_fields['partner_id'];
if (is_numeric($form_fields['course_id']) and $form_fields['course_id'] > 0) $where_clause .= ' AND c.course_id = '.$form_fields['course_id'];
if (is_numeric($form_fields['cycle_id']) and $form_fields['cycle_id'] > 0) $where_clause .= ' AND c.cycle_id = '.$form_fields['cycle_id'];
if (is_numeric($form_fields['course_subject_id']) and $form_fields['course_subject_id'] > 0) $where_clause .= ' AND c.course_subject_id = '.$form_fields['course_subject_id'];
if (is_numeric($form_fields['order_status']) and $form_fields['order_status'] != -1) $where_clause .= ' AND o.is_active = '.$form_fields['order_status'];
else $form_fields['order_status'] = -1;
if (is_numeric($form_fields['course_status_id']) and $form_fields['course_status_id'] > 0) $where_clause .= ' AND uc.course_status_id = '.$form_fields['course_status_id'];
if (is_numeric($form_fields['registration_type_id']) and $form_fields['registration_type_id'] > 0) $where_clause .= ' AND uc.registration_type_id = '.$form_fields['registration_type_id'];
if (is_numeric($form_fields['payment_status_id']) and $form_fields['payment_status_id'] > 0) $where_clause .= ' AND o.payment_status_id = '.$form_fields['payment_status_id'];
if (is_numeric($form_fields['discount_type_id']) and $form_fields['discount_type_id'] > 0)
{
  $discount_type_id = floor($form_fields['discount_type_id'] / 10000);
  $where_clause .= ' AND o.discount_type_id = '.$discount_type_id;
  $discount_id = $form_fields['discount_type_id'] % 10000;
  if ($discount_id > 0) $where_clause .= ' AND o.discount_id = '.$discount_id;
}
/* query sorting parameters */
$sort_field_array = array(1 => 'o.order_id', 'o.order_date', 'o.is_active', 'o.payment_status_id', 'o.discount_type_id', 'c.description', 'uc.course_status_id', 'u.last_name');
$sort_options_array = array(1 => 'Order ID', 'Order Date', 'Order Status', 'Payment Status', 'Discount Type', 'Course', 'Course Status', 'Student Name');
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
  SELECT COUNT(*)
  FROM users_courses AS uc, users AS u, user_info AS i, credits AS r, courses AS c, orders AS o, payment_status AS s
  WHERE uc.user_course_id = o.product_id
    AND uc.user_id = o.customer_id
    AND uc.user_id = u.user_id
    AND u.user_id = i.user_id
    AND uc.user_role_id = 5
    AND uc.course_id = c.course_id
    AND o.product_type_id = 1
    AND o.customer_type_id = 1
    AND o.customer_id = r.customer_id
    AND r.customer_type_id = 1
    AND o.payment_status_id = s.payment_status_id
  $where_clause
END_QUERY;
$result = mysql_query($total_rows_query, $site_info['db_conn']);
$total_rows = mysql_result($result, 0);
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
    SELECT o.order_id, o.order_date, IF(o.is_active, 'Active', 'Inactive') AS is_active, s.description, u.user_id, u.first_name, u.last_name, i.primary_email, c.code, c.description, c.course_id, t.description
    FROM users_courses AS uc, users AS u, user_info AS i, credits AS r, courses AS c, orders AS o, payment_status AS s, course_status AS t
    WHERE uc.user_course_id = o.product_id
      AND uc.user_id = o.customer_id
      AND uc.user_id = u.user_id
      AND u.user_id = i.user_id
      AND uc.user_role_id = 5
      AND uc.course_id = c.course_id
      AND uc.course_status_id = t.course_status_id
      AND o.product_type_id = 1
      AND o.customer_type_id = 1
      AND o.customer_id = r.customer_id
      AND r.customer_type_id = 1
      AND o.payment_status_id = s.payment_status_id
END_QUERY;
  if (in_array($form_fields['export_data'], array(1, 2))) $export_query .= $where_clause;
  $export_query .= $order_clause;
  if ($form_fields['export_data'] == 1) $export_query .= $limit_clause;
  $result = mysql_query($export_query, $site_info['db_conn']);
  $export_array = array();
  $export_array[] = array('Order ID', 'Order Date', 'Order Status', 'Payment', 'Student ID', 'First Name', 'Last Name', 'Email', 'C. Code', 'Course', 'C. ID', 'Course Status');
  while ($record = mysql_fetch_row($result)) $export_array[] = $record;
  switch ($form_fields['export_format'])
  {
    case 1:
      vlc_export_data($export_array, 'stu-reg-orders', 1);
      break;
    case 2:
      vlc_export_data($export_array, 'stu-reg-orders', 2, 'P');
      break;
    case 3:
      vlc_export_data($export_array, 'stu-reg-orders', 2, 'L');
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
  $first_url = 'cms/student_orders.php?'.$url_parameters.'&start='.$first_start;
  $first_link = vlc_internal_link('&laquo; First', $first_url);
  $prev_start = $form_fields['start'] - $form_fields['num'];
  if ($prev_start < 0) $prev_start = 0;
  $prev_url = 'cms/student_orders.php?'.$url_parameters.'&start='.$prev_start;
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
  $next_url = 'cms/student_orders.php?'.$url_parameters.'&start='.$next_start;
  $next_link = vlc_internal_link('Next &rsaquo;', $next_url);
  /* determine the index of the last record of the search results (the index of the first record is "0") */
  $last_index = $total_rows - 1;
  /* determine the index of the first record of the last page of search results */
  $last_start = $last_index - ($last_index % $form_fields['num']);
  $last_url = 'cms/student_orders.php?'.$url_parameters.'&start='.$last_start;
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
    if ($page_num == $current_page) $page_links_array[] = $page_num;
    else
    {
      $page_link_url = 'cms/student_orders.php?'.$url_parameters.'&start='.$page_start;
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
/* build array for "state" select box */
$state_options_array = array();
$state_query = <<< END_QUERY
  SELECT state_id, code, description
  FROM states
  ORDER BY description
END_QUERY;
$result = mysql_query($state_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $state_options_array[$record['state_id']] = $record['description'].' ('.$record['code'].')';
/* build array for "country" select box */
$country_options_array = array();
$country_query = <<< END_QUERY
  SELECT country_id, code, description
  FROM countries
  ORDER BY description
END_QUERY;
$result = mysql_query($country_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $country_options_array[$record['country_id']] = $record['description'].' ('.$record['code'].')';
/* build array for "diocese" select box */
$diocese_options_array = array();
$diocese_query = <<< END_QUERY
  SELECT p.partner_id, p.description, p.is_partner, IFNULL(s.code, c.description) AS state_country
  FROM partners AS p LEFT JOIN states AS s USING (state_id), countries AS c
  WHERE p.is_diocese = 1
  AND (p.country_id = 222 OR p.is_partner = 1)
  AND p.country_id = c.country_id
  ORDER BY p.description
END_QUERY;
$result = mysql_query($diocese_query, $site_info['db_conn']);
$diocese_options_array[1]['label'] = 'Partner';
$diocese_options_array[0]['label'] = 'Non-Partner';
while ($record = mysql_fetch_array($result))
{
  if ($record['is_partner']) $diocese_options_array[1]['options'][$record['partner_id']] = $record['description'].' ('.$record['state_country'].')';
  else $diocese_options_array[0]['options'][$record['partner_id']] = $record['description'].' ('.$record['state_country'].')';
}
/* build array for "special partner" select box */
$partner_options_array = array();
$partner_query = <<< END_QUERY
  SELECT partner_id, description, is_partner
  FROM partners
  WHERE is_diocese = 0
  ORDER BY description
END_QUERY;
$result = mysql_query($partner_query, $site_info['db_conn']);
$partner_options_array[1]['label'] = 'Partner';
$partner_options_array[0]['label'] = 'Non-Partner';
while ($record = mysql_fetch_array($result))
{
  if ($record['is_partner']) $partner_options_array[1]['options'][$record['partner_id']] = $record['description'];
  else $partner_options_array[0]['options'][$record['partner_id']] = $record['description'];
}
/* build array for "cycle" select box */
$cycle_options_array = array();
$cycle_options_query = <<< END_QUERY
  SELECT cycle_id, code AS cycle_code, UNIX_TIMESTAMP(cycle_start) AS cycle_start
  FROM cycles
  ORDER BY YEAR(cycle_start) DESC, cycle_start
END_QUERY;
$result = mysql_query($cycle_options_query, $site_info['db_conn']);
$previous_year = 0;
while ($record = mysql_fetch_array($result))
{
  $current_year = date('Y', $record['cycle_start']);
  if ($current_year != $previous_year)
  {
    $previous_year = $current_year;
    $cycle_options_array[$current_year]['label'] = $current_year;
  }
  $cycle_options_array[$current_year]['options'][$record['cycle_id']] = $record['cycle_code'].' - '.date('M. d, Y', $record['cycle_start']).' ('.$record['cycle_id'].')';
}
/* build array for "course" select box */
$course_options_array = array();
$course_options_query = <<< END_QUERY
  SELECT c.course_id, c.code AS course_code, c.description, y.cycle_id, y.code AS cycle_code, UNIX_TIMESTAMP(y.cycle_start) AS cycle_start
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
    $course_options_array[$current_cycle]['label'] = $record['cycle_code'].' - '.date('M. d, Y', $record['cycle_start']).' ('.$record['cycle_id'].')';
  }
  $course_options_array[$current_cycle]['options'][$record['course_id']] = $record['course_code'].' - '.$record['description'].' ('.$record['course_id'].')';
}
/* build array for "course subject" select box */
$course_subject_options_array = array();
$course_subject_options_query = <<< END_QUERY
  SELECT s.course_subject_id, s.description, l.course_level_id, l.description AS course_level
  FROM course_subjects AS s, course_levels AS l
  WHERE s.course_level_id = l.course_level_id
  ORDER BY l.course_level_id, s.description
END_QUERY;
$result = mysql_query($course_subject_options_query, $site_info['db_conn']);
$previous_level = 0;
while ($record = mysql_fetch_array($result))
{
  $current_level = $record['course_level_id'];
  if ($current_level != $previous_level)
  {
    $previous_level = $current_level;
    $course_subject_options_array[$current_level]['label'] = $record['course_level'];
  }
  $course_subject_options_array[$current_level]['options'][$record['course_subject_id']] = $record['description'].' ('.$record['course_subject_id'].')';
}
/* build array for "course status" select box */
$course_status_options_array = array();
$course_status_query = <<< END_QUERY
  SELECT course_status_id, description
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
/* build array for "payment status" select box */
$payment_status_options_array = array();
$payment_status_query = <<< END_QUERY
  SELECT payment_status_id, description
  FROM payment_status
  ORDER BY payment_status_id
END_QUERY;
$result = mysql_query($payment_status_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $payment_status_options_array[$record['payment_status_id']] = $record['description'];
/* build array for "discount type" select box */
$discount_options_array = array();
$discount_options_array[1]['label'] = 'Payment Codes';
$discount_options_array[1]['options'][10000] = '* All Payment Codes *';
$discount_options_array[2]['label'] = 'Partnering Organizations';
$discount_options_array[2]['options'][20000] = '* All Partnering Organizations *';
$discount_options_array[3]['label'] = 'Partnering Dioceses';
$discount_options_array[3]['options'][30000] = '* All Partnering Dioceses *';
/* get payment codes */
$payment_codes_query = <<< END_QUERY
  SELECT payment_code_id, code AS payment_code, description
  FROM payment_codes
  ORDER BY code
END_QUERY;
$result = mysql_query($payment_codes_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $discount_options_array[1]['options'][10000 + $record['payment_code_id']] = $record['payment_code'].' - '.$record['description'];
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
  if ($record['is_diocese']) $discount_options_array[3]['options'][30000 + $record['partner_id']] = $record['description'].' ('.$record['state_country'].')';
  else $discount_options_array[2]['options'][20000 + $record['partner_id']] = $record['description'];
}
/* order status values */
$order_status_options_array = array(1 => 'Active', 0 => 'Inactive');
/* credit values */
$credit_options_array = array(1 => 'Yes', 0 => 'No');
/* year, month, day arrays */
for ($i = 2000; $i <= 2020; $i++) $years_array[$i] = $i;
for ($i = 1; $i <= 12; $i++) $months_array[$i] = date('F', mktime(0,0,0,$i,1,0));
for ($i = 1; $i <= 31; $i++) $days_array[$i] = $i;
/* select boxes for cycle dates */
$order_date_min_year_dropdown = vlc_select_box($years_array, 'array', 'order_date_min_year', $form_fields['order_date_min_year'], true);
$order_date_min_month_dropdown = vlc_select_box($months_array, 'array', 'order_date_min_month', $form_fields['order_date_min_month'], true);
$order_date_min_day_dropdown = vlc_select_box($days_array, 'array', 'order_date_min_day', $form_fields['order_date_min_day'], true);
$order_date_max_year_dropdown = vlc_select_box($years_array, 'array', 'order_date_max_year', $form_fields['order_date_max_year'], true);
$order_date_max_month_dropdown = vlc_select_box($months_array, 'array', 'order_date_max_month', $form_fields['order_date_max_month'], true);
$order_date_max_day_dropdown = vlc_select_box($days_array, 'array', 'order_date_max_day', $form_fields['order_date_max_day'], true);
/* build array for "export data" select box */
$export_data_array = array(1 => 'this page of search results', 'all search results', 'all records');
/* build array for "export format" select box */
$export_format_array = array(1 => 'CSV', 'PDF (Portrait)', 'PDF (Landscape)');
/* build hidden fields for export form */
$export_hidden_fields = vlc_create_hidden_fields($form_fields);
/* build array for "num" select box */
$num_results_array = array();
for ($i = 10; $i <= 100; $i+=10) $num_results_array[$i] = $i;
/* results/pages found labels (singular/plural) */
$results_label = ($total_rows == 1) ? 'Result' : 'Results';
$pages_label = ($last_page == 1) ? 'Page' : 'Pages';
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = htmlspecialchars($value);
}
/* begin building page output */
$output = '<h2>Course Registrations</h2>';
$output .= '<div align="center">';
$output .= '<form method="get" action="student_orders.php">';
$output .= $export_hidden_fields;
$output .= '<p align="center">Export '.vlc_select_box($export_data_array, 'array', 'export_data', -1, true).' to '.vlc_select_box($export_format_array, 'array', 'export_format', -1, true).' <input type="submit" value="Go"></p>';
$output .= '</form>';
$output .= '<p><b>'.$total_rows.'</b> '.$results_label.' Found (<b>'.$last_page.'</b> '.$pages_label.')</p>';
$output .= '<form method="get" action="student_orders.php">';
$output .= '<input type="hidden" name="start" value="0">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Order ID:</b></nobr></td><td colspan="3"><input type="text" size="10" name="order_id" value="'.$form_fields['order_id'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Course Registration ID:</b></nobr></td><td><input type="text" size="10" name="user_course_id" value="'.$form_fields['user_course_id'].'"></td>';
$output .= '<td align="right"><nobr><b>User ID:</b></nobr></td><td><div class="yui-skin-sam"><div id="ac_user_id" class="autocomplete"><input type="text" name="user_id" id="ac_user_id_field" value="'.$form_fields['user_id'].'"><div id="ac_user_id_container"></div></div></div></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>First Name:</b></nobr></td><td><div class="yui-skin-sam"><div id="ac_first_name" class="autocomplete"><input type="text" name="first_name" id="ac_first_name_field" value="'.$form_fields['first_name'].'"><div id="ac_first_name_container"></div></div></div></td>';
$output .= '<td align="right"><nobr><b>Username:</b></nobr></td><td><div class="yui-skin-sam"><div id="ac_username" class="autocomplete"><input type="text" name="username" id="ac_username_field" value="'.$form_fields['username'].'"><div id="ac_username_container"></div></div></div></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Last Name:</b></nobr></td><td><div class="yui-skin-sam"><div id="ac_last_name" class="autocomplete"><input type="text" name="last_name" id="ac_last_name_field" value="'.$form_fields['last_name'].'"><div id="ac_last_name_container"></div></div></div></td>';
$output .= '<td align="right"><nobr><b>E-Mail Address:</b></nobr></td><td><div class="yui-skin-sam"><div id="ac_primary_email" class="autocomplete"><input type="text" name="primary_email" id="ac_primary_email_field" value="'.$form_fields['primary_email'].'"><div id="ac_primary_email_container"></div></div></div></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>State:</b></nobr></td><td>'.vlc_select_box($state_options_array, 'array', 'state_id', $form_fields['state_id'], false).'</td>';
$output .= '<td align="right"><nobr><b>Country:</b></nobr></td><td>'.vlc_select_box($country_options_array, 'array', 'country_id', $form_fields['country_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr><td align="right"><nobr><b>Diocese:</b></nobr></td><td colspan="3">'.vlc_select_box($diocese_options_array, 'array', 'diocese_id', $form_fields['diocese_id'], false).'</td></tr>';
$output .= '<tr><td align="right"><nobr><b>Special Partner:</b></nobr></td><td colspan="3">'.vlc_select_box($partner_options_array, 'array', 'partner_id', $form_fields['partner_id'], false).'</td></tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Cycle:</b></nobr></td><td>'.vlc_select_box($cycle_options_array, 'array', 'cycle_id', $form_fields['cycle_id'], false).'</td>';
$output .= '<td align="right"><nobr><b>Course:</b></nobr></td><td>'.vlc_select_box($course_options_array, 'array', 'course_id', $form_fields['course_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Course Subject:</b></nobr></td><td>'.vlc_select_box($course_subject_options_array, 'array', 'course_subject_id', $form_fields['course_subject_id'], false).'</td>';
$output .= '<td align="right"><nobr><b>Order Status:</b></nobr></td><td>'.vlc_select_box($order_status_options_array, 'array', 'order_status', $form_fields['order_status'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Course Status:</b></nobr></td><td>'.vlc_select_box($course_status_options_array, 'array', 'course_status_id', $form_fields['course_status_id'], false).'</td>';
$output .= '<td align="right"><nobr><b>Payment Status:</b></nobr></td><td>'.vlc_select_box($payment_status_options_array, 'array', 'payment_status_id', $form_fields['payment_status_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Credit / Raincheck:</b></nobr></td><td>'.vlc_select_box($credit_options_array, 'array', 'credit_raincheck', $form_fields['credit_raincheck'], false).'</td>';
$output .= '<td align="right"><nobr><b>Registration Type:</b></nobr></td><td>'.vlc_select_box($registration_type_options_array, 'array', 'registration_type_id', $form_fields['registration_type_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr><td align="right"><nobr><b>Discount Type:</b></nobr></td><td colspan="3">'.vlc_select_box($discount_options_array, 'array', 'discount_type_id', $form_fields['discount_type_id'], false).'</td></tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Order Date:</b></nobr></td>';
$output .= '<td colspan="3">Between '.$order_date_min_month_dropdown.' '.$order_date_min_day_dropdown.' '.$order_date_min_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].order_date_min_year,document.forms[1].order_date_min_month,document.forms[1].order_date_min_day,false,false,this);"> and '.$order_date_max_month_dropdown.' '.$order_date_max_day_dropdown.' '.$order_date_max_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].order_date_max_year,document.forms[1].order_date_max_month,document.forms[1].order_date_max_day,false,false,this);"></td>';
$output .= '</tr>';
$output .= '<tr><td align="right" valign="top" rowspan="3"><nobr><b>Sort By:</b></nobr></td><td colspan="3">'.$sort_post_vars_string.'</td></tr>';
$output .= '<tr><td colspan="4" align="center"><nobr><b>Results / Page:</b></nobr> '.vlc_select_box($num_results_array, 'array', 'num', $form_fields['num'], true).' <input type="submit" value="Submit"> <input type="button" value="Clear Search Fields" onclick="clear_fields(this.form); this.form.num.selectedIndex = 0;"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
$output .= '</div>';
/* search results navigation */
if ($total_rows > 0) $output .= "<p align=\"right\"><b>Showing $list_start - $list_end of $total_rows (Page $current_page of $last_page)</b></p>";
$output .= $nav_links;
/* transaction status values */
$transaction_status_array = array(0 => 'Unsuccessful', 1 => 'Successful');
/* get registration records */
$order_query = <<< END_QUERY
  SELECT uc.user_course_id, uc.user_id, uc.course_id, uc.course_status_id, c.cycle_id,
    u.first_name, u.last_name, u.username, u.password, i.primary_email, r.credit_amount,
    o.order_id, o.is_active AS order_status, UNIX_TIMESTAMP(o.order_date) AS order_date, s.description AS payment_status,
    IFNULL(o.discount_type_id, 0) AS discount_type_id, IFNULL(o.discount_id, 0) AS discount_id,
    o.order_cost, o.amount_paid, o.amount_due
  FROM users_courses AS uc, users AS u, user_info AS i, credits AS r, courses AS c, orders AS o, payment_status AS s
  WHERE uc.user_course_id = o.product_id
    AND uc.user_id = o.customer_id
    AND uc.user_id = u.user_id
    AND u.user_id = i.user_id
    AND uc.user_role_id = 5
    AND uc.course_id = c.course_id
    AND o.product_type_id = 1
    AND o.customer_type_id = 1
    AND o.customer_id = r.customer_id
    AND r.customer_type_id = 1
    AND o.payment_status_id = s.payment_status_id
END_QUERY;
$order_query .= $where_clause;
$order_query .= $order_clause;
$order_query .= $limit_clause;
$result = mysql_query($order_query, $site_info['db_conn']);
/* compile search results */
if (mysql_num_rows($result) > 0)
{
  $orders = array();
  while ($record = mysql_fetch_array($result))
  {
    $orders[$record['order_id']] = $record;
    $user_course_id_array[] = $record['user_course_id'];
  }
  $order_id_list = join(', ', array_keys($orders));
  /* get transactions */
  $transaction_query = <<< END_QUERY
    SELECT ot.order_id, ot.order_transaction_amount,
      UNIX_TIMESTAMP(ot.credit_issue_date) AS credit_issue_date,
      UNIX_TIMESTAMP(ot.refund_issue_date) AS refund_issue_date,
      t.transaction_id, t.transaction_amount, m.description AS payment_method, IFNULL(t.check_number, '&nbsp;') AS check_number,
      t.transaction_status, IF(t.transaction_status, 'Successful', 'Unsuccessful') AS transaction_status_desc,
      UNIX_TIMESTAMP(t.transaction_date) AS transaction_date
    FROM orders_transactions AS ot, transactions AS t, payment_methods AS m
    WHERE ot.transaction_id = t.transaction_id
    AND t.payment_method_id = m.payment_method_id
    AND ot.order_id IN ($order_id_list)
END_QUERY;
  $result = mysql_query($transaction_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) $orders[$record['order_id']]['transactions'][$record['transaction_id']] = $record;
  /* remove "all payment codes", "all dioceses", and "all partners" options */
  unset($discount_options_array[1]['options'][10000]);
  unset($discount_options_array[2]['options'][20000]);
  unset($discount_options_array[3]['options'][30000]);
  /* build results table */
  $result_num = $list_start;
  $output .= '<form method="post" action="order_action.php?'.$url_parameters.'&start='.$form_fields['start'].'">';
  $output .= '<input type="hidden" name="is_stu_reg_order" value="1">';
  $output .= '<h3>Update All</h3>';
  $output .= '<p>To update all checked records at once, select one or more of the following options and click <b>&quot;Submit&quot;</b>:</p>';
  $output .= '<ul>';
  $output .= '<li><nobr><b>Order Status:</b></nobr> '.vlc_select_box($order_status_options_array, 'array', 'update_all_order_status', -1, false).'</li>';
  $output .= '<li><nobr><b>Course Status:</b></nobr> '.vlc_select_box($course_status_options_array, 'array', 'update_all_course_status_id', -1, false).'</li>';
  $output .= '<li><nobr><b>Course:</b></nobr> '.vlc_select_box($course_options_array, 'array', 'update_all_course_id', -1, false).'</li>';
  $output .= '<li><nobr><b>Discount Type:</b></nobr> '.vlc_select_box($discount_options_array, 'array', 'update_all_discount_type_id', -1, false).'</li>';
  $output .= '</ul>';
  $output .= '<p>&nbsp;</p>';
  $output .= '<table border="1" cellpadding="5" cellspacing="0">';
  $output .= '<tr>';
  $output .= '<th rowspan="2"><input type="checkbox" name="check_all_checkbox" onclick="check_all(this, \'order_id_array[]\');" checked></th>';
  /* build column headers with sort links */
  $i = 1;
  foreach ($sort_options_array as $sort_by_key => $sort_by_value)
  {
    $sort_asc_url = 'cms/student_orders.php?'.$url_parameters_without_sort.'sort_by[]='.$sort_by_key.'&sort_dir[]=1&sort_by[]=NULL&sort_dir[]=NULL&sort_by[]=NULL&sort_dir[]=NULL&num='.$form_fields['num'].'&start=0';
    $sort_asc_link = vlc_internal_link('<img border="0" src="'.$site_info['images_url'].'sort-asc.png">', $sort_asc_url);
    $sort_desc_url = 'cms/student_orders.php?'.$url_parameters_without_sort.'sort_by[]='.$sort_by_key.'&sort_dir[]=2&sort_by[]=NULL&sort_dir[]=NULL&sort_by[]=NULL&sort_dir[]=NULL&num='.$form_fields['num'].'&start=0';
    $sort_desc_link = vlc_internal_link('<img border="0" src="'.$site_info['images_url'].'sort-desc.png">', $sort_desc_url);
    $output .= "<th><nobr>$sort_by_value $sort_asc_link $sort_desc_link</nobr></th>";
    if ($i % 4 == 0) $output .= '</tr><tr>';
    $i++;
  }
  $output .= '</tr>';
  foreach ($orders as $order)
  {
    $discount_type_id = $order['discount_type_id'] * 10000 + $order['discount_id'];
    if ($discount_type_id == 0) $discount_type_id = 'NULL';
    $output .= '<tr>';
    $output .= '<td rowspan="9" valign="top">';
    $output .= '<a name="order-'.$order['order_id'].'"></a>';
    $output .= '<nobr><input type="checkbox" name="order_id_array[]" value="'.$order['order_id'].'" id="'.$order['order_id'].'" checked> <label for="'.$order['order_id'].'">'.$result_num.'.</label></nobr>';
    $output .= '</td>';
    $output .= '<td><nobr><b>Order ID:</b></nobr></td><td>'.vlc_internal_link($order['order_id'], 'cms/order_details.php?order='.$order['order_id']).'</td>';
    $output .= '<td><nobr><b>Order Date:</b></nobr></td><td>'.date('n/j/Y', $order['order_date']).'</td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><nobr><b>Course Registration ID:</b></nobr></td><td>'.vlc_internal_link($order['user_course_id'], 'cms/user_course_details.php?user_course='.$order['user_course_id']).'</td>';
    $output .= '<td><nobr><b>Payment Status:</b></nobr></td><td>'.$order['payment_status'].'</td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td valign="top"><nobr><b>Student:</b></td><td colspan="3">'.$order['first_name'].' '.$order['last_name'].'<ul><li><b>User ID:</b> '.vlc_internal_link($order['user_id'], 'cms/user_details.php?user='.$order['user_id']).'</li><li><b>Username / Password:</b> '.$order['username'].' / '.$order['password'].'</li><li><b>E-Mail:</b></nobr> '.vlc_mailto_link($order['primary_email'], $order['primary_email']).'</li></ul></td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><nobr><b>Course:</b></nobr></td><td>'.vlc_select_box($course_options_array[$order['cycle_id']]['options'], 'array', 'orders['.$order['order_id'].'][course_id]', $order['course_id'], true).' ('.vlc_internal_link($order['course_id'], 'cms/course_details.php?course='.$order['course_id']).')</td>';
    $output .= '<td><nobr><b>Order Cost:</b></nobr></td><td align="right">$'.number_format($order['order_cost'] / 100, 2).'</td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><nobr><b>Course Status:</b></nobr></td><td>'.vlc_select_box($course_status_options_array, 'array', 'orders['.$order['order_id'].'][course_status_id]', $order['course_status_id'], true).'</td>';
    $output .= '<td><nobr><b>Amount Paid:</b></nobr></td><td align="right">$'.number_format($order['amount_paid'] / 100, 2).'</td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><nobr><b>Order Status:</b></nobr></td><td>'.vlc_select_box($order_status_options_array, 'array', 'orders['.$order['order_id'].'][order_status]', $order['order_status'], true).'</td>';
    $output .= '<td><nobr><b>Amount Due:</b></nobr></td><td align="right">$'.number_format($order['amount_due'] / 100, 2).'</td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><nobr><b>Discount Type:</b></nobr></td><td colspan="3">'.vlc_select_box($discount_options_array, 'array', 'orders['.$order['order_id'].'][discount_type_id]', $discount_type_id, false).'</td>';
    $output .= '</tr>';
    /* list transactions */
    $payment_method_options_array = array(1 => 'Cash', 2 => 'Check', 3 => 'Credit Card', 7 => 'Lump Sum CC', 4 => 'VLCFF Credit', 5 => 'Prepaid Funds', 6 => 'Wire Transfer', 8 => 'ACTA Fund', 9 => 'Connor Trust', 10 => 'Marianist');
    $output .= '<tr><td colspan="4" valign="top"><nobr><b>Transactions:</b></nobr></td></tr>';
    $output .= '<tr>';
    $output .= '<td colspan="4">';
    $output .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
    $output .= '<tr><td align="center"><b>ID</b></td><td align="center"><b>Status</b></td><td align="center"><b>Date</b></td><td align="center"><b>Amount</b></td><td align="center"><b>Method</b></td><td align="center"><b>Check Number</b></td><td align="center"><b>VLCFF Credit</b></td><td align="center"><b>Refund</b></td></tr>';
    if (isset($order['transactions']) and is_array($order['transactions']) and count($order['transactions']))
    {
      foreach ($order['transactions'] as $transaction)
      {
        $credit_checked = $credit_disabled = $credit_issued_field = $credit_issue_date = '';
        $refund_checked = $refund_disabled = $refund_issued_field = $refund_issue_date = '';
        if ($transaction['transaction_status'] == 0)
        {
          $order_transaction_amount = '<span class="inactive">($'.number_format($transaction['order_transaction_amount'] / 100, 2).')</span>';
          $credit_disabled = ' disabled';
          $refund_disabled = ' disabled';
        }
        elseif (isset($transaction['credit_issue_date']))
        {
          $order_transaction_amount = '<span class="inactive">($'.number_format($transaction['order_transaction_amount'] / 100, 2).')</span><input type="hidden" name="orders['.$order['order_id'].'][transactions]['.$transaction['transaction_id'].'][order_transaction_amount]" value="'.number_format($transaction['order_transaction_amount'] / 100, 2).'"><input type="hidden" name="orders['.$order['order_id'].'][transactions]['.$transaction['transaction_id'].'][previous_order_transaction_amount]" value="'.number_format($transaction['order_transaction_amount'] / 100, 2).'">';
          if ($order['credit_amount'] >= $transaction['order_transaction_amount'])
          {
            $credit_checked = ' checked id="transaction-'.$transaction['transaction_id'].'"';
            $credit_issue_date = ' <label for="transaction-'.$transaction['transaction_id'].'">'.date('n/j/Y', $transaction['credit_issue_date']).'</label>';
            $credit_issued_field = '<input type="hidden" name="orders['.$order['order_id'].'][transactions]['.$transaction['transaction_id'].'][credit_issued]" value="1">';
          }
          else
          {
            $credit_checked = ' checked';
            $credit_disabled = ' disabled';
            $credit_issue_date = ' <span class="inactive">('.date('n/j/Y', $transaction['credit_issue_date']).')</span>';
          }
          $refund_disabled = ' disabled';
        }
        elseif (isset($transaction['refund_issue_date']))
        {
          $order_transaction_amount = '<span class="inactive">($'.number_format($transaction['order_transaction_amount'] / 100, 2).')</span><input type="hidden" name="orders['.$order['order_id'].'][transactions]['.$transaction['transaction_id'].'][order_transaction_amount]" value="'.number_format($transaction['order_transaction_amount'] / 100, 2).'"><input type="hidden" name="orders['.$order['order_id'].'][transactions]['.$transaction['transaction_id'].'][previous_order_transaction_amount]" value="'.number_format($transaction['order_transaction_amount'] / 100, 2).'">';
          $refund_checked = ' checked id="transaction-'.$transaction['transaction_id'].'"';
          $refund_issue_date = ' <label for="transaction-'.$transaction['transaction_id'].'">'.date('n/j/Y', $transaction['refund_issue_date']).'</label>';
          $refund_issued_field = '<input type="hidden" name="orders['.$order['order_id'].'][transactions]['.$transaction['transaction_id'].'][refund_issued]" value="1">';
          $credit_disabled = ' disabled';
        }
        else
        {
          $order_transaction_amount = '$'.number_format($transaction['order_transaction_amount'] / 100, 2);
          if ($transaction['order_transaction_amount'] == 0)
          {
            $credit_disabled = ' disabled';
            $refund_disabled = ' disabled';
          }
        }
        $issue_credit_field = '<input type="checkbox" name="orders['.$order['order_id'].'][transactions]['.$transaction['transaction_id'].'][issue_credit]" value="1"'.$credit_checked.$credit_disabled.'>'.$credit_issued_field;
        $issue_refund_field = '<input type="checkbox" name="orders['.$order['order_id'].'][transactions]['.$transaction['transaction_id'].'][issue_refund]" value="1"'.$refund_checked.$refund_disabled.'>'.$refund_issued_field;
        $output .= '<tr>';
        $output .= '<td align="center">'.vlc_internal_link($transaction['transaction_id'], 'cms/transaction_details.php?transaction='.$transaction['transaction_id']).'</td>';
        $output .= '<td align="center">'.$transaction['transaction_status_desc'].'</td>';
        $output .= '<td align="center">'.date('n/j/Y', $transaction['transaction_date']).'</td>';
        $output .= '<td align="center"><nobr>'.$order_transaction_amount.'</nobr></td>';
        $output .= '<td align="center">'.$transaction['payment_method'].'</td>';
        $output .= '<td align="center">'.$transaction['check_number'].'</td>';
        $output .= '<td align="center"><nobr>'.$issue_credit_field.$credit_issue_date.'</nobr></td>';
        $output .= '<td align="center"><nobr>'.$issue_refund_field.$refund_issue_date.'</nobr></td>';
        $output .= '</tr>';
      }
    }
    else $output .= '<tr><td colspan="9" align="center">No Transactions Found.</td></tr>';
    if (isset($order['credit_amount']) and $order['credit_amount'] > 0) $payment_method_options_array[4] .= ' ($'.number_format($order['credit_amount'] / 100, 2).')';
    else unset($payment_method_options_array[4]);
    $output .= '<tr><td colspan="3" align="right"><nobr><b>New Transaction:</b></nobr></td><td align="center"><nobr>$ <input type="text" size="10" name="orders['.$order['order_id'].'][transaction_amount]" style="text-align:right"></nobr></td><td align="center">'.vlc_select_box($payment_method_options_array, 'array', 'orders['.$order['order_id'].'][payment_method_id]', -1, true).'</td><td align="center"><input type="text" size="10" name="orders['.$order['order_id'].'][check_number]" style="text-align:right"></td><td colspan="2">&nbsp;</td></tr>';
    $output .= '</table>';
    $output .= '</td>';
    $output .= '</tr>';
    $output .= '<tr><td colspan="5" align="center"><input type="submit" value="Submit"></td></tr>';
    $result_num++;
  }
  $output .= '</table>';
  $output .= '</form>';
}
else $output .= '<p><b>No results found.</b></p>';
/* search results navigation */
$output .= $nav_links;
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<style type="text/css">
#ac_user_id { z-index:9003; }
#ac_first_name { z-index:9002; }
#ac_username { z-index:9001; }
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
    /* user_id */
    this.ac_user_id_data_source = new YAHOO.widget.DS_XHR('xhr.php', ["\n", "\t"]);
    this.ac_user_id_data_source.responseType = YAHOO.widget.DS_XHR.TYPE_FLAT;
    this.ac_user_id_data_source.scriptQueryAppend = 'field=user_id';
    this.ac_user_id_widget = new YAHOO.widget.AutoComplete('ac_user_id_field', 'ac_user_id_container', this.ac_user_id_data_source);
    this.ac_user_id_widget.autoHighlight = false;
    this.ac_user_id_widget.formatResult = function(result_array, query_string) {
      var result_id = result_array[0];
      var result_string = result_array[1];
      return format_result(result_id, result_string, query_string);
    };
    /* first_name */
    this.ac_first_name_data_source = new YAHOO.widget.DS_XHR("xhr.php", ["\n", "\t"]);
    this.ac_first_name_data_source.responseType = YAHOO.widget.DS_XHR.TYPE_FLAT;
    this.ac_first_name_data_source.scriptQueryAppend = 'field=first_name';
    this.ac_first_name_widget = new YAHOO.widget.AutoComplete('ac_first_name_field', 'ac_first_name_container', this.ac_first_name_data_source);
    this.ac_first_name_widget.autoHighlight = false;
    this.ac_first_name_widget.formatResult = function(result_array, query_string) {
      var result_id = result_array[1];
      var result_string = result_array[0];
      return format_result(result_id, result_string, query_string);
    };
    /* last_name */
    this.ac_last_name_data_source = new YAHOO.widget.DS_XHR("xhr.php", ["\n", "\t"]);
    this.ac_last_name_data_source.responseType = YAHOO.widget.DS_XHR.TYPE_FLAT;
    this.ac_last_name_data_source.scriptQueryAppend = 'field=last_name';
    this.ac_last_name_widget = new YAHOO.widget.AutoComplete('ac_last_name_field', 'ac_last_name_container', this.ac_last_name_data_source);
    this.ac_last_name_widget.autoHighlight = false;
    this.ac_last_name_widget.formatResult = function(result_array, query_string) {
      var result_id = result_array[1];
      var result_string = result_array[0];
      return format_result(result_id, result_string, query_string);
    };
    /* username */
    this.ac_username_data_source = new YAHOO.widget.DS_XHR("xhr.php", ["\n", "\t"]);
    this.ac_username_data_source.responseType = YAHOO.widget.DS_XHR.TYPE_FLAT;
    this.ac_username_data_source.scriptQueryAppend = 'field=username';
    this.ac_username_widget = new YAHOO.widget.AutoComplete('ac_username_field', 'ac_username_container', this.ac_username_data_source);
    this.ac_username_widget.autoHighlight = false;
    this.ac_username_widget.formatResult = function(result_array, query_string) {
      var result_id = result_array[1];
      var result_string = result_array[0];
      return format_result(result_id, result_string, query_string);
    };
    /* primary_email */
    this.ac_primary_email_data_source = new YAHOO.widget.DS_XHR("xhr.php", ["\n", "\t"]);
    this.ac_primary_email_data_source.responseType = YAHOO.widget.DS_XHR.TYPE_FLAT;
    this.ac_primary_email_data_source.scriptQueryAppend = 'field=primary_email';
    this.ac_primary_email_widget = new YAHOO.widget.AutoComplete('ac_primary_email_field', 'ac_primary_email_container', this.ac_primary_email_data_source);
    this.ac_primary_email_widget.autoHighlight = false;
    this.ac_primary_email_widget.formatResult = function(result_array, query_string) {
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
