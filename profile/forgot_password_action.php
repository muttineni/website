<?php
$page_info['section'] = 'profile';
$lang = vlc_get_language();
if (strlen(trim($_POST['username']))) $where_clause = "AND BINARY u.username = '".trim(addslashes($_POST['username']))."'";
elseif (strlen(trim($_POST['email']))) $where_clause = "AND i.primary_email = '".trim(addslashes($_POST['email']))."'";
else vlc_exit_page('<li>'.$lang['profile']['forgot-password']['status']['username-required'].'</li>', 'error', 'profile/forgot_password.php');
$password_query = <<< END_QUERY
    SELECT u.first_name, u.last_name, u.username, u.password, IFNULL(i.primary_email, '') AS email
    FROM users u, user_info i
    WHERE u.user_id = i.user_id
    $where_clause
END_QUERY;
$result = mysql_query($password_query, $site_info['db_conn']);
$num_rows = mysql_num_rows($result);
if ($num_rows == 1)
{
  $row = mysql_fetch_array($result);
  /* send reminder message to user from administrator */
  $from = 'From: "'.$lang['common']['misc']['vlcff-admin'].'" <'.$site_info['vlcff_email'].'>';
  $to = $row['email'];
  $subject = $lang['profile']['email']['forgot-password']['subject'];
  $message = sprintf($lang['profile']['email']['forgot-password']['message'], $row['first_name'], $row['username'], $row['password'], $site_info['vlcff_email'], $row['username'], $row['email']);
  mail($to, $subject, $message, $from);
  /* send additional message to administrator from user */
  $from = 'From: "'.$row['first_name'].' '.$row['last_name'].'" <'.$row['email'].'>';
  $to = $site_info['webmaster_email'];
  mail($to, $subject, $message, $from);
  vlc_exit_page($lang['profile']['forgot-password']['status']['password-sent'], 'success', 'profile/forgot_password.php');
}
elseif ($num_rows > 1) vlc_exit_page('<li>'.$lang['profile']['forgot-password']['status']['multiple-records'].'</li>', 'error', 'profile/forgot_password.php');
else vlc_exit_page('<li>'.$lang['profile']['forgot-password']['status']['username-incorrect'].'</li>', 'error', 'profile/forgot_password.php');
?>
