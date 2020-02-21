<?php
$page_info['section'] = 'profile';
$lang = vlc_get_language();
$error_message = '';

if (isset($_POST['username']) and strlen(trim($_POST['username']))) $username = trim($_POST['username']);
//else $error_message .= '<li>'.$lang['profile']['index']['status']['username-required'].'</li>';
if (isset($_POST['password']) and strlen(trim($_POST['password']))) $password = trim($_POST['password']);
//else $error_message .= '<li>'.$lang['profile']['index']['status']['password-required'].'</li>';

/* exit page if errors have occurred */
if (strlen($error_message)) vlc_exit_page($error_message, 'login-error', 'profile/');
/* username cookie */
setcookie('vlc_username', $username, time()+2592000, '/');
$username = addslashes($username);
/* the "binary" function in the where clause makes the comparison case-sensitive, so "username" is not equal to "USERNAME" */
$login_query = <<< END_QUERY
    SELECT u.user_id, u.username, u.password,
      UNIX_TIMESTAMP() AS current_unix_timestamp,
      UNIX_TIMESTAMP(u.active_start) AS active_start,
      UNIX_TIMESTAMP(u.active_end) AS active_end,
      IF(LENGTH(TRIM(IFNULL(u.nickname, ''))) > 0, u.nickname, u.first_name) AS name,
      CONCAT(u.first_name, ' ', u.last_name) AS full_name,
		u.first_name,
		u.last_name,
		IFNULL(i.primary_phone, '') AS phone,
      IFNULL(i.primary_email, '') AS email
    FROM users u, user_info i
    WHERE u.user_id = i.user_id
    AND BINARY u.username = '$username'
END_QUERY;

$result = mysql_query($login_query, $site_info['db_conn']);
$num_rows = mysql_num_rows($result);
if ($num_rows == 1)
{
  $login_info = mysql_fetch_array($result);
  if ($password == $login_info['password'])
  {
    if ($login_info['current_unix_timestamp'] > $login_info['active_start'] and $login_info['current_unix_timestamp'] < $login_info['active_end'])
    {
      /* retrieve user roles from database (users can have more than one user role) */
      $user_role_query = <<< END_QUERY
        SELECT user_role_id
        FROM users_roles
        WHERE user_id = {$login_info['user_id']}
END_QUERY;
      $result = mysql_query($user_role_query, $site_info['db_conn']);
      while ($record = mysql_fetch_array($result)) $user_role_array[] = $record['user_role_id'];
      /* retrieve list of courses user is registered for */
      $course_id_query = <<< END_QUERY
        SELECT c.course_id, uc.user_role_id, UNIX_TIMESTAMP() AS current_unix_timestamp,
          UNIX_TIMESTAMP(c.facilitator_start) AS facilitator_start,
          UNIX_TIMESTAMP(c.facilitator_end + INTERVAL 1 DAY) AS facilitator_end,
          UNIX_TIMESTAMP(c.student_start) AS student_start,
          UNIX_TIMESTAMP(c.student_end + INTERVAL 1 DAY) AS student_end
        FROM courses AS c, users_courses AS uc
        WHERE c.course_id = uc.course_id
        AND uc.user_id = {$login_info['user_id']}
        AND uc.course_status_id NOT IN (1, 4, 5)
        AND c.is_active = 1
        ORDER BY c.course_id
END_QUERY;
      $result = mysql_query($course_id_query, $site_info['db_conn']);
      $course_id_array = array();
      while ($course_info = mysql_fetch_array($result))
      {
        switch ($course_info['user_role_id'])
        {
          /* facilitator */
          case 4:
            if ($course_info['current_unix_timestamp'] > $course_info['facilitator_start'] and $course_info['current_unix_timestamp'] < $course_info['facilitator_end']) $course_id_array[$course_info['course_id']] = $course_info['user_role_id'];
            break;
          /* student */
          case 5:
          /* guest */
          case 7:
            if ($course_info['current_unix_timestamp'] > $course_info['student_start'] and $course_info['current_unix_timestamp'] < $course_info['student_end']) $course_id_array[$course_info['course_id']] = $course_info['user_role_id'];
            break;
        }
      }
      /* set PHPSESSID cookie to expire when browser is closed */
      setcookie('PHPSESSID', session_id(), 0, '/');
      /* set user info session variables */
      $_SESSION['user_info']['logged_in'] = 1;
      $_SESSION['user_info']['user_id'] = $login_info['user_id'];
      $_SESSION['user_info']['user_roles'] = $user_role_array;
      $_SESSION['user_info']['username'] = $login_info['username'];
      $_SESSION['user_info']['email'] = $login_info['email'];
      $_SESSION['user_info']['name'] = $login_info['name'];
      $_SESSION['user_info']['first_name'] = $login_info['name'];
      $_SESSION['user_info']['full_name'] = $login_info['full_name'];
      $_SESSION['user_info']['last_name'] = $login_info['last_name'];
      $_SESSION['user_info']['courses'] = $course_id_array;
      $_SESSION['user_info']['phone'] = $login_info['phone'];
      /* record user's login time */
      $ip_address = $_SERVER['REMOTE_ADDR'];
      $php_session_id = session_id();
      /* get environment variables */
      ob_start();
      print_r($_SERVER);
      $env_vars = ob_get_contents();
      ob_end_clean();
      $env_vars = addslashes($env_vars);
      $user_access_query = <<< END_QUERY
        INSERT INTO user_access
        SET user_id = {$login_info['user_id']}, ip_address = '$ip_address', php_session_id = '$php_session_id', env_vars = '$env_vars'
END_QUERY;
      $result = mysql_query($user_access_query, $site_info['db_conn']);
      if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "user_access"');
      $_SESSION['user_info']['user_access_id'] = mysql_insert_id();
      /* logged in successfully */
      if (isset($_SESSION['continue_url']))
      {
        $redirect_to = $_SESSION['continue_url']; 
        //$_SESSION['continue_url'] = null; Mod by Bob 2019-01-19
      }
      else $redirect_to = 'profile/';
      vlc_redirect($redirect_to);
    }
    else vlc_exit_page('<li>'.$lang['profile']['index']['status']['profile-inactive'].'</li>', 'error', 'profile/');
  }
  else vlc_exit_page('<li>'.$lang['profile']['index']['status']['password-incorrect'].'</li>', 'error', 'profile/');
}
else vlc_exit_page('<li>'.$lang['profile']['index']['status']['username-incorrect'].'</li><li>'.$lang['profile']['index']['status']['case-sensitive'].'</li>', 'error', 'profile/');
?>

