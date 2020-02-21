<?php
if (isset($_GET['article']) and is_numeric($_GET['article'])) $article_id = $_GET['article'];
else vlc_redirect('misc/error.php?error=404');
$page_info['section'] = 'news';
$page_info['page'] = 'article';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get article */
$article_query = <<< END_QUERY
  SELECT r.title, r.content, r.url, d.author, d.source, d.notes, DATE_FORMAT(active_start, '%b. %Y') AS article_date
  FROM resources AS r, resource_details AS d
  WHERE r.resource_id = d.resource_id
  AND r.resource_type_id = 2
  AND r.resource_id = $article_id
  AND CURDATE() >= r.active_start
  AND CURDATE() <= r.active_end
END_QUERY;
$result = mysql_query($article_query, $site_info['db_conn']);
if (mysql_num_rows($result) == 0) 
  vlc_redirect('misc/error.php?error=404');
else $article_details = mysql_fetch_array($result);
  $output = '<div class="news-article"><h1>'.$article_details['title'].'</h1>';

if (isset($article_details['author']) and strlen($article_details['author'])) 
  $output .= '<h3 class="text-muted">'.$article_details['author'].'</h3>';

$output .= vlc_convert_code($article_details['content']);

/* Create article footer */
$article_footer = '<dl class="row news-footer">';

if (isset($article_details['source']) and strlen($article_details['source'])) 
  $article_footer .= '<dt class="col-sm-3">'.$lang['news']['article']['misc']['source'].'</dt><dd class="col-sm-9">'.$article_details['source'].'</dd>';

if (isset($article_details['notes']) and strlen($article_details['notes'])) 
  $article_footer .= '<dt class="col-sm-3">'.$lang['news']['article']['misc']['notes'].'</dt><dd class="col-sm-9">'.$article_details['notes'].'</dd>';

if (isset($article_details['url']) and strlen($article_details['url'])) 
  $article_footer .= '<dt class="col-sm-3">'.$lang['news']['article']['misc']['url'].'</dt><dd class="col-sm-9">'.vlc_external_link($article_details['url'], $article_details['url']).'</dd>';

$article_footer .= '<dt class="col-sm-3">'.$lang['news']['article']['misc']['release-date'].'</dt><dd class="col-sm-9">'.$article_details['article_date'].'</dd></dl>';

$output .= $article_footer.'</div>';
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

