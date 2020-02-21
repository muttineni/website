<?php
$page_info['section'] = 'classes';
$page_info['sub_section'] = 'students';
$page_info['login_required'] = 1;
$page_info['course_id'] = vlc_get_url_variable($site_info, 'course', true);
list($user_info, $course_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get student information */
$student_query = <<< END_QUERY
  SELECT u.user_id, CONCAT(IFNULL(u.first_name, ''), ' ', IFNULL(u.last_name, '')) AS name, IFNULL(i.diocese_id, -1) AS diocese_id, IFNULL(i.state_id, -1) AS state_id, i.country_id, IFNULL(i.biography, '') AS biography, IFNULL(i.image, '') AS image
  FROM users_courses AS uc, users AS u, user_info AS i
  WHERE uc.user_id = u.user_id
  AND u.user_id = i.user_id
  AND uc.user_role_id = 5
  AND uc.course_id = {$page_info['course_id']}
  AND uc.course_status_id IN (2, 3, 6, 7)
  ORDER BY u.last_name, u.first_name
END_QUERY;
$result = mysql_query($student_query, $site_info['db_conn']);
$student_details = '<table>';
while ($record = mysql_fetch_array($result))
{
  $student_details .= '<tr><td valign="top">';
  if (strlen(trim($record['image'])) == 0) $student_details .= '<img src="'.$site_info['images_url'].$lang['common']['misc']['current-language-code'].'/'.'no_pic.gif" width="125" height="175" alt="'.$lang['classes']['common']['misc']['picture-not-available'].'" title="'.$lang['classes']['common']['misc']['picture-not-available'].'" style="border: 1px solid #000;">';
  else $student_details .= '<img src="'.$site_info['images_url'].'users/'.$record['image'].'" width="125" height="175" alt="'.sprintf($lang['classes']['common']['misc']['picture-label'], $record['name']).'" title="'.sprintf($lang['classes']['common']['misc']['picture-label'], $record['name']).'" style="border: 1px solid #000;">';
  $student_details .= '</td><td valign="top">';
  $student_details .= '<p class="table-background"><b>'.$record['name'].'</b> ['.vlc_internal_link($lang['classes']['common']['misc']['send-mail'], 'classes/mail_form.php?course='.$page_info['course_id'].'&recipient='.$record['user_id'].'&action=2', 'table-background').']</p>';
  $record['location'] = $lang['database']['countries'][$record['country_id']];
  if (isset($lang['database']['states'][$record['state_id']])) $record['location'] = $lang['database']['states'][$record['state_id']].', '.$record['location'];
  $student_details .= '<p class="table-background"><b>'.$lang['classes']['common']['misc']['location'].':</b> '.$record['location'].'</p>';
  if (isset($lang['database']['partners'][$record['diocese_id']])) $record['diocese'] = $lang['database']['partners'][$record['diocese_id']];
  else $record['diocese'] = $lang['classes']['common']['misc']['diocese-not-available'];
  $student_details .= '<p class="table-background"><b>'.$lang['classes']['common']['misc']['diocese'].':</b> '.$record['diocese'].'</p>';
  if (strlen(trim($record['biography'])) == 0) $record['biography'] = $lang['classes']['common']['misc']['bio-not-available'];
  $student_details .= '<p class="table-background">'.$record['biography'].'</p>';
  $student_details .= '</td></tr>';
}
$student_details .= '</table>';
print $header;
?>
<!-- begin page content -->
<?php print $student_details ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

