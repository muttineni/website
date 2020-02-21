<?php
$page_info['section'] = 'classes';
$page_info['sub_section'] = 'home';
$page_info['login_required'] = 1;
$page_info['course_id'] = vlc_get_url_variable($site_info, 'course', true);
list($user_info, $course_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get course information */
$course_info_query = <<< END_QUERY
  SELECT r.resource_id, r.resource_type_id, IFNULL(r.title, '') AS title, IFNULL(r.content, '') AS content, IFNULL(r.url, '') AS url
  FROM courses AS c, course_subjects AS s, resources AS r
  WHERE c.course_subject_id = s.course_subject_id
  AND s.course_subject_id = r.course_subject_id
  AND r.resource_type_id IN (11, 13)
  AND c.course_id = {$page_info['course_id']}
  AND r.session_id IS NULL
  ORDER BY r.display_order
END_QUERY;
$result = mysql_query($course_info_query, $site_info['db_conn']);
$course_intro = '';
$course_objectives = '';
$course_links = '';
while ($record = mysql_fetch_array($result))
{
  if ($record['resource_type_id'] == 11) $course_intro .= '<p>'.vlc_convert_code($record['content'], $page_info['course_id']).'</p>';
  elseif ($record['resource_type_id'] == 13) $course_objectives .= '<li>'.vlc_convert_code($record['content'], $page_info['course_id']).'</li>';
  elseif ($record['resource_type_id'] == 4) $course_links .= '<li>'.$lang['database']['resource-types'][$record['resource_type_id']].': '.vlc_internal_link($record['title'], 'classes/resource.php?course='.$page_info['course_id'].'&resource='.$record['resource_id']).'</li>';
  else $course_links .= '<li>'.vlc_internal_link($lang['database']['resource-types'][$record['resource_type_id']], 'classes/resource.php?course='.$page_info['course_id'].'&resource='.$record['resource_id']).'</li>';
}
if (strlen($course_intro) > 0) $course_intro = '<h3>'.$lang['classes']['index']['heading']['introduction'].'</h3>'.$course_intro;
if (strlen($course_objectives) > 0) $course_objectives = '<h3>'.$lang['classes']['index']['heading']['objectives'].'</h3><ul>'.$course_objectives.'</ul>';
if (strlen($course_links) > 0) $course_links = '<h3>'.$lang['classes']['index']['heading']['resources'].'</h3><p>'.$lang['classes']['index']['misc']['please-review-resources'].'</p><ol>'.$course_links.'</ol>';
print $header;
?>
<!-- begin page content -->
<?php print $course_intro ?>
<?php print $course_objectives ?>
<?php print $course_links ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

