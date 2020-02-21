<?php
$page_info['section'] = 'certificates';
$page_info['page'] = 'catechesis-sp';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
if ($user_info['logged_in'] == true)
{
  $register_link = $lang['certificates']['catechesis-sp']['content']['register-instructions'];
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
  /* has not applied for either cert prog */
  if (!isset($cert_prog_array[7]))
  {
    $register_link .= '<li><b>'.vlc_internal_link($lang['certificates']['catechesis-sp']['content']['register-level-i'], 'certificates/catechesis_sp_lvl_i_app.php').'</b></li>';
    $register_link .= '<li><b>'.$lang['certificates']['catechesis-sp']['content']['register-level-ii'].'</b><ul><li>'.$lang['certificates']['catechesis-sp']['content']['must-complete-level-i'].'</li></ul></li>';
    $_SESSION['cat_cert_app_status'] = 1;
  }
  /* has applied for level i, but has not completed level i
  elseif ($cert_prog_array[7]['cert_status_id'] != 3)
  {
    $register_link .= '<li><b>'.$lang['certificates']['catechesis-sp']['content']['register-level-i'].'</b><ul><li>'.$lang['certificates']['catechesis-sp']['content']['already-registered'].'</li></ul></li>';
    $register_link .= '<li><b>'.$lang['certificates']['catechesis-sp']['content']['register-level-ii'].'</b><ul><li>'.$lang['certificates']['catechesis-sp']['content']['must-complete-level-i'].'</li></ul></li>';
    $_SESSION['cat_cert_app_status'] = 2;
  } */
  /* has applied for level i, completed level i, but has not applied for level ii */
  elseif (!isset($cert_prog_array[8]))
  {
    $register_link .= '<li><b>'.$lang['certificates']['catechesis-sp']['content']['register-level-i'].'</b><ul><li>'.$lang['certificates']['catechesis-sp']['content']['already-registered'].'</li></ul></li>';
    $register_link .= '<li><b>'.vlc_internal_link($lang['certificates']['catechesis-sp']['content']['register-level-ii'], 'certificates/catechesis_sp_lvl_ii_app.php').'</b></li>';
    $_SESSION['cat_cert_app_status'] = 3;
  }
  /* has applied for level i, completed level i, and applied for level ii */
  else
  {
    $register_link .= '<li><b>'.$lang['certificates']['catechesis-sp']['content']['register-level-i'].'</b><ul><li>'.$lang['certificates']['catechesis-sp']['content']['already-registered'].'</li></ul></li>';
    $register_link .= '<li><b>'.$lang['certificates']['catechesis-sp']['content']['register-level-ii'].'</b><ul><li>'.$lang['certificates']['catechesis-sp']['content']['already-registered'].'</li></ul></li>';
    $_SESSION['cat_cert_app_status'] = 4;
  }
  $register_link .= '</ol>';
}
else
{
  $register_link = $lang['certificates']['catechesis-sp']['content']['create-profile'];
}
print $header;
?>
<!-- begin page content -->
<h1><?php print $lang['certificates']['catechesis-sp']['page-title'] ?></h1>
<h2><?php print $lang['certificates']['catechesis-sp']['heading']['audience'] ?></h2>
<?php print $lang['certificates']['catechesis-sp']['content']['audience'] ?>
<h2><?php print $lang['certificates']['catechesis-sp']['heading']['requirements'] ?></h2>
<?php print $lang['certificates']['catechesis-sp']['content']['requirements'] ?>
<h2><?php print $lang['certificates']['catechesis-sp']['heading']['register'] ?></h2>
<?php print $register_link ?>
<h1><?php print $lang['certificates']['common']['misc']['level-i'] ?></h1>
<?php print $lang['certificates']['catechesis-sp']['content']['level-i'] ?>
<h1><?php print $lang['certificates']['common']['misc']['level-ii'] ?></h1>
<?php print $lang['certificates']['catechesis-sp']['content']['level-ii'] ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
