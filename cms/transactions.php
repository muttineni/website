<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'transactions';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* initialize form fields variable */
$form_fields = array
(
  'start' => 0,
  'num' => 10,
  'transaction_id' => '',
  'payment_method_id' => 'NULL',
  'transaction_status' => -1,
  'transaction_report_status' => -1,
  'customer_id' => '',
  'customer_type_id' => 'NULL',
  'check_number' => '',
  'transaction_date_min_year' => 2000,
  'transaction_date_min_month' => 1,
  'transaction_date_min_day' => 1,
  'transaction_date_max_year' => 2020,
  'transaction_date_max_month' => 12,
  'transaction_date_max_day' => 31,
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
if (!is_numeric($form_fields['transaction_date_min_year'])) $form_fields['transaction_date_min_year'] = 2000;
if (!is_numeric($form_fields['transaction_date_min_month'])) $form_fields['transaction_date_min_month'] = 1;
if (!is_numeric($form_fields['transaction_date_min_day'])) $form_fields['transaction_date_min_day'] = 1;
if (!is_numeric($form_fields['transaction_date_max_year'])) $form_fields['transaction_date_max_year'] = 2020;
if (!is_numeric($form_fields['transaction_date_max_month'])) $form_fields['transaction_date_max_month'] = 12;
if (!is_numeric($form_fields['transaction_date_max_day'])) $form_fields['transaction_date_max_day'] = 31;
$transaction_date_min = $form_fields['transaction_date_min_year'].'-'.$form_fields['transaction_date_min_month'].'-'.$form_fields['transaction_date_min_day'];
$transaction_date_max = $form_fields['transaction_date_max_year'].'-'.$form_fields['transaction_date_max_month'].'-'.$form_fields['transaction_date_max_day'];
$where_clause = " WHERE t.transaction_date >= '".$transaction_date_min."'";
$where_clause .= " AND t.transaction_date <= '".$transaction_date_max."'";
if (is_numeric($form_fields['transaction_id']) and $form_fields['transaction_id'] > 0) $where_clause .= ' AND t.transaction_id = '.$form_fields['transaction_id'];
if (is_numeric($form_fields['transaction_status']) and $form_fields['transaction_status'] != -1) $where_clause .= ' AND t.transaction_status = '.$form_fields['transaction_status'];
else $form_fields['transaction_status'] = -1;
if (is_numeric($form_fields['transaction_report_status']) and $form_fields['transaction_report_status'] != -1)
{
  switch ($form_fields['transaction_report_status'])
  {
    /* successful */
    case 1:
      $where_clause .= ' AND t.payment_method_id = 3 AND r.transaction_type_id = 1 AND r.transaction_report_status = 1';
      break;
    /* unsuccessful */
    case 2:
      $where_clause .= ' AND t.payment_method_id = 3 AND r.transaction_id IS NULL';
      break;
    /* refund */
    case 3:
      $where_clause .= ' AND t.payment_method_id = 3 AND r.transaction_type_id = 2 AND r.transaction_report_status = 1';
      break;
    /* error */
    case 4:
      $where_clause .= ' AND t.payment_method_id = 3 AND r.transaction_type_id = 1 AND r.transaction_report_status = 0';
      break;
  }
}
else $form_fields['transaction_report_status'] = -1;
if (is_numeric($form_fields['payment_method_id']) and $form_fields['payment_method_id'] > 0) $where_clause .= ' AND t.payment_method_id = '.$form_fields['payment_method_id'];
if (is_numeric($form_fields['customer_id']) and $form_fields['customer_id'] > 0) $where_clause .= ' AND t.customer_id = '.$form_fields['customer_id'];
if (is_numeric($form_fields['customer_type_id']) and $form_fields['customer_type_id'] > 0) $where_clause .= ' AND t.customer_type_id = '.$form_fields['customer_type_id'];
if (strlen($form_fields['check_number']) > 0) $where_clause .= " AND t.check_number LIKE '".$form_fields['check_number']."'";
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = stripslashes($value);
}
/* query sorting parameters */
$sort_field_array = array(1 => 't.transaction_id', 't.transaction_date', 'customer', 't.payment_method_id', 't.transaction_amount', 't.transaction_status', 't.check_number', 'r.transaction_id');
$sort_options_array = array(1 => 'Transaction ID', 'Transaction Date', 'Customer', 'Payment Method', 'Transaction Amount', 'Transaction Status', 'Check Number', 'Transaction Report Status');
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
  FROM transactions AS t
      LEFT JOIN transaction_reports AS r ON t.transaction_id = r.transaction_id
      LEFT JOIN users AS m ON t.customer_id = m.user_id
      LEFT JOIN partners AS p ON t.customer_id = p.partner_id
  $where_clause
  GROUP BY t.transaction_id
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
    SELECT t.transaction_id, t.transaction_date,
      IF(t.customer_type_id = 2, p.description, CONCAT(m.first_name, ' ', m.last_name)) AS customer,
      d.description, t.transaction_amount / 100.00, IF(t.transaction_status, 'Successful', 'Unsuccessful'), t.check_number,
      IF(r.transaction_id IS NULL, 'Unsuccessful', 'Successful')
    FROM transactions AS t
      LEFT JOIN transaction_reports AS r ON t.transaction_id = r.transaction_id
      LEFT JOIN users AS m ON t.customer_id = m.user_id
      LEFT JOIN partners AS p ON t.customer_id = p.partner_id
      INNER JOIN payment_methods AS d ON t.payment_method_id = d.payment_method_id
END_QUERY;
  if (in_array($form_fields['export_data'], array(1, 2))) $export_query .= $where_clause;
  $export_query .= ' GROUP BY t.transaction_id';
  $export_query .= $order_clause;
  if ($form_fields['export_data'] == 1) $export_query .= $limit_clause;
  $result = mysql_query($export_query, $site_info['db_conn']);
  $export_array = array();
  $export_array[] = array('Transaction ID', 'Transaction Date', 'Customer', 'Payment Method', 'Transaction Amount', 'Transaction Status', 'Check Number', 'Transaction Report Status');
  while ($record = mysql_fetch_row($result)) $export_array[] = $record;
  switch ($form_fields['export_format'])
  {
    case 1:
      vlc_export_data($export_array, 'transactions', 1);
      break;
    case 2:
      vlc_export_data($export_array, 'transactions', 2, 'P');
      break;
    case 3:
      vlc_export_data($export_array, 'transactions', 2, 'L');
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
  $first_url = 'cms/transactions.php?'.$url_parameters.'&start='.$first_start;
  $first_link = vlc_internal_link('&laquo; First', $first_url);
  $prev_start = $form_fields['start'] - $form_fields['num'];
  if ($prev_start < 0) $prev_start = 0;
  $prev_url = 'cms/transactions.php?'.$url_parameters.'&start='.$prev_start;
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
  $next_url = 'cms/transactions.php?'.$url_parameters.'&start='.$next_start;
  $next_link = vlc_internal_link('Next &rsaquo;', $next_url);
  /* determine the index of the last record of the search results (the index of the first record is "0") */
  $last_index = $total_rows - 1;
  /* determine the index of the first record of the last page of search results */
  $last_start = $last_index - ($last_index % $form_fields['num']);
  $last_url = 'cms/transactions.php?'.$url_parameters.'&start='.$last_start;
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
      $page_link_url = 'cms/transactions.php?'.$url_parameters.'&start='.$page_start;
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
/* build array for "payment method" select box */
$payment_method_options_array = array();
$payment_method_query = <<< END_QUERY
  SELECT payment_method_id, description
  FROM payment_methods
  ORDER BY payment_method_id
END_QUERY;
$result = mysql_query($payment_method_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $payment_method_options_array[$record['payment_method_id']] = $record['description'];
/* build array for "customer type" select box */
$customer_type_options_array = array();
$customer_type_query = <<< END_QUERY
  SELECT customer_type_id, description
  FROM customer_types
  ORDER BY customer_type_id
END_QUERY;
$result = mysql_query($customer_type_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $customer_type_options_array[$record['customer_type_id']] = $record['description'];
/* transaction status values */
$transaction_status_options_array = array(1 => 'Successful', 0 => 'Unsuccessful');
/* transaction report status values */
$transaction_report_status_options_array = array(1 => 'Successful', 2 => 'Unsuccessful', 3 => 'Refund', 4 => 'Error');
/* year, month, day arrays */
for ($i = 2000; $i <= 2020; $i++) $years_array[$i] = $i;
for ($i = 1; $i <= 12; $i++) $months_array[$i] = date('F', mktime(0,0,0,$i,1,0));
for ($i = 1; $i <= 31; $i++) $days_array[$i] = $i;
/* select boxes for cycle dates */
$transaction_date_min_year_dropdown = vlc_select_box($years_array, 'array', 'transaction_date_min_year', $form_fields['transaction_date_min_year'], true);
$transaction_date_min_month_dropdown = vlc_select_box($months_array, 'array', 'transaction_date_min_month', $form_fields['transaction_date_min_month'], true);
$transaction_date_min_day_dropdown = vlc_select_box($days_array, 'array', 'transaction_date_min_day', $form_fields['transaction_date_min_day'], true);
$transaction_date_max_year_dropdown = vlc_select_box($years_array, 'array', 'transaction_date_max_year', $form_fields['transaction_date_max_year'], true);
$transaction_date_max_month_dropdown = vlc_select_box($months_array, 'array', 'transaction_date_max_month', $form_fields['transaction_date_max_month'], true);
$transaction_date_max_day_dropdown = vlc_select_box($days_array, 'array', 'transaction_date_max_day', $form_fields['transaction_date_max_day'], true);
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
$output = '';
$output .= '<h3>Transaction Reports</h3>';
$output .= '<p>To upload a transaction report, click <b>&quot;Browse...&quot;</b> to find the file and click <b>&quot;Submit&quot;</b>:</p>';
$output .= '<form method="post" action="transaction_report_upload_action.php?'.$url_parameters.'&start='.$form_fields['start'].'" enctype="multipart/form-data">';
$output .= '<input type="hidden" name="MAX_FILE_SIZE" value="2097152">';
$output .= '<ul><li>Upload Transaction Report: <input type="file" size="40" name="webpay_report">&nbsp;<input type="submit" value="Submit"></li></ul>';
$output .= '</form>';
$output .= '<h3>Export</h3>';
$output .= '<p>To export data, select what you would like to export, the format you would like to export to, and click <b>&quot;Go&quot;</b>:</p>';
$output .= '<form method="get" action="transactions.php">';
$output .= $export_hidden_fields;
$output .= '<ul><li>Export '.vlc_select_box($export_data_array, 'array', 'export_data', -1, true).' to '.vlc_select_box($export_format_array, 'array', 'export_format', -1, true).' <input type="submit" value="Go"></li></ul>';
$output .= '</form>';
$output .= '<p align="center"><b>'.$total_rows.'</b> '.$results_label.' Found (<b>'.$last_page.'</b> '.$pages_label.')</p>';
$output .= '<form method="get" action="transactions.php">';
$output .= '<input type="hidden" name="start" value="0">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
$output .= '<tr>';
$output .= '<td align="right"><b>Transaction ID:</b></td><td><input type="text" size="10" name="transaction_id" value="'.$form_fields['transaction_id'].'"></td>';
$output .= '<td align="right"><b>Payment Method:</b></td><td>'.vlc_select_box($payment_method_options_array, 'array', 'payment_method_id', $form_fields['payment_method_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><b>Customer ID:</b></td><td><input type="text" size="10" name="customer_id" value="'.$form_fields['customer_id'].'"></td>';
$output .= '<td align="right"><b>Customer Type:</b></td><td>'.vlc_select_box($customer_type_options_array, 'array', 'customer_type_id', $form_fields['customer_type_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr><td align="right"><b>Check Number:</b></td><td colspan="3"><input type="text" size="10" name="check_number" value="'.$form_fields['check_number'].'"></td></tr>';
$output .= '<tr>';
$output .= '<td align="right"><b>Transaction Status:</b></td><td>'.vlc_select_box($transaction_status_options_array, 'array', 'transaction_status', $form_fields['transaction_status'], false).'</td>';
$output .= '<td align="right"><b>Transaction Report Status:</b></td><td>'.vlc_select_box($transaction_report_status_options_array, 'array', 'transaction_report_status', $form_fields['transaction_report_status'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><b>Transaction Date:</b></td>';
$output .= '<td colspan="3">Between '.$transaction_date_min_month_dropdown.' '.$transaction_date_min_day_dropdown.' '.$transaction_date_min_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[2].transaction_date_min_year,document.forms[2].transaction_date_min_month,document.forms[2].transaction_date_min_day,false,false,this);"> and '.$transaction_date_max_month_dropdown.' '.$transaction_date_max_day_dropdown.' '.$transaction_date_max_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[2].transaction_date_max_year,document.forms[2].transaction_date_max_month,document.forms[2].transaction_date_max_day,false,false,this);"></td>';
$output .= '</tr>';
$output .= '<tr><td align="right" valign="top" rowspan="3"><b>Sort By:</b></td><td colspan="3">'.$sort_post_vars_string.'</td></tr>';
$output .= '<tr><td colspan="4" align="center"><b>Results / Page:</b> '.vlc_select_box($num_results_array, 'array', 'num', $form_fields['num'], true).' <input type="submit" value="Submit"> <input type="button" value="Clear Search Fields" onclick="clear_fields(this.form); this.form.num.selectedIndex = 0;"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
/* search results navigation */
if ($total_rows > 0) $output .= "<p align=\"right\"><b>Showing $list_start - $list_end of $total_rows (Page $current_page of $last_page)</b></p>";
$output .= $nav_links;
/* get registration records */
$transaction_query = <<< END_QUERY
  SELECT t.transaction_id, t.transaction_status, UNIX_TIMESTAMP(t.transaction_date) AS transaction_date,
    t.transaction_amount, t.payment_method_id, t.check_number,
    r.transaction_report_id, r.transaction_type_id, r.transaction_report_status,
    IF(t.customer_type_id = 2, p.description, CONCAT(m.first_name, ' ', m.last_name)) AS customer
  FROM transactions AS t
      LEFT JOIN transaction_reports AS r ON t.transaction_id = r.transaction_id
      LEFT JOIN users AS m ON t.customer_id = m.user_id
      LEFT JOIN partners AS p ON t.customer_id = p.partner_id
  $where_clause
  GROUP BY t.transaction_id
END_QUERY;
$transaction_query .= $order_clause;
$transaction_query .= $limit_clause;
$result = mysql_query($transaction_query, $site_info['db_conn']);
/* compile search results */
if (mysql_num_rows($result) > 0)
{
  $transactions = array();
  while ($record = mysql_fetch_array($result)) $transactions[$record['transaction_id']] = $record;
  $transaction_id_list = join(', ', array_keys($transactions));
  /* build results table */
  $result_num = $list_start;
  $output .= '<form method="post" action="transaction_action.php?'.$url_parameters.'&start='.$form_fields['start'].'">';
  $output .= '<h3>Update All</h3>';
  $output .= '<p>To update all checked records at once, select one of the following options and click <b>&quot;Submit&quot;</b>:</p>';
  $output .= '<ul><li><b>Transaction Status:</b> '.vlc_select_box($transaction_status_options_array, 'array', 'update_all_transaction_status', -1, false).'</li></ul>';
  $output .= '<p>&nbsp;</p>';
  $output .= '<table border="1" cellpadding="5" cellspacing="0">';
  $output .= '<tr>';
  $output .= '<th><input type="checkbox" name="check_all_checkbox" onclick="check_all(this, \'transaction_id_array[]\');" id="check-all" checked>&nbsp;<label for="check-all">Check All</label></th>';
  /* build column headers with sort links */
  foreach ($sort_options_array as $sort_by_key => $sort_by_value)
  {
    $sort_asc_url = 'cms/transactions.php?'.$url_parameters_without_sort.'sort_by[]='.$sort_by_key.'&sort_dir[]=1&sort_by[]=NULL&sort_dir[]=NULL&sort_by[]=NULL&sort_dir[]=NULL&num='.$form_fields['num'].'&start=0';
    $sort_asc_link = vlc_internal_link('<img border="0" src="'.$site_info['images_url'].'sort-asc.png">', $sort_asc_url);
    $sort_desc_url = 'cms/transactions.php?'.$url_parameters_without_sort.'sort_by[]='.$sort_by_key.'&sort_dir[]=2&sort_by[]=NULL&sort_dir[]=NULL&sort_by[]=NULL&sort_dir[]=NULL&num='.$form_fields['num'].'&start=0';
    $sort_desc_link = vlc_internal_link('<img border="0" src="'.$site_info['images_url'].'sort-desc.png">', $sort_desc_url);
    $output .= "<th>$sort_by_value<br>$sort_asc_link $sort_desc_link</th>";
  }
  $output .= '</tr>';
  foreach ($transactions as $transaction)
  {
    /* only credit card transactions show up in the transaction reports */
    if ($transaction['payment_method_id'] == 3)
    {
      if (isset($transaction['transaction_report_id']))
      {
        if ($transaction['transaction_report_status'])
        {
          if ($transaction['transaction_type_id'] == 1) $transaction_report_status = 'Successful';
          else $transaction_report_status = 'Refund';
        }
        else $transaction_report_status = 'Error';
      }
      else $transaction_report_status = 'Unsuccessful';
    }
    else $transaction_report_status = '<span class="inactive">(N/A)</span>';
    if (!isset($transaction['check_number'])) $transaction['check_number'] = '<span class="inactive">(N/A)</span>';
    $output .= '<tr>';
    $output .= '<td><nobr><input type="checkbox" name="transaction_id_array[]" value="'.$transaction['transaction_id'].'" id="'.$transaction['transaction_id'].'" checked> <label for="'.$transaction['transaction_id'].'">'.$result_num.'.</label></nobr><a name="transaction-'.$transaction['transaction_id'].'"></a></td>';
    $output .= '<td align="center">'.vlc_internal_link($transaction['transaction_id'], 'cms/transaction_details.php?transaction='.$transaction['transaction_id']).'</td>';
    $output .= '<td align="center">'.date('n/j/Y', $transaction['transaction_date']).'</td>';
    $output .= '<td><nobr>'.$transaction['customer'].'</nobr></td>';
    $output .= '<td align="center">'.$payment_method_options_array[$transaction['payment_method_id']].'</td>';
    $output .= '<td align="right">$'.number_format($transaction['transaction_amount'] / 100, 2).'</td>';
    $output .= '<td align="center">'.vlc_select_box($transaction_status_options_array, 'array', 'transactions['.$transaction['transaction_id'].'][transaction_status]', $transaction['transaction_status'], true).'<input type="hidden" name="transactions['.$transaction['transaction_id'].'][previous_transaction_status]" value="'.$transaction['transaction_status'].'"></td>';
    $output .= '<td align="center">'.$transaction['check_number'].'</td>';
    $output .= '<td align="center">'.$transaction_report_status.'</td>';
    $output .= '</tr>';
    $result_num++;
  }
  $output .= '<tr><td colspan="9" align="center"><input type="submit" value="Submit"></td></tr>';
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
