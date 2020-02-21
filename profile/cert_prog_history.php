<?php
$page_info['section'] = 'profile';
$page_info['page'] = 'cert-prog-history';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<div class="container">
  <h1><?php print $lang['profile']['cert-prog-history']['heading']['cert-prog-history'] ?></h1>
  <div class="return-link">
    <i class="fa fa-arrow-left"></i>
    <?php echo vlc_internal_link($lang['profile']['shared']['return-link'], 'profile/') ?>
  </div>
<?php
/* initialize cert_prog_record variable */
$cert_prog_record = '';
/* get cert prog records */
$cert_prog_record_query = <<< END_QUERY
  SELECT c.cert_prog_id, c.description, cu.cert_user_id, cu.cert_status_id,
    UNIX_TIMESTAMP(cu.CREATED) AS app_date
  FROM cert_progs AS c, certs_users AS cu
  WHERE c.cert_prog_id = cu.cert_prog_id
  AND cu.user_id = {$user_info['user_id']}
  ORDER BY cu.CREATED DESC
END_QUERY;
$result = mysql_query($cert_prog_record_query, $site_info['db_conn']);
$i = 0;
while ($record = mysql_fetch_array($result))
{
  $cert_prog_status = $lang['database']['cert-status'][$record['cert_status_id']];
  /* get application date */
  $app_date = date('j|n|Y', $record['app_date']);
  $app_date_array = explode('|', $app_date);
  $app_date_array[1] = $lang['common']['months']['abbr'][$app_date_array[1]];
  array_unshift($app_date_array, $lang['common']['misc']['short-date-format']);
  $app_date = call_user_func_array('sprintf', $app_date_array);
  $cert_prog_record .= '<tr>';
  $cert_prog_details_link = vlc_internal_link($lang['profile']['cert-prog-history']['misc']['details'], 'profile/cert_prog_details.php?id='.$record['cert_user_id']);
  $cert_prog_record .= '<td>'.$record['description'].'</td><td>'.$app_date.'</td><td>'.$cert_prog_status.'</td><td>'.$cert_prog_details_link.'</td></tr>';
  $i++;
}
/* if the user has not applied for any certificate progrmas, show this message */
if (strlen($cert_prog_record) == 0) print '<p class="center">'.$lang['profile']['cert-prog-history']['content']['no-cert-progs'].'</p>';
else
{
  print '<div class="alert alert-info">'.$lang['profile']['cert-prog-history']['content']['cert-prog-status-info'].'</div>';
  print <<< END_TEXT
    <table class="table table-striped">
    <thead><tr><th>{$lang['profile']['cert-prog-history']['misc']['cert-prog']}</th><th>{$lang['profile']['cert-prog-history']['misc']['app-date']}</th><th>{$lang['profile']['cert-prog-history']['misc']['cert-prog-status']}</th><th>{$lang['profile']['cert-prog-history']['misc']['course-progress']}</th></tr></thead>
    $cert_prog_record
    </table>
END_TEXT;
}
?>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

