<?php
$page_info['section'] = 'cms';
$page_info['login_required'] = 1;
$user_info = vlc_get_user_info($page_info['login_required']);
$lang = vlc_get_language();
/* get form fields from posted variables */
$form_fields = $_POST;
/* create return url */
$return_url = 'cms/user_details.php';
$error_message = '';
$db_events_array = array();
if (isset($form_fields['courses']))
{
  $return_url .= '?user='.$form_fields['user_id'];
  /* get existing courses */
  $course_query = 'SELECT course_id FROM users_courses WHERE user_id = '.$form_fields['user_id'];
  $result = mysql_query($course_query, $site_info['db_conn']);
  $course_id_array = array();
  while ($record = mysql_fetch_array($result)) $course_id_array[] = $record['course_id'];
  $num_courses_inserted = 0;
  $insert_query_array = array();
  foreach ($form_fields['courses'] as $course)
  {
    if (is_numeric($course['course_id']) and !in_array($course['course_id'], $course_id_array))
    {
      $course_id_array[] = $course['course_id'];
      /* get course cost */
      $course_cost_query = <<< END_QUERY
        SELECT t.non_partner_cost
        FROM courses AS c, course_subjects AS s, course_types AS t
        WHERE c.course_subject_id = s.course_subject_id
        AND s.course_type_id = t.course_type_id
        AND c.course_id = {$course['course_id']}
END_QUERY;
      $result = mysql_query($course_cost_query, $site_info['db_conn']);
      $non_partner_cost = mysql_result($result, 0);
      /* insert course registration */
      $insert_query_array[] = 'INSERT INTO users_courses (user_course_id, course_id, user_id, user_role_id, course_status_id, registration_type_id, certificate_date, notes, is_scored, score_level_id, facilitator_notes, UPDATED, UPDATEDBY, CREATED, CREATEDBY) VALUES (NULL, '.$course['course_id'].', '.$form_fields['user_id'].', 5, 2, 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '.$user_info['user_id'].')';
      /* get new user-course id and event type id */
      $insert_query_array[] = 'SELECT '.USERS_COURSES_CREATE.' AS event_type_id, LAST_INSERT_ID() AS new_id';
      /* insert order */
      $insert_query_array[] = 'INSERT INTO orders VALUES (NULL, 1, 1, 2, CURDATE(), 1, LAST_INSERT_ID(), 1, '.$form_fields['user_id'].', NULL, NULL, '.$non_partner_cost.', 0, '.$non_partner_cost.', NULL, NULL, NULL, '.$user_info['user_id'].')';
      /* get new order id and event type id */
      $insert_query_array[] = 'SELECT '.ORDERS_CREATE.' AS event_type_id, LAST_INSERT_ID() AS new_id';
      $db_events_array[] = array(COURSES_ADD_USER, $course['course_id']);
      $db_events_array[] = array(USERS_ADD_COURSE, $form_fields['user_id']);
      $num_courses_inserted++;
    }
  }
  if (count($insert_query_array))
  {
    foreach ($insert_query_array as $insert_query)
    {
      $result = mysql_query($insert_query, $site_info['db_conn']);
      if (is_numeric(strpos($insert_query, 'INSERT INTO')) and mysql_affected_rows() < 1) trigger_error('INSERT FAILED');
      if (is_numeric(strpos($insert_query, 'SELECT')) and mysql_num_rows($result) == 1)
      {
        $record = mysql_fetch_array($result);
        $db_events_array[] = array($record['event_type_id'], $record['new_id']);
      }
    }
    vlc_insert_events($db_events_array);
    vlc_exit_page($num_courses_inserted.' Course(s) Added.', 'success', $return_url);
  }
  else
  {
    $_SESSION['form_fields'] = $form_fields;
    vlc_exit_page('<li>No Course(s) Selected or Duplicate Course(s) Selected.</li>', 'error', $return_url);
  }
}
elseif (isset($form_fields['transfer_user_id']) and isset($form_fields['transfer_amount']))
{
  $return_url .= '?user='.$form_fields['user_id'];
  if (is_numeric($form_fields['transfer_user_id']) and $form_fields['transfer_user_id'] > 0 and is_numeric($form_fields['transfer_amount']) and $form_fields['transfer_amount'] > 0)
  {
    $transfer_amount = $form_fields['transfer_amount'] * 100;
    /* get users' credit amounts */
    $credit_amount_query = 'SELECT customer_id, credit_id, credit_amount FROM credits WHERE customer_type_id = 1 AND customer_id IN ('.$form_fields['user_id'].', '.$form_fields['transfer_user_id'].')';
    $result = mysql_query($credit_amount_query, $site_info['db_conn']);
    if (mysql_num_rows($result) == 2)
    {
      while ($record = mysql_fetch_array($result)) $credit_details[$record['customer_id']] = $record;
      if ($transfer_amount <= $credit_details[$form_fields['user_id']]['credit_amount'])
      {
        $subtract_credit_query = 'UPDATE credits SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', credit_amount = credit_amount - '.$transfer_amount.' WHERE credit_id = '.$credit_details[$form_fields['user_id']]['credit_id'];
        $result = mysql_query($subtract_credit_query, $site_info['db_conn']);
        if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "credits"');
        $db_events_array[] = array(CREDITS_UPDATE, $credit_details[$form_fields['user_id']]['credit_id']);
        $add_credit_query = 'UPDATE credits SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', credit_amount = credit_amount + '.$transfer_amount.' WHERE credit_id = '.$credit_details[$form_fields['transfer_user_id']]['credit_id'];
        $result = mysql_query($add_credit_query, $site_info['db_conn']);
        if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "credits"');
        $db_events_array[] = array(CREDITS_UPDATE, $credit_details[$form_fields['transfer_user_id']]['credit_id']);
        vlc_insert_events($db_events_array);
        vlc_exit_page('Credit successfully transferred.', 'success', $return_url);
      }
    }
  }
  vlc_exit_page('<li>Invalid user ID or transfer amount.</li>', 'error', $return_url);
}
elseif (isset($form_fields['transfer_user_id']))
{
  $return_url .= '?user='.$form_fields['user_id'];
  if (is_numeric($form_fields['transfer_user_id']) and $form_fields['transfer_user_id'] > 0 and isset($form_fields['user_course_id_array']) and count($form_fields['user_course_id_array']) > 0)
  {
    $user_course_id_list = join(', ', $form_fields['user_course_id_array']);
    $num_user_course_updates = count($form_fields['user_course_id_array']);
    /* track update events */
    $user_course_query = 'SELECT user_course_id, course_id FROM users_courses WHERE user_course_id IN ('.$user_course_id_list.')';
    $result = mysql_query($user_course_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result))
    {
      $db_events_array[] = array(USERS_COURSES_UPDATE, $record['user_course_id']);
      $db_events_array[] = array(COURSES_REMOVE_USER, $record['course_id']);
      $db_events_array[] = array(COURSES_ADD_USER, $record['course_id']);
      $db_events_array[] = array(USERS_REMOVE_COURSE, $form_fields['user_id']);
      $db_events_array[] = array(USERS_ADD_COURSE, $form_fields['transfer_user_id']);
    }
    /* update users_courses */
    $update_users_courses_query = 'UPDATE users_courses SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', user_id = '.$form_fields['transfer_user_id'].' WHERE user_course_id IN ('.$user_course_id_list.')';
    $result = mysql_query($update_users_courses_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "users_courses"');
    $num_order_updates = $num_transaction_updates = 0;
    /* get related orders */
    $order_id_array = array();
    $order_query = 'SELECT order_id FROM orders WHERE customer_type_id = 1 AND customer_id = '.$form_fields['user_id'].' AND product_type_id = 1 AND product_id IN ('.$user_course_id_list.')';
    $result = mysql_query($order_query, $site_info['db_conn']);
    if (mysql_num_rows($result))
    {
      /* get order id's */
      while ($record = mysql_fetch_array($result)) $order_id_array[] = $record['order_id'];
      $order_id_list = join(', ', $order_id_array);
      $num_order_updates = count($order_id_array);
      foreach ($order_id_array as $order_id) $db_events_array[] = array(ORDERS_UPDATE, $order_id);
      /* update orders */
      $update_orders_query = 'UPDATE orders SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', customer_id = '.$form_fields['transfer_user_id'].' WHERE order_id IN ('.$order_id_list.')';
      $result = mysql_query($update_orders_query, $site_info['db_conn']);
      if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "orders"');
      /* get related transactions */
      $transaction_id_array = array();
      $transaction_query = 'SELECT t.transaction_id FROM transactions AS t, orders_transactions AS ot WHERE t.transaction_id = ot.transaction_id AND t.customer_type_id = 1 AND t.customer_id = '.$form_fields['user_id'].' AND ot.order_id IN ('.$order_id_list.')';
      $result = mysql_query($transaction_query, $site_info['db_conn']);
      if (mysql_num_rows($result))
      {
        /* get transaction id's */
        while ($record = mysql_fetch_array($result)) $transaction_id_array[] = $record['transaction_id'];
        $transaction_id_list = join(', ', $transaction_id_array);
        $num_transaction_updates = count($transaction_id_array);
        foreach ($transaction_id_array as $transaction_id) $db_events_array[] = array(TRANSACTIONS_UPDATE, $transaction_id);
        /* update transactions */
        $update_transactions_query = 'UPDATE transactions SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', customer_id = '.$form_fields['transfer_user_id'].' WHERE transaction_id IN ('.$transaction_id_list.')';
        $result = mysql_query($update_transactions_query, $site_info['db_conn']);
        if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "transactions"');
      }
    }
    vlc_insert_events($db_events_array);
    vlc_exit_page($num_user_course_updates.' course registration(s) successfully transferred, '.$num_order_updates.' order(s) successfully transferred, '.$num_transaction_updates.' transaction(s) successfully transferred.', 'success', $return_url);
  }
  vlc_exit_page('<li>Invalid user ID or no courses selected.</li>', 'error', $return_url);
}
else
{
  /* check to see if required fields were filled in */
  if (!isset($form_fields['user_roles']) or count($form_fields['user_roles']) == 0)
  {
    $form_fields['user_roles'] = array();
    $error_message .= '<li>User role is required.</li>';
  }
  /* check for valid username */
  if (strlen($form_fields['username'] = trim($form_fields['username'])))
  {
    if (!isset($form_fields['user_id']) or $form_fields['username'] != $form_fields['original_username'])
    {
      /* has username already been taken? */
      $username_query = <<< END_QUERY
        SELECT *
        FROM users
        WHERE BINARY username = '{$form_fields['username']}'
END_QUERY;
      $result = mysql_query($username_query, $site_info['db_conn']);
      if (mysql_num_rows($result)) $error_message .= '<li>The username you have chosen is already in use.</li>';
    }
    /* is username 6 chars and only consists of letters, numbers, and underscore? */
    elseif (!ereg('^[a-zA-Z0-9_]{6}$', $form_fields['username'])) $error_message .= '<li>Username must be exactly six characters long, consisting of only letters, numbers, and the underscore (&quot;_&quot;) character.</li>';
  }
  else $error_message .= '<li>Username is required.</li>';
  /* check for valid password */
  if (strlen($form_fields['password'] = trim($form_fields['password'])))
  {
    /* is password 6 chars and only consists of letters, numbers, and underscore? */
    if (!ereg('^[a-zA-Z0-9_]{6}$', $form_fields['password'])) $error_message .= '<li>Password must be exactly six characters long, consisting of only letters, numbers, and the underscore (&quot;_&quot;) character.</li>';
  }
  else $error_message .= '<li>Password is required.</li>';
  /* required fields */
  if (!(strlen($form_fields['first_name'] = trim($form_fields['first_name'])))) $error_message .= '<li>First name is required.</li>';
  if (!(strlen($form_fields['last_name'] = trim($form_fields['last_name'])))) $error_message .= '<li>Last name is required.</li>';
  /* check for valid e-mail address */
  if (strlen($form_fields['primary_email'] = trim($form_fields['primary_email'])))
  {
    if (!ereg('^[^@]+@[^@]+\.[^@]+$', $form_fields['primary_email'])) $error_message .= '<li>E-mail address does not appear to be valid.</li>';
  }
  else $error_message .= '<li>E-Mail Address is required.</li>';
  if (!isset($form_fields['send_email_notification'])) $form_fields['send_email_notification'] = 0;
  /* if errors have occurred, go back to form */
  if (strlen($error_message) > 0)
  {
    if (!is_numeric($form_fields['is_us_citizen'])) $form_fields['is_us_citizen'] = -1;
    $_SESSION['form_fields'] = $form_fields;
    vlc_exit_page($error_message, 'error', $return_url);
  }
  $form_fields['active_start'] = $form_fields['active_start_year'].'-'.$form_fields['active_start_month'].'-'.$form_fields['active_start_day'];
  $form_fields['active_end'] = $form_fields['active_end_year'].'-'.$form_fields['active_end_month'].'-'.$form_fields['active_end_day'];
  if (is_numeric($form_fields['birth_date_year']) and is_numeric($form_fields['birth_date_month']) and is_numeric($form_fields['birth_date_day'])) $form_fields['birth_date'] = $form_fields['birth_date_year'].'-'.$form_fields['birth_date_month'].'-'.$form_fields['birth_date_day'];
  else $form_fields['birth_date'] = '';
  foreach ($form_fields as $key => $value)
  {
    if (is_string($value)) $form_fields[$key] = addslashes($value);
  }
  /* update user details */
  if (isset($form_fields['user_id']))
  {
    /* add user id to return url */
    $return_url .= '?user='.$form_fields['user_id'];
    /* update users table */
    $update_user_query = <<< END_QUERY
      UPDATE users
      SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']},
        username = '{$form_fields['username']}', password = '{$form_fields['password']}',
        prefix = NULLIF('{$form_fields['prefix']}', ''),
        first_name = '{$form_fields['first_name']}', middle_name = NULLIF('{$form_fields['middle_name']}', ''), last_name = '{$form_fields['last_name']}',
        suffix = NULLIF('{$form_fields['suffix']}', ''), nickname = NULLIF('{$form_fields['nickname']}', ''),
        active_start = '{$form_fields['active_start']}', active_end = '{$form_fields['active_end']}'
      WHERE user_id = {$form_fields['user_id']}
END_QUERY;
    $result = mysql_query($update_user_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "users"');
    /* update user_info table */
    $update_user_info_query = <<< END_QUERY
      UPDATE user_info
      SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']},
        birth_date = NULLIF('{$form_fields['birth_date']}', ''), is_us_citizen = {$form_fields['is_us_citizen']},
        marital_status_id = {$form_fields['marital_status_id']}, religion_id = {$form_fields['religion_id']},
        gender_type_id = {$form_fields['gender_type_id']}, race_type_id = {$form_fields['race_type_id']}, occupation_id = {$form_fields['occupation_id']},
        partner_id = {$form_fields['partner_id']}, diocese_id = {$form_fields['diocese_id']}, diocese = NULLIF('{$form_fields['diocese']}', ''),
		  parish = NULLIF('{$form_fields['parish']}', ''),
        address_1 = NULLIF('{$form_fields['address_1']}', ''), address_2 = NULLIF('{$form_fields['address_2']}', ''),
        city = NULLIF('{$form_fields['city']}', ''), state_id = {$form_fields['state_id']}, zip = NULLIF('{$form_fields['zip']}', ''),
        country_id = {$form_fields['country_id']}, international_address = NULLIF('{$form_fields['international_address']}', ''),
        primary_phone = NULLIF('{$form_fields['primary_phone']}', ''), secondary_phone = NULLIF('{$form_fields['secondary_phone']}', ''), fax = NULLIF('{$form_fields['fax']}', ''),
        primary_email = '{$form_fields['primary_email']}', secondary_email = NULLIF('{$form_fields['secondary_email']}', ''),
        url = NULLIF('{$form_fields['url']}', ''), title = NULLIF('{$form_fields['title']}', ''), image = NULLIF('{$form_fields['image']}', ''), biography = NULLIF('{$form_fields['biography']}', ''),
        send_email_notification = {$form_fields['send_email_notification']}
      WHERE user_id = {$form_fields['user_id']}
END_QUERY;
    $result = mysql_query($update_user_info_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "user_info"');
    /* delete old user role(s) */
    $delete_user_roles_query = 'DELETE FROM users_roles WHERE user_id = '.$form_fields['user_id'];
    $result = mysql_query($delete_user_roles_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('DELETE FAILED: "users_roles"');
    /* insert new user role(s) */
    foreach ($form_fields['user_roles'] as $user_role_id) $insert_user_roles_array[] = '('.$form_fields['user_id'].', '.$user_role_id.', NULL, NULL, NULL, '.$user_info['user_id'].')';
    $insert_user_roles_query = 'INSERT INTO users_roles VALUES '.join(', ', $insert_user_roles_array);
    $result = mysql_query($insert_user_roles_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "users_roles"');
    /* create success message */
    $success_message = '<p>User Details Updated.</p>';
    $db_events_array[] = array(USERS_UPDATE, $form_fields['user_id']);
  }
  /* insert new user */
  else
  {
    /* insert into users table */
    $insert_user_query = <<< END_QUERY
      INSERT INTO users
      SET CREATED = NULL, CREATEDBY = {$user_info['user_id']},
        username = '{$form_fields['username']}', password = '{$form_fields['password']}',
        prefix = NULLIF('{$form_fields['prefix']}', ''),
        first_name = '{$form_fields['first_name']}', middle_name = NULLIF('{$form_fields['middle_name']}', ''), last_name = '{$form_fields['last_name']}',
        suffix = NULLIF('{$form_fields['suffix']}', ''), nickname = NULLIF('{$form_fields['nickname']}', ''),
        active_start = '{$form_fields['active_start']}', active_end = '{$form_fields['active_end']}'
END_QUERY;
    $result = mysql_query($insert_user_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "users"');
    $new_user_id = mysql_insert_id();
    /* add new user id to return url */
    $return_url .= '?user='.$new_user_id;
    /* insert into user_info table */
    $insert_user_info_query = <<< END_QUERY
      INSERT INTO user_info
      SET user_id = $new_user_id, CREATED = NULL, CREATEDBY = {$user_info['user_id']},
        birth_date = NULLIF('{$form_fields['birth_date']}', ''), is_us_citizen = {$form_fields['is_us_citizen']},
        marital_status_id = {$form_fields['marital_status_id']}, religion_id = {$form_fields['religion_id']},
        gender_type_id = {$form_fields['gender_type_id']}, race_type_id = {$form_fields['race_type_id']}, occupation_id = {$form_fields['occupation_id']},
        partner_id = {$form_fields['partner_id']}, diocese_id = {$form_fields['diocese_id']}, diocese = NULLIF('{$form_fields['diocese']}', ''),
		  parish = NULLIF('{$form_fields['parish']}', ''),
        address_1 = NULLIF('{$form_fields['address_1']}', ''), address_2 = NULLIF('{$form_fields['address_2']}', ''),
        city = NULLIF('{$form_fields['city']}', ''), state_id = {$form_fields['state_id']}, zip = NULLIF('{$form_fields['zip']}', ''),
        country_id = {$form_fields['country_id']}, international_address = NULLIF('{$form_fields['international_address']}', ''),
        primary_phone = NULLIF('{$form_fields['primary_phone']}', ''), secondary_phone = NULLIF('{$form_fields['secondary_phone']}', ''), fax = NULLIF('{$form_fields['fax']}', ''),
        primary_email = '{$form_fields['primary_email']}', secondary_email = NULLIF('{$form_fields['secondary_email']}', ''),
        url = NULLIF('{$form_fields['url']}', ''), title = NULLIF('{$form_fields['title']}', ''), image = NULLIF('{$form_fields['image']}', ''), biography = NULLIF('{$form_fields['biography']}', ''),
        send_email_notification = {$form_fields['send_email_notification']}
END_QUERY;
    $result = mysql_query($insert_user_info_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "user_info"');
    /* insert new user role(s) */
    foreach ($form_fields['user_roles'] as $user_role_id) $insert_user_roles_array[] = '('.$new_user_id.', '.$user_role_id.', NULL, NULL, NULL, '.$user_info['user_id'].')';
    $insert_user_roles_query = 'INSERT INTO users_roles VALUES '.join(', ', $insert_user_roles_array);
    $result = mysql_query($insert_user_roles_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "users_roles"');
    /* insert credit */
    $insert_credit_query = <<< END_QUERY
      INSERT INTO credits
      SET CREATED = NULL, CREATEDBY = {$user_info['user_id']}, customer_id = $new_user_id, customer_type_id = 1, credit_amount = 0
END_QUERY;
    $result = mysql_query($insert_credit_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "credits"');
    /* create success message */
    $success_message = '<p>User Successfully Created.</p>';
    $db_events_array[] = array(USERS_CREATE, $new_user_id);
  }
}
vlc_insert_events($db_events_array);
/* return to user details page */
vlc_exit_page($success_message, 'success', $return_url);
?>
