<?php
if (isset($_GET['newsletter']) and is_numeric($_GET['newsletter'])) $newsletter_id = $_GET['newsletter'];
else vlc_redirect('misc/error.php?error=404');
$page_info['section'] = 'newsletter';
$page_info['page'] = 'newsletter';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get newsletter */
$newsletter_query = <<< END_QUERY
  SELECT r.title, r.content, d.author, DATE_FORMAT(active_start, '%b. %Y') AS date
  FROM resources AS r, resource_details AS d
  WHERE r.resource_id = d.resource_id
  AND r.resource_type_id = 57
  AND r.resource_id = $newsletter_id
  AND CURDATE() >= r.active_start
  AND CURDATE() <= r.active_end
END_QUERY;
$result = mysql_query($newsletter_query, $site_info['db_conn']);
if (mysql_num_rows($result) == 0) vlc_redirect('misc/error.php?error=404');
else $newsletter_details = mysql_fetch_array($result);
$output = '<h1 align="center">'.$newsletter_details['title'].'</h1>';
if (isset($newsletter_details['author']) and strlen($newsletter_details['author'])) $output .= '<p class="center"><b>'.$newsletter_details['author'].'</b></p>';
$output .= vlc_convert_code($newsletter_details['content']);
$output .= '<p><b>'.$lang['newsletter']['newsletter']['misc']['release-date'].':</b> '.$newsletter_details['date'].'</p>';
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

