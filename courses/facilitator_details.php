<?php
if (isset($_GET['facilitator']) and is_numeric($_GET['facilitator'])) $facilitator_id = $_GET['facilitator'];
else vlc_redirect('misc/error.php?error=404');
$page_info['section'] = 'courses';
$page_info['page'] = 'facilitator-details';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
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
  if (strlen(trim($facilitator_bio)) == 0) $facilitator_bio = $lang['courses']['facilitator-details']['misc']['bio-not-available'];
  if (strlen(trim($facilitator_picture)) == 0) $facilitator_picture = '<img src="'.$site_info['images_url'].$lang['common']['misc']['current-language-code'].'/no_pic.gif" width="125" height="175" class="non_responsive float-sm-left profile-img" alt="'.$lang['courses']['facilitator-details']['misc']['picture-not-available'].'" title="'.$lang['courses']['facilitator-details']['misc']['picture-not-available'].'">';
  else $facilitator_picture = '<img src="'.$site_info['images_url'].'users/'.$facilitator_picture.'" width="125" height="175" class="non_responsive float-sm-left profile-img" alt="'.sprintf($lang['courses']['facilitator-details']['misc']['picture-label'], $facilitator_name).'" title="'.sprintf($lang['courses']['facilitator-details']['misc']['picture-label'], $facilitator_name).'">';
  if (isset($_SERVER['HTTP_REFERER'])) $previous_page = $_SERVER['HTTP_REFERER'];
  else $previous_page = 'javascript:history.back();';
  $facilitator_details = <<< END_DETAILS
    <h1>{$lang['courses']['facilitator-details']['heading']['facilitator-details']}</h1>
    <div class="return-link">
      <i class="fa fa-arrow-left"></i> <a href="$previous_page">{$lang['courses']['facilitator-details']['misc']['previous-page-return-link']}</a>
    </div>
    <h2>$facilitator_name</h2>
    <div class="facilitator-content my-3">
      $facilitator_picture
      <p><b>{$lang['courses']['facilitator-details']['misc']['location-label']}:</b> $facilitator_location</p>
      <p>$facilitator_bio</p>
    </div>
END_DETAILS;
}
else vlc_redirect('misc/error.php?error=404');
print $header;
?>
<!-- begin page content -->
<div class="container">
  <?php print $facilitator_details ?>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

