<?php
$page_info['section'] = 'certificates';
$page_info['page'] = 'scoring';
$login_required = 0;
$user_info = vlc_get_user_info($login_required);
$lang = vlc_get_language();
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
<link rel="stylesheet" type="text/css" href="<?php print $site_info['css_url'] ?>style.css">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Language" content="<?php print $lang['common']['misc']['current-language-code'] ?>">
<meta name="Keywords" content="<?php print $lang['common']['misc']['meta-keywords'] ?>">
<meta name="Description" content="<?php print $lang['common']['misc']['meta-description'] ?>">
<meta name="Author" content="<?php print $lang['common']['misc']['meta-author'] ?>">
</head>
<body onload="self.focus();">
<!-- begin page content -->
<h1><?php print $lang['certificates']['scoring']['page-title'] ?></h1>
<?php print $lang['certificates']['scoring']['content']['scoring'] ?>
<p style="text-align: center;"><?php print $lang['certificates']['scoring']['content']['print-link'] ?> - <?php print $lang['certificates']['scoring']['content']['close-window-link'] ?></p>
<!-- end page content -->
</body>
</html>
