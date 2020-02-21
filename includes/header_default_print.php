<?php
if (isset($_SERVER['HTTP_REFERER'])) $return_link = $_SERVER['HTTP_REFERER'];
else $return_link = 'javascript:history.go(-1);';
$return_link = sprintf($lang['common']['misc']['return-link'], $return_link);
$print_link = sprintf($lang['common']['misc']['print-link'], 'javascript:window.print();');
$page_title = $lang['common']['misc']['vlcff'].' @ UD &gt; '.$lang[$page_info['section']][$page_info['page']]['page-title'].' ('.$lang['common']['misc']['printer-friendly-link'].')';
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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="{$lang['common']['misc']['current-language-code']}">
<meta name="Keywords" content="{$lang['common']['misc']['meta-keywords']}">
<meta name="Description" content="{$lang['common']['misc']['meta-description']}">
<meta name="Author" content="{$lang['common']['misc']['meta-author']}">
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

