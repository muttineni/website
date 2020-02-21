<?php
$page_info['section'] = 'classes';
$page_info['sub_section'] = 'mail';
$page_info['login_required'] = 1;
$page_info['course_id'] = vlc_get_url_variable($site_info, 'course', true);
$page_info['action_id'] = vlc_get_url_variable($site_info, 'action', true, $page_info['course_id']);
$page_info['mail_id'] = vlc_get_url_variable($site_info, 'mail', false, $page_info['course_id']);
$page_info['recipient_id'] = vlc_get_url_variable($site_info, 'recipient', false, $page_info['course_id']);
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
/* initialize error message variable */
$error_message = '';
/* get form fields from session variable if form has been submitted and errors occurred on action page */
if (strlen($status_message) > 0 and isset($_SESSION['form_fields']))
{
  $form_fields = $_SESSION['form_fields'];
  $_SESSION['form_fields'] = null;
}
/* populate form fields based on action variable */
switch ($page_info['action_id'])
{
  /* new message (no recipients selected in "recipients" field) */
  case 1:
    if (!isset($form_fields['recipient_id_array'])) $form_fields['recipient_id_array'] = array();
    if (!isset($form_fields['subject'])) $form_fields['subject'] = '';
    if (isset($form_fields['message_rte'])) $form_fields['message'] = vlc_convert_html($form_fields['message_rte']);
    elseif (isset($form_fields['message'])) $form_fields['message'] = str_replace("\n", "[br]\n", stripslashes(trim($form_fields['message'])));
    else $form_fields['message'] = '';
    break;
  /* new message (one recipient selected) */
  case 2:
    $form_fields['recipient_id_array'] = array($page_info['recipient_id']);
    $form_fields['subject'] = '';
    $form_fields['message'] = '';
    break;
  /* reply (one recipient selected) */
  case 3:
  /* reply to all (all reply recipients selected) */
  case 4:
    $get_reply_details_query = <<< END_QUERY
      SELECT DATE_FORMAT(m.CREATED, '%c-%e-%Y %l:%i %p') AS create_date, m.from_user_id, CONCAT(f.first_name, ' ', f.last_name) AS from_name, m.subject, m.message
      FROM users AS f, mail AS m, mail_users AS mu, users AS t
      WHERE f.user_id = m.from_user_id
      AND m.mail_id = mu.mail_id
      AND mu.to_user_id = t.user_id
      AND mu.mail_status_id != 4
      AND m.mail_id = {$page_info['mail_id']}
      AND m.course_id = {$page_info['course_id']}
      AND mu.to_user_id = {$user_info['user_id']}
END_QUERY;
    $result = mysql_query($get_reply_details_query, $site_info['db_conn']);
    $reply_info = mysql_fetch_array($result);
    $form_fields['recipient_id_array'] = array($reply_info['from_user_id']);
    if (strstr($reply_info['subject'], 'Re: ')) $form_fields['subject'] = $reply_info['subject'];
    else $form_fields['subject'] = 'Re: '.$reply_info['subject'];
    $form_fields['message'] = sprintf($lang['classes']['mail']['misc']['reply-history-note'], $reply_info['create_date'], $reply_info['from_name']);
    $form_fields['message'] .= $reply_info['message'];
    /* if action is reply to all, get all recipients from database */
    if ($page_info['action_id'] == 4)
    {
      $get_reply_recipients_query = <<< END_QUERY
        SELECT mu.to_user_id
        FROM mail_users AS mu
        WHERE mu.mail_id = {$page_info['mail_id']}
        ORDER BY mu.to_user_id
END_QUERY;
      $result = mysql_query($get_reply_recipients_query, $site_info['db_conn']);
      while ($record = mysql_fetch_array($result)) $form_fields['recipient_id_array'][] = $record['to_user_id'];
    }
    break;
  /* display sent message */
  case 5:
    $message_details_query = <<< END_QUERY
      SELECT m.subject, m.message
      FROM mail AS m
      WHERE m.mail_id = {$page_info['mail_id']}
      AND m.course_id = {$page_info['course_id']}
      AND m.from_user_id = {$user_info['user_id']}
END_QUERY;
    $result = mysql_query($message_details_query, $site_info['db_conn']);
    $form_fields = mysql_fetch_array($result);
    $message_recipients_query = <<< END_QUERY
      SELECT mu.to_user_id
      FROM mail_users AS mu
      WHERE mu.mail_id = {$page_info['mail_id']}
      ORDER BY mu.to_user_id
END_QUERY;
    $result = mysql_query($message_recipients_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result)) $form_fields['recipient_id_array'][] = $record['to_user_id'];
    break;
  default:
    trigger_error('INVALID ACTION ID: '.$page_info['action_id']);
}
/* get all course users for "recipients" field */
$get_users_query = <<< END_QUERY
  SELECT uc.user_id, uc.user_role_id, CONCAT(u.first_name, ' ', u.last_name) AS name
  FROM users_courses AS uc, users AS u
  WHERE uc.user_id = u.user_id
  AND uc.course_id = {$page_info['course_id']}
  AND uc.course_status_id IN (2, 3, 6, 7)
  ORDER BY u.last_name, u.first_name
END_QUERY;
$result = mysql_query($get_users_query, $site_info['db_conn']);
$facilitator_options = '';
$student_options = '';
while ($record = mysql_fetch_array($result))
{
  if (in_array($record['user_id'], $form_fields['recipient_id_array'])) $selected = ' selected';
  else $selected = '';
  if ($record['user_role_id'] == 4) $facilitator_options .= '<option value="'.$record['user_id'].'"'.$selected.'>'.$record['name'].'</option>';
  elseif ($record['user_role_id'] == 5) $student_options .= '<option value="'.$record['user_id'].'"'.$selected.'>'.$record['name'].'</option>';
}
$message_field = vlc_rte_field('message', $form_fields['message'], $page_info['course_id']);
$return_link = vlc_internal_link($lang['classes']['mail']['misc']['cancel-link'], 'classes/mail.php?course='.$page_info['course_id'].'&folder='.$page_info['folder_id'], 'table-background');
$message_form = <<< END_HTML
<div align="center">
<p style="text-align:center">$folder_links</p>
<form method="post" action="mail_action.php?course={$page_info['course_id']}" onsubmit="updateRTEs(); this.submitbtn.disabled = true;">
<input type="hidden" name="action" value="4">
<table>
<tr>
  <td align="right" width="20%"><b>{$lang['classes']['mail']['misc']['to-label']}:</b></td>
  <td>
    <select name="recipient_id_array[]" size="5" class="select-box" required="true" message="{$lang['classes']['mail']['status']['recipient-required']}" multiple>
      <optgroup label="{$lang['classes']['mail']['form-fields']['facilitator-label']}">$facilitator_options</optgroup>
      <optgroup label="{$lang['classes']['mail']['form-fields']['students-label']}">$student_options</optgroup>
    </select>
    <input type="checkbox" name="check_all_checkbox" id="check_all_checkbox" onclick="check_all(this, 'recipient_id_array[]');"><label for="check_all_checkbox">{$lang['classes']['mail']['form-fields']['select-all-label']}</label>
  </td>
</tr>
<tr>
  <td align="right"><b>{$lang['classes']['mail']['misc']['subject-label']}:</b></td>
  <td><input type="text" name="subject" size="50" value="{$form_fields['subject']}" class="text-box" required="true" message="{$lang['classes']['mail']['status']['subject-required']}"></td>
</tr>
<tr>
  <td colspan="2">
$message_field
  </td>
</tr>
<tr>
  <td colspan="2">
    <table style="background-color: transparent;">
    <tr>
      <td>$return_link</td>
      <td align="right"><input type="submit" name="submitbtn" value="{$lang['classes']['mail']['form-fields']['send-button']}" class="submit-button"></td>
    </tr>
    </table>
  </td>
</tr>
</table>
</form>
</div>
END_HTML;
print $header;
?>
<!-- begin page content -->
<?php print $message_form ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

