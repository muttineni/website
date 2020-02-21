<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'resources';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* initialize form fields variable */
$form_fields = array
(
  'start' => 0,
  'num' => 10,
  'resource_id' => '',
  'title' => '',
  'resource_type_id' => 'NULL',
  'language_id' => 'NULL',
  'active_start_min_year' => 2000,
  'active_start_min_month' => 1,
  'active_start_min_day' => 1,
  'active_start_max_year' => 2020,
  'active_start_max_month' => 12,
  'active_start_max_day' => 31,
  'active_end_min_year' => 2000,
  'active_end_min_month' => 1,
  'active_end_min_day' => 1,
  'active_end_max_year' => 2020,
  'active_end_max_month' => 12,
  'active_end_max_day' => 31,
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
if (!is_numeric($form_fields['active_start_min_year'])) $form_fields['active_start_min_year'] = 2000;
if (!is_numeric($form_fields['active_start_min_month'])) $form_fields['active_start_min_month'] = 1;
if (!is_numeric($form_fields['active_start_min_day'])) $form_fields['active_start_min_day'] = 1;
if (!is_numeric($form_fields['active_start_max_year'])) $form_fields['active_start_max_year'] = 2020;
if (!is_numeric($form_fields['active_start_max_month'])) $form_fields['active_start_max_month'] = 12;
if (!is_numeric($form_fields['active_start_max_day'])) $form_fields['active_start_max_day'] = 31;
if (!is_numeric($form_fields['active_end_min_year'])) $form_fields['active_end_min_year'] = 2000;
if (!is_numeric($form_fields['active_end_min_month'])) $form_fields['active_end_min_month'] = 1;
if (!is_numeric($form_fields['active_end_min_day'])) $form_fields['active_end_min_day'] = 1;
if (!is_numeric($form_fields['active_end_max_year'])) $form_fields['active_end_max_year'] = 2020;
if (!is_numeric($form_fields['active_end_max_month'])) $form_fields['active_end_max_month'] = 12;
if (!is_numeric($form_fields['active_end_max_day'])) $form_fields['active_end_max_day'] = 31;
$active_start_min = $form_fields['active_start_min_year'].'-'.$form_fields['active_start_min_month'].'-'.$form_fields['active_start_min_day'];
$active_start_max = $form_fields['active_start_max_year'].'-'.$form_fields['active_start_max_month'].'-'.$form_fields['active_start_max_day'];
$active_end_min = $form_fields['active_end_min_year'].'-'.$form_fields['active_end_min_month'].'-'.$form_fields['active_end_min_day'];
$active_end_max = $form_fields['active_end_max_year'].'-'.$form_fields['active_end_max_month'].'-'.$form_fields['active_end_max_day'];
$where_clause .= " AND r.active_start >= '".$active_start_min."'";
$where_clause .= " AND r.active_start <= '".$active_start_max."'";
$where_clause .= " AND r.active_end >= '".$active_end_min."'";
$where_clause .= " AND r.active_end <= '".$active_end_max."'";
if (is_numeric($form_fields['resource_id']) and $form_fields['resource_id'] > 0) $where_clause .= ' AND r.resource_id = '.$form_fields['resource_id'];
if (strlen($form_fields['title']) > 0) $where_clause .= " AND r.title LIKE '".$form_fields['title']."'";
if (is_numeric($form_fields['resource_type_id']) and $form_fields['resource_type_id'] > 0) $where_clause .= ' AND r.resource_type_id = '.$form_fields['resource_type_id'];
if (is_numeric($form_fields['language_id']) and $form_fields['language_id'] > 0) $where_clause .= ' AND r.language_id = '.$form_fields['language_id'];
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = stripslashes($value);
}
/* query sorting parameters */
$sort_field_array = array(1 => 'r.resource_id', 'r.resource_type_id', 'r.title', 'r.content');
$sort_options_array = array(1 => 'Resource ID', 'Resource Type', 'Title', 'Content');
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
  FROM resources AS r
  WHERE 1
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
    SELECT r.resource_id, t.description AS resource_type, IFNULL(r.title, '') AS title
    FROM resources AS r, resource_types AS t
    WHERE r.resource_type_id = t.resource_type_id
END_QUERY;
  if (in_array($form_fields['export_data'], array(1, 2))) $export_query .= $where_clause;
  $export_query .= $order_clause;
  if ($form_fields['export_data'] == 1) $export_query .= $limit_clause;
  $result = mysql_query($export_query, $site_info['db_conn']);
  $export_array = array();
  $export_array[] = array('Resource ID', 'Resource Type', 'Title');
  while ($record = mysql_fetch_row($result)) $export_array[] = $record;
  switch ($form_fields['export_format'])
  {
    case 1:
      vlc_export_data($export_array, 'resources', 1);
      break;
    case 2:
      vlc_export_data($export_array, 'resources', 2, 'P');
      break;
    case 3:
      vlc_export_data($export_array, 'resources', 2, 'L');
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
  $first_url = 'cms/resources.php?'.$url_parameters.'&start='.$first_start;
  $first_link = vlc_internal_link('&laquo; First', $first_url);
  $prev_start = $form_fields['start'] - $form_fields['num'];
  if ($prev_start < 0) $prev_start = 0;
  $prev_url = 'cms/resources.php?'.$url_parameters.'&start='.$prev_start;
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
  $next_url = 'cms/resources.php?'.$url_parameters.'&start='.$next_start;
  $next_link = vlc_internal_link('Next &rsaquo;', $next_url);
  /* determine the index of the last record of the search results (the index of the first record is "0") */
  $last_index = $total_rows - 1;
  /* determine the index of the first record of the last page of search results */
  $last_start = $last_index - ($last_index % $form_fields['num']);
  $last_url = 'cms/resources.php?'.$url_parameters.'&start='.$last_start;
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
      $page_link_url = 'cms/resources.php?'.$url_parameters.'&start='.$page_start;
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
/* build array for "resource type" select box */
$resource_type_options_array = array();
$resource_type_query = <<< END_QUERY
  SELECT resource_type_id, description
  FROM resource_types
  ORDER BY description
END_QUERY;
$result = mysql_query($resource_type_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $resource_type_options_array[$record['resource_type_id']] = $record['description'];
/* build array for "language" select box */
$language_options_array = array();
$language_query = <<< END_QUERY
  SELECT language_id, description
  FROM languages
  ORDER BY language_id
END_QUERY;
$result = mysql_query($language_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $language_options_array[$record['language_id']] = $record['description'];
/* year, month, day arrays */
for ($i = 2000; $i <= 2020; $i++) $years_array[$i] = $i;
for ($i = 1; $i <= 12; $i++) $months_array[$i] = date('F', mktime(0,0,0,$i,1,0));
for ($i = 1; $i <= 31; $i++) $days_array[$i] = $i;
/* select boxes for active start and end dates */
$active_start_min_year_dropdown = vlc_select_box($years_array, 'array', 'active_start_min_year', $form_fields['active_start_min_year'], true);
$active_start_min_month_dropdown = vlc_select_box($months_array, 'array', 'active_start_min_month', $form_fields['active_start_min_month'], true);
$active_start_min_day_dropdown = vlc_select_box($days_array, 'array', 'active_start_min_day', $form_fields['active_start_min_day'], true);
$active_start_max_year_dropdown = vlc_select_box($years_array, 'array', 'active_start_max_year', $form_fields['active_start_max_year'], true);
$active_start_max_month_dropdown = vlc_select_box($months_array, 'array', 'active_start_max_month', $form_fields['active_start_max_month'], true);
$active_start_max_day_dropdown = vlc_select_box($days_array, 'array', 'active_start_max_day', $form_fields['active_start_max_day'], true);
$active_end_min_year_dropdown = vlc_select_box($years_array, 'array', 'active_end_min_year', $form_fields['active_end_min_year'], true);
$active_end_min_month_dropdown = vlc_select_box($months_array, 'array', 'active_end_min_month', $form_fields['active_end_min_month'], true);
$active_end_min_day_dropdown = vlc_select_box($days_array, 'array', 'active_end_min_day', $form_fields['active_end_min_day'], true);
$active_end_max_year_dropdown = vlc_select_box($years_array, 'array', 'active_end_max_year', $form_fields['active_end_max_year'], true);
$active_end_max_month_dropdown = vlc_select_box($months_array, 'array', 'active_end_max_month', $form_fields['active_end_max_month'], true);
$active_end_max_day_dropdown = vlc_select_box($days_array, 'array', 'active_end_max_day', $form_fields['active_end_max_day'], true);
/* build array for "resource" select box */
$resource_options_array = array(1 => 'VLCFF Announcement', 59 => 'VLCFF Announcement with Audio', 2 => 'VLCFF News Article', 56 => 'New Course Announcement', 57 => 'Newsletter');
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
$output .= '<form method="get" action="resource_details.php">';
$output .= '<p align="center"><b>Create a new resource:</b> '.vlc_select_box($resource_options_array, 'array', 'type', -1, true).' <input type="submit" value="Go"></p>';
$output .= '</form>';
$output .= '<form method="get" action="resources.php">';
$output .= $export_hidden_fields;
$output .= '<p align="center">Export '.vlc_select_box($export_data_array, 'array', 'export_data', -1, true).' to '.vlc_select_box($export_format_array, 'array', 'export_format', -1, true).' <input type="submit" value="Go"></p>';
$output .= '</form>';
$output .= '<p align="center"><b>'.$total_rows.'</b> '.$results_label.' Found (<b>'.$last_page.'</b> '.$pages_label.')</p>';
$output .= '<form method="get" action="resources.php">';
$output .= '<input type="hidden" name="start" value="0">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
$output .= '<tr>';
$output .= '<td align="right"><b>Resource ID:</b></td><td><input type="text" size="10" name="resource_id" value="'.$form_fields['resource_id'].'"></td>';
$output .= '<td align="right"><b>Title:</b></td><td><input type="text" size="30" name="title" value="'.$form_fields['title'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><b>Resource Type:</b></td><td>'.vlc_select_box($resource_type_options_array, 'array', 'resource_type_id', $form_fields['resource_type_id'], false).'</td>';
$output .= '<td align="right"><b>Language:</b></td><td>'.vlc_select_box($language_options_array, 'array', 'language_id', $form_fields['language_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><b>Active Start Date:</b></td>';
$output .= '<td colspan="3">Between '.$active_start_min_month_dropdown.' '.$active_start_min_day_dropdown.' '.$active_start_min_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[2].active_start_min_year,document.forms[2].active_start_min_month,document.forms[2].active_start_min_day,false,false,this);"> and '.$active_start_max_month_dropdown.' '.$active_start_max_day_dropdown.' '.$active_start_max_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[2].active_start_max_year,document.forms[2].active_start_max_month,document.forms[2].active_start_max_day,false,false,this);"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><b>Active End Date:</b></td>';
$output .= '<td colspan="3">Between '.$active_end_min_month_dropdown.' '.$active_end_min_day_dropdown.' '.$active_end_min_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[2].active_end_min_year,document.forms[2].active_end_min_month,document.forms[2].active_end_min_day,false,false,this);"> and '.$active_end_max_month_dropdown.' '.$active_end_max_day_dropdown.' '.$active_end_max_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[2].active_end_max_year,document.forms[2].active_end_max_month,document.forms[2].active_end_max_day,false,false,this);"></td>';
$output .= '</tr>';
$output .= '<tr><td align="right" valign="top" rowspan="3"><b>Sort By:</b></td><td colspan="3">'.$sort_post_vars_string.'</td></tr>';
$output .= '<tr><td colspan="4" align="center"><b>Results / Page:</b> '.vlc_select_box($num_results_array, 'array', 'num', $form_fields['num'], true).' <input type="submit" value="Submit"> <input type="button" value="Clear Search Fields" onclick="clear_fields(this.form); this.form.num.selectedIndex = 0;"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
/* search results navigation */
if ($total_rows > 0) $output .= "<p align=\"right\"><b>Showing $list_start - $list_end of $total_rows (Page $current_page of $last_page)</b></p>";
$output .= $nav_links;
/* get resources */
$resource_query = <<< END_QUERY
  SELECT r.resource_id, r.resource_type_id, r.title, r.content
  FROM resources AS r
  WHERE 1
  $where_clause
END_QUERY;
$resource_query .= $order_clause;
$resource_query .= $limit_clause;
$result = mysql_query($resource_query, $site_info['db_conn']);
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
    $sort_asc_url = 'cms/resources.php?'.$url_parameters_without_sort.'sort_by[]='.$sort_by_key.'&sort_dir[]=1&sort_by[]=NULL&sort_dir[]=NULL&sort_by[]=NULL&sort_dir[]=NULL&num='.$form_fields['num'].'&start=0';
    $sort_asc_link = vlc_internal_link('<img border="0" src="'.$site_info['images_url'].'sort-asc.png">', $sort_asc_url);
    $sort_desc_url = 'cms/resources.php?'.$url_parameters_without_sort.'sort_by[]='.$sort_by_key.'&sort_dir[]=2&sort_by[]=NULL&sort_dir[]=NULL&sort_by[]=NULL&sort_dir[]=NULL&num='.$form_fields['num'].'&start=0';
    $sort_desc_link = vlc_internal_link('<img border="0" src="'.$site_info['images_url'].'sort-desc.png">', $sort_desc_url);
    $output .= "<th><nobr>$sort_by_value $sort_asc_link $sort_desc_link</nobr></th>";
  }
  $output .= '</tr>';
  while ($record = mysql_fetch_array($result))
  {
    if (isset($record['title']))
    {
      if (strlen($record['title']) > 30) $record['title'] = substr($record['title'], 0, strrpos(substr($record['title'], 0, 30), ' ')).' ...';
    }
    else $record['title'] = '--';
    if (isset($record['content']))
    {
      if (strlen($record['content']) > 30) $record['content'] = substr($record['content'], 0, strrpos(substr($record['content'], 0, 30), ' ')).' ...';
    }
    else $record['content'] = '--';
    $output .= '<tr>';
    $output .= '<td>'.$result_num.'.</td>';
    $output .= '<td align="center">'.vlc_internal_link($record['resource_id'], 'cms/resource_details.php?resource='.$record['resource_id']).'</td>';
    $output .= '<td>'.$resource_type_options_array[$record['resource_type_id']].'</td>';
    $output .= '<td>'.$record['title'].'</td>';
    $output .= '<td>'.$record['content'].'</td>';
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
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
