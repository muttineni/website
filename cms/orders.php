<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'orders';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* initialize form fields variable */
$form_fields = array
(
  'start' => 0,
  'num' => 10,
  'order_id' => '',
  'product_id' => '',
  'customer_id' => '',
  'product_type_id' => 'NULL',
  'customer_type_id' => 'NULL',
  'order_status' => -1,
  'order_date_min_year' => 2000,
  'order_date_min_month' => 1,
  'order_date_min_day' => 1,
  'order_date_max_year' => 2020,
  'order_date_max_month' => 12,
  'order_date_max_day' => 31,
  'payment_status_id' => 'NULL',
  'discount_type_id' => 'NULL',
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
if (is_numeric($form_fields['product_id']) and $form_fields['product_id'] > 0) $where_clause .= ' AND o.product_id = '.$form_fields['product_id'];
if (is_numeric($form_fields['customer_id']) and $form_fields['customer_id'] > 0) $where_clause .= ' AND o.customer_id = '.$form_fields['customer_id'];
if (is_numeric($form_fields['product_type_id']) and $form_fields['product_type_id'] > 0) $where_clause .= ' AND o.product_type_id = '.$form_fields['product_type_id'];
if (is_numeric($form_fields['customer_type_id']) and $form_fields['customer_type_id'] > 0) $where_clause .= ' AND o.customer_type_id = '.$form_fields['customer_type_id'];
if (is_numeric($form_fields['payment_status_id']) and $form_fields['payment_status_id'] > 0) $where_clause .= ' AND o.payment_status_id = '.$form_fields['payment_status_id'];
if (is_numeric($form_fields['order_status']) and $form_fields['order_status'] != -1) $where_clause .= ' AND o.is_active = '.$form_fields['order_status'];
else $form_fields['order_status'] = -1;
if (is_numeric($form_fields['discount_type_id']) and $form_fields['discount_type_id'] > 0)
{
  $discount_type_id = floor($form_fields['discount_type_id'] / 10000);
  $where_clause .= ' AND o.discount_type_id = '.$discount_type_id;
  $discount_id = $form_fields['discount_type_id'] % 10000;
  if ($discount_id > 0) $where_clause .= ' AND o.discount_id = '.$discount_id;
}
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = stripslashes($value);
}
/* query sorting parameters */
$sort_field_array = array(1 => 'o.order_id', 'o.order_date', 'o.is_active', 'o.payment_status_id', 'o.product_type_id', 'product', 'o.customer_type_id', 'customer');
$sort_options_array = array(1 => 'Order ID', 'Order Date', 'Order Status', 'Payment Status ID', 'Product Type ID', 'Product ID', 'Customer Type ID', 'Customer ID');
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
  FROM orders AS o
    LEFT JOIN users_courses AS uc ON o.product_id = uc.user_course_id
    LEFT JOIN users AS u ON uc.user_id = u.user_id
    LEFT JOIN courses AS c ON uc.course_id = c.course_id
    LEFT JOIN users AS m ON o.customer_id = m.user_id
    LEFT JOIN courses AS n ON o.product_id = n.course_id
    LEFT JOIN resources AS r ON o.product_id = r.resource_id
    LEFT JOIN course_subjects AS b ON o.product_id = b.course_subject_id
    LEFT JOIN partners AS p ON o.customer_id = p.partner_id
    LEFT JOIN credits AS e ON o.customer_id = e.customer_id AND o.customer_type_id = e.customer_type_id
    LEFT JOIN certs_users AS cu ON o.product_id = cu.cert_user_id
    LEFT JOIN cert_progs AS g ON cu.cert_prog_id = g.cert_prog_id
    LEFT JOIN users AS h ON cu.user_id = h.user_id,
    customer_types AS s, product_types AS t, payment_status AS y
  WHERE o.customer_type_id = s.customer_type_id
  AND o.product_type_id = t.product_type_id
  AND o.payment_status_id = y.payment_status_id
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
    SELECT o.order_id, o.order_date, IF(o.is_active, 'Active', 'Inactive') AS is_active, y.description,
      t.description,
      CASE o.product_type_id
        WHEN 1 THEN CONCAT(u.first_name, ' ', u.last_name, ' - ', c.description)
        WHEN 2 THEN r.title
        WHEN 3 THEN n.description
        WHEN 4 THEN b.description
        WHEN 6 THEN CONCAT(h.first_name, ' ', h.last_name, ' - ', g.description)
      END AS product,
      s.description,
      IF(o.customer_type_id = 2, p.description, CONCAT(m.first_name, ' ', m.last_name)) AS customer
    FROM orders AS o
      LEFT JOIN users_courses AS uc ON o.product_id = uc.user_course_id
      LEFT JOIN users AS u ON uc.user_id = u.user_id
      LEFT JOIN courses AS c ON uc.course_id = c.course_id
      LEFT JOIN resources AS r ON o.product_id = r.resource_id
      LEFT JOIN courses AS n ON o.product_id = n.course_id
      LEFT JOIN course_subjects AS b ON o.product_id = b.course_subject_id
      LEFT JOIN users AS m ON o.customer_id = m.user_id
      LEFT JOIN partners AS p ON o.customer_id = p.partner_id
      LEFT JOIN credits AS e ON o.customer_id = e.customer_id AND o.customer_type_id = e.customer_type_id
      LEFT JOIN certs_users AS cu ON o.product_id = cu.cert_user_id
      LEFT JOIN cert_progs AS g ON cu.cert_prog_id = g.cert_prog_id
      LEFT JOIN users AS h ON cu.user_id = h.user_id,
      customer_types AS s, product_types AS t, payment_status AS y
    WHERE o.customer_type_id = s.customer_type_id
    AND o.product_type_id = t.product_type_id
    AND o.payment_status_id = y.payment_status_id
END_QUERY;
  if (in_array($form_fields['export_data'], array(1, 2))) $export_query .= $where_clause;
  $export_query .= $order_clause;
  if ($form_fields['export_data'] == 1) $export_query .= $limit_clause;
  $result = mysql_query($export_query, $site_info['db_conn']);
  $export_array = array();
  $export_array[] = array('Order ID', 'Order Date', 'Order Status', 'Payment Status', 'Product Type', 'Product', 'Customer Type', 'Customer');
  while ($record = mysql_fetch_row($result)) $export_array[] = $record;
  switch ($form_fields['export_format'])
  {
    case 1:
      vlc_export_data($export_array, 'orders', 1);
      break;
    case 2:
      vlc_export_data($export_array, 'orders', 2, 'P');
      break;
    case 3:
      vlc_export_data($export_array, 'orders', 2, 'L');
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
  $first_url = 'cms/orders.php?'.$url_parameters.'&start='.$first_start;
  $first_link = vlc_internal_link('&laquo; First', $first_url);
  $prev_start = $form_fields['start'] - $form_fields['num'];
  if ($prev_start < 0) $prev_start = 0;
  $prev_url = 'cms/orders.php?'.$url_parameters.'&start='.$prev_start;
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
  $next_url = 'cms/orders.php?'.$url_parameters.'&start='.$next_start;
  $next_link = vlc_internal_link('Next &rsaquo;', $next_url);
  /* determine the index of the last record of the search results (the index of the first record is "0") */
  $last_index = $total_rows - 1;
  /* determine the index of the first record of the last page of search results */
  $last_start = $last_index - ($last_index % $form_fields['num']);
  $last_url = 'cms/orders.php?'.$url_parameters.'&start='.$last_start;
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
      $page_link_url = 'cms/orders.php?'.$url_parameters.'&start='.$page_start;
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
/* build array for "product type" select box */
$product_type_options_array = array();
$product_type_query = <<< END_QUERY
  SELECT product_type_id, description
  FROM product_types
  ORDER BY product_type_id
END_QUERY;
$result = mysql_query($product_type_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $product_type_options_array[$record['product_type_id']] = $record['description'];
/* build array for "customer type" select box */
$customer_type_options_array = array();
$customer_type_query = <<< END_QUERY
  SELECT customer_type_id, description
  FROM customer_types
  ORDER BY customer_type_id
END_QUERY;
$result = mysql_query($customer_type_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $customer_type_options_array[$record['customer_type_id']] = $record['description'];
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
/* begin building page output */
$output = '';
$output .= '<div align="center">';
$output .= '<form method="get" action="orders.php">';
$output .= $export_hidden_fields;
$output .= '<p align="center">Export '.vlc_select_box($export_data_array, 'array', 'export_data', -1, true).' to '.vlc_select_box($export_format_array, 'array', 'export_format', -1, true).' <input type="submit" value="Go"></p>';
$output .= '</form>';
$output .= '<p><b>'.$total_rows.'</b> '.$results_label.' Found (<b>'.$last_page.'</b> '.$pages_label.')</p>';
$output .= '<form method="get" action="orders.php">';
$output .= '<input type="hidden" name="start" value="0">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
$output .= '<tr><td align="right"><b>Order ID:</b></td><td colspan="3"><input type="text" size="10" name="order_id" value="'.$form_fields['order_id'].'"></td></tr>';
$output .= '<tr>';
$output .= '<td align="right"><b>Product ID:</b></td><td><input type="text" size="10" name="product_id" value="'.$form_fields['product_id'].'"></td>';
$output .= '<td align="right"><b>Customer ID:</b></td><td><input type="text" size="10" name="customer_id" value="'.$form_fields['customer_id'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><b>Product Type:</b></td><td>'.vlc_select_box($product_type_options_array, 'array', 'product_type_id', $form_fields['product_type_id'], false).'</td>';
$output .= '<td align="right"><b>Customer Type:</b></td><td>'.vlc_select_box($customer_type_options_array, 'array', 'customer_type_id', $form_fields['customer_type_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><b>Order Status:</b></td><td>'.vlc_select_box($order_status_options_array, 'array', 'order_status', $form_fields['order_status'], false).'</td>';
$output .= '<td align="right"><b>Payment Status:</b></td><td>'.vlc_select_box($payment_status_options_array, 'array', 'payment_status_id', $form_fields['payment_status_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><b>Order Date:</b></td>';
$output .= '<td colspan="3">Between '.$order_date_min_month_dropdown.' '.$order_date_min_day_dropdown.' '.$order_date_min_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].order_date_min_year,document.forms[1].order_date_min_month,document.forms[1].order_date_min_day,false,false,this);"> and '.$order_date_max_month_dropdown.' '.$order_date_max_day_dropdown.' '.$order_date_max_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].order_date_max_year,document.forms[1].order_date_max_month,document.forms[1].order_date_max_day,false,false,this);"></td>';
$output .= '</tr>';
$output .= '<tr><td align="right"><b>Discount Type:</b></td><td colspan="3">'.vlc_select_box($discount_options_array, 'array', 'discount_type_id', $form_fields['discount_type_id'], false).'</td></tr>';
$output .= '<tr><td align="right" valign="top" rowspan="3"><b>Sort By:</b></td><td colspan="3">'.$sort_post_vars_string.'</td></tr>';
$output .= '<tr><td colspan="4" align="center"><b>Results / Page:</b> '.vlc_select_box($num_results_array, 'array', 'num', $form_fields['num'], true).' <input type="submit" value="Submit"> <input type="button" value="Clear Search Fields" onclick="clear_fields(this.form); this.form.num.selectedIndex = 0;"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
$output .= '</div>';
/* search results navigation */
if ($total_rows > 0) $output .= "<p align=\"right\"><b>Showing $list_start - $list_end of $total_rows (Page $current_page of $last_page)</b></p>";
$output .= $nav_links;
/* get orders */
$order_query = <<< END_QUERY
  SELECT o.order_id, o.is_active AS order_status, y.description AS payment_status,
    UNIX_TIMESTAMP(o.order_date) AS order_date_timestamp,
    IFNULL(o.discount_type_id, 0) AS discount_type_id, IFNULL(o.discount_id, 0) AS discount_id,
    o.order_cost, o.amount_paid, o.amount_due, e.credit_amount,
    o.product_type_id, t.description AS product_type, o.product_id,
    CASE o.product_type_id
      WHEN 1 THEN CONCAT(u.first_name, ' ', u.last_name, ' - ', c.description)
      WHEN 2 THEN r.title
      WHEN 3 THEN n.description
      WHEN 4 THEN b.description
      WHEN 6 THEN CONCAT(h.first_name, ' ', h.last_name, ' - ', g.description)
    END AS product,
    o.customer_type_id, s.description AS customer_type, o.customer_id,
    IF(o.customer_type_id = 2, p.description, CONCAT(m.first_name, ' ', m.last_name)) AS customer,
    uc.course_id
  FROM orders AS o
    LEFT JOIN users_courses AS uc ON o.product_id = uc.user_course_id
    LEFT JOIN users AS u ON uc.user_id = u.user_id
    LEFT JOIN courses AS c ON uc.course_id = c.course_id
    LEFT JOIN resources AS r ON o.product_id = r.resource_id
    LEFT JOIN courses AS n ON o.product_id = n.course_id
    LEFT JOIN course_subjects AS b ON o.product_id = b.course_subject_id
    LEFT JOIN users AS m ON o.customer_id = m.user_id
    LEFT JOIN partners AS p ON o.customer_id = p.partner_id
    LEFT JOIN credits AS e ON o.customer_id = e.customer_id AND o.customer_type_id = e.customer_type_id
    LEFT JOIN certs_users AS cu ON o.product_id = cu.cert_user_id
    LEFT JOIN cert_progs AS g ON cu.cert_prog_id = g.cert_prog_id
    LEFT JOIN users AS h ON cu.user_id = h.user_id,
    customer_types AS s, product_types AS t, payment_status AS y
  WHERE o.customer_type_id = s.customer_type_id
  AND o.product_type_id = t.product_type_id
  AND o.payment_status_id = y.payment_status_id
END_QUERY;
$order_query .= $where_clause;
$order_query .= $order_clause;
$order_query .= $limit_clause;
$result = mysql_query($order_query, $site_info['db_conn']);
/* compile search results */
if (mysql_num_rows($result) > 0)
{
  $orders = $product_array = array();
  while ($record = mysql_fetch_array($result))
  {
    $orders[$record['order_id']] = $record;
    $product_array[$record['product_type_id']][] = $record['product_id'];
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
  $output .= '<h3>Update All</h3>';
  $output .= '<p>To update all checked records at once, select one or more of the following options and click <b>&quot;Submit&quot;</b>:</p>';
  $output .= '<ul>';
  $output .= '<li><b>Order Status:</b> '.vlc_select_box($order_status_options_array, 'array', 'update_all_order_status', -1, false).'</li>';
  $output .= '<li><b>Discount Type:</b> '.vlc_select_box($discount_options_array, 'array', 'update_all_discount_type_id', -1, false).'</li>';
  $output .= '</ul>';
  $output .= '<p>&nbsp;</p>';
  $output .= '<table border="1" cellpadding="5" cellspacing="0">';
  $output .= '<tr>';
  $output .= '<th rowspan="2"><input type="checkbox" name="check_all_checkbox" onclick="check_all(this, \'order_id_array[]\');" checked></th>';
  /* build column headers with sort links */
  $i = 1;
  foreach ($sort_options_array as $sort_by_key => $sort_by_value)
  {
    $sort_asc_url = 'cms/orders.php?'.$url_parameters_without_sort.'sort_by[]='.$sort_by_key.'&sort_dir[]=1&sort_by[]=NULL&sort_dir[]=NULL&sort_by[]=NULL&sort_dir[]=NULL&num='.$form_fields['num'].'&start=0';
    $sort_asc_link = vlc_internal_link('<img border="0" src="'.$site_info['images_url'].'sort-asc.png">', $sort_asc_url);
    $sort_desc_url = 'cms/orders.php?'.$url_parameters_without_sort.'sort_by[]='.$sort_by_key.'&sort_dir[]=2&sort_by[]=NULL&sort_dir[]=NULL&sort_by[]=NULL&sort_dir[]=NULL&num='.$form_fields['num'].'&start=0';
    $sort_desc_link = vlc_internal_link('<img border="0" src="'.$site_info['images_url'].'sort-desc.png">', $sort_desc_url);
    $output .= "<th><nobr>$sort_by_value $sort_asc_link $sort_desc_link</nobr></th>";
    if ($i % 4 == 0) $output .= '</tr><tr>';
    $i++;
  }
  $output .= '</tr>';
  foreach ($orders as $order)
  {
    /* product details link */
    switch ($order['product_type_id'])
    {
      case 1:
        $product_details_link = vlc_internal_link($order['product_id'], 'cms/user_course_details.php?user_course='.$order['product_id']);
        break;
      case 2:
        $product_details_link = vlc_internal_link($order['product_id'], 'cms/resource_details.php?resource='.$order['product_id']);
        break;
      case 3:
        $product_details_link = vlc_internal_link($order['product_id'], 'cms/course_details.php?course='.$order['product_id']);
        break;
      case 4:
        $product_details_link = vlc_internal_link($order['product_id'], 'cms/course_subject_details.php?course_subject='.$order['product_id']);
        break;
      case 6:
        $product_details_link = vlc_internal_link($order['product_id'], 'cms/cert_user_details.php?cert_user='.$order['product_id']);
        break;
    }
    /* customer details link and discount type */
    $discount_type_id = $order['discount_type_id'] * 10000 + $order['discount_id'];
    if ($discount_type_id == 0) $discount_type_id = 'NULL';
    /* do not allow users to edit discount type for partner orders */
    if ($order['customer_type_id'] == 2)
    {
      $customer_details_link = vlc_internal_link($order['customer_id'], 'cms/partner_details.php?partner='.$order['customer_id']);
      $discount_type_options = $discount_options_array[$order['discount_type_id']]['options'][$discount_type_id];
      $discount_type_options .= '<input type="hidden" name="orders['.$order['order_id'].'][discount_type_id]" value="'.$discount_type_id.'">';
    }
    else
    {
      $customer_details_link = vlc_internal_link($order['customer_id'], 'cms/user_details.php?user='.$order['customer_id']);
      $discount_type_options = vlc_select_box($discount_options_array, 'array', 'orders['.$order['order_id'].'][discount_type_id]', $discount_type_id, false);
    }
    /* add course id field if product type is course registration */
    if (isset($order['course_id'])) $course_id_field = '<input type="hidden" name="orders['.$order['order_id'].'][course_id]" value="'.$order['course_id'].'">';
    else $course_id_field = '';
    /* order details */
    $output .= '<tr>';
    $output .= '<td rowspan="9" valign="top">';
    $output .= '<nobr><input type="checkbox" name="order_id_array[]" value="'.$order['order_id'].'" id="'.$order['order_id'].'" checked> <label for="'.$order['order_id'].'">'.$result_num.'.</label></nobr>';
    $output .= '<a name="order-'.$order['order_id'].'"></a>';
    $output .= '</td>';
    $output .= '<td><b>Order ID:</b></td><td>'.vlc_internal_link($order['order_id'], 'cms/order_details.php?order='.$order['order_id']).'</td>';
    $output .= '<td><b>Order Date:</b></td><td align="right">'.date('n/j/Y', $order['order_date_timestamp']).'</td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><b>Order Status:</b></td><td>'.vlc_select_box($order_status_options_array, 'array', 'orders['.$order['order_id'].'][order_status]', $order['order_status'], true).'</td>';
    $output .= '<td><b>Payment Status:</b></td><td align="right">'.$order['payment_status'].'</td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><b>Product Type:</b></td><td colspan="3">'.$order['product_type'].'</td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><b>Product:</b></td><td>'.$order['product'].' ('.$product_details_link.')'.$course_id_field.'</td>';
    $output .= '<td><b>Order Cost:</b></td><td align="right">$'.number_format($order['order_cost'] / 100, 2).'</td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><b>Customer Type:</b></td><td>'.$order['customer_type'].'</td>';
    $output .= '<td><b>Amount Paid:</b></td><td align="right">$'.number_format($order['amount_paid'] / 100, 2).'</td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><b>Customer:</b></td><td>'.$order['customer'].' ('.$customer_details_link.')</td>';
    $output .= '<td><b>Amount Due:</b></td><td align="right">$'.number_format($order['amount_due'] / 100, 2).'</td>';
    $output .= '</tr>';
    $output .= '<tr>';
    $output .= '<td><b>Discount Type:</b></td>';
    $output .= '<td colspan="3">'.$discount_type_options.'</td>';
    $output .= '</tr>';
    /* list transactions */
    $payment_method_options_array = array(1 => 'Cash'
										, 2 => 'Check'
										, 3 => 'Credit Card'
										, 7 => 'Lump Sum CC'
										, 4 => 'VLCFF Credit'
										, 5 => 'Prepaid Funds'
										, 6 => 'Wire Transfer'
										, 8 => 'ACTA Fund'
										, 9 => 'Connor Trust'
										, 10 => 'Marianist'
										, 11 => 'Bosco Schlrshp'
										, 12 => 'Spc Needs Schlrshp'
										, 13 => 'Gift Certificate'
	);
    $output .= '<tr><td colspan="4" valign="top"><b>Transactions:</b></td></tr>';
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
    $output .= '<tr><td colspan="3" align="right"><b>New Transaction:</b></td><td align="center"><nobr>$ <input type="text" size="10" name="orders['.$order['order_id'].'][transaction_amount]" style="text-align:right"></nobr></td><td align="center">'.vlc_select_box($payment_method_options_array, 'array', 'orders['.$order['order_id'].'][payment_method_id]', -1, true).'</td><td align="center"><input type="text" size="10" name="orders['.$order['order_id'].'][check_number]" style="text-align:right"></td><td colspan="2">&nbsp;</td></tr>';
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
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
