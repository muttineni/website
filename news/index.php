<?php
$page_info['section'] = 'news';
$page_info['page'] = 'index';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get articles */
$article_query = <<< END_QUERY
  SELECT resource_id, title, content, DATE_FORMAT(active_start, '%b. %Y') AS article_date
  FROM resources
  WHERE resource_type_id = 2
  AND CURDATE() >= active_start
  AND CURDATE() <= active_end
  AND language_id = {$lang['common']['misc']['current-language-id']}
  ORDER BY active_start DESC, active_end ASC, CREATED DESC
END_QUERY;
$result = mysql_query($article_query, $site_info['db_conn']);
$article_list = '';
/* Construct Article List */
while ($record = mysql_fetch_array($result)){
  $article_url = 'news/article.php?article='.$record['resource_id'];
  $article_preview = 'doot doot';
  $article_content = strip_tags(vlc_convert_code($record['content']));
  $article_preview = $article_content;

  /* Break content into array */
  $array = explode(' ', $article_content);
  if (!count($array)<=40) {
    /*  If article not less than 40 words  */
    array_splice($array, 40);
    $article_preview = implode(' ', $array).' . . . '.vlc_internal_link('more', $article_url);
  }
  $article_list .= '<li class="news-list__news-item"><div class="media-body">
    <h5>'.vlc_internal_link(strip_tags($record['title']), $article_url).'</h5>
    <h6 class="text-muted">'.$record['article_date'].'</h6>
    <p>'.$article_preview.'</p>
  </div></li>';
}

if (strlen($article_list) == 0) $article_list = '<li>'.$lang['news']['index']['content']['no-articles'].'</li>';
$output = '<h1>'.$lang['news']['index']['heading']['vlcff-news'].'</h1>';
$output .= '<ul class="list-unstyled news-list">'.$article_list.'<ul>';
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

