<?php
$page_info['section'] = 'profile';
$login_required = 0;
$lang = vlc_get_language();
$user_info = vlc_get_user_info($login_required);
if (isset($user_info['user_access_id']) and is_numeric($user_info['user_access_id']))
{
  $logout_query = <<< END_QUERY
    UPDATE user_access
    SET UPDATED = NULL
    WHERE user_id = {$user_info['user_id']}
    AND user_access_id = {$user_info['user_access_id']}
    LIMIT 1
END_QUERY;
  $result = mysql_query($logout_query, $site_info['db_conn']);
}
$_SESSION = array();
vlc_exit_page($lang['profile']['index']['status']['logged-out'], 'success', 'profile/');
?>

