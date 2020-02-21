<?php
$page_info['section'] = 'certificates';
$page_info['page'] = 'special_needs';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
if ($user_info['logged_in'] == true)
{
  $register_link = $lang['certificates']['special_needs']['content']['register-instructions'];
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
  /* has not applied for cert prog */
  if (!isset($cert_prog_array[10]))
  {
    $register_link .= '<li><b>'.vlc_internal_link($lang['certificates']['special_needs']['content']['register'], 'certificates/special_needs_app.php').'</b></li>';
    $_SESSION['special_needs_cert_app_status'] = 1;
  }
  /* has applied for cert prog */
  else
  {
    $register_link .= '<li><b>'.$lang['certificates']['special_needs']['content']['register'].'</b><ul><li>'.$lang['certificates']['special_needs']['content']['already-registered'].'</li></ul></li>';
    $_SESSION['special_needs_cert_app_status'] = 2;
  }
  $register_link .= '</ol>';
}
else
{
  $register_link = $lang['certificates']['special_needs']['content']['create-profile'];
}
print $header;
?>
<!-- begin page content -->
<div class="container">
<h1><?php print $lang['certificates']['special_needs']['page-title'] ?></h1>
<div class="return-link">
    <i class="fa fa-arrow-left"></i> <?php echo vlc_internal_link($lang['certificates']['shared']['return-link'], 'certificates/') ?>
  </div>
<h2><?php print $lang['certificates']['special_needs']['heading']['audience'] ?></h2>
<?php print $lang['certificates']['special_needs']['content']['audience'] ?>
<h2><?php print $lang['certificates']['special_needs']['heading']['requirements'] ?></h2>
<?php print $lang['certificates']['special_needs']['content']['requirements'] ?>
<h2><?php print $lang['certificates']['special_needs']['heading']['register'] ?></h2>
<?php print $register_link ?>
<h1><?php print $lang['certificates']['special_needs']['heading']['required-courses'] ?></h1>
<?php print $lang['certificates']['special_needs']['content']['required-courses'] ?>
<h1><?php print $lang['certificates']['special_needs']['heading']['completion'] ?></h1>
<?php print $lang['certificates']['special_needs']['content']['completion'] ?>
<h1><?php print $lang['certificates']['special_needs']['heading']['books'] ?></h1>
<?php print $lang['certificates']['special_needs']['content']['books'] ?>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
