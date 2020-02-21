<?php
$page_info['section'] = 'classes';
$page_info['sub_section'] = 'mail';
$page_info['login_required'] = 1;
$page_info['course_id'] = vlc_get_url_variable($site_info, 'course', true);
$page_info['mail_id'] = vlc_get_url_variable($site_info, 'mail', true, $page_info['course_id']);
$page_info['folder_id'] = vlc_get_url_variable($site_info, 'folder', false, $page_info['course_id']);
list($user_info, $course_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* set folder id default value if it has not been set */
if (!isset($page_info['folder_id'])) $page_info['folder_id'] = 1;
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
/* get message details */
$get_message_details_query = <<< END_QUERY
  SELECT DATE_FORMAT(m.CREATED, '%c-%e-%Y %l:%i %p') AS create_date, mu.mail_status_id, CONCAT(f.first_name, ' ', f.last_name) AS from_name, m.subject, m.message
  FROM users AS f, mail AS m, mail_users AS mu, users AS t
  WHERE f.user_id = m.from_user_id
  AND m.mail_id = mu.mail_id
  AND mu.to_user_id = t.user_id
  AND m.mail_id = {$page_info['mail_id']}
  AND m.course_id = {$page_info['course_id']}
  AND mu.to_user_id = {$user_info['user_id']}
  AND mu.mail_status_id != 4
END_QUERY;
$result = mysql_query($get_message_details_query, $site_info['db_conn']);
if (mysql_num_rows($result) == 1) $mail_info = mysql_fetch_array($result);
else trigger_error('INVALID MESSAGE ID: '.$page_info['mail_id']);
/* format message text */
$mail_info['message'] = vlc_convert_code($mail_info['message'], $page_info['course_id'], 'table-background');
/* get message recipients */
$get_message_recipients_query = <<< END_QUERY
  SELECT t.user_id, uc.course_status_id, CONCAT(t.first_name, ' ', t.last_name) AS to_name
  FROM mail_users AS mu, users AS t, users_courses AS uc
  WHERE mu.to_user_id = t.user_id
  AND t.user_id = uc.user_id
  AND uc.course_id = {$page_info['course_id']}
  AND mu.mail_id = {$page_info['mail_id']}
  ORDER BY t.last_name, t.first_name
END_QUERY;
$result = mysql_query($get_message_recipients_query, $site_info['db_conn']);
$recipient_array = array();
while ($record = mysql_fetch_array($result))
{
  if (in_array($record['course_status_id'], array(2, 3, 6, 7))) $recipient_array[] = vlc_internal_link($record['to_name'], 'classes/mail_form.php?course='.$page_info['course_id'].'&recipient='.$record['user_id'].'&action=2', 'table-background');
  else $recipient_array[] = $record['to_name'];
}
$recipient_list = implode(', ', $recipient_array);
/* update "has been read" field */
if ($mail_info['mail_status_id'] == 1)
{
  $update_message_query = <<< END_QUERY
    UPDATE mail_users
    SET mail_status_id = 2
    WHERE mail_id = {$page_info['mail_id']}
    AND to_user_id = {$user_info['user_id']}
END_QUERY;
  $result = mysql_query($update_message_query, $site_info['db_conn']);
}
/* generate message details */
$return_link = vlc_internal_link($lang['classes']['mail']['misc']['return-link'], 'classes/mail.php?course='.$page_info['course_id'].'&folder='.$page_info['folder_id'], 'table-background');
$reply_link = vlc_internal_link($lang['classes']['mail']['misc']['reply-link'], 'classes/mail_form.php?course='.$page_info['course_id'].'&mail='.$page_info['mail_id'].'&folder='.$page_info['folder_id'].'&action=3', 'table-background');
$reply_all_link = vlc_internal_link($lang['classes']['mail']['misc']['reply-all-link'], 'classes/mail_form.php?course='.$page_info['course_id'].'&mail='.$page_info['mail_id'].'&folder='.$page_info['folder_id'].'&action=4', 'table-background');
if ($mail_info['mail_status_id'] == 3) $delete_restore_link = vlc_internal_link($lang['classes']['mail']['misc']['restore-to-unread'], 'classes/mail_action.php?course='.$page_info['course_id'].'&mail='.$page_info['mail_id'].'&action=5', 'table-background');
else $delete_restore_link = vlc_internal_link($lang['classes']['mail']['misc']['send-to-trash'], 'classes/mail_action.php?course='.$page_info['course_id'].'&mail='.$page_info['mail_id'].'&action=1', 'table-background');
$message_details = <<< END_HTML
<div align="center">
<p style="text-align:center">$folder_links</p>
<table>
<tr><td align="right" width="20%"><b>{$lang['classes']['mail']['misc']['date-label']}:</b></td><td>{$mail_info['create_date']}</td></tr>
<tr><td align="right"><b>{$lang['classes']['mail']['misc']['from-label']}:</b></td><td>{$mail_info['from_name']}</td></tr>
<tr><td align="right"><b>{$lang['classes']['mail']['misc']['to-label']}:</b></td><td>$recipient_list</td></tr>
<tr><td align="right"><b>{$lang['classes']['mail']['misc']['subject-label']}:</b></td><td>{$mail_info['subject']}</td></tr>
<tr><td colspan="2">{$mail_info['message']}</td></tr>
<tr><td align="center" colspan="2">$return_link - $reply_link - $reply_all_link - $delete_restore_link</td></tr>
</table>
</div>
END_HTML;
print $header;
?>
<!-- begin page content -->
<?php print $message_details ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

