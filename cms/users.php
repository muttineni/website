<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'users';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* initialize form fields variable */
$form_fields = array
(
  'start' => 0,
  'num' => 10,
  'user_id' => '',
  'user_role_id' => '',
  'first_name' => '',
  'last_name' => '',
  'username' => '',
  'primary_email' => '',
  'state_id' => 'NULL',
  'country_id' => 'NULL',
  'diocese_id' => 'NULL',
  'partner_id' => 'NULL',
  'credit_raincheck' => -1,
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
if (is_numeric($form_fields['user_id']) and $form_fields['user_id'] > 0) $where_clause .= ' AND u.user_id = '.$form_fields['user_id'];
if (is_numeric($form_fields['user_role_id']) and $form_fields['user_role_id'] > 0) $where_clause .= ' AND ur.user_role_id = '.$form_fields['user_role_id'];
if (strlen($form_fields['first_name']) > 0) $where_clause .= " AND u.first_name LIKE '".$form_fields['first_name']."'";
if (strlen($form_fields['last_name']) > 0) $where_clause .= " AND u.last_name LIKE '".$form_fields['last_name']."'";
if (strlen($form_fields['username']) > 0) $where_clause .= " AND u.username LIKE '".$form_fields['username']."'";
if (strlen($form_fields['primary_email']) > 0) $where_clause .= " AND i.primary_email LIKE '".$form_fields['primary_email']."'";
if (is_numeric($form_fields['state_id']) and $form_fields['state_id'] > 0) $where_clause .= ' AND i.state_id = '.$form_fields['state_id'];
if (is_numeric($form_fields['country_id']) and $form_fields['country_id'] > 0) $where_clause .= ' AND i.country_id = '.$form_fields['country_id'];
if (is_numeric($form_fields['diocese_id']) and $form_fields['diocese_id'] > 0) $where_clause .= ' AND i.diocese_id = '.$form_fields['diocese_id'];
if (is_numeric($form_fields['partner_id']) and $form_fields['partner_id'] > 0) $where_clause .= ' AND i.partner_id = '.$form_fields['partner_id'];
if (is_numeric($form_fields['credit_raincheck']) and $form_fields['credit_raincheck'] != -1) $where_clause .= $form_fields['credit_raincheck'] ? ' AND c.credit_amount > 0' : ' AND c.credit_amount = 0';
else $form_fields['credit_raincheck'] = -1;
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = stripslashes($value);
}
/* query sorting parameters */
$sort_field_array = array(1 => 'u.user_id', 'u.first_name', 'u.last_name', 'u.username', 'u.password', 'i.primary_email');
$sort_options_array = array(1 => 'User ID', 'First Name', 'Last Name', 'Username', 'Password', 'E-Mail');
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
  SELECT u.user_id
  FROM users AS u, user_info AS i, credits AS c, users_roles AS ur
  WHERE u.user_id = i.user_id
  AND u.user_id = c.customer_id
  AND c.customer_type_id = 1
  AND u.user_id = ur.user_id
  $where_clause
  GROUP BY u.user_id
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
    SELECT u.user_id, u.first_name, u.last_name, u.username, u.password, i.primary_email
    FROM users AS u, user_info AS i, credits AS c, users_roles AS ur
    WHERE u.user_id = i.user_id
    AND u.user_id = c.customer_id
    AND c.customer_type_id = 1
    AND u.user_id = ur.user_id
END_QUERY;
  if (in_array($form_fields['export_data'], array(1, 2))) $export_query .= $where_clause;
  $export_query .= ' GROUP BY u.user_id';
  $export_query .= $order_clause;
  if ($form_fields['export_data'] == 1) $export_query .= $limit_clause;
  $result = mysql_query($export_query, $site_info['db_conn']);
  $export_array = array();
  $export_array[] = array('User ID', 'First Name', 'Last Name', 'Username', 'Password', 'E-Mail');
  while ($record = mysql_fetch_row($result)) $export_array[] = $record;
  switch ($form_fields['export_format'])
  {
    case 1:
      vlc_export_data($export_array, 'users', 1);
      break;
    case 2:
      vlc_export_data($export_array, 'users', 2, 'P');
      break;
    case 3:
      vlc_export_data($export_array, 'users', 2, 'L');
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
  $first_url = 'cms/users.php?'.$url_parameters.'&start='.$first_start;
  $first_link = vlc_internal_link('&laquo; First', $first_url);
  $prev_start = $form_fields['start'] - $form_fields['num'];
  if ($prev_start < 0) $prev_start = 0;
  $prev_url = 'cms/users.php?'.$url_parameters.'&start='.$prev_start;
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
  $next_url = 'cms/users.php?'.$url_parameters.'&start='.$next_start;
  $next_link = vlc_internal_link('Next &rsaquo;', $next_url);
  /* determine the index of the last record of the search results (the index of the first record is "0") */
  $last_index = $total_rows - 1;
  /* determine the index of the first record of the last page of search results */
  $last_start = $last_index - ($last_index % $form_fields['num']);
  $last_url = 'cms/users.php?'.$url_parameters.'&start='.$last_start;
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
      $page_link_url = 'cms/users.php?'.$url_parameters.'&start='.$page_start;
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
/* build array for "user role" select box */
$user_role_options_array = array();
$user_role_query = <<< END_QUERY
  SELECT user_role_id, description
  FROM user_roles
  ORDER BY description
END_QUERY;
$result = mysql_query($user_role_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $user_role_options_array[$record['user_role_id']] = $record['description'];
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
/* credit values */
$credit_options_array = array(1 => 'Yes', 0 => 'No');
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
$output .= '<p align="center">'.vlc_internal_link('Click here to create a new user.', 'cms/user_details.php').'</p>';
$output .= '<form method="get" action="users.php">';
$output .= $export_hidden_fields;
$output .= '<p align="center">Export '.vlc_select_box($export_data_array, 'array', 'export_data', -1, true).' to '.vlc_select_box($export_format_array, 'array', 'export_format', -1, true).' <input type="submit" value="Go"></p>';
$output .= '</form>';
$output .= '<p align="center"><b>'.$total_rows.'</b> '.$results_label.' Found (<b>'.$last_page.'</b> '.$pages_label.')</p>';
$output .= '<form method="get" action="users.php">';
$output .= '<input type="hidden" name="start" value="0">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
$output .= '<tr>';
$output .= '<td align="right"><nobr><b>User ID:</b></nobr></td><td><div class="yui-skin-sam"><div id="ac_user_id" class="autocomplete"><input type="text" name="user_id" id="ac_user_id_field" value="'.$form_fields['user_id'].'"><div id="ac_user_id_container"></div></div></div></td>';
$output .= '<td align="right"><nobr><b>User Role:</b></nobr></td><td>'.vlc_select_box($user_role_options_array, 'array', 'user_role_id', $form_fields['user_role_id'], false).'</td>';
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
$output .= '<tr><td align="right"><nobr><b>Credit / Raincheck:</b></nobr></td><td colspan="3">'.vlc_select_box($credit_options_array, 'array', 'credit_raincheck', $form_fields['credit_raincheck'], false).'</td></tr>';
$output .= '<tr><td align="right" valign="top" rowspan="3"><nobr><b>Sort By:</b></nobr></td><td colspan="3">'.$sort_post_vars_string.'</td></tr>';
$output .= '<tr><td colspan="4" align="center"><nobr><b>Results / Page:</b></nobr> '.vlc_select_box($num_results_array, 'array', 'num', $form_fields['num'], true).' <input type="submit" value="Submit"> <input type="button" value="Clear Search Fields" onclick="clear_fields(this.form); this.form.num.selectedIndex = 0;"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
/* search results navigation */
if ($total_rows > 0) $output .= "<p align=\"right\"><b>Showing $list_start - $list_end of $total_rows (Page $current_page of $last_page)</b></p>";
$output .= $nav_links;
/* get users */
$user_query = <<< END_QUERY
  SELECT u.user_id, u.first_name, u.last_name, u.username, u.password, i.primary_email
  FROM users AS u, user_info AS i, credits AS c, users_roles AS ur
  WHERE u.user_id = i.user_id
  AND u.user_id = c.customer_id
  AND c.customer_type_id = 1
  AND u.user_id = ur.user_id
  $where_clause
  GROUP BY u.user_id
END_QUERY;
$user_query .= $order_clause;
$user_query .= $limit_clause;
$result = mysql_query($user_query, $site_info['db_conn']);
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
    $sort_asc_url = 'cms/users.php?'.$url_parameters_without_sort.'sort_by[]='.$sort_by_key.'&sort_dir[]=1&sort_by[]=NULL&sort_dir[]=NULL&sort_by[]=NULL&sort_dir[]=NULL&num='.$form_fields['num'].'&start=0';
    $sort_asc_link = vlc_internal_link('<img border="0" src="'.$site_info['images_url'].'sort-asc.png">', $sort_asc_url);
    $sort_desc_url = 'cms/users.php?'.$url_parameters_without_sort.'sort_by[]='.$sort_by_key.'&sort_dir[]=2&sort_by[]=NULL&sort_dir[]=NULL&sort_by[]=NULL&sort_dir[]=NULL&num='.$form_fields['num'].'&start=0';
    $sort_desc_link = vlc_internal_link('<img border="0" src="'.$site_info['images_url'].'sort-desc.png">', $sort_desc_url);
    $output .= "<th><nobr>$sort_by_value $sort_asc_link $sort_desc_link</nobr></th>";
  }
  $output .= '</tr>';
  while ($record = mysql_fetch_array($result))
  {
    $output .= '<tr>';
    $output .= '<td>'.$result_num.'.</td>';
    $output .= '<td align="center">'.vlc_internal_link($record['user_id'], 'cms/user_details.php?user='.$record['user_id']).'</td>';
    $output .= '<td>'.$record['first_name'].'</td>';
    $output .= '<td>'.$record['last_name'].'</td>';
    $output .= '<td>'.$record['username'].'</td>';
    $output .= '<td>'.$record['password'].'</td>';
    $output .= '<td>'.$record['primary_email'].'</td>';
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
