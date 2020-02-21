<?php
$page_info['section'] = 'profile';
$login_required = 0;
$lang = vlc_get_language();
$user_info = vlc_get_user_info($login_required, 1);
/* initially there are no error messages */
$error_message = '';
$db_events_array = array();
/* get form fields */
$form_fields = $_POST;
/* define variables to be used throughout the page */
$action = $form_fields['action'];
/* define required fields */
$required_fields = array('first_name', 'last_name', 'address_1', 'city', 'zip', 'primary_phone', 'primary_email', 'verify_primary_email');
if ($action == 'create') array_unshift($required_fields, 'username', 'password', 'verify_password');
/* trim white space from form fields */
foreach ($form_fields as $key => $value) $form_fields[$key] = trim($value);
/* if creating new profile, validate username and password */
if ($action == 'create')
{
  $username_with_slashes = addslashes($form_fields['username']);
  /* has username already been taken? */
  $username_query = <<< END_QUERY
    SELECT *
    FROM users
    WHERE BINARY username = '$username_with_slashes'
END_QUERY;
  $result = mysql_query($username_query, $site_info['db_conn']);
  if (mysql_num_rows($result)) $error_message .= '<li>'.$lang['profile']['profile']['status']['username-not-unique'].'</li>';
  /* is username 6 chars and only consists of letters, numbers, and underscore? */
  if (!preg_match('/^[a-zA-Z0-9_]{6}$/', $form_fields['username'])) $error_message .= '<li>'.$lang['profile']['profile']['status']['username-guidelines'].'</li>';
  /* is password 6 chars and only consists of letters, numbers, and underscore? */
  if (!preg_match('/^[a-zA-Z0-9_]{6}$/', $form_fields['password'])) $error_message .= '<li>'.$lang['profile']['profile']['status']['password-guidelines'].'</li>';
  /* do passwords match? */
  if ($form_fields['password'] != $form_fields['verify_password']) $error_message .= '<li>'.$lang['profile']['profile']['status']['password-must-match'].'</li>';
}
/* check to see if required fields were filled in */
foreach ($required_fields as $field)
{
  if (strlen($form_fields[$field]) == 0)
  {
    $field_name = str_replace('_', '-', $field);
    $error_message .= '<li>'.sprintf($lang['profile']['profile']['status']['generic-required'], $lang['profile']['profile']['form-fields'][$field_name]).'</li>';
  }
}
/* does e-mail address appear to be valid? we are only checking for the following format: "something at something dot something" (something@something.something - where "something" is anything but the "@" symbol); one attempt at a more restrictive format: ^[a-zA-Z0-9_]+(\.[a-zA-Z0-9_]+)*@([a-zA-Z0-9-]+\.)+[a-zA-Z0-9]{2,4}$ */
if (!preg_match('/^[^@]+@[^@]+\.[^@]+$/', $form_fields['primary_email'])) $error_message .= '<li>'.$lang['profile']['profile']['status']['email-invalid'].'</li>';

/* if errors have occurred, go back to form */
if (strlen($error_message))
{
  $_SESSION['form_fields'] = $form_fields;
  vlc_exit_page($error_message, 'error', 'profile/profile.php');
}
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = addslashes($value);
}
/* create date from parts */
$form_fields['birth_date'] = $form_fields['birth_year'].'-'.$form_fields['birth_month'].'-'.$form_fields['birth_day'];
/* if no errors have occurred, create or edit profile and send e-mail message */
if ($action == 'create')
{
  /* insert user */
  $insert_users_query = <<< END_INSERT
    INSERT INTO users
    SET active_start = CURDATE(), active_end = CURDATE() + INTERVAL 10 YEAR,
      username = '{$form_fields['username']}', password = '{$form_fields['password']}',
      prefix = NULLIF('{$form_fields['prefix']}', ''), first_name = '{$form_fields['first_name']}',
      middle_name = NULLIF('{$form_fields['middle_name']}', ''), last_name = '{$form_fields['last_name']}',
      suffix = NULLIF('{$form_fields['suffix']}', ''), nickname = NULLIF('{$form_fields['nickname']}', '')
END_INSERT;
  $result = mysql_query($insert_users_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "users"');
  $new_user_id = mysql_insert_id();
  $update_user_query = 'UPDATE users SET CREATEDBY = user_id WHERE user_id = '.$new_user_id;
  $result = mysql_query($update_user_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "users"');
  /* insert user info */
  $insert_user_info_query = <<< END_INSERT
    INSERT INTO user_info
    SET CREATEDBY = LAST_INSERT_ID(), user_id = LAST_INSERT_ID(),
      address_1 = '{$form_fields['address_1']}', address_2 = NULLIF('{$form_fields['address_2']}', ''),
      city = '{$form_fields['city']}', state_id = {$form_fields['state_id']}, zip = '{$form_fields['zip']}',
      country_id = {$form_fields['country_id']}, is_us_citizen = {$form_fields['is_us_citizen']},
      diocese_id = {$form_fields['diocese_id']},
      parish = NULLIF('{$form_fields['parish']}', ''),
      primary_phone = '{$form_fields['primary_phone']}', secondary_phone = NULLIF('{$form_fields['secondary_phone']}', ''),
      primary_email = '{$form_fields['primary_email']}', secondary_email = NULLIF('{$form_fields['secondary_email']}', ''),
      gender_type_id = {$form_fields['gender_type_id']}, birth_date = '{$form_fields['birth_date']}',
      race_type_id = {$form_fields['race_type_id']}, occupation_id = {$form_fields['occupation_id']},
      marital_status_id = {$form_fields['marital_status_id']}, religion_id = {$form_fields['religion_id']},
      biography = NULLIF('{$form_fields['biography']}', ''), title = NULLIF('{$form_fields['title']}', ''), url = NULLIF('{$form_fields['url']}', '')
END_INSERT;
  $result = mysql_query($insert_user_info_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "user_info"');
  /* insert user role */
  $insert_user_role_query = <<< END_INSERT
    INSERT INTO users_roles
    SET CREATEDBY = LAST_INSERT_ID(), user_id = LAST_INSERT_ID(), user_role_id = 5
END_INSERT;
  $result = mysql_query($insert_user_role_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "users_roles"');
  /* insert credit */
  $insert_credit_query = <<< END_INSERT
    INSERT INTO credits
    SET CREATEDBY = LAST_INSERT_ID(), customer_id = LAST_INSERT_ID(), customer_type_id = 1, credit_amount = 0
END_INSERT;
  $result = mysql_query($insert_credit_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "credits"');
  $db_events_array[] = array(USERS_CREATE, $new_user_id);
  vlc_insert_events($db_events_array, $new_user_id);
  foreach ($form_fields as $key => $value)
  {
    if (is_string($value)) $form_fields[$key] = stripslashes($value);
  }
  /* send message to user from administrator */
  $from = 'From: "'.$lang['common']['misc']['vlcff-admin'].'" <'.$site_info['vlcff_email'].'>';
  $to = $form_fields['primary_email'];
  $subject = $lang['profile']['email']['create-profile']['subject'];
  $message = sprintf($lang['profile']['email']['create-profile']['message'], $form_fields['first_name'], $form_fields['username'], $form_fields['password'], $form_fields['primary_email']);
  mail($to, $subject, $message, $from);
  /* send additional message to administrator from user */
  $from = 'From: "'.$form_fields['first_name'].' '.$form_fields['last_name'].'" <'.$form_fields['primary_email'].'>';
  $to = $site_info['register_email'];
  mail($to, $subject, $message, $from);
  /* add e-mail address to magnet mail newsletter list if user clicked subscribe */
  if (isset($form_fields['mm_subscribe']) and $form_fields['mm_subscribe'] == 1)
  {
    $curl_data = array(
      'user_id' => 'UofDayton',
      'subgroups' => '327851',
      'custom_validation_redir' => 'http://vlc.udayton.edu/profile/profile.php',
      'custom_end_location' => 'http://vlc.udayton.edu/profile/profile.php',
      'recipient_first_name' => $form_fields['first_name'],
      'recipient_last_name' => $form_fields['last_name'],
      'recipient_email' => $form_fields['primary_email'],
      'text_only' => $form_fields['mm_text_only']
    );
    $curl_opts = array(
      CURLOPT_URL            => 'http://www.magnetmail.net/actions/subscription_form_action.cfm',
      CURLOPT_POST           => 1,
      CURLOPT_POSTFIELDS     => $curl_data
    );
    $curl_handler = curl_init();
    curl_setopt_array($curl_handler, $curl_opts);
    $curl_result = curl_exec($curl_handler);
    curl_close($curl_handler);
    if (!$curl_result) trigger_error('cURL Execution Failed');
  }
  vlc_exit_page($lang['profile']['profile']['status']['create-profile-success'], 'success', 'profile/');
}
else
{
  $update_users_query = <<< END_UPDATE
    UPDATE users
    SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']}, prefix = NULLIF('{$form_fields['prefix']}', ''), first_name = '{$form_fields['first_name']}',
      middle_name = NULLIF('{$form_fields['middle_name']}', ''), last_name = '{$form_fields['last_name']}',
      suffix = NULLIF('{$form_fields['suffix']}', ''), nickname = NULLIF('{$form_fields['nickname']}', '')
    WHERE user_id = {$user_info['user_id']}
END_UPDATE;
  $result = mysql_query($update_users_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "users"');
  $update_user_info_query = <<< END_UPDATE
    UPDATE user_info
    SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']}, address_1 = '{$form_fields['address_1']}', address_2 = NULLIF('{$form_fields['address_2']}', ''),
      city = '{$form_fields['city']}', state_id = {$form_fields['state_id']}, zip = '{$form_fields['zip']}',
      country_id = {$form_fields['country_id']}, is_us_citizen = {$form_fields['is_us_citizen']},
      diocese_id = {$form_fields['diocese_id']},
		parish = NULLIF('{$form_fields['parish']}', ''),
      primary_phone = '{$form_fields['primary_phone']}', secondary_phone = NULLIF('{$form_fields['secondary_phone']}', ''),
      primary_email = '{$form_fields['primary_email']}', secondary_email = NULLIF('{$form_fields['secondary_email']}', ''),
      gender_type_id = {$form_fields['gender_type_id']}, birth_date = '{$form_fields['birth_date']}',
      race_type_id = {$form_fields['race_type_id']}, occupation_id = {$form_fields['occupation_id']},
      marital_status_id = {$form_fields['marital_status_id']}, religion_id = {$form_fields['religion_id']},
      biography = NULLIF('{$form_fields['biography']}', ''), title = NULLIF('{$form_fields['title']}', ''), url = NULLIF('{$form_fields['url']}', '')
    WHERE user_id = {$user_info['user_id']}
END_UPDATE;
  $result = mysql_query($update_user_info_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "user_info"');
  $db_events_array[] = array(USERS_UPDATE, $user_info['user_id']);
  vlc_insert_events($db_events_array);
  foreach ($form_fields as $key => $value)
  {
    if (is_string($value)) $form_fields[$key] = stripslashes($value);
  }
  /* send message to user from administrator */
  $from = 'From: "'.$lang['common']['misc']['vlcff-admin'].'" <'.$site_info['vlcff_email'].'>';
  $to = $form_fields['primary_email'];
  $subject = $lang['profile']['email']['update-profile']['subject'];
  $message = sprintf($lang['profile']['email']['update-profile']['message'], $form_fields['first_name'], $user_info['username'], $form_fields['primary_email']);
  mail($to, $subject, $message, $from);
  /* send additional message to administrator from user */
  $from = 'From: "'.$form_fields['first_name'].' '.$form_fields['last_name'].'" <'.$form_fields['primary_email'].'>';
  $to = $site_info['register_email'];
  mail($to, $subject, $message, $from);
  /* update session variables */
  $_SESSION['user_info']['email'] = $form_fields['primary_email'];
  $_SESSION['user_info']['name'] = $form_fields['first_name'];
  $_SESSION['user_info']['first_name'] = $form_fields['first_name'];
  $_SESSION['user_info']['last_name'] = $form_fields['last_name'];
  $_SESSION['user_info']['phone'] = $form_fields['primary_phone'];
  /* return to profile page */
  vlc_exit_page($lang['profile']['profile']['status']['update-profile-success'], 'success', 'profile/');
}
?>
