<?php
$page_info['section'] = 'partnership';
$page_info['page'] = 'partner-list';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get partners */
$partner_query = <<< END_QUERY
  SELECT partner_id, url
  FROM partners
  WHERE is_partner = 1
  ORDER BY description
END_QUERY;
$result = mysql_query($partner_query, $site_info['db_conn']);
$partner_array = array();
while ($record = mysql_fetch_array($result)) $partner_array[] = '<li class="list-group-item">'.vlc_external_link($lang['database']['partners'][$record['partner_id']], $record['url']).'</li>';
$partner_list = join("", $partner_array);
print $header;
?>
<!-- begin page content -->
<div class="container">
  <h1><?php print $lang['partnership']['partner-list']['heading']['partner-list'] ?></h1>
  <div class="return-link">
    <i class="fa fa-arrow-left"></i> <?php print vlc_internal_link($lang['partnership']['partner-list']['misc']['return-link'], 'partnership/') ?>
  </div>
  <div class="card list">
    <div class="card-header">
      <h3><?php print $lang['partnership']['partner-list']['content']['question'] ?></h3>
    </div>   
    <ul class="list-group list-group-flush">
      <?php print $partner_list ?>
    </ul>
  </div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
