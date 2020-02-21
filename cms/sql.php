<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'sql';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
if (isset($_SESSION['sql_history'])) $sql_history_array = $_SESSION['sql_history'];
else $sql_history_array = array();
$sql_results = $query_status_msg = '';
$form_fields = array('sql' => '', 'format' => 4);
if (isset($_GET['sql']) and isset($_GET['format']))
{
  $form_fields['format'] = $_GET['format'];
  $sql_history_array[] = $form_fields['sql'] = stripslashes($_GET['sql']);
  $sql_history_array = array_unique($sql_history_array);
  $_SESSION['sql_history'] = $sql_history_array;
  $result = @mysql_query($form_fields['sql'], $site_info['db_conn']);
  if ($mysql_errno = mysql_errno() and strlen($mysql_error = mysql_error()))
  {
    $query_status_msg = '<p>MySQL Error Code: '.$mysql_errno.'</p><p>MySQL Error: '.$mysql_error.'</p>';
  }
  elseif (is_resource($result))
  {
    $num_rows = mysql_num_rows($result);
    $query_status_msg = '<p>Number of Records: '.$num_rows.'</p>';
    $num_fields = mysql_num_fields($result);
    $field_name_array = array();
    for ($i = 0; $i < $num_fields; $i++)
    {
      $field_name_array[] = str_replace('_', ' ', mysql_field_table($result, $i).' . '.mysql_field_name($result, $i));
    }
    if (in_array($form_fields['format'], array(1, 2, 3)))
    {
      $sql_results_array = array();
      $sql_results_array[] = $field_name_array;
      while ($record = mysql_fetch_row($result)) $sql_results_array[] = $record;
      switch ($form_fields['format'])
      {
        case 1:
          vlc_export_data($sql_results_array, 'sql-results', 1);
          break;
        case 2:
          vlc_export_data($sql_results_array, 'sql-results', 2, 'P');
          break;
        case 3:
          vlc_export_data($sql_results_array, 'sql-results', 2, 'L');
          break;
      }
    }
    else
    {
      $sql_results = '<table border="1" cellpadding="5" cellspacing="0">';
      $sql_results .= '<tr><th>'.join('</th><th>', $field_name_array).'</th></tr>';
      while ($record = mysql_fetch_row($result))
      {
        foreach ($record as $key => $value)
        {
          if (!isset($record[$key]))
          {
            $record[$key] .= '[NULL]';
          }
          elseif (strlen(trim($record[$key])) == 0)
          {
            $record[$key] .= '&nbsp;';
          }
        }
        $sql_results .= '<tr><td><pre>'.join('</pre></td><td><pre>', $record).'</pre></td></tr>';
      }
      $sql_results .= '</table>';
    }
  }
  else
  {
    $num_rows = mysql_affected_rows();
    $query_status_msg = '<p>Number of Records Affected: '.$num_rows.'</p>';
  }
}
/* sql history array */
array_unshift($sql_history_array, ' -- Query History -- ');
$sql_history_array_js = '';
foreach ($sql_history_array as $index => $query)
{
  $query = preg_replace(array("/\s*\n\s*/", "/'/"), array(" ", "\'"), trim($query));
  $query_abbrev = substr($query, 0, 100);
  $sql_history_array_js .= "sql_history_array[$index] = new Array('$query_abbrev', '$query');\n";
}
/* format options array */
$format_options_array = array(1 => 'Export to Excel (CSV)', 'Printable Table (PDF - Portrait)', 'Printable Table (PDF - Landscape)', 'Printable Table (HTML)');
$format_options = vlc_select_box($format_options_array, 'array', 'format', $form_fields['format'], true);
$output = <<< END_HTML
<h3>Run SQL Query</h3>
$query_status_msg
<form method="get" action="sql.php">
<script type="text/javascript">
document.write('<p>');
document.write('<input type="button" value="Select All" onclick="select_all(this.form.sql, false);">');
if (document.all)
{
  document.write('&nbsp;<input type="button" value="Cut" onclick="select_all(this.form.sql, \'cut\');">');
  document.write('&nbsp;<input type="button" value="Copy" onclick="select_all(this.form.sql, \'copy\');">');
  document.write('&nbsp;<input type="button" value="Paste" onclick="select_all(this.form.sql, \'paste\');">');
}
var sql_history_array = new Array();
$sql_history_array_js
document.write('&nbsp;<select name="sql_history">');
for (var i = 0; i < sql_history_array.length; i++)
{
  document.write('<option value="' + i + '">' + sql_history_array[i][0] + '</option>');
}
document.write('</select>');
document.write('</p>');
</script>
<p><textarea name="sql" rows="15" cols="100">{$form_fields['sql']}</textarea></p>
<p>
$format_options
&nbsp;<input type="submit" value="Submit">
&nbsp;<input type="reset" value="Reset">
</p>
</form>
<script type="text/javascript">
function update_field(a, b)
{
  if (parseInt(a.selectedIndex))
  {
    b.value = sql_history_array[a.selectedIndex][1];
    b.focus();
  }
  return;
}
function init_events(f)
{
  window.onload=function(){f.sql.focus()};
  f.onsubmit=function(){replace_white_space(this.sql, '', '', false, false);};
  f.sql_history.onchange=function(){update_field(this, this.form.sql);};
  f.sql.onkeydown=capture_keys;
  f.sql.onfocus=function(){select_all(this, false);};
}
init_events(document.forms[0]);
</script>
$sql_results
END_HTML;
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
