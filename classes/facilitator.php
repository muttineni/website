<?php
$page_info['section'] = 'classes';
$page_info['sub_section'] = 'facilitator';
$page_info['login_required'] = 1;
$page_info['course_id'] = vlc_get_url_variable($site_info, 'course', true);
list($user_info, $course_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get facilitator information */
$facilitator_query = <<< END_QUERY
  SELECT u.user_id, TRIM(CONCAT(IFNULL(u.prefix, ''), ' ', IFNULL(u.first_name, ''), ' ', IFNULL(u.middle_name, ''), ' ', IFNULL(u.last_name, ''), ' ', IFNULL(u.suffix, ''))) AS name, IFNULL(i.city, '') AS city, IFNULL(i.state_id, -1) AS state_id, i.country_id, IFNULL(i.primary_email, '') AS primary_email, IFNULL(i.secondary_email, '') AS secondary_email, IFNULL(i.biography, '') AS biography, IFNULL(i.image, '') AS image
  FROM users_courses AS uc, users AS u, user_info AS i
  WHERE uc.user_id = u.user_id
  AND u.user_id = i.user_id
  AND uc.user_role_id = 4
  AND uc.course_id = {$page_info['course_id']}
  AND uc.course_status_id IN (2, 3, 6, 7)
  ORDER BY u.last_name, u.first_name
END_QUERY;
$result = mysql_query($facilitator_query, $site_info['db_conn']);
$facilitator_details = '';
while ($facilitator_info = mysql_fetch_array($result))
{
  $vlc_mail_link = vlc_internal_link(sprintf($lang['classes']['common']['misc']['send-mail-to'], $facilitator_info['name']), 'classes/mail_form.php?course='.$page_info['course_id'].'&recipient='.$facilitator_info['user_id'].'&action=2');
  $facilitator_info['email_link'] = vlc_mailto_link($facilitator_info['primary_email'], $facilitator_info['primary_email'], sprintf($lang['classes']['common']['misc']['vlcff-course'], $course_info['title']));
  if (strlen(trim($facilitator_info['secondary_email'])) > 0) $facilitator_info['email_link'] .= ', '.vlc_mailto_link($facilitator_info['secondary_email'], $facilitator_info['secondary_email'], sprintf($lang['classes']['common']['misc']['vlcff-course'], $course_info['title']));
  if (strlen(trim($facilitator_info['biography'])) == 0) $facilitator_info['biography'] = $lang['classes']['common']['misc']['bio-not-available'];
  if (strlen(trim($facilitator_info['image'])) == 0) $facilitator_info['image_tag'] = '<img src="'.$site_info['images_url'].$lang['common']['misc']['current-language-code'].'/no_pic.gif" width="125" height="175" alt="'.$lang['classes']['common']['misc']['picture-not-available'].'" title="'.$lang['classes']['common']['misc']['picture-not-available'].'" style="border: 1px solid #000;">';
  else $facilitator_info['image_tag'] = '<img src="'.$site_info['images_url'].'users/'.$facilitator_info['image'].'" width="125" height="175" alt="'.sprintf($lang['classes']['common']['misc']['picture-label'], $facilitator_info['name']).'" title="'.sprintf($lang['classes']['common']['misc']['picture-label'], $facilitator_info['name']).'" style="border: 1px solid #000;">';
  $facilitator_info['location'] = $facilitator_info['city'];
  if (isset($lang['database']['states'][$facilitator_info['state_id']])) $facilitator_info['location'] .= ', '.$lang['database']['states'][$facilitator_info['state_id']];
  if (isset($lang['database']['countries'][$facilitator_info['country_id']])) $facilitator_info['location'] .= ', '.$lang['database']['countries'][$facilitator_info['country_id']];
  $facilitator_details .= <<< END_TEXT
    <h2>{$facilitator_info['name']}</h2>
    <p>{$facilitator_info['image_tag']}</p>
    <p>$vlc_mail_link</p>
    <p><b>{$lang['classes']['common']['misc']['email-address']}:</b> {$facilitator_info['email_link']}</p>
    <p><b>{$lang['classes']['common']['misc']['location']}:</b> {$facilitator_info['location']}</p>
    <p>{$facilitator_info['biography']}</p>
END_TEXT;
}
print $header;
?>
<!-- begin page content -->
<?php print $facilitator_details ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

