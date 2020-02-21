<?php
$page_info['section'] = 'profile';
$login_required = 1; /* user must be logged in to access this page */
$lang = vlc_get_language();
$user_info = vlc_get_user_info($login_required, 1);
$error_message = '';
$db_events_array = array();
if (strlen(trim($_POST['current_password']))) $current_password = trim($_POST['current_password']);
else {$current_password = ''; $error_message .= '<li>'.$lang['profile']['change-password']['status']['current-password-required'].'</li>';}
if (strlen(trim($_POST['new_password']))) $new_password = trim($_POST['new_password']);
else {$new_password = ''; $error_message .= '<li>'.$lang['profile']['change-password']['status']['new-password-required'].'</li>';}
if (strlen(trim($_POST['verify_password']))) $verify_password = trim($_POST['verify_password']);
else {$verify_password = ''; $error_message .= '<li>'.$lang['profile']['change-password']['status']['verify-password'].'</li>';}
if (strlen($new_password) != 6) $error_message .= '<li>'.$lang['profile']['change-password']['status']['new-password-six-chars'].'</li>';
if (!preg_match('/^[a-zA-Z0-9_]+$/', $new_password)) $error_message .= '<li>'.$lang['profile']['change-password']['status']['new-password-characters'].'</li>';
if ($new_password != $verify_password) $error_message .= '<li>'.$lang['profile']['change-password']['status']['new-password-must-match'].'</li>';
if (strlen($error_message)) vlc_exit_page($error_message, 'error', 'profile/change_password.php');
$current_password = addslashes($current_password);
$new_password = addslashes($new_password);
$get_password_query = <<< END_QUERY
  SELECT *
  FROM users
  WHERE user_id = {$user_info['user_id']}
  AND password = '$current_password'
END_QUERY;
$result = mysql_query($get_password_query, $site_info['db_conn']);
if (mysql_num_rows($result))
{
  $change_password_query = <<< END_QUERY
    UPDATE users
    SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']},
      password = '$new_password'
    WHERE user_id = {$user_info['user_id']}
    AND password = '$current_password'
    LIMIT 1
END_QUERY;
  $result = mysql_query($change_password_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "users"');
  $db_events_array[] = array(USERS_UPDATE, $user_info['user_id']);
  /* send reminder message to user from administrator */
  $from = 'From: "'.$lang['common']['misc']['vlcff-admin'].'" <'.$site_info['vlcff_email'].'>';
  $to = $user_info['email'];
  $subject = $lang['profile']['email']['change-password']['subject'];
  $message = sprintf($lang['profile']['email']['change-password']['message'], $user_info['name'], stripslashes($new_password), $site_info['vlcff_email'], $user_info['username'], $user_info['email']);
  mail($to, $subject, $message, $from);
  /* send additional message to administrator from user */
  $from = 'From: "'.$user_info['full_name'].'" <'.$user_info['email'].'>';
  $to = $site_info['webmaster_email'];
  mail($to, $subject, $message, $from);
  vlc_insert_events($db_events_array);
  vlc_exit_page($lang['profile']['change-password']['status']['password-change-success'], 'success', 'profile/change_password.php');
}
else vlc_exit_page('<li>'.$lang['profile']['change-password']['status']['incorrect-current-password'].'</li>', 'error', 'profile/change_password.php');
?>
