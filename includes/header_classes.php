<?php
$css_file = $site_info['styles_url'].$course_info['style_dir'].'/classes.css';
$date = date('n|j|Y');
$date_array = explode('|', $date);
$date = sprintf($lang['common']['misc']['medium-date-format'], $lang['common']['months']['full'][$date_array[0]], $date_array[1], $date_array[2]);
/* build top menu */
$top_links_array = array();
$top_links_array[] = array('home', $lang['classes']['navigation']['home'], 'classes/');
$top_links_array[] = array('facilitator', $lang['classes']['navigation']['facilitator'], 'classes/facilitator.php');
$top_links_array[] = array('students', $lang['classes']['navigation']['students'], 'classes/students.php');
if (isset($course_info['resources'][46])) $top_links_array[] = array('meeting-place', $lang['classes']['navigation']['meeting-place'], 'classes/meet.php');
$top_links_array[] = array('mail', $lang['classes']['navigation']['mail'], 'classes/mail.php');
$top_menu = '';
foreach ($top_links_array as $link)
{
  if ($page_info['sub_section'] == $link[0])
  {
    $page_info['heading'] = $page_info['title'] = $link[1];
    $css_class = 'top-menu-selected';
  }
  else
  {
    $css_class = 'top-menu';
  }
  $top_menu .= '<span class="'.$css_class.'" onmouseover="switch_style(this, \'top-menu-selected\', \'top-menu-selected\')" onmouseout="restore_style(this)" onclick="go_to_href(this)">'.vlc_internal_link($link[1], $link[2].'?course='.$page_info['course_id'], $css_class).'</span>&nbsp;';
}
$top_menu .= '<span class="top-menu" onmouseover="switch_style(this, \'top-menu-selected\', \'top-menu-selected\')" onmouseout="restore_style(this)" onclick="go_to_href(this)">'.vlc_internal_link($lang['classes']['navigation']['log-out'], 'profile/logout_action.php', 'top-menu').'</span>';
/* build left menu */
$css_class = 'left-menu';
if (isset($page_info['session_id']))
{
  $page_info['title'] = $lang['classes']['common']['misc']['session-home'];
  $left_menu_query = <<< END_QUERY
    SELECT r.resource_id, r.resource_type_id, r.session_id, IFNULL(r.title, '') AS resource_title
    FROM resources AS r
    WHERE r.resource_type_id IN (4, 22, 23, 24, 25, 26, 35, 36, 40, 44, 48, 49, 50, 51, 52, 58)
    AND r.course_subject_id = {$course_info['course_subject_id']}
    AND r.session_id = {$page_info['session_id']}
    ORDER BY r.session_id, r.display_order
END_QUERY;
  $result = mysql_query($left_menu_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result))
  {
    $record['resource_type'] = $lang['database']['resource-types'][$record['resource_type_id']];
    $session_resources[$record['session_id']][] = $record;
  }
}
/* define title and heading for resource page */
elseif ($page_info['sub_section'] == 'resource' and isset($page_info['resource_id']))
{
  /* if this is the resource page, get page title */
  $resource_info_query = <<< END_QUERY
    SELECT resource_type_id, IFNULL(r.title, '') AS resource_title
    FROM resources AS r
    WHERE r.resource_id = {$page_info['resource_id']}
END_QUERY;
  $result = mysql_query($resource_info_query, $site_info['db_conn']);
  $resource_info = mysql_fetch_array($result);
  $resource_info['resource_type'] = $lang['database']['resource-types'][$resource_info['resource_type_id']];
  $page_info['title'] = $resource_info['resource_type'];
  if (strlen($resource_info['resource_title']) > 0) $page_info['heading'] = $resource_info['resource_title'];
  else $page_info['heading'] = $resource_info['resource_type'];
}
$image_title = $course_info['title'].' '.$lang['common']['misc']['logo'];
$left_menu = '<img src="'.$site_info['files_url'].$course_info['logo'].'" width="200" height="200" border="0" alt="'.$image_title.'" title="'.$image_title.'"><br>';
$course_related_links = '';
foreach ($course_info['resources'] as $resource)
{
  $resource_id = $resource['resource_id'];
  $resource_type_id = $resource['resource_type_id'];
  $resource_type = $lang['database']['resource-types'][$resource_type_id];
  $resource_url = 'classes/resource.php?course='.$page_info['course_id'].'&resource='.$resource_id;
  if (isset($page_info['resource_id']) and $page_info['resource_id'] == $resource_id)
  {
    $css_class = 'left-menu-selected';
    $on_mouse_over = '';
    $page_info['heading'] = $page_info['title'] = $resource_type;
  }
  else
  {
    $css_class = 'left-menu';
    $on_mouse_over = ' onmouseover="switch_style(this, \'left-menu-hover\', \'left-menu-hover\')" onmouseout="restore_style(this)" onclick="go_to_href(this)"';
  }
  $menu_link = '<div class="'.$css_class.'"'.$on_mouse_over.'>'.vlc_internal_link($resource_type, $resource_url, $css_class).'</div>';
  if ($resource_type_id == 6) $left_menu .= $menu_link;
  if ($resource_type_id == 37) $course_related_links = $menu_link;
}
$link_trail_session = '';
foreach ($course_info['sessions'] as $session)
{
  $session_id = $session['session_id'];
  $session_num = sprintf($lang['classes']['common']['misc']['session'], $session['display_order']);
  $session_title = $session['session_title'];
  /* session start date */
  $start_date = date('n|j', $session['start_date']);
  $start_date_array = explode('|', $start_date);
  $session_start = sprintf($lang['common']['misc']['shorter-date-format'], $lang['common']['months']['abbr'][$start_date_array[0]], $start_date_array[1]);
  /* session end date */
  $end_date = date('n|j', $session['end_date']);
  $end_date_array = explode('|', $end_date);
  $session_end = sprintf($lang['common']['misc']['shorter-date-format'], $lang['common']['months']['abbr'][$end_date_array[0]], $end_date_array[1]);
  $session_url = 'classes/session.php?course='.$page_info['course_id'].'&session='.$session_id;
  if (isset($page_info['session_id']) and $page_info['session_id'] == $session_id)
  {
    $css_class = 'left-menu-selected';
    $on_mouse_over = '';
    $page_info['heading'] = $session_num.': '.$session_title.'<br>('.$session_start.' - '.$session_end.')';
    $link_trail_session = ' &gt; '.vlc_internal_link($session_num, $session_url, 'link-trail');
  }
  else
  {
    $css_class = 'left-menu';
    $on_mouse_over = ' onmouseover="switch_style(this, \'left-menu-hover\', \'left-menu-hover\')" onmouseout="restore_style(this)" onclick="go_to_href(this)"';
  }
  $left_menu .= '<div class="'.$css_class.'" title="'.$session_title.'"'.$on_mouse_over.'>'.vlc_internal_link($session_num, $session_url, $css_class, $session_title);
  if (isset($session_resources[$session_id]))
  {
    for ($i = 0; $i < count($session_resources[$session_id]); $i++)
    {
      $resource_id = $session_resources[$session_id][$i]['resource_id'];
      $resource_type_id = $session_resources[$session_id][$i]['resource_type_id'];
      $resource_type = $session_resources[$session_id][$i]['resource_type'];
      $resource_title = $session_resources[$session_id][$i]['resource_title'];
      if (strlen($resource_title) > 20) $resource_title_abbrev = substr($resource_title, 0, 20).'...';
      else $resource_title_abbrev = $resource_title;
      $resource_url = 'classes/resource.php?course='.$page_info['course_id'].'&session='.$session_id.'&resource='.$resource_id;
      if (isset($page_info['resource_id']) and $page_info['resource_id'] == $resource_id)
      {
        $css_class = 'left-sub-menu-selected';
        $page_info['title'] = $resource_type;
        if (strlen($resource_title) > 0) $page_info['heading'] = $resource_title;
        else $page_info['heading'] = $resource_type;
      }
      else $css_class = 'left-sub-menu';
      if (in_array($resource_type_id, array(40, 44, 48, 49, 50, 51, 52))) $left_menu .= '<div class="'.$css_class.'" title="'.$resource_type.'" onmouseover="switch_style(this, \'left-sub-menu-selected\', \'left-sub-menu-selected\')" onmouseout="restore_style(this)" onclick="go_to_href(this)">&nbsp;&raquo;&nbsp;'.vlc_internal_link($resource_type, $resource_url, $css_class, $resource_type).'</div>';
      else $left_menu .= '<div class="'.$css_class.'" title="'.$resource_type.': '.$resource_title.'" onmouseover="switch_style(this, \'left-sub-menu-selected\', \'left-sub-menu-selected\')" onmouseout="restore_style(this)" onclick="go_to_href(this)">&nbsp;&raquo;&nbsp;'.vlc_internal_link($resource_title_abbrev, $resource_url, $css_class, $resource_type.': '.$resource_title).'</div>';
    }
  }
  $left_menu .= '</div>';
}
/* add extra links to left menu */
$left_menu .= '<div class="left-menu" onmouseover="switch_style(this, \'left-menu-hover\', \'left-menu-hover\')" onmouseout="restore_style(this)" onclick="go_to_href(this)">'.vlc_external_link($lang['classes']['navigation']['bible'], $lang['classes']['navigation']['bible-url'], 'left-menu').'</div>';
$left_menu .= '<div class="left-menu" onmouseover="switch_style(this, \'left-menu-hover\', \'left-menu-hover\')" onmouseout="restore_style(this)" onclick="go_to_href(this)">'.vlc_external_link($lang['classes']['navigation']['catechism'], $lang['classes']['navigation']['catechism-url'], 'left-menu').'</div>';
$left_menu .= '<div class="left-menu" onmouseover="switch_style(this, \'left-menu-hover\', \'left-menu-hover\')" onmouseout="restore_style(this)" onclick="go_to_href(this)">'.vlc_external_link($lang['classes']['navigation']['catechism-adult'], $lang['classes']['navigation']['catechism-adult-url'], 'left-menu').'</div>';
$left_menu .= '<div class="left-menu" onmouseover="switch_style(this, \'left-menu-hover\', \'left-menu-hover\')" onmouseout="restore_style(this)" onclick="go_to_href(this)">'.vlc_external_link($lang['classes']['navigation']['catechism-adult-2'], $lang['classes']['navigation']['catechism-adult-url-2'], 'left-menu').'</div>';
$left_menu .= $course_related_links;
/* build link trail */
$link_trail = vlc_internal_link($lang['common']['misc']['vlcff'].' @ UD', 'profile/', 'link-trail');
$link_trail .= ' &gt; '.vlc_internal_link($course_info['title'], 'classes/?course='.$page_info['course_id'], 'link-trail');
$link_trail .= $link_trail_session;
$link_trail .= ' &gt; '.$page_info['title'];
/* strip html tags from link trail to create page title */
$page_info['title'] = strip_tags($link_trail);
/* define print url for printer-friendly link */
$print_url = substr(str_replace('index.php', '', $_SERVER['PHP_SELF']), strlen($site_info['home_url']));
$url_array = parse_url($_SERVER['REQUEST_URI']);
if (isset($url_array['query'])) $print_url = $print_url.'?'.$url_array['query'].'&print=1';
else $print_url = $print_url.'?print=1';
$print_link = vlc_internal_link($lang['common']['misc']['printer-friendly-link'], $print_url, 'print-link');
$header = <<< END_HEADER
<!--
  Virtual Learning Community for Faith Formation (VLCFF)
  Institute for Pastoral Initiatives (IPI)
  University of Dayton
  vlcff@udayton.edu
  http://vlcff.udayton.edu/
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang['common']['misc']['current-language-code']}" lang="{$lang['common']['misc']['current-language-code']}">
<head>
<title>{$page_info['title']}</title>
<link rel="stylesheet" type="text/css" href="$css_file">
<link rel="icon" href="{$site_info['images_url']}favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="{$site_info['images_url']}favicon.ico" type="image/x-icon">
<script type="text/javascript" src="{$site_info['js_url']}{$lang['common']['misc']['current-language-code']}.js"></script>
<script type="text/javascript" src="{$site_info['js_url']}global.js"></script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="{$lang['common']['misc']['current-language-code']}">
</head>
<body onload="get_default_styles()">
<table class="main">
<tr><td colspan="3" class="course-title"><span class="course-title">{$course_info['title']}</span></td></tr>
<tr><td colspan="3" class="date-time"><span class="date-time">$date</span></td></tr>
<tr><td colspan="3" class="top-menu">$top_menu</td></tr>
<tr>
  <td colspan="3" class="link-trail-print-link">
    <table class="main">
    <tr>
      <td class="link-trail"><span class="link-trail">$link_trail</span></td>
      <td class="print-link"><span class="print-link">$print_link</span></td>
    </tr>
    </table>
  </td>
</tr>
<tr>
  <td class="left-menu">
$left_menu
  </td>
  <td class="vertical-bar">&nbsp;</td>
  <td class="main-content">
<h1>{$page_info['heading']}</h1>
<hr width="75%">
$status_message
<!-- end header -->
END_HEADER;
return $header;
?>

