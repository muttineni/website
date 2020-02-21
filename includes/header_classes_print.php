<?php
if (isset($_SERVER['HTTP_REFERER'])) $return_link = $_SERVER['HTTP_REFERER'];
else $return_link = 'javascript:history.go(-1);';
$return_link = sprintf($lang['common']['misc']['return-link'], $return_link);
$print_link = sprintf($lang['common']['misc']['print-link'], 'javascript:window.print();');
if (isset($page_info['session_id'])) $session_num = ' &gt; '.sprintf($lang['classes']['common']['misc']['session'], $course_info['sessions'][$page_info['session_id']]['display_order']);
else $session_num = '';
/* define page title based on sub-section variable */
switch ($page_info['sub_section'])
{
  case 'home':
    $page_info['title'] = $course_info['title'].' &gt; '.$lang['classes']['navigation']['home'];
    break;
  case 'facilitator':
    $page_info['title'] = $course_info['title'].' &gt; '.$lang['classes']['navigation']['facilitator'];
    break;
  case 'students':
    $page_info['title'] = $course_info['title'].' &gt; '.$lang['classes']['navigation']['students'];
    break;
  case 'meeting-place':
    $page_info['title'] = $course_info['title'].' &gt; '.$lang['classes']['navigation']['meeting-place'];
    break;
  case 'mail':
    $page_info['title'] = $course_info['title'].' &gt; '.$lang['classes']['navigation']['mail'];
    break;
  case 'session':
    $page_info['title'] = $course_info['title'].$session_num.' &gt; '.$lang['classes']['common']['misc']['session-home'];
    break;
  case 'resource':
    $resource_info_query = <<< END_QUERY
      SELECT r.resource_type_id, IFNULL(r.title, '') AS resource_title
      FROM resources AS r
      WHERE r.resource_id = {$page_info['resource_id']}
END_QUERY;
    $result = mysql_query($resource_info_query, $site_info['db_conn']);
    $resource_info = mysql_fetch_array($result);
    $page_info['title'] = $course_info['title'];
    $page_info['title'] .= $session_num;
    $page_info['title'] .= ' &gt; '.$lang['database']['resource-types'][$resource_info['resource_type_id']];
    if (strlen($resource_info['resource_title']) > 0) $page_info['title'] .= ' &gt; '.$resource_info['resource_title'];
    break;
}
$page_title = $lang['common']['misc']['vlcff'].' @ UD &gt; '.$page_info['title'].' ('.$lang['common']['misc']['printer-friendly-link'].')';
$header = <<< END_HEADER
<!--
  Virtual Learning Community for Faith Formation (VLCFF)
  Institute for Pastoral Initiatives (IPI)
  University of Dayton
  vlcff@udayton.edu
  http://vlc.udayton.edu/
-->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang['common']['misc']['current-language-code']}" lang="{$lang['common']['misc']['current-language-code']}">
<head>
<title>$page_title</title>
<link rel="icon" href="{$site_info['images_url']}favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="{$site_info['images_url']}favicon.ico" type="image/x-icon">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Language" content="{$lang['common']['misc']['current-language-code']}">
</head>
<body>
<h2 align="center">$page_title</h2>
<p style="font-size: x-small;">
  $print_link<br>
  $return_link
</p>
<hr width="75%">
<!-- end header -->
END_HEADER;
return $header;
?>
