<?php
$page_info['section'] = 'classes';
$page_info['sub_section'] = 'meeting-place';
$page_info['login_required'] = 1;
$page_info['course_id'] = vlc_get_url_variable($site_info, 'course', true);
list($user_info, $course_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* begin meeting place table */
$meeting_place_output = '<table><tr><td align="right" valign="top" width="25%"><b>'.$lang['classes']['discussion']['misc']['topic'].':</b></td><td width="75%">'.$lang['classes']['meeting-place']['topic'].'</td></tr><tr><th>'.$lang['classes']['discussion']['misc']['author'].'</th><th>'.$lang['classes']['discussion']['misc']['message'].'</th></tr>';
/* get meeting place messages */
$meeting_place_id = $course_info['resources'][46]['resource_id'];
$message_query = <<< END_QUERY
  SELECT m.message, DATE_FORMAT(m.CREATED, '%c-%e-%Y %l:%i %p') AS create_date, u.user_id, CONCAT(IF(LENGTH(TRIM(IFNULL(u.nickname, ''))) > 0, u.nickname, u.first_name), ' ', u.last_name) AS name
  FROM messages AS m LEFT JOIN users_courses AS uc ON m.course_id = uc.course_id AND m.user_id = uc.user_id, users AS u, users_roles AS ur
  WHERE m.user_id = u.user_id
    AND u.user_id = ur.user_id
    AND (uc.course_status_id IN (2, 3, 6, 7) OR ur.user_role_id = 1)
    AND m.discussion_board_id = $meeting_place_id
    AND m.course_id = {$page_info['course_id']}
  GROUP BY m.message_id
  ORDER BY m.CREATED
END_QUERY;
$result = mysql_query($message_query, $site_info['db_conn']);
$discussion_output = '';
$i = 0;
while ($record = mysql_fetch_array($result))
{
  if ($i % 2 == 0) $css_class = 'table-background';
  else $css_class = 'alternate-row';
  $record['message'] = vlc_convert_code($record['message'], $page_info['course_id'], $css_class);
  $mail_link = vlc_internal_link($record['name'], 'classes/mail_form.php?course='.$page_info['course_id'].'&recipient='.$record['user_id'].'&action=2', $css_class);
  $discussion_output .= '<tr><td valign="top" class="'.$css_class.'">'.$mail_link.'<br><br>['.$record['create_date'].']</td>';
  $discussion_output .= '<td valign="top" class="'.$css_class.'">'.$record['message'].'</td></tr>';
  $i++;
}
if (strlen($discussion_output) > 0) $meeting_place_output .= $discussion_output;
else $meeting_place_output .= '<tr><td>&nbsp;</td><td>'.$lang['classes']['discussion']['misc']['no-messages'].'</td></tr>';
$form_action = 'resource_action.php?course='.$page_info['course_id'].'&resource='.$meeting_place_id;
$message_field = vlc_rte_field('message', '', $page_info['course_id']);
$meeting_place_output .= <<< END_TEXT
<tr>
  <td align="right" valign="top"><b>{$lang['classes']['discussion']['misc']['directions-label']}:</b></td>
  <td>{$lang['classes']['meeting-place']['directions']}</td>
</tr>
<tr>
  <td align="right" valign="top"><b>{$lang['classes']['discussion']['misc']['add-message']}:</b></td>
  <td>
    <form method="post" action="$form_action" onsubmit="updateRTEs(); this.submitbtn.disabled = true;">
$message_field
      <br>{$lang['classes']['discussion']['misc']['review-message']}<br><br>
      <input type="submit" name="submitbtn" value="{$lang['classes']['discussion']['form-fields']['add-message-button']}" class="submit-button">
    </form>
  </td>
</tr>
</table>
END_TEXT;
print $header;
?>
<!-- begin page content -->
<?php print $meeting_place_output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

