<?php
if (isset($_GET['course']) and is_numeric($_GET['course'])) $course_subject_id = $_GET['course'];
else vlc_redirect('misc/error.php?error=404');
$page_info['section'] = 'calendar';
$page_info['page'] = 'course-details';
$login_required = 0;
$user_info = vlc_get_user_info($login_required);
$lang = vlc_get_language();
$course_details_query = <<< END_QUERY
  SELECT s.description, s.amazon_link, t.ceu, s.course_level_id 
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
    $course_details['sessions'][$record['session_id']]['title'] = sprintf($lang['calendar']['course-details']['misc']['session-title'], $record['display_order'], $record['description']);
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
      $course_material_label = sprintf($lang['calendar']['course-details']['misc']['required-materials'], $lang['database']['resource-formats'][$record['resource_format_id']]);
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
      $course_material_label = sprintf($lang['calendar']['course-details']['misc']['optional-materials'], $lang['database']['resource-formats'][$record['resource_format_id']]);
      $course_details['materials'][] = '<b>'.$course_material_label.':</b> '.join(' ', $course_material_array);
    }
    elseif ($record['resource_type_id'] == 53) $course_details['summary'] = vlc_convert_code($record['content']);
  }
  $output = '<h1 style="text-align: center">'.$course_details['description'].'</h1>';
  
  $output .= '<div style="text-align: center; font-weight: bold">'.sprintf($lang['courses']['course-details']['misc']['course-level'], $lang['database']['course-levels'][$course_details['course_level_id']]).'</div>';
  
  if (isset($course_details['summary']))
  {
    $output .= '<h2>'.$lang['calendar']['course-details']['heading']['course-details'].'</h2>';
    $output .= $course_details['summary'];
  }
  $output .= '<p>'.sprintf($lang['calendar']['course-details']['misc']['ceu-statement'], $course_details['ceu']).'</p>';
  if (isset($course_details['objectives']) and count($course_details['objectives']))
  {
    $output .= '<h2>'.$lang['calendar']['course-details']['heading']['course-objectives'].'</h2>';
    $output .= '<ul>';
    foreach ($course_details['objectives'] as $objective) $output .= '<li>'.$objective.'</li>';
    $output .= '</ul>';
  }
  $output .= '<h2>'.$lang['calendar']['course-details']['heading']['course-materials'].'</h2>';
  $output .= '<ul>';
  if ($num_required == 0) $output .= '<li><b>'.$lang['calendar']['course-details']['misc']['all-materials-online'].'</b></li>';
  if (isset($course_details['materials']) and count($course_details['materials']))
  {
    foreach ($course_details['materials'] as $material) $output .= '<li>'.$material.'</li>';
  }
  //if (isset($course_details['amazon_link'])) $output .= '<li>'.sprintf($lang['calendar']['course-details']['misc']['amazon-link'], $course_details['amazon_link']).'</li>';
  $output .= '</ul>';
  if (isset($course_details['sessions']) and count($course_details['sessions']))
  {
    $output .= '<h2>'.$lang['calendar']['course-details']['heading']['course-structure'].'</h2>';
    $output .= '<ul>';
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
    $output .= '</ul>';
  }
}
else vlc_redirect('misc/error.php?error=404');
$output .= '<p style="text-align: center;">'.$lang['calendar']['course-details']['content']['print-link'].' - '.$lang['calendar']['course-details']['content']['close-window-link'].'</p>';
?>
<!--
  Virtual Learning Community for Faith Formation (VLCFF)
  Institute for Pastoral Initiatives (IPI)
  University of Dayton
  vlcff@udayton.edu
  http://vlc.udayton.edu/
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php print $lang['common']['misc']['current-language-code'] ?>" lang="<?php print $lang['common']['misc']['current-language-code'] ?>">
<head>
<title><?php print $lang['common']['misc']['vlcff'] ?> @ UD &gt; <?php print $lang[$page_info['section']][$page_info['page']]['page-title'] ?></title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
<link rel="stylesheet" type="text/css" href="<?php print $site_info['css_url'] ?>popup.css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="<?php print $lang['common']['misc']['current-language-code'] ?>">
<meta name="Keywords" content="<?php print $lang['common']['misc']['meta-keywords'] ?>">
<meta name="Description" content="<?php print $lang['common']['misc']['meta-description'] ?>">
<meta name="Author" content="<?php print $lang['common']['misc']['meta-author'] ?>">
</head>
<body onload="self.focus();">
<!-- begin page content -->
<div class="container">
  <?php print $output ?>
</div>
<!-- end page content -->
</body>
</html>
