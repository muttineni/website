<?php
$page_info['section'] = 'courses';
$page_info['page'] = 'course-catalog';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;

$where_clause = $order_by_clause = '';
$track_id = $level_id = -1;
$group_by_track = $group_by_level = 0;
$group_by_level_checked = $group_by_track_checked = '';
if (isset($_GET['level']) and is_numeric($_GET['level']) and $_GET['level'] > 0)
{
  $level_id = $_GET['level'];
  $where_clause .= ' AND c.course_level_id = '.$level_id;
}
if (isset($_GET['track']) and is_numeric($_GET['track']) and $_GET['track'] > 0)
{
  $track_id = $_GET['track'];
  $where_clause .= ' AND ct.course_track_id = '.$track_id;
}
if (isset($_GET['group_by_level']) and is_numeric($_GET['group_by_level']) and $_GET['group_by_level'] > 0)
{
  $group_by_level = 1;
  $group_by_level_checked = ' checked';
}
if (isset($_GET['group_by_track']) and is_numeric($_GET['group_by_track']) and $_GET['group_by_track'] > 0)
{
  $group_by_track = 1;
  $group_by_track_checked = ' checked';
}

/* Create Catalog Search Form */
$course_levels_options_array = array(-1 => $lang['courses']['course-catalog']['misc']['all-levels-label']) + $lang['database']['course-levels'];
$course_tracks_options_array = array(-1 => $lang['courses']['course-catalog']['misc']['all-tracks-label']) + $lang['database']['course-tracks'];

$form_level_select = vlc_select_box($course_levels_options_array, 'array', 'level', $level_id, true, 'custom-select form-control');
$form_track_select = vlc_select_box($course_tracks_options_array, 'array', 'track', $track_id, true, 'custom-select form-control');

$course_form = <<< COURSE_FORM
  <form action="courses.php#levels" method="get">
    <div class="form-group form-inline">
      <label for="level" class="p-2">{$lang['courses']['course-catalog']['misc']['choose-level-label']}: </label>
      $form_level_select
      <label class="custom-control custom-checkbox p-2">
        <input id="group_by_level_cbx" name="group_by_level" value="1" $group_by_level_checked
          type="checkbox" class="custom-control-input" />
        <span class="custom-control-indicator"></span>
        <span class="custom-control-description">{$lang['courses']['course-catalog']['misc']['group-by-level-label']}</span>
      </label>
    </div>
    <div class="form-group form-inline">
      <label for="track" class="p-2">{$lang['courses']['course-catalog']['misc']['choose-track-label']}: </label>
      $form_track_select
      <label class="custom-control custom-checkbox p-2">
        <input id="group_by_track_cbx" name="group_by_track" value="1" $group_by_track_checked
          type="checkbox" class="custom-control-input" />
        <span class="custom-control-indicator"></span>
        <span class="custom-control-description">{$lang['courses']['course-catalog']['misc']['group-by-track-label']}</span>
      </label>
    </div>
    <div class="form-row">
      <button type="submit" class="btn btn-vlc">
        {$lang['courses']['course-catalog']['form-fields']['refresh-button']}
      </button>
    </div>
  </form>
COURSE_FORM;

$course_details_query = <<< END_QUERY
  SELECT c.course_subject_id, c.course_level_id, c.description, ct.course_track_id, t.ceu
  FROM course_subjects AS c LEFT JOIN courses_tracks AS ct ON c.course_subject_id = ct.course_subject_id, course_types AS t
  WHERE c.course_type_id = t.course_type_id
  AND c.is_restricted = 0
  AND c.is_active = 1
  AND c.language_id = {$lang['common']['misc']['current-language-id']}
  $where_clause
  ORDER BY c.description
END_QUERY;
$result = mysql_query($course_details_query, $site_info['db_conn']);
$course_list = '';
$courses = $course_details_array = array();
while ($record = mysql_fetch_array($result))
{
  if ($group_by_track)
  {
    if (isset($record['course_track_id'])) $track_group_id = $record['course_track_id'];
    else $track_group_id = 'default';
  }
  else $track_group_id = 0;
  if ($group_by_level) $level_group_id = $record['course_level_id'];
  else $level_group_id = 0;
  $courses['tracks'][$track_group_id]['levels'][$level_group_id]['courses'][$record['course_subject_id']] = $record;
}
/* levels */
if ($level_id > 0) $course_levels = array($level_id => $lang['database']['course-levels'][$level_id]);
else $course_levels = $lang['database']['course-levels'];
$course_levels[0] = '';
/* tracks */
if ($track_id > 0) $course_tracks = array($track_id => $lang['database']['course-tracks'][$track_id]);
else $course_tracks = $lang['database']['course-tracks'];
$course_tracks[0] = '';
if (isset($courses['tracks']['default'])) $course_tracks['default'] = $lang['courses']['course-catalog']['misc']['default-track-label'];
/* course outline */
// $course_outline = $group_by_track || $group_by_level ? '<ul>' : '<ul class="list-group">';
$course_outline = '<ul class="catalog-list">';
$track_counter = 0;
$level_counter = 0;

/* for collapsible section headers */
function get_ul_head($item, $type, $list_group) {
  global $track_counter;
  global $level_counter;
  if ($type == 'track') {
    $track_counter++;
    $count = $track_counter;
  } else {
    $level_counter++;
    $count = $level_counter;
  }
  $group = $list_group ? ' list-group' : '';
  $id = $type.'_'.$count;
  $item = <<< ITEM_END
    <li class="{$type}-head li-head">
      <h4><a href="#{$id}" class="toggle-link" data-toggle="collapse"><i class="fa fa-arrow-up"></i> {$item}</a></h4>
      <ul class="collapse show" id="{$id}">
ITEM_END;
  return $item;
}

foreach ($course_tracks as $track_id => $track)
{
  if (isset($courses['tracks'][$track_id]))
  {
    if ($group_by_track) $course_outline .= get_ul_head($track, 'track','');
    foreach ($course_levels as $level_id => $level)
    {
      if (isset($courses['tracks'][$track_id]['levels'][$level_id]['courses']))
      {
        if ($group_by_level) $course_outline .= get_ul_head($level, 'level','');
        foreach ($courses['tracks'][$track_id]['levels'][$level_id]['courses'] as $course_subject_id => $course)
        {
          $course_outline .= '<li class="course-item">'.vlc_internal_link($course['description'], 'courses/course_details.php?course='.$course_subject_id).'</li>';
        }
        if ($group_by_level) $course_outline .= '</ul></li>';
      }
    }
    if ($group_by_track) $course_outline .= '</ul></li>';
  }
}
$course_outline .= '</ul>';
?>
<!-- begin page content -->
<div class="container">
  <h1><?php print $lang['courses']['course-catalog']['page-title'] ?></h1>
  <div class="return-link">
    <i class="fa fa-arrow-left"></i> <?php print vlc_internal_link($lang['courses']['shared']['return-link'], 'courses/') ?>
  </div>
  <div>
    <p>
      <?php print $lang['courses']['course-catalog']['content']['intro'] ?>
    </p>
  </div>
  <div class="card">
    <div class="card-header">
      <h3><?php print $lang['courses']['course-catalog']['heading']['search'] ?></h3>
    </div> 
    <div class="card-body">
      <div class="alert alert-info" role="alert">
        <h5><?php print $lang['courses']['course-catalog']['heading']['course-levels'] ?></h5>
        <?php print $lang['courses']['course-catalog']['content']['course-levels'] ?>
      </div>      
      <?php print $course_form ?>
    </div>
  </div>
  <div class="catalog">
    <?php print $course_outline ?>
  </div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
