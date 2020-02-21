<?php
$page_info['section'] = 'courses';
$page_info['page'] = 'facilitator-list';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;

$facilitator_query = <<< END_QUERY
  SELECT u.user_id, IFNULL(u.prefix, '') AS prefix, u.first_name, IFNULL(u.middle_name, '') AS middle_name, u.last_name, IFNULL(u.suffix, '') AS suffix
  FROM user_info AS i, users AS u, users_roles AS r
  WHERE i.user_id = u.user_id
  AND u.user_id = r.user_id
  AND r.user_role_id = 4
  ORDER BY u.last_name, u.first_name
END_QUERY;
$result = mysql_query($facilitator_query, $site_info['db_conn']);
$facilitator_list = '';
while($record = mysql_fetch_array($result))
{
  $facilitator_name = $record['prefix'].' '.$record['first_name'].' '.$record['middle_name'].' '.$record['last_name'].' '.$record['suffix'];
  $facilitator_name = trim($facilitator_name);
  $facilitator_list .= '<li class="list-group-item">'.vlc_internal_link($facilitator_name, 'courses/facilitator_details.php?facilitator='.$record['user_id']).'</li>';
}
?>
<!-- begin page content -->
<div class="container">
  <h1><?php print $lang['courses']['facilitator-list']['heading']['facilitators'] ?></h1>
  <div class="return-link">
    <i class="fa fa-arrow-left"></i> <?php print vlc_internal_link($lang['courses']['shared']['return-link'], 'courses/') ?>
  </div>
  <div class="card list">
    <div class="card-header">
      <h3><?php print $lang['courses']['facilitator-list']['content']['intro']?></h3>
    </div>   
    <ul class="list-group list-group-flush">
      <?php print $facilitator_list ?>
    </ul>
  </div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

