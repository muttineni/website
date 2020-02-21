<?php
$page_info['section'] = 'classes';
$page_info['sub_section'] = 'mail';
$page_info['login_required'] = 1;
$page_info['course_id'] = vlc_get_url_variable($site_info, 'course', true);
$page_info['folder_id'] = vlc_get_url_variable($site_info, 'folder', false, $page_info['course_id']);
$page_info['sort_id'] = vlc_get_url_variable($site_info, 'sort', false, $page_info['course_id']);
$page_info['dir_id'] = vlc_get_url_variable($site_info, 'dir', false, $page_info['course_id']);
list($user_info, $course_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* set default values for page info variables */
if (!isset($page_info['folder_id'])) $page_info['folder_id'] = 1;
if (!isset($page_info['sort_id'])) $page_info['sort_id'] = 3;
if (!isset($page_info['dir_id'])) $page_info['dir_id'] = 2;
/* vlc-mail key to success link */
if (isset($course_info['resources'][47])) $key_link = '<p style="text-align:center">'.vlc_internal_link('<img src="'.$site_info['images_url'].'key.jpg" width="61" height="16" alt="'.$lang['database']['resource-types'][47].'" title="'.$lang['database']['resource-types'][47].'">', 'classes/resource.php?course='.$page_info['course_id'].'&resource='.$course_info['resources'][47]['resource_id']).'</p>';
else $key_link = '';
/* new message link */
$new_message_link = vlc_internal_link($lang['classes']['mail']['misc']['new-message-link'], 'classes/mail_form.php?course='.$page_info['course_id'].'&action=1');
/* folder links */
$folder_array = array(
  array($lang['classes']['mail']['misc']['unread-label'], 1),
  array($lang['classes']['mail']['misc']['read-label'], 2),
  array($lang['classes']['mail']['misc']['sent-label'], 4),
  array($lang['classes']['mail']['misc']['trash-label'], 3)
);
foreach ($folder_array as $folder)
{
  if ($page_info['folder_id'] == $folder[1]) $folder_links_array[] = '<span style="border-top: solid 2px #666; border-bottom: solid 2px #666; padding: 5px;">'.vlc_internal_link($folder[0], 'classes/mail.php?course='.$page_info['course_id'].'&folder='.$folder[1]).'</span>';
  else $folder_links_array[] = vlc_internal_link($folder[0], 'classes/mail.php?course='.$page_info['course_id'].'&folder='.$folder[1]);
}
$folder_links = implode('&nbsp;|&nbsp;', $folder_links_array);
/* sent message folder */
if ($page_info['folder_id'] == 4)
{
  /* create variables to allow sorting */
  $sort_array = array(
    1 => 'u.last_name {DIR}, u.first_name {DIR}, m.CREATED {DIR}',
    2 => 'm.subject {DIR}, m.CREATED {DIR}',
    3 => 'm.CREATED {DIR}'
  );
  $dir_array = array(
    1 => 'ASC',
    2 => 'DESC'
  );
  $sort_order_string = str_replace('{DIR}', $dir_array[$page_info['dir_id']], $sort_array[$page_info['sort_id']]);
  $heading_array = array(
    1 => $lang['classes']['mail']['misc']['to-label'],
    2 => $lang['classes']['mail']['misc']['subject-label'],
    3 => $lang['classes']['mail']['misc']['date-label']
  );
  /* dynamically create table column headers with sort links */
  $column_headers = '';
  /* CHOOSE A SORTING METHOD */
  if (1) /* METHOD 1 - column heading is a link; click once to sort asc, click again to sort desc */
  {
    foreach ($heading_array as $key => $value)
    {
      /* sort link defaults to ascending */
      $sort_dir = 1;
      /* if the current page is sorted by this heading ascending, sort link should be descending */
      if ($page_info['sort_id'] == $key and $page_info['dir_id'] == 1) $sort_dir = 2;
      $column_headers .= '<th>'.vlc_internal_link($value, 'classes/mail.php?course='.$page_info['course_id'].'&folder='.$page_info['folder_id'].'&sort='.$key.'&dir='.$sort_dir, 'table-background', sprintf($lang['classes']['mail']['misc']['sort-by'], $value, $dir_array[$sort_dir])).'</th>';
    }
  }
  else /* METHOD 2 - column heading is plain text; click asc character to sort asc, click desc character to sort desc */
  {
    foreach ($heading_array as $key => $value)
    {
      $sort_asc_link = vlc_internal_link('&and;', 'classes/mail.php?course='.$page_info['course_id'].'&folder='.$page_info['folder_id'].'&sort='.$key.'&dir=1', 'table-background', sprintf($lang['classes']['mail']['misc']['sort-by'], $value, 'ASC'));
      $sort_desc_link = vlc_internal_link('&or;', 'classes/mail.php?course='.$page_info['course_id'].'&folder='.$page_info['folder_id'].'&sort='.$key.'&dir=2', 'table-background', sprintf($lang['classes']['mail']['misc']['sort-by'], $value, 'DESC'));
      $column_headers .= '<th>'.$value.'&nbsp;'.$sort_asc_link.'&nbsp;'.$sort_desc_link.'</th>';
    }
  }
  $get_messages_query = <<< END_QUERY
    SELECT m.mail_id, CONCAT(u.first_name, ' ', u.last_name) AS to_name, m.subject, DATE_FORMAT(m.CREATED, '%c-%e-%Y %l:%i %p') AS create_date
    FROM mail AS m, mail_users AS mu, users AS u
    WHERE m.mail_id = mu.mail_id
    AND mu.to_user_id = u.user_id
    AND m.course_id = {$page_info['course_id']}
    AND m.from_user_id = {$user_info['user_id']}
    GROUP BY m.mail_id
    ORDER BY $sort_order_string
END_QUERY;
  $result = mysql_query($get_messages_query, $site_info['db_conn']);
  $num_messages = mysql_num_rows($result);
  if ($num_messages > 0)
  {
    while ($record = mysql_fetch_array($result)) $message_array[] = $record;
    $message_rows = '';
    foreach ($message_array as $message)
    {
      $message_rows .= '<tr>';
      $message_rows .= '<td>'.$message['to_name'].'</td>';
      $message_rows .= '<td>'.vlc_internal_link($message['subject'], 'classes/mail_form.php?course='.$page_info['course_id'].'&mail='.$message['mail_id'].'&folder='.$page_info['folder_id'].'&action=5', 'table-background').'</td>';
      $message_rows .= '<td>'.$message['create_date'].'</td>';
      $message_rows .= '</tr>';
    }
  }
  else $message_rows = '<tr><td align="center" colspan="3">'.$lang['classes']['mail']['misc']['no-messages'].'</td></tr>';
  $message_table = <<< END_HTML
<div align="center">
<p style="text-align:center">$new_message_link</p>
<p style="text-align:center">$folder_links</p>
<table>
<tr>$column_headers</tr>
$message_rows
</table>
</div>
END_HTML;
}
/* all other folders */
else
{
  $style_bold = '';
  $empty_trash_link = '';
  switch ($page_info['folder_id'])
  {
    /* unread folder */
    case 1:
      $mail_status_id = 1;
      $select_options = '<option value="2">'.$lang['classes']['mail']['form-fields']['mark-read-option'].'</option><option value="1">'.$lang['classes']['mail']['misc']['send-to-trash'].'</option>';
      $style_bold = ' style="font-weight: bold;"';
      break;
    /* read folder */
    case 2:
      $mail_status_id = 2;
      $select_options = '<option value="3">'.$lang['classes']['mail']['form-fields']['mark-unread-option'].'</option><option value="1">'.$lang['classes']['mail']['misc']['send-to-trash'].'</option>';
      break;
    /* trash folder */
    case 3:
      $mail_status_id = 3;
      $select_options = '<option value="5">'.$lang['classes']['mail']['misc']['restore-to-unread'].'</option>';
      $empty_trash_link = '<p style="text-align:center">'.vlc_internal_link($lang['classes']['mail']['misc']['empty-trash-link'], 'classes/mail_action.php?course='.$page_info['course_id'].'&folder='.$page_info['folder_id'].'&action=6').'<br>('.$lang['classes']['mail']['misc']['empty-trash-note'].')</p>';
      break;
    default:
      trigger_error('INVALID FOLDER ID: '.$page_info['folder_id']);
  }
  /* create variables to allow sorting */
  $sort_array = array(
    1 => 'f.last_name {DIR}, f.first_name {DIR}, m.CREATED {DIR}',
    2 => 'm.subject {DIR}, m.CREATED {DIR}',
    3 => 'm.CREATED {DIR}'
  );
  $dir_array = array(
    1 => 'ASC',
    2 => 'DESC'
  );
  $sort_order_string = str_replace('{DIR}', $dir_array[$page_info['dir_id']], $sort_array[$page_info['sort_id']]);
  $heading_array = array(
    1 => $lang['classes']['mail']['misc']['from-label'],
    2 => $lang['classes']['mail']['misc']['subject-label'],
    3 => $lang['classes']['mail']['misc']['date-label']
  );
  /* dynamically create table column headers with sort links */
  $column_headers = '';
  /* CHOOSE A SORTING METHOD */
  if (1) /* METHOD 1 - column heading is a link; click once to sort asc, click again to sort desc */
  {
    foreach ($heading_array as $key => $value)
    {
      /* sort link defaults to ascending */
      $sort_dir = 1;
      /* if the current page is sorted by this heading ascending, sort link should be descending */
      if ($page_info['sort_id'] == $key and $page_info['dir_id'] == 1) $sort_dir = 2;
      $column_headers .= '<th>'.vlc_internal_link($value, 'classes/mail.php?course='.$page_info['course_id'].'&folder='.$page_info['folder_id'].'&sort='.$key.'&dir='.$sort_dir, 'table-background', sprintf($lang['classes']['mail']['misc']['sort-by'], $value, $dir_array[$sort_dir])).'</th>';
    }
  }
  else /* METHOD 2 - column heading is plain text; click asc character to sort asc, click desc character to sort desc */
  {
    foreach ($heading_array as $key => $value)
    {
      $sort_asc_link = vlc_internal_link('&and;', 'classes/mail.php?course='.$page_info['course_id'].'&folder='.$page_info['folder_id'].'&sort='.$key.'&dir=1', 'table-background', sprintf($lang['classes']['mail']['misc']['sort-by'], $value, 'ASC'));
      $sort_desc_link = vlc_internal_link('&or;', 'classes/mail.php?course='.$page_info['course_id'].'&folder='.$page_info['folder_id'].'&sort='.$key.'&dir=2', 'table-background', sprintf($lang['classes']['mail']['misc']['sort-by'], $value, 'DESC'));
      $column_headers .= '<th>'.$value.'&nbsp;'.$sort_asc_link.'&nbsp;'.$sort_desc_link.'</th>';
    }
  }
  /* get messages based on query conditions defined above */
  $get_messages_query = <<< END_QUERY
    SELECT m.mail_id, CONCAT(f.first_name, ' ', f.last_name) AS from_name, m.subject, DATE_FORMAT(m.CREATED, '%c-%e-%Y %l:%i %p') AS create_date
    FROM users AS f, mail AS m, mail_users AS mu
    WHERE f.user_id = m.from_user_id
    AND m.mail_id = mu.mail_id
    AND m.course_id = {$page_info['course_id']}
    AND mu.to_user_id = {$user_info['user_id']}
    AND mu.mail_status_id = $mail_status_id
    ORDER BY $sort_order_string
END_QUERY;
  $result = mysql_query($get_messages_query, $site_info['db_conn']);
  $message_rows = '';
  while ($record = mysql_fetch_array($result))
  {
    $message_rows .= '<tr>';
    $message_rows .= '<td align="center"><input type="checkbox" name="mail_id_array[]" value="'.$record['mail_id'].'"></td>';
    $message_rows .= '<td'.$style_bold.'>'.$record['from_name'].'</td>';
    $message_rows .= '<td>'.vlc_internal_link($record['subject'], 'classes/mail_details.php?course='.$page_info['course_id'].'&mail='.$record['mail_id'].'&folder='.$page_info['folder_id'], 'table-background').'</td>';
    $message_rows .= '<td'.$style_bold.'>'.$record['create_date'].'</td>';
    $message_rows .= '</tr>';
  }
  if (strlen($message_rows) == 0)
  {
    $message_rows = '<tr><td align="center" colspan="4">'.$lang['classes']['mail']['misc']['no-messages'].'</td></tr>';
    $disabled = ' disabled';
  }
  else $disabled = '';
  $message_table = <<< END_HTML
<div align="center">
$empty_trash_link
$key_link
<p style="text-align:center">$new_message_link</p>
<p style="text-align:center">$folder_links</p>
<form method="post" action="mail_action.php?course={$page_info['course_id']}&folder={$page_info['folder_id']}">
<table>
<tr>
  <th><input type="checkbox" onclick="check_all(this, 'mail_id_array[]');"$disabled></th>
  $column_headers
</tr>
$message_rows
<tr>
  <td colspan="4">
    {$lang['classes']['mail']['form-fields']['with-selected-label']}:
    <select name="action"$disabled>$select_options</select>
    <input type="submit" name="submit" value="{$lang['classes']['mail']['form-fields']['submit-button']}" class="submit-button"$disabled>
  </td>
</tr>
</table>
</form>
</div>
END_HTML;
}
print $header;
?>
<!-- begin page content -->
<?php print $message_table ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

