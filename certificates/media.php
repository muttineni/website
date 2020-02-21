<?php
$page_info['section'] = 'certificates';
$page_info['page'] = 'media';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
if ($user_info['logged_in'] == true)
{
  $register_link = $lang['certificates']['media']['content']['register-instructions'];
  /* see which certificate programs the student has applied for */
  $cert_prog_query = <<< END_QUERY
    SELECT cert_prog_id, cert_status_id
    FROM certs_users
    WHERE user_id = {$user_info['user_id']}
    AND cert_status_id != 4
END_QUERY;
  $result = mysql_query($cert_prog_query, $site_info['db_conn']);
  $cert_prog_array = array();
  while ($record = mysql_fetch_array($result)) $cert_prog_array[$record['cert_prog_id']] = $record;
  $register_link .= '<ol>';
  /* disable application link for now */
  if (1)
  {
    $register_link .= 'Registration will begin in October (2009)';
  }
  /* has not applied for level i */
  elseif (!isset($cert_prog_array[3]))
  {
    $register_link .= '<li><b>'.vlc_internal_link($lang['certificates']['media']['content']['register-level-i'], 'certificates/media_lvl_i_app.php').'</b></li>';
    $register_link .= '<li><b>'.$lang['certificates']['media']['content']['register-level-ii'].'</b><ul><li>'.$lang['certificates']['media']['content']['must-complete-level-i'].'</li></ul></li>';
    $register_link .= '<li><b>'.$lang['certificates']['media']['content']['register-level-iii'].'</b><ul><li>'.$lang['certificates']['media']['content']['must-complete-level-ii'].'</li></ul></li>';
    $_SESSION['media_cert_app_status'] = 1;
  }
  /* has applied for level i, but has not completed level i */
  elseif ($cert_prog_array[3]['cert_status_id'] != 3)
  {
    $register_link .= '<li><b>'.$lang['certificates']['media']['content']['register-level-i'].'</b><ul><li>'.$lang['certificates']['media']['content']['already-registered'].'</li></ul></li>';
    $register_link .= '<li><b>'.$lang['certificates']['media']['content']['register-level-ii'].'</b><ul><li>'.$lang['certificates']['media']['content']['must-complete-level-i'].'</li></ul></li>';
    $register_link .= '<li><b>'.$lang['certificates']['media']['content']['register-level-iii'].'</b><ul><li>'.$lang['certificates']['media']['content']['must-complete-level-ii'].'</li></ul></li>';
    $_SESSION['media_cert_app_status'] = 2;
  }
  /* has applied for level i, completed level i, but has not applied for level ii */
  elseif (!isset($cert_prog_array[4]))
  {
    $register_link .= '<li><b>'.$lang['certificates']['media']['content']['register-level-i'].'</b><ul><li>'.$lang['certificates']['media']['content']['already-registered'].'</li></ul></li>';
    $register_link .= '<li><b>'.vlc_internal_link($lang['certificates']['media']['content']['register-level-ii'], 'certificates/media_lvl_ii_app.php').'</b></li>';
    $register_link .= '<li><b>'.$lang['certificates']['media']['content']['register-level-iii'].'</b><ul><li>'.$lang['certificates']['media']['content']['must-complete-level-ii'].'</li></ul></li>';
    $_SESSION['media_cert_app_status'] = 3;
  }
  /* has applied for level i, completed level i, and applied for level ii, but not completed level ii */
  elseif ($cert_prog_array[4]['cert_status_id'] != 3)
  {
    $register_link .= '<li><b>'.$lang['certificates']['media']['content']['register-level-i'].'</b><ul><li>'.$lang['certificates']['media']['content']['already-registered'].'</li></ul></li>';
    $register_link .= '<li><b>'.$lang['certificates']['media']['content']['register-level-ii'].'</b><ul><li>'.$lang['certificates']['media']['content']['already-registered'].'</li></ul></li>';
    $register_link .= '<li><b>'.$lang['certificates']['media']['content']['register-level-iii'].'</b><ul><li>'.$lang['certificates']['media']['content']['must-complete-level-ii'].'</li></ul></li>';
    $_SESSION['media_cert_app_status'] = 4;
  }
  /* has applied for level i, completed level i, applied for level ii, completed level ii, but not applied for level iii */
  elseif (!isset($cert_prog_array[5]))
  {
    $register_link .= '<li><b>'.$lang['certificates']['media']['content']['register-level-i'].'</b><ul><li>'.$lang['certificates']['media']['content']['already-registered'].'</li></ul></li>';
    $register_link .= '<li><b>'.$lang['certificates']['media']['content']['register-level-ii'].'</b><ul><li>'.$lang['certificates']['media']['content']['already-registered'].'</li></ul></li>';
    $register_link .= '<li><b>'.vlc_internal_link($lang['certificates']['media']['content']['register-level-iii'], 'certificates/media_lvl_iii_app.php').'</b></li>';
    $_SESSION['media_cert_app_status'] = 5;
  }
  /* has applied for level i, completed level i, applied for level ii, completed level ii, and applied for level iii */
  else
  {
    $register_link .= '<li><b>'.$lang['certificates']['media']['content']['register-level-i'].'</b><ul><li>'.$lang['certificates']['media']['content']['already-registered'].'</li></ul></li>';
    $register_link .= '<li><b>'.$lang['certificates']['media']['content']['register-level-ii'].'</b><ul><li>'.$lang['certificates']['media']['content']['already-registered'].'</li></ul></li>';
    $register_link .= '<li><b>'.$lang['certificates']['media']['content']['register-level-iii'].'</b><ul><li>'.$lang['certificates']['media']['content']['already-registered'].'</li></ul></li>';
    $_SESSION['media_cert_app_status'] = 6;
  }
  $register_link .= '</ol>';
}
else
{
  $register_link = $lang['certificates']['media']['content']['create-profile'];
}
print $header;
?>
<!-- begin page content -->
<h1><?php print $lang['certificates']['media']['page-title'] ?></h1>
<?php print $lang['certificates']['media']['content']['notice'] ?>
<h2><?php print $lang['certificates']['media']['heading']['audience'] ?></h2>
<?php print $lang['certificates']['media']['content']['audience'] ?>
<h2><?php print $lang['certificates']['media']['heading']['requirements'] ?></h2>
<?php print $lang['certificates']['media']['content']['requirements'] ?>
<h2><?php print $lang['certificates']['media']['heading']['certificates'] ?></h2>
<?php print $lang['certificates']['media']['content']['certificates'] ?>
<h2><?php print $lang['certificates']['media']['heading']['register'] ?></h2>
<?php print $register_link ?>
<h1><?php print $lang['certificates']['common']['misc']['prereqs'] ?></h1>
<?php print $lang['certificates']['media']['content']['prereqs'] ?>
<h1><?php print $lang['certificates']['common']['misc']['level-i'] ?> (<?php print $lang['certificates']['common']['misc']['basic'] ?>)</h1>
<?php print $lang['certificates']['media']['content']['level-i'] ?>
<h1><?php print $lang['certificates']['common']['misc']['level-ii'] ?> (<?php print $lang['certificates']['common']['misc']['intermediate'] ?>)</h1>
<?php print $lang['certificates']['media']['content']['level-ii'] ?>
<h1><?php print $lang['certificates']['common']['misc']['level-iii'] ?> (<?php print $lang['certificates']['common']['misc']['advanced'] ?>)</h1>
<?php print $lang['certificates']['media']['content']['level-iii'] ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
