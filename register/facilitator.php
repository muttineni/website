<?php
if (isset($_GET['facilitator']) and is_numeric($_GET['facilitator'])) $facilitator_id = $_GET['facilitator'];
else vlc_redirect('misc/error.php?error=404');
$page_info['section'] = 'register';
$page_info['page'] = 'facilitator-details';
$login_required = 1;
$user_info = vlc_get_user_info($login_required);
$lang = vlc_get_language();
$facilitator_query = <<< END_QUERY
  SELECT IFNULL(u.prefix, '') AS prefix, u.first_name, IFNULL(u.middle_name, '') AS middle_name, u.last_name, IFNULL(u.suffix, '') AS suffix, city, IFNULL(i.state_id, -1) AS state_id, i.country_id, IFNULL(i.biography, 'Biographical information not available.') AS biography, IFNULL(i.image, '') AS image
  FROM users AS u, user_info AS i, users_roles AS ur
  WHERE u.user_id = i.user_id
  AND u.user_id = $facilitator_id
  AND u.user_id = ur.user_id
  AND ur.user_role_id = 4
END_QUERY;
$result = mysql_query($facilitator_query, $site_info['db_conn']);
if (mysql_num_rows($result) > 0)
{
  $record = mysql_fetch_array($result);
  $facilitator_name = $record['prefix'].' '.$record['first_name'].' '.$record['middle_name'].' '.$record['last_name'].' '.$record['suffix'];
  $facilitator_name = trim($facilitator_name);
  $facilitator_location = $record['city'];
  if (isset($lang['database']['states'][$record['state_id']])) $facilitator_location .= ', '.$lang['database']['states'][$record['state_id']];
  if (isset($lang['database']['countries'][$record['country_id']])) $facilitator_location .= ', '.$lang['database']['countries'][$record['country_id']];
  $facilitator_bio = $record['biography'];
  $facilitator_picture = $record['image'];
  if (strlen(trim($facilitator_bio)) == 0) $facilitator_bio = $lang['register']['facilitator-details']['misc']['bio-not-available'];
  if (strlen(trim($facilitator_picture)) == 0) $facilitator_picture = '<img src="'.$site_info['images_url'].$lang['common']['misc']['current-language-code'].'/no_pic.gif" width="125" height="175" class="non_responsive" alt="'.$lang['register']['facilitator-details']['misc']['picture-not-available'].'" title="'.$lang['register']['facilitator-details']['misc']['picture-not-available'].'">';
  else $facilitator_picture = '<img src="'.$site_info['images_url'].'users/'.$facilitator_picture.'" width="125" height="175" class="non_responsive" alt="'.sprintf($lang['register']['facilitator-details']['misc']['picture-label'], $facilitator_name).'" title="'.sprintf($lang['register']['facilitator-details']['misc']['picture-label'], $facilitator_name).'">';
}
else vlc_redirect('misc/error.php?error=404');
/* build page content */
$page_content = '<div class="container"><h2>'.$facilitator_name.'</h2>';
$page_content .= '<p>'.$facilitator_picture.'</p>';
$page_content .= '<p><b>'.$lang['register']['facilitator-details']['misc']['location-label'].':</b> '.$facilitator_location.'</p>';
$page_content .= '<p>'.$facilitator_bio.'</p>';
$page_content .= '<p style="text-align: center;">'.$lang['register']['facilitator-details']['content']['print-link'].' - '.$lang['register']['facilitator-details']['content']['close-window-link'].'</p></div>';
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
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Language" content="<?php print $lang['common']['misc']['current-language-code'] ?>">
<meta name="Keywords" content="<?php print $lang['common']['misc']['meta-keywords'] ?>">
<meta name="Description" content="<?php print $lang['common']['misc']['meta-description'] ?>">
<meta name="Author" content="<?php print $lang['common']['misc']['meta-author'] ?>">
</head>
<body onload="self.focus();" onblur="self.close();">
<h1 align="center"><?php print $lang['register']['facilitator-details']['page-title'] ?></h1>
<!-- begin page content -->
<?php print $page_content ?>
<!-- end page content -->
</body>
</html>
