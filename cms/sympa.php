<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'sympa';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
$form_fields['sympa'] = '';
$sympa_command_array = array();
if (isset($_GET['cycle']))
{
  $cycle_id = $_GET['cycle'];
  $sympa_query = <<< END_QUERY
    SELECT c.course_email, uc.user_role_id, i.primary_email, TRIM(IFNULL(i.secondary_email, '')) AS secondary_email
    FROM courses AS c, users_courses AS uc, users AS u, user_info AS i
    WHERE c.course_id = uc.course_id
    AND uc.user_id = u.user_id
    AND u.user_id = i.user_id
    AND uc.course_status_id IN (2, 3)
    AND c.is_active = 1
    AND c.cycle_id = $cycle_id
    ORDER BY c.course_email, i.primary_email
END_QUERY;
  $result = mysql_query($sympa_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result))
  {
    if (strlen($record['course_email']))
    {
      $sympa_command_array[] = 'add '.$record['course_email'].' '.$record['primary_email'];
      if ($record['user_role_id'] == 4 and strlen($record['secondary_email'])) $sympa_command_array[] = 'add '.$record['course_email'].' '.$record['secondary_email'];
    }
  }
}
if (isset($_POST['sympa']))
{
  $placeholder_email = 'erskinpv@notes.udayton.edu';
  $input_array = explode('>', $_POST['sympa']);
  foreach ($input_array as $haystack)
  {
    $needle = 'review ';
    $needle_pos = strpos($haystack, $needle);
    if ($needle_pos === false) continue;
    $list_name_pos = $needle_pos + strlen($needle);
    $new_line_pos = strpos($haystack, chr(10), $list_name_pos) - 1;
    $list_name_len = $new_line_pos - $list_name_pos;
    $list_name = substr($haystack, $list_name_pos, $list_name_len);
    $email_array = explode(chr(10), $haystack);
    foreach ($email_array as $email)
    {
      $email_pos = strpos($email, ' - ');
      if ($email_pos !== false)
      {
        $email = substr($email, 0, $email_pos);
        if (!stristr($placeholder_email, $email)) $sympa_command_array[] = 'delete '.$list_name.' '.$email;
      }
    }
  }
}
$form_fields['sympa'] = join("\n", $sympa_command_array);
/* get cycles */
$cycle_query = <<< END_QUERY
  SELECT cycle_id, IFNULL(code, cycle_id) AS code, UNIX_TIMESTAMP(cycle_start) AS cycle_start_timestamp
  FROM cycles
  ORDER BY cycle_start DESC
END_QUERY;
$result = mysql_query($cycle_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $cycle_options_array[$record['cycle_id']] = $record['code'].' ('.date('M. Y', $record['cycle_start_timestamp']).')';
print $header;
?>
<!-- begin page content -->
<h3>Generate Sympa Commands</h3>
<form method="get" action="sympa.php">
<p>Select a cycle: <?php print vlc_select_box($cycle_options_array, 'array', 'cycle', -1, true); ?> <input type="submit" value="Go"></p>
</form>
<form method="post" action="sympa.php">
<p><textarea name="sympa" rows="15" cols="100" onfocus="select_all(this, false);"><?php print $form_fields['sympa'] ?></textarea></p>
<p>
  <input type="button" value="Review" onclick="replace_white_space(this.form.sympa, 'review ', '', true, true);">
  <input type="button" value="Add" onclick="replace_white_space(this.form.sympa, 'add ', ' '+prompt('Enter an e-mail address:', 'erskinpv@notes.udayton.edu'), true, true);">
  <input type="button" value="Delete" onclick="replace_white_space(this.form.sympa, 'delete ', ' '+prompt('Enter an e-mail address:', 'erskinpv@notes.udayton.edu'), true, true);">
  <input type="button" value="Cut All" onclick="select_all(this.form.sympa, 'cut');">
  <input type="button" value="Copy All" onclick="select_all(this.form.sympa, 'copy');">
  <input type="button" value="Paste (Replace All)" onclick="select_all(this.form.sympa, 'paste');">
  <input type="submit" value="Submit">
  <input type="reset" value="Reset">
</p>
</form>
<script type="text/javascript">
window.onload=function(){document.forms[1].sympa.focus()};
document.forms[1].sympa.onkeydown=capture_keys;
</script>
<ol>
  <li><b>To get all list names:</b><ul><li>Send <b>&quot;which&quot;</b> command to <?php print vlc_mailto_link('sympa@lists.udayton.edu', 'sympa@lists.udayton.edu', 'which'); ?></li></ul></li>
  <li><b>To add a &quot;place-holder&quot; e-mail address to all lists:</b><ul><li>Paste results of <b>&quot;which&quot;</b> command</li><li>Click <b>&quot;Add&quot;</b></li><li>Enter e-mail address</li><li>Copy <b>&quot;add&quot;</b> commands and send to <?php print vlc_mailto_link('sympa@lists.udayton.edu', 'sympa@lists.udayton.edu'); ?></li><li><b>Note:</b> The purpose of this step is to avoid generating an error when attempting to review an empty list</li></ul></li>
  <li><b>To get all current list recipients:</b><ul><li>Paste results of <b>&quot;which&quot;</b> command</li><li>Click <b>&quot;Review&quot;</b></li><li>Copy <b>&quot;review&quot;</b> commands and send to <?php print vlc_mailto_link('sympa@lists.udayton.edu', 'sympa@lists.udayton.edu'); ?></li></ul></li>
  <li><b>To delete all current list recipients:</b><ul><li>Paste results of <b>&quot;review&quot;</b> command</li><li>Click <b>&quot;Submit&quot;</b></li><li>Copy <b>&quot;delete&quot;</b> commands and send to <?php print vlc_mailto_link('sympa@lists.udayton.edu', 'sympa@lists.udayton.edu'); ?></li></ul></li>
  <li><b>To add new list recipients:</b><ul><li>Select a cycle</li><li>Click <b>&quot;Go&quot;</b></li><li>Copy <b>&quot;add&quot;</b> commands and send to <?php print vlc_mailto_link('sympa@lists.udayton.edu', 'sympa@lists.udayton.edu'); ?></li></ul></li>
  <li><b>To remove the &quot;place-holder&quot; e-mail address from all lists:</b><ul><li>Paste results of <b>&quot;which&quot;</b> command</li><li>Click <b>&quot;Delete&quot;</b></li><li>Enter e-mail address</li><li>Copy <b>&quot;add&quot;</b> commands and send to <?php print vlc_mailto_link('sympa@lists.udayton.edu', 'sympa@lists.udayton.edu'); ?></li></ul></li>
</ol>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
