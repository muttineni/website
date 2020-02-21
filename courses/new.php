<?php
$page_info['section'] = 'courses';
$page_info['page'] = 'new-courses';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get new course entries */
$new_course_query = <<< END_QUERY
  SELECT content
  FROM resources
  WHERE resource_type_id = 56
  AND CURDATE() >= active_start
  AND CURDATE() <= active_end
  AND language_id = {$lang['common']['misc']['current-language-id']}
  ORDER BY active_start DESC, active_end ASC, CREATED DESC
  LIMIT 1
END_QUERY;
$result = mysql_query($new_course_query, $site_info['db_conn']);
$output = '<h2>'.$lang['courses']['new-courses']['heading']['new-courses'].'</h2>';
if (mysql_num_rows($result))
{
  $record = mysql_fetch_array($result);
  $output .= vlc_convert_code($record['content']);
}

else $output .= '<p>'.$lang['courses']['new-courses']['content']['no-new-courses'].'</p>';
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
