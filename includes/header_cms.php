<?php
$date = date("F j, Y");
$home_link = vlc_internal_link('VLCFF Content Management System', 'cms/');
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
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang['common']['misc']['current-language-code']}" lang="{$lang['common']['misc']['current-language-code']}"><head>
<title>VLCFF @ UD &gt; CMS &gt; {$lang[$page_info['section']][$page_info['page']]['page-title']}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="Content-Language" content="{$lang['common']['misc']['current-language-code']}">
<link rel="icon" href="{$site_info['images_url']}favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="{$site_info['images_url']}favicon.ico" type="image/x-icon">
<script type="text/javascript">
<!--
  var home_url = '{$site_info['home_url']}';
  var curr_lang = '{$lang['common']['misc']['current-language-code']}';
//-->
</script>
<script type="text/javascript" src="{$site_info['js_url']}{$lang['common']['misc']['current-language-code']}.js"></script>
<script type="text/javascript" src="{$site_info['js_url']}global.js"></script>
<link rel="stylesheet" type="text/css" href="{$site_info['css_url']}cms.css">
<!-- calendar - js/css -->
<script type="text/javascript" src="{$site_info['js_url']}calendar/calendar.js"></script>
<link rel="stylesheet" type="text/css" href="{$site_info['js_url']}calendar/calendar.css">
<!-- autocomplete - js/css -->
<script type="text/javascript" src="{$site_info['js_url']}yui/yahoo-dom-event.js"></script>
<script type="text/javascript" src="{$site_info['js_url']}yui/animation-min.js"></script>
<script type="text/javascript" src="{$site_info['js_url']}yui/connection-min.js"></script>
<script type="text/javascript" src="{$site_info['js_url']}yui/autocomplete-min.js"></script>
<link rel="stylesheet" type="text/css" href="{$site_info['js_url']}yui/autocomplete.css" />
</head>
<body>
<div style="text-align: right;">$date [<a href="help.php" target="_blank">Help</a>]</div>
<h1 class="title">$home_link</h1>
<h2 align="center">{$lang[$page_info['section']][$page_info['page']]['page-title']}</h2>
$status_message
<!-- end header -->
END_HEADER;
return $header;
?>
