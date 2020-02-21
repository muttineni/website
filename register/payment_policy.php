<?php
$page_info['section'] = 'register';
$page_info['page'] = 'payment-policy';
$login_required = 1;
$user_info = vlc_get_user_info($login_required);
$lang = vlc_get_language();
/* build page content */
$page_content = '<h2>'.$lang['register']['payment-policy']['heading']['payment-policy'].'</h2>';
$page_content .= $lang['register']['payment-policy']['content']['payment-policy'];
$page_content .= '<h2>'.$lang['register']['payment-policy']['heading']['refund-policy'].'</h2>';
$page_content .= '<p>'.$lang['register']['payment-policy']['content']['refund-policy'].'</p>';
$page_content .= '<p style="text-align: center;">'.$lang['register']['payment-policy']['content']['print-link'].' - '.$lang['register']['payment-policy']['content']['close-window-link'].'</p>';
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
<h1 align="center"><?php print $lang['register']['payment-policy']['page-title'] ?></h1>
<!-- begin page content -->
<?php print $page_content ?>
<!-- end page content -->
</body>
</html>
