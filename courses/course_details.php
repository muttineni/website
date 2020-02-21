<?php
if (isset($_GET['course']) and is_numeric($_GET['course'])) $course_subject_id = $_GET['course'];
else vlc_redirect('misc/error.php?error=404');
$page_info['section'] = 'courses';
$page_info['page'] = 'course-details';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
$course_details_query = <<< END_QUERY
  SELECT s.description, s.amazon_link, s.course_level_id, t.ceu
  FROM course_subjects AS s, course_types AS t
  WHERE s.course_type_id = t.course_type_id
  AND s.course_subject_id = $course_subject_id
END_QUERY;
$result = mysql_query($course_details_query, $site_info['db_conn']);
if (mysql_num_rows($result))
{
  $course_details = mysql_fetch_array($result);
  $sessions_query = <<< END_QUERY
    SELECT session_id, description, display_order
    FROM sessions
    WHERE course_subject_id = $course_subject_id
    ORDER BY display_order
END_QUERY;
  $result = mysql_query($sessions_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result))
  {
    $course_details['sessions'][$record['session_id']]['title'] = sprintf($lang['courses']['course-details']['misc']['session-title'], $record['display_order'], $record['description']);
  }
  $resources_query = <<< END_QUERY
    SELECT r.session_id, r.resource_type_id, r.title, r.content, d.author, d.source, d.isbn, d.resource_format_id
    FROM resources AS r LEFT JOIN resource_details AS d ON r.resource_id = d.resource_id
    WHERE r.resource_type_id IN (13, 21, 53, 54, 55)
    AND r.course_subject_id = $course_subject_id
    ORDER BY r.resource_type_id, r.display_order
END_QUERY;
  $result = mysql_query($resources_query, $site_info['db_conn']);
  $num_required = 0;
  while ($record = mysql_fetch_array($result))
  {
    if ($record['resource_type_id'] == 13) $course_details['objectives'][] = $record['content'];
    elseif ($record['resource_type_id'] == 21) $course_details['sessions'][$record['session_id']]['objectives'][] = $record['content'];
    elseif ($record['resource_type_id'] == 54)
    {
      $course_material_array = array();
      if (isset($record['author'])) $course_material_array[] = $record['author'];
      if (isset($record['title'])) $course_material_array[] = '<i>'.$record['title'].'</i>';
      if (isset($record['source'])) $course_material_array[] = $record['source'];
      if (isset($record['isbn'])) $course_material_array[] = 'ISBN: '.$record['isbn'];
      if (isset($record['content'])) $course_material_array[] = '<ul><li>'.vlc_convert_code($record['content']).'</li></ul>';
      $course_material_label = sprintf($lang['courses']['course-details']['misc']['required-materials'], $lang['database']['resource-formats'][$record['resource_format_id']]);
      $course_details['materials'][] = '<b>'.$course_material_label.':</b> '.join(' ', $course_material_array);
      if ($record['resource_format_id'] == 1) $num_required++;
    }
    elseif ($record['resource_type_id'] == 55)
    {
      $course_material_array = array();
      if (isset($record['author'])) $course_material_array[] = $record['author'];
      if (isset($record['title'])) $course_material_array[] = '<i>'.$record['title'].'</i>';
      if (isset($record['source'])) $course_material_array[] = $record['source'];
      if (isset($record['isbn'])) $course_material_array[] = '<b>ISBN:</b> '.$record['isbn'];
      if (isset($record['content'])) $course_material_array[] = '<ul><li>'.vlc_convert_code($record['content']).'</li></ul>';
      $course_material_label = sprintf($lang['courses']['course-details']['misc']['optional-materials'], $lang['database']['resource-formats'][$record['resource_format_id']]);
      $course_details['materials'][] = '<b>'.$course_material_label.':</b> '.join(' ', $course_material_array);
    }
    elseif ($record['resource_type_id'] == 53) $course_details['summary'] = vlc_convert_code($record['content']);
  }
  $course_header = $course_details['description'].'<small class="text-muted d-block">'.sprintf($lang['courses']['course-details']['misc']['course-level'], $lang['database']['course-levels'][$course_details['course_level_id']]).'</small>';
  $output = '';
  /* summary */
  $ceu_statment = '<p>'.sprintf($lang['courses']['course-details']['misc']['ceu-statement'], $course_details['ceu']).'</p>';
  if (isset($course_details['summary']))
  {
    $output .= '<h3>'.$lang['courses']['course-details']['heading']['course-summary'].'</h3>';
    $output .= '<div class="course-detail">'.$course_details['summary'].$ceu_statment.'</div>';
  }
  else $output .= $ceu_statment;
  /* objectives */
  if (isset($course_details['objectives']) and count($course_details['objectives']))
  {
    $output .= '<h3>'.$lang['courses']['course-details']['heading']['course-objectives'].'</h3>';
    $output .= '<div class="course-detail"><ul>';
    foreach ($course_details['objectives'] as $objective) $output .= '<li>'.$objective.'</li>';
    $output .= '</ul></div>';
  }
  /* course materials */
  $output .= '<h3>'.$lang['courses']['course-details']['heading']['course-materials'].'</h3>';
  $output .= '<div class="course-detail"><ul>';
  if ($num_required == 0) $output .= '<li><b>'.$lang['courses']['course-details']['misc']['all-materials-online'].'</b></li>';
  if (isset($course_details['materials']) and count($course_details['materials']))
  {
    foreach ($course_details['materials'] as $material) $output .= '<li>'.$material.'</li>';
  }
  //commented out by RWS 2017/10/31 - if (isset($course_details['amazon_link'])) $output .= '<li>'.sprintf($lang['courses']['course-details']['misc']['amazon-link'], $course_details['amazon_link']).'</li>';
  $output .= '</ul></div>';
  /* sessions */
  if (isset($course_details['sessions']) and count($course_details['sessions']))
  {
    $output .= '<h3>'.$lang['courses']['course-details']['heading']['course-structure'].'</h3>';
    $output .= '<div class="course-detail"><ul>';
    foreach ($course_details['sessions'] as $session)
    {
      $output .= '<li><b>'.$session['title'].'</b>';
      if (isset($session['objectives']) and count($session['objectives']))
      {
        $output .= '<ul>';
        foreach ($session['objectives'] as $objective) $output .= '<li>'.$objective.'</li>';
        $output .= '</ul>';
      }
      $output .= '</li>';
    }
    $output .= '</ul></div>';
  }
}
else vlc_redirect('misc/error.php?error=404');
print $header;
?>
<!-- begin page content -->
<div class="container">
  <h1><?php print $course_header ?></h1>
  <div class="return-link">
    <i class="fa fa-arrow-left"></i> <?php print vlc_internal_link($lang['courses']['course-details']['misc']['course-catalog-return-link'], 'courses/courses.php?group_by_track=1#levels') ?>
  </div>
  <?php print $output ?>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
