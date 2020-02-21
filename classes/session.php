<?php
$page_info['section'] = 'classes';
$page_info['sub_section'] = 'session';
$page_info['login_required'] = 1;
$page_info['course_id'] = vlc_get_url_variable($site_info, 'course', true);
$page_info['session_id'] = vlc_get_url_variable($site_info, 'session', true, $page_info['course_id']);
list($user_info, $course_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get course information */
$course_info_query = <<< END_QUERY
  SELECT r.resource_id, r.resource_type_id, IFNULL(r.title, '') AS title, IFNULL(r.content, '') AS content, IFNULL(r.url, '') AS url, IFNULL(rd.author, '') AS author, IFNULL(rd.source, '') AS source, IFNULL(rd.notes, '') AS notes
  FROM courses AS c, course_subjects AS s, resources AS r LEFT JOIN resource_details AS rd ON r.resource_id = rd.resource_id
  WHERE c.course_subject_id = s.course_subject_id
  AND s.course_subject_id = r.course_subject_id
  AND r.resource_type_id IN (4, 19, 21, 22, 23, 24, 25, 26, 35, 36, 40, 44, 48, 49, 50, 51, 52, 58)
  AND c.course_id = {$page_info['course_id']}
  AND r.session_id = {$page_info['session_id']}
  ORDER BY r.display_order
END_QUERY;

$result = mysql_query($course_info_query, $site_info['db_conn']);
$session_intro = '';
$session_objectives = '';
$session_requirements = '';

while ($record = mysql_fetch_array($result))
{
  $resource_type = $lang['database']['resource-types'][$record['resource_type_id']];
  /* get author, source, and notes for readings */
  if (in_array($record['resource_type_id'], array(22, 23, 24, 25, 35, 36)))
  {
    $author_source_notes = '';
    if (strlen($record['author']) > 0) $author_source_notes .= '<li>'.$lang['classes']['session']['misc']['author'].': '.$record['author'].'</li>';
    if (strlen($record['source']) > 0) $author_source_notes .= '<li>'.$lang['classes']['session']['misc']['source'].': '.$record['source'].'</li>';
    if (strlen($record['notes']) > 0) $author_source_notes .= '<li><b>'.$lang['classes']['session']['misc']['notes'].':</b> '.$record['notes'].'</li>';
  }
  /* session introduction */
  if ($record['resource_type_id'] == 19) $session_intro .= '<p>'.vlc_convert_code($record['content'], $page_info['course_id']).'</p>';
  /* session objectives */
  elseif ($record['resource_type_id'] == 21) $session_objectives .= '<li>'.vlc_convert_code($record['content'], $page_info['course_id']).'</li>';
  /* weekly study chart, course evaluation */
  elseif (in_array($record['resource_type_id'], array(40, 44))) $session_requirements .= '<li>'.vlc_internal_link($resource_type, 'classes/resource.php?course='.$page_info['course_id'].'&session='.$page_info['session_id'].'&resource='.$record['resource_id']).'</li>';
  /* keys */
  elseif (in_array($record['resource_type_id'], array(48, 49, 50, 51, 52))) $session_requirements .= '<li>'.vlc_internal_link('<img src="'.$site_info['images_url'].'key.jpg" width="61" height="16" alt="'.$resource_type.'" title="'.$resource_type.'">', 'classes/resource.php?course='.$page_info['course_id'].'&session='.$page_info['session_id'].'&resource='.$record['resource_id']).'</li>';
  /* reading - hardcopy */
  elseif (in_array($record['resource_type_id'], array(23, 25)))
  {
    if (strlen($author_source_notes) > 0) $author_source_notes = '<ul>'.$author_source_notes.'</ul>';
    $session_requirements .= '<li>'.$resource_type.': '.vlc_internal_link($record['title'], 'classes/resource.php?course='.$page_info['course_id'].'&session='.$page_info['session_id'].'&resource='.$record['resource_id']).$author_source_notes.'</li>';
  }
  /* reading - external website */
  elseif (in_array($record['resource_type_id'], array(35, 36)))
  {
    $reading_details = '<ul><li>'.$lang['classes']['reading']['misc']['url'].': '.vlc_external_link($record['url'], $record['url']).'</li>'.$author_source_notes.'</ul>';
    $session_requirements .= '<li>'.$resource_type.': '.vlc_internal_link($record['title'], 'classes/resource.php?course='.$page_info['course_id'].'&session='.$page_info['session_id'].'&resource='.$record['resource_id']).$reading_details.'</li>';
  }
  /* reading - from our database */
  elseif (in_array($record['resource_type_id'], array(22, 24)))
  {
    if (strlen($author_source_notes) > 0) $author_source_notes = '<ul>'.$author_source_notes.'</ul>';
    $session_requirements .= '<li>'.$resource_type.': '.vlc_internal_link($record['title'], 'classes/resource.php?course='.$page_info['course_id'].'&session='.$page_info['session_id'].'&resource='.$record['resource_id']).$author_source_notes.'</li>';
  }
  /* everything else - discussion boards, exercises, etc. */
  else $session_requirements .= '<li>'.$resource_type.': '.vlc_internal_link($record['title'], 'classes/resource.php?course='.$page_info['course_id'].'&session='.$page_info['session_id'].'&resource='.$record['resource_id']).'</li>';
}
if (strlen($session_intro) > 0) $session_intro = '<h3>'.$lang['classes']['session']['heading']['introduction'].'</h3>'.$session_intro;
if (strlen($session_objectives) > 0) $session_objectives = '<h3>'.$lang['classes']['session']['heading']['objectives'].'</h3><ul>'.$session_objectives.'</ul>';
if (strlen($session_requirements) > 0) $session_requirements = '<h3>'.$lang['classes']['session']['heading']['requirements'].'</h3><ol>'.$session_requirements.'</ol>';
print $header;
?>
<!-- begin page content -->
<?php print $session_intro ?>
<?php print $session_objectives ?>
<?php print $session_requirements ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

