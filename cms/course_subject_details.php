<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'course-subject-details';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get form fields */
if (strlen($status_message) > 0 and isset($_SESSION['form_fields']))
{
  $form_fields = $_SESSION['form_fields'];
  $_SESSION['form_fields'] = null;
}
elseif (isset($_GET['subject']))
{
  $form_fields['course_subject_id'] = intval($_GET['subject']);
  /* get course subject details */
  $course_subject_query = <<< END_QUERY
    SELECT course_subject_id, IFNULL(description, '') AS description, IFNULL(amazon_link, '') AS amazon_link,
      course_type_id, course_level_id, language_id, is_restricted, is_active
    FROM course_subjects
    WHERE course_subject_id = {$form_fields['course_subject_id']}
END_QUERY;
  $result = mysql_query($course_subject_query, $site_info['db_conn']);
  $form_fields = mysql_fetch_array($result);
  /* get track(s) */
  $track_query = 'SELECT course_track_id FROM courses_tracks WHERE course_subject_id = '.$form_fields['course_subject_id'];
  $result = mysql_query($track_query, $site_info['db_conn']);
  $form_fields['course_tracks'] = array();
  while ($record = mysql_fetch_array($result)) $form_fields['course_tracks'][] = $record['course_track_id'];
}
else
{
  $form_fields = array
  (
    'description' => '',
    'amazon_link' => '',
    'course_type_id' => -1,
    'course_level_id' => -1,
    'language_id' => -1,
    'is_restricted' => 0,
    'is_active' => 0
  );
  $form_fields['course_tracks'] = array();
}
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = $value; // Removed htmlspecialchars() - rws 2015-05-20
}
/* get event details */
if (isset($form_fields['course_subject_id']))
{
  $entity_id = $form_fields['course_subject_id'];
  $event_type_array = array(
    COURSE_SUBJECTS_CREATE,
    COURSE_SUBJECTS_UPDATE,
    COURSE_SUBJECTS_ADD_SESSION,
    COURSE_SUBJECTS_UPDATE_SESSION,
    COURSE_SUBJECTS_REMOVE_SESSION,
    COURSE_SUBJECTS_ADD_RESOURCE,
    COURSE_SUBJECTS_UPDATE_RESOURCE,
    COURSE_SUBJECTS_REMOVE_RESOURCE
  );
  $event_history = vlc_get_event_history($event_type_array, $entity_id);
}
/* build array for "course type" select box */
$course_type_options_array = array();
$course_type_query = <<< END_QUERY
  SELECT course_type_id, description
  FROM course_types
  ORDER BY course_type_id
END_QUERY;
$result = mysql_query($course_type_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $course_type_options_array[$record['course_type_id']] = $record['description'];
/* build array for "course level" select box */
$course_level_options_array = array();
$course_level_query = <<< END_QUERY
  SELECT course_level_id, description
  FROM course_levels
  ORDER BY course_level_id
END_QUERY;
$result = mysql_query($course_level_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $course_level_options_array[$record['course_level_id']] = $record['description'];
/* build array for "course track" select box */
$course_track_options_array = array();
$course_track_query = <<< END_QUERY
  SELECT course_track_id, description
  FROM course_tracks
  ORDER BY description
END_QUERY;
$result = mysql_query($course_track_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $course_track_options_array[$record['course_track_id']] = $record['description'];
/* build array for "language" select box */
$language_options_array = array();
$language_query = <<< END_QUERY
  SELECT language_id, description
  FROM languages
  ORDER BY language_id
END_QUERY;
$result = mysql_query($language_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $language_options_array[$record['language_id']] = $record['description'];
/* begin output variable */
$output = '';
/* return to previous page link */
if (isset($_SERVER['HTTP_REFERER']) and $return_url = strstr($_SERVER['HTTP_REFERER'], 'cms/')) $output .= '<p>'.vlc_internal_link('Return to Previous Page', $return_url).'</p>';
/* add course subject details to output */
$output .= '<form method="post" action="course_subject_action.php">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
if (isset($form_fields['course_subject_id'])) $output .= '<tr><td><b>Course Subject ID:</b></td><td colspan="3">'.$form_fields['course_subject_id'].'<input type="hidden" name="course_subject_id" value="'.$form_fields['course_subject_id'].'"></td></tr>';
$output .= '<tr>';
$output .= '<td><b>Description:</b></td><td><input type="text" size="50" name="description" value="'.$form_fields['description'].'"></td>';
$output .= '<td><b>Language:</b></td><td>'.vlc_select_box($language_options_array, 'array', 'language_id', $form_fields['language_id'], true).'</td>';
$output .= '</tr>';
$output .= '<tr><td><b>Amazon Link:</b></td><td colspan="3"><input type="text" size="50" name="amazon_link" value="'.$form_fields['amazon_link'].'"></td></tr>';
$output .= '<tr>';
$output .= '<td><b>Restricted:</b></td><td><input type="checkbox" name="is_restricted" value="1"'.($form_fields['is_restricted'] ? ' checked="checked"' : '').'></td>';
$output .= '<td><b>Active:</b></td><td><input type="checkbox" name="is_active" value="1"'.($form_fields['is_active'] ? ' checked="checked"' : '').'></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Course Type:</b></td><td>'.vlc_select_box($course_type_options_array, 'array', 'course_type_id', $form_fields['course_type_id'], true).'</td>';
$output .= '<td><b>Course Level:</b></td><td>'.vlc_select_box($course_level_options_array, 'array', 'course_level_id', $form_fields['course_level_id'], true).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td valign="top"><b>Course Track(s):</b></td>';
$output .= '<td colspan="3">';
$output .= '<select multiple size="3" name="course_tracks[]">';
foreach ($course_track_options_array as $course_track_id => $course_track)
{
  if (in_array($course_track_id, $form_fields['course_tracks'])) $selected = ' selected';
  else $selected = '';
  $output .= '<option value="'.$course_track_id.'"'.$selected.'>'.$course_track.'</option>';
}
$output .= '</select> (Note: Use CTRL or SHIFT to select multiple options.)';
$output .= '</td>';
$output .= '</tr>';
$output .= '<tr><td colspan="4" align="center"><input type="submit" value="Submit"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
if (isset($form_fields['course_subject_id']))
{
  /* build array for "course" select box */
  $course_options_array = array();
  $course_options_query = <<< END_QUERY
    SELECT course_id, code, description
    FROM courses
    WHERE course_subject_id = {$form_fields['course_subject_id']}
    ORDER BY student_start, code
END_QUERY;
  $result = mysql_query($course_options_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) $course_options_array[$record['course_id']] = $record['code'].' - '.$record['description'].' ('.$record['course_id'].')';
  /* list course offerings */
  $output .= '<h2>Course Offerings</h2>';
  $output .= '<form method="get" action="'.$site_info['home_url'].'classes/">';
  $output .= '<p>To see this course subject in the classes user interface, choose a course offering and click <b>&quot;Submit&quot;</b>:</p>';
  $output .= '<ul>';
  if (count($course_options_array)) $output .= '<li>'.vlc_select_box($course_options_array, 'array', 'course', -1, true).' <input type="submit" value="Submit"></li>';
  else $output .= '<li>This course has not yet been offered.</li>';
  $output .= '</ul>';
  $output .= '</form>';
  $resource_type_query = <<< END_QUERY
    SELECT rt.resource_type_id, rt.description AS resource_type
    FROM resource_types AS rt
    ORDER BY rt.description
END_QUERY;
  $result = mysql_query($resource_type_query, $site_info['db_conn']);
  $course_resource_type_ids = array(6, 11, 13, 37, 46, 47, 53, 54, 55); /* 15, 16, 17, 18, 38, 39, 41 */
  $session_resource_type_ids = array(4, 19, 21, 22, 23, 24, 25, 26, 35, 36, 40, 44, 48, 49, 50, 51, 52, 58);
  $all_resource_type_ids = array_unique(array_merge($course_resource_type_ids, $session_resource_type_ids));
  while ($record = mysql_fetch_array($result))
  {
    if (in_array($record['resource_type_id'], $course_resource_type_ids)) $resource_type_array['course'][$record['resource_type_id']] = $record['resource_type'];
    if (in_array($record['resource_type_id'], $session_resource_type_ids)) $resource_type_array['session'][$record['resource_type_id']] = $record['resource_type'];
  }
  $course_resource_types = vlc_select_box($resource_type_array['course'], 'array', 'type', -1, true, 'form-field');
  $session_resource_types = vlc_select_box($resource_type_array['session'], 'array', 'type', -1, true, 'form-field');
  for ($i = 1; $i <= 25; $i++) $display_order_array[$i] = $i;
  $session_query = <<< END_QUERY
    SELECT s.session_id, s.display_order, s.description AS session_description
    FROM course_subjects AS cs, sessions AS s
    WHERE cs.course_subject_id = s.course_subject_id
    AND cs.course_subject_id = {$form_fields['course_subject_id']}
    ORDER BY s.display_order
END_QUERY;
  $result = mysql_query($session_query, $site_info['db_conn']);
  /* initialize session array */
  $course_info['sessions'] = array();
  while ($record = mysql_fetch_array($result))
  {
    $course_info['sessions'][$record['session_id']] = $record;
    /* initialize session resources array */
    $course_info['sessions'][$record['session_id']]['resources'] = array();
  }
  $resource_type_id_list = join(', ', $all_resource_type_ids);
  $resource_query = <<< END_QUERY
    SELECT r.resource_id, IFNULL(r.title, '') AS resource_title, IFNULL(IF(LENGTH(r.content) > 50, CONCAT(LEFT(r.content, 50), '...'), r.content), '') AS resource_content, r.resource_type_id, rt.description AS resource_type, IFNULL(r.session_id, '') AS session_id, IFNULL(r.display_order, '') AS display_order
    FROM resources AS r, resource_types AS rt
    WHERE r.resource_type_id = rt.resource_type_id
    AND r.resource_type_id IN ($resource_type_id_list)
    AND r.course_subject_id = {$form_fields['course_subject_id']}
    ORDER BY r.session_id, r.display_order, rt.description
END_QUERY;
  $result = mysql_query($resource_query, $site_info['db_conn']);
  /* initialize course resources array */
  $course_info['resources'] = array();
  while ($record = mysql_fetch_array($result))
  {
    if (isset($course_info['sessions'][$record['session_id']])) $course_info['sessions'][$record['session_id']]['resources'][] = $record;
    else $course_info['resources'][] = $record;
  }
  $course_summary = $course_materials = $course_intro = $course_objectives = $course_resources = '';
  foreach ($course_info['resources'] as $resource)
  {
    if ($resource['resource_type_id'] == 53) $course_summary .= '<li><b>'.$resource['resource_type'].'</b> ['.vlc_internal_link('Edit', 'cms/resource_details.php?subject='.$form_fields['course_subject_id'].'&resource='.$resource['resource_id']).'] ['.vlc_internal_link('Delete', 'cms/resource_action.php?subject='.$form_fields['course_subject_id'].'&resource='.$resource['resource_id'].'&type='.$resource['resource_type_id'].'&action=6', '', '', 0, 0, 1).']</li>';
    elseif ($resource['resource_type_id'] == 11) $course_intro .= '<li><b>'.$resource['resource_type'].'</b> ['.vlc_internal_link('Edit', 'cms/resource_details.php?subject='.$form_fields['course_subject_id'].'&resource='.$resource['resource_id']).'] ['.vlc_internal_link('Delete', 'cms/resource_action.php?subject='.$form_fields['course_subject_id'].'&resource='.$resource['resource_id'].'&type='.$resource['resource_type_id'].'&action=6', '', '', 0, 0, 1).']</li>';
    elseif ($resource['resource_type_id'] == 13) $course_objectives .= '<li>'.vlc_select_box($display_order_array, 'array', $resource['resource_id'], $resource['display_order'], true, 'form-field').'&nbsp;'.$resource['resource_type'].' ('.$resource['resource_content'].') ['.vlc_internal_link('Edit', 'cms/resource_details.php?subject='.$form_fields['course_subject_id'].'&resource='.$resource['resource_id']).'] ['.vlc_internal_link('Delete', 'cms/resource_action.php?subject='.$form_fields['course_subject_id'].'&resource='.$resource['resource_id'].'&type='.$resource['resource_type_id'].'&action=6', '', '', 0, 0, 1).']</li>';
    elseif (in_array($resource['resource_type_id'], array(54, 55))) $course_materials .= '<li>'.vlc_select_box($display_order_array, 'array', $resource['resource_id'], $resource['display_order'], true, 'form-field').'&nbsp;'.$resource['resource_type'].' ('.$resource['resource_title'].') ['.vlc_internal_link('Edit', 'cms/resource_details.php?subject='.$form_fields['course_subject_id'].'&resource='.$resource['resource_id']).'] ['.vlc_internal_link('Delete', 'cms/resource_action.php?subject='.$form_fields['course_subject_id'].'&resource='.$resource['resource_id'].'&type='.$resource['resource_type_id'].'&action=6', '', '', 0, 0, 1).']</li>';
    else $course_resources .= '<li>'.$resource['resource_type'].' ['.vlc_internal_link('Edit', 'cms/resource_details.php?subject='.$form_fields['course_subject_id'].'&resource='.$resource['resource_id']).'] ['.vlc_internal_link('Delete', 'cms/resource_action.php?subject='.$form_fields['course_subject_id'].'&resource='.$resource['resource_id'].'&type='.$resource['resource_type_id'].'&action=6', '', '', 0, 0, 1).']</li>';
  }
  $output .= '<h2>Course Outline</h2>';
  $output .= '<h3>Course Resources</h3>';
  $output .= '<ul>';
  $output .= $course_summary;
  if (strlen($course_materials)) $output .= '<form action="resource_action.php?subject='.$form_fields['course_subject_id'].'" method="post"><input type="hidden" name="action" value="2"><li><b>Course Materials</b><ul>'.$course_materials.'</ul></li><li><input type="submit" value="Update Display Order"></li></form>';
  $output .= $course_intro;
  if (strlen($course_objectives)) $output .= '<form action="resource_action.php?subject='.$form_fields['course_subject_id'].'" method="post"><input type="hidden" name="action" value="2"><li><b>Course Objectives</b><ul>'.$course_objectives.'</ul></li><li><input type="submit" value="Update Display Order"></li></form>';
  if (strlen($course_resources)) $output .= '<li><b>Course Resources</b><ul>'.$course_resources.'</ul></li>';
  $output .= '<form action="resource_details.php" method="get">';
  $output .= '<input type="hidden" name="subject" value="'.$form_fields['course_subject_id'].'">';
  $output .= '<li>Add a Course Resource: '.$course_resource_types.'&nbsp;<input type="submit" value="Go"></li>';
  $output .= '</form>';
  $output .= '</ul>';
  $output .= '<h3>Course Sessions</h3>';
  $output .= '<ul>';
  foreach ($course_info['sessions'] as $session)
  {
    $session_intro = $session_objectives = $session_resources = '';
    $session_id = $session['session_id'];
    $session_num = $session['display_order'];
    $session_description = $session['session_description'];
    $output .= '<li>';
    $output .= '<form action="resource_action.php?subject='.$form_fields['course_subject_id'].'" method="post" onsubmit="return validate_form(this);">';
    $output .= '<input type="hidden" name="action" value="5">';
    $output .= '<input type="hidden" name="session_id" value="'.$session_id.'">';
    $output .= '<b>Session</b> '.vlc_select_box($display_order_array, 'array', 'display_order', $session_num, true, 'form-field').': ';
    $output .= '<input type="text" name="session_description" value="'.$session_description.'" size="40" class="form-field-required" required="true" message="Session description is required.">&nbsp;';
    $output .= '<input type="submit" value="Update Session">';
    $output .= '</form>';
    $output .= '<ul>';
    foreach ($session['resources'] as $resource)
    {
      $resource_id = $resource['resource_id'];
      $resource_type_id = $resource['resource_type_id'];
      $resource_type = $resource['resource_type'];
      $resource_title = $resource['resource_title'];
      $resource_content = $resource['resource_content'];
      $resource_url = 'cms/resource_details.php?subject='.$form_fields['course_subject_id'].'&session='.$session_id.'&resource='.$resource_id;
      if ($resource_type_id == 19) $session_intro .= '<li><b>'.$resource_type.'</b> ['.vlc_internal_link('Edit', $resource_url).'] ['.vlc_internal_link('Delete', 'cms/resource_action.php?subject='.$form_fields['course_subject_id'].'&resource='.$resource['resource_id'].'&type='.$resource['resource_type_id'].'&action=6', '', '', 0, 0, 1).']</li>';
      elseif ($resource_type_id == 21) $session_objectives .= '<li>'.vlc_select_box($display_order_array, 'array', $resource['resource_id'], $resource['display_order'], true, 'form-field').'&nbsp;'.$resource_type.' ('.$resource_content.') ['.vlc_internal_link('Edit', $resource_url).'] ['.vlc_internal_link('Delete', 'cms/resource_action.php?subject='.$form_fields['course_subject_id'].'&resource='.$resource['resource_id'].'&type='.$resource['resource_type_id'].'&action=6', '', '', 0, 0, 1).']</li>';
      elseif (in_array($resource_type_id, array(40, 44))) $session_resources .= '<li>'.vlc_select_box($display_order_array, 'array', $resource['resource_id'], $resource['display_order'], true, 'form-field').'&nbsp;'.$resource_type.' ['.vlc_internal_link('Edit', $resource_url).'] ['.vlc_internal_link('Delete', 'cms/resource_action.php?subject='.$form_fields['course_subject_id'].'&resource='.$resource['resource_id'].'&type='.$resource['resource_type_id'].'&action=6', '', '', 0, 0, 1).']</li>';
      else $session_resources .= '<li>'.vlc_select_box($display_order_array, 'array', $resource['resource_id'], $resource['display_order'], true, 'form-field').'&nbsp;'.$resource_type.': '.$resource_title.' ['.vlc_internal_link('Edit', $resource_url).'] ['.vlc_internal_link('Delete', 'cms/resource_action.php?subject='.$form_fields['course_subject_id'].'&resource='.$resource['resource_id'].'&type='.$resource['resource_type_id'].'&action=6', '', '', 0, 0, 1).']</li>';
    }
    $output .= $session_intro;
    if (strlen($session_objectives)) $output .= '<form action="resource_action.php?subject='.$form_fields['course_subject_id'].'" method="post"><input type="hidden" name="action" value="2"><li><b>Session Objectives</b><ul>'.$session_objectives.'</ul></li><li><input type="submit" value="Update Display Order"></li></form>';
    if (strlen($session_resources)) $output .= '<form action="resource_action.php?subject='.$form_fields['course_subject_id'].'" method="post"><input type="hidden" name="action" value="2"><li><b>Session Resources</b><ul>'.$session_resources.'</ul></li><li><input type="submit" value="Update Display Order"></li></form>';
    $output .= '<form action="resource_details.php" method="get"><input type="hidden" name="subject" value="'.$form_fields['course_subject_id'].'"><input type="hidden" name="session" value="'.$session_id.'"><li>Add a Session Resource:&nbsp;'.$session_resource_types.'&nbsp;<input type="submit" value="Go"></li></form></ul></li>';
  }
  if (isset($session_num)) $next_session_num = $session_num + 1;
  else $next_session_num = 1;
  $output .= '<form action="resource_action.php?subject='.$form_fields['course_subject_id'].'" method="post" onsubmit="return validate_form(this);">';
  $output .= '<input type="hidden" name="action" value="1">';
  $output .= '<input type="hidden" name="next_session_num" value="'.$next_session_num.'">';
  $output .= '<li>';
  $output .= 'Add a Session:&nbsp;';
  $output .= '<input type="text" name="session_description" size="40" class="form-field-required" required="true" message="Session description is required.">&nbsp;';
  $output .= '<input type="submit" value="Go">';
  $output .= '</li>';
  $output .= '</form>';
  $output .= '</ul>';
  $output .= $event_history;
}
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

