<?php
$page_info['section'] = 'av';
$page_info['page'] = 'index';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get audio/video entries */
$av_query = <<< END_QUERY
  SELECT content, url
  FROM resources
  WHERE resource_type_id = 59
  AND language_id = {$lang['common']['misc']['current-language-id']}
  ORDER BY active_start DESC, active_end ASC, CREATED DESC
END_QUERY;
$result = mysql_query($av_query, $site_info['db_conn']);
$av_list = '';
while ($record = mysql_fetch_array($result))
{
  $audio_button = '';
  if (isset($record['url']))
  {
    $audio_file = $site_info['audio_url'].'files/'.$record['url'];
    $audio_button = vlc_embed_audio($audio_file, 35, 35, '&playingColor=006669&grinderColor=636363&rollOverColor=C9C984');
  }
  $av_list .= '<li>'.$audio_button.' '.vlc_convert_code($record['content']).'</li>';
}
if (strlen($av_list)) $av_list = '<ul>'.$av_list.'</ul>';
else $av_list = '<p>'.$lang['av']['index']['misc']['none'].'</p>';
$output = '<h2>'.$lang['av']['index']['heading']['multimedia'].'</h2>';
$output .= $lang['av']['index']['content']['intro'];
$output .= $av_list;
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

