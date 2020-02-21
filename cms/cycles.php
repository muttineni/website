<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'cycles';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* initialize form fields variable */
$form_fields = array
(
  'start' => 0,
  'num' => 10,
  'cycle_id' => '',
  'code' => '',
  'description' => '',
  'cycle_start_min_year' => 2000,
  'cycle_start_min_month' => 1,
  'cycle_start_min_day' => 1,
  'cycle_start_max_year' => 2020,
  'cycle_start_max_month' => 12,
  'cycle_start_max_day' => 31,
  'cycle_end_min_year' => 2000,
  'cycle_end_min_month' => 1,
  'cycle_end_min_day' => 1,
  'cycle_end_max_year' => 2020,
  'cycle_end_max_month' => 12,
  'cycle_end_max_day' => 31,
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
if (!is_numeric($form_fields['cycle_start_min_year'])) $form_fields['cycle_start_min_year'] = 2000;
if (!is_numeric($form_fields['cycle_start_min_month'])) $form_fields['cycle_start_min_month'] = 1;
if (!is_numeric($form_fields['cycle_start_min_day'])) $form_fields['cycle_start_min_day'] = 1;
if (!is_numeric($form_fields['cycle_start_max_year'])) $form_fields['cycle_start_max_year'] = 2020;
if (!is_numeric($form_fields['cycle_start_max_month'])) $form_fields['cycle_start_max_month'] = 12;
if (!is_numeric($form_fields['cycle_start_max_day'])) $form_fields['cycle_start_max_day'] = 31;
if (!is_numeric($form_fields['cycle_end_min_year'])) $form_fields['cycle_end_min_year'] = 2000;
if (!is_numeric($form_fields['cycle_end_min_month'])) $form_fields['cycle_end_min_month'] = 1;
if (!is_numeric($form_fields['cycle_end_min_day'])) $form_fields['cycle_end_min_day'] = 1;
if (!is_numeric($form_fields['cycle_end_max_year'])) $form_fields['cycle_end_max_year'] = 2020;
if (!is_numeric($form_fields['cycle_end_max_month'])) $form_fields['cycle_end_max_month'] = 12;
if (!is_numeric($form_fields['cycle_end_max_day'])) $form_fields['cycle_end_max_day'] = 31;
$cycle_start_min = $form_fields['cycle_start_min_year'].'-'.$form_fields['cycle_start_min_month'].'-'.$form_fields['cycle_start_min_day'];
$cycle_start_max = $form_fields['cycle_start_max_year'].'-'.$form_fields['cycle_start_max_month'].'-'.$form_fields['cycle_start_max_day'];
$cycle_end_min = $form_fields['cycle_end_min_year'].'-'.$form_fields['cycle_end_min_month'].'-'.$form_fields['cycle_end_min_day'];
$cycle_end_max = $form_fields['cycle_end_max_year'].'-'.$form_fields['cycle_end_max_month'].'-'.$form_fields['cycle_end_max_day'];
$where_clause = " WHERE cycle_start >= '$cycle_start_min'";
$where_clause .= " AND cycle_start <= '$cycle_start_max'";
$where_clause .= " AND cycle_end >= '$cycle_end_min'";
$where_clause .= " AND cycle_end <= '$cycle_end_max'";
if (is_numeric($form_fields['cycle_id']) and $form_fields['cycle_id'] > 0) $where_clause .= ' AND cycle_id = '.$form_fields['cycle_id'];
if (strlen($form_fields['code']) > 0) $where_clause .= " AND code LIKE '".$form_fields['code']."'";
if (strlen($form_fields['description']) > 0) $where_clause .= " AND description LIKE '".$form_fields['description']."'";
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = stripslashes($value);
}
/* query sorting parameters */
$sort_field_array = array(1 => 'cycle_id', 'code', 'description', 'cycle_start', 'cycle_end', 'registration_start', 'registration_end');
$sort_options_array = array(1 => 'Cycle ID', 'Code', 'Description', 'Cycle Start', 'Cycle End', 'Registration Start', 'Registration End');
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
  FROM cycles
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
    SELECT cycle_id, IFNULL(code, '') AS code, IFNULL(description, '') AS description,
      cycle_start, cycle_end,
      registration_start, registration_end
    FROM cycles
END_QUERY;
  if (in_array($form_fields['export_data'], array(1, 2))) $export_query .= $where_clause;
  $export_query .= $order_clause;
  if ($form_fields['export_data'] == 1) $export_query .= $limit_clause;
  $result = mysql_query($export_query, $site_info['db_conn']);
  $export_array = array();
  $export_array[] = array('Cycle ID', 'Cycle Code', 'Cycle Description', 'Cycle Start Date', 'Cycle End Date', 'Registration Start Date', 'Registration End Date');
  while ($record = mysql_fetch_row($result)) $export_array[] = $record;
  switch ($form_fields['export_format'])
  {
    case 1:
      vlc_export_data($export_array, 'cycles', 1);
      break;
    case 2:
      vlc_export_data($export_array, 'cycles', 2, 'P');
      break;
    case 3:
      vlc_export_data($export_array, 'cycles', 2, 'L');
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
  $first_url = 'cms/cycles.php?'.$url_parameters.'&start='.$first_start;
  $first_link = vlc_internal_link('&laquo; First', $first_url);
  $prev_start = $form_fields['start'] - $form_fields['num'];
  if ($prev_start < 0) $prev_start = 0;
  $prev_url = 'cms/cycles.php?'.$url_parameters.'&start='.$prev_start;
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
  $next_url = 'cms/cycles.php?'.$url_parameters.'&start='.$next_start;
  $next_link = vlc_internal_link('Next &rsaquo;', $next_url);
  /* determine the index of the last record of the search results (the index of the first record is "0") */
  $last_index = $total_rows - 1;
  /* determine the index of the first record of the last page of search results */
  $last_start = $last_index - ($last_index % $form_fields['num']);
  $last_url = 'cms/cycles.php?'.$url_parameters.'&start='.$last_start;
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
      $page_link_url = 'cms/cycles.php?'.$url_parameters.'&start='.$page_start;
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
/* select boxes for cycle dates */
$cycle_start_min_year_dropdown = vlc_select_box($years_array, 'array', 'cycle_start_min_year', $form_fields['cycle_start_min_year'], true);
$cycle_start_min_month_dropdown = vlc_select_box($months_array, 'array', 'cycle_start_min_month', $form_fields['cycle_start_min_month'], true);
$cycle_start_min_day_dropdown = vlc_select_box($days_array, 'array', 'cycle_start_min_day', $form_fields['cycle_start_min_day'], true);
$cycle_start_max_year_dropdown = vlc_select_box($years_array, 'array', 'cycle_start_max_year', $form_fields['cycle_start_max_year'], true);
$cycle_start_max_month_dropdown = vlc_select_box($months_array, 'array', 'cycle_start_max_month', $form_fields['cycle_start_max_month'], true);
$cycle_start_max_day_dropdown = vlc_select_box($days_array, 'array', 'cycle_start_max_day', $form_fields['cycle_start_max_day'], true);
$cycle_end_min_year_dropdown = vlc_select_box($years_array, 'array', 'cycle_end_min_year', $form_fields['cycle_end_min_year'], true);
$cycle_end_min_month_dropdown = vlc_select_box($months_array, 'array', 'cycle_end_min_month', $form_fields['cycle_end_min_month'], true);
$cycle_end_min_day_dropdown = vlc_select_box($days_array, 'array', 'cycle_end_min_day', $form_fields['cycle_end_min_day'], true);
$cycle_end_max_year_dropdown = vlc_select_box($years_array, 'array', 'cycle_end_max_year', $form_fields['cycle_end_max_year'], true);
$cycle_end_max_month_dropdown = vlc_select_box($months_array, 'array', 'cycle_end_max_month', $form_fields['cycle_end_max_month'], true);
$cycle_end_max_day_dropdown = vlc_select_box($days_array, 'array', 'cycle_end_max_day', $form_fields['cycle_end_max_day'], true);
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
$output = '<p align="center">'.vlc_internal_link('Click here to create a new cycle.', 'cms/cycle_details.php').'</p>';
$output .= '<form method="get" action="cycles.php">';
$output .= $export_hidden_fields;
$output .= '<p align="center">Export '.vlc_select_box($export_data_array, 'array', 'export_data', -1, true).' to '.vlc_select_box($export_format_array, 'array', 'export_format', -1, true).' <input type="submit" value="Go"></p>';
$output .= '</form>';
$output .= '<p align="center"><b>'.$total_rows.'</b> '.$results_label.' Found (<b>'.$last_page.'</b> '.$pages_label.')</p>';
$output .= '<form method="get" action="cycles.php">';
$output .= '<input type="hidden" name="start" value="0">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
$output .= '<tr><td align="right"><nobr><b>Cycle ID:</b></nobr></td><td colspan="3"><div class="yui-skin-sam"><div id="ac_cycle_id" class="autocomplete"><input type="text" name="cycle_id" id="ac_cycle_id_field" value="'.$form_fields['cycle_id'].'"><div id="ac_cycle_id_container"></div></div></div></td></tr>';
$output .= '<tr>';
$output .= '<td align="right" width="10%"><nobr><b>Code:</b></nobr></td><td width="40%"><div class="yui-skin-sam"><div id="ac_code" class="autocomplete"><input type="text" name="code" id="ac_code_field" value="'.$form_fields['code'].'"><div id="ac_code_container"></div></div></div></td>';
$output .= '<td align="right" width="10%"><nobr><b>Description:</b></nobr></td><td width="40%"><div class="yui-skin-sam"><div id="ac_description" class="autocomplete"><input type="text" name="description" id="ac_description_field" value="'.$form_fields['description'].'"><div id="ac_description_container"></div></div></div></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Cycle Start:</b></nobr></td>';
$output .= '<td colspan="3">Between '.$cycle_start_min_month_dropdown.' '.$cycle_start_min_day_dropdown.' '.$cycle_start_min_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].cycle_start_min_year,document.forms[1].cycle_start_min_month,document.forms[1].cycle_start_min_day,false,false,this);"> and '.$cycle_start_max_month_dropdown.' '.$cycle_start_max_day_dropdown.' '.$cycle_start_max_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].cycle_start_max_year,document.forms[1].cycle_start_max_month,document.forms[1].cycle_start_max_day,false,false,this);"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>Cycle End:</b></nobr></td>';
$output .= '<td colspan="3">Between '.$cycle_end_min_month_dropdown.' '.$cycle_end_min_day_dropdown.' '.$cycle_end_min_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].cycle_end_min_year,document.forms[1].cycle_end_min_month,document.forms[1].cycle_end_min_day,false,false,this);"> and '.$cycle_end_max_month_dropdown.' '.$cycle_end_max_day_dropdown.' '.$cycle_end_max_year_dropdown.' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[1].cycle_end_max_year,document.forms[1].cycle_end_max_month,document.forms[1].cycle_end_max_day,false,false,this);"></td>';
$output .= '</tr>';
$output .= '<tr><td align="right" valign="top" rowspan="3"><nobr><b>Sort By:</b></nobr></td><td colspan="3">'.$sort_post_vars_string.'</td></tr>';
$output .= '<tr><td colspan="4" align="center"><nobr><b>Results / Page:</b></nobr> '.vlc_select_box($num_results_array, 'array', 'num', $form_fields['num'], true).' <input type="submit" value="Submit"> <input type="button" value="Clear Search Fields" onclick="clear_fields(this.form); this.form.num.selectedIndex = 0;"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
/* search results navigation */
if ($total_rows > 0) $output .= "<p align=\"right\"><b>Showing $list_start - $list_end of $total_rows (Page $current_page of $last_page)</b></p>";
$output .= $nav_links;
/* get cycles */
$cycle_query = <<< END_QUERY
  SELECT cycle_id, IFNULL(code, '--') AS code, IFNULL(description, '--') AS description,
    UNIX_TIMESTAMP(cycle_start) AS cycle_start_timestamp,
    UNIX_TIMESTAMP(cycle_end) AS cycle_end_timestamp,
    UNIX_TIMESTAMP(registration_start) AS registration_start_timestamp,
    UNIX_TIMESTAMP(registration_end) AS registration_end_timestamp
  FROM cycles
END_QUERY;
$cycle_query .= $where_clause;
$cycle_query .= $order_clause;
$cycle_query .= $limit_clause;
$result = mysql_query($cycle_query, $site_info['db_conn']);
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
    $sort_asc_url = 'cms/cycles.php?'.$url_parameters_without_sort.'sort_by[]='.$sort_by_key.'&sort_dir[]=1&sort_by[]=NULL&sort_dir[]=NULL&sort_by[]=NULL&sort_dir[]=NULL&num='.$form_fields['num'].'&start=0';
    $sort_asc_link = vlc_internal_link('<img border="0" src="'.$site_info['images_url'].'sort-asc.png">', $sort_asc_url);
    $sort_desc_url = 'cms/cycles.php?'.$url_parameters_without_sort.'sort_by[]='.$sort_by_key.'&sort_dir[]=2&sort_by[]=NULL&sort_dir[]=NULL&sort_by[]=NULL&sort_dir[]=NULL&num='.$form_fields['num'].'&start=0';
    $sort_desc_link = vlc_internal_link('<img border="0" src="'.$site_info['images_url'].'sort-desc.png">', $sort_desc_url);
    $output .= "<th><nobr>$sort_by_value $sort_asc_link $sort_desc_link</nobr></th>";
  }
  $output .= '</tr>';
  while ($record = mysql_fetch_array($result))
  {
    $output .= '<tr>';
    $output .= '<td>'.$result_num.'.</td>';
    $output .= '<td align="center">'.vlc_internal_link($record['cycle_id'], 'cms/cycle_details.php?cycle='.$record['cycle_id']).'</td>';
    $output .= '<td align="center">'.$record['code'].'</td>';
    $output .= '<td align="center">'.$record['description'].'</td>';
    $output .= '<td align="center">'.(isset($record['cycle_start_timestamp']) ? date('n/j/Y', $record['cycle_start_timestamp']) : '--').'</td>';
    $output .= '<td align="center">'.(isset($record['cycle_end_timestamp']) ? date('n/j/Y', $record['cycle_end_timestamp']) : '--').'</td>';
    $output .= '<td align="center">'.(isset($record['registration_start_timestamp']) ? date('n/j/Y', $record['registration_start_timestamp']) : '--').'</td>';
    $output .= '<td align="center">'.(isset($record['registration_end_timestamp']) ? date('n/j/Y', $record['registration_end_timestamp']) : '--').'</td>';
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
#ac_cycle_id { z-index:9001; }
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
    /* cycle_id */
    this.ac_cycle_id_data_source = new YAHOO.widget.DS_XHR('xhr.php', ["\n", "\t"]);
    this.ac_cycle_id_data_source.responseType = YAHOO.widget.DS_XHR.TYPE_FLAT;
    this.ac_cycle_id_data_source.scriptQueryAppend = 'field=cycle_id';
    this.ac_cycle_id_widget = new YAHOO.widget.AutoComplete('ac_cycle_id_field', 'ac_cycle_id_container', this.ac_cycle_id_data_source);
    this.ac_cycle_id_widget.autoHighlight = false;
    this.ac_cycle_id_widget.formatResult = function(result_array, query_string) {
      var result_id = result_array[0];
      var result_string = result_array[1];
      return format_result(result_id, result_string, query_string);
    };
    /* code */
    this.ac_code_data_source = new YAHOO.widget.DS_XHR("xhr.php", ["\n", "\t"]);
    this.ac_code_data_source.responseType = YAHOO.widget.DS_XHR.TYPE_FLAT;
    this.ac_code_data_source.scriptQueryAppend = 'field=cycle_code';
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
    this.ac_description_data_source.scriptQueryAppend = 'field=cycle_desc';
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
