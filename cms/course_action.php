<?php
$page_info['section'] = 'cms';
$page_info['login_required'] = 1;
$user_info = vlc_get_user_info($page_info['login_required']);
$lang = vlc_get_language();
/* get form fields from posted variables */
$form_fields = $_POST;
/* create return url */
$return_url = 'cms/course_details.php';
$error_message = '';
$db_events_array = array();
if (isset($form_fields['facilitators']))
{
  $return_url .= '?course='.$form_fields['course_id'];
  /* get facilitators */
  $facilitator_query = <<< END_QUERY
    SELECT uc.user_course_id, uc.user_id, uc.course_id, uc.course_status_id,
      o.order_id, o.order_cost, o.amount_paid
    FROM users_courses AS uc, orders AS o
    WHERE uc.course_id = o.product_id
    AND uc.user_id = o.customer_id
    AND o.product_type_id = 3
    AND o.customer_type_id = 3
    AND uc.user_role_id = 4
    AND uc.course_id = {$form_fields['course_id']}
END_QUERY;
  $result = mysql_query($facilitator_query, $site_info['db_conn']);
  $num_facilitators = mysql_num_rows($result);
  $facilitator_array = array();
  while ($record = mysql_fetch_array($result)) $facilitator_array[$record['user_course_id']] = $record;
  $order_cost_total = 0;
  $update_query_array = $updated_users_array = $delete_query_array = $deleted_users_array = array();
  foreach ($form_fields['facilitators'] as $key => $facilitator)
  {
    if (isset($facilitator['remove']))
    {
      $transaction_query = 'SELECT transaction_id FROM orders_transactions WHERE order_id = '.$facilitator_array[$key]['order_id'];
      $result = mysql_query($transaction_query, $site_info['db_conn']);
      if (mysql_num_rows($result)) vlc_exit_page('<li>Facilitator order has transactions.</li>', 'error', $return_url);
      elseif ($num_facilitators == 1) vlc_exit_page('<li>There must be at least one facilitator.</li>', 'error', $return_url);
      else
      {
        $delete_query_array[] = 'DELETE FROM users_courses WHERE user_course_id = '.$key;
        $delete_query_array[] = 'DELETE FROM orders WHERE order_id = '.$facilitator_array[$key]['order_id'];
        $deleted_users_array[] = $facilitator_array[$key]['user_id'];
        $db_events_array[] = array(USERS_COURSES_DELETE, $key);
        $db_events_array[] = array(COURSES_REMOVE_USER, $facilitator_array[$key]['course_id']);
        $db_events_array[] = array(USERS_REMOVE_COURSE, $facilitator_array[$key]['user_id']);
        $db_events_array[] = array(ORDERS_DELETE, $facilitator_array[$key]['order_id']);
        $num_facilitators--;
      }
    }
    else
    {
      if ($facilitator['course_status_id'] != $facilitator_array[$key]['course_status_id'])
      {
        $update_query_array[] = 'UPDATE users_courses SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', course_status_id = '.$facilitator['course_status_id'].' WHERE user_course_id = '.$key;
        $updated_users_array[] = $facilitator_array[$key]['user_id'];
        $db_events_array[] = array(USERS_COURSES_UPDATE, $key);
      }
      if (is_numeric($facilitator['order_cost']) and $facilitator['order_cost'] > 0) $order_cost_total += $facilitator['order_cost'] * 100;
    }
  }
  if (isset($form_fields['update_facilitator_stipend']))
  {
    /* get course type */
    $course_type_query = <<< END_QUERY
      SELECT s.course_type_id
      FROM courses AS c, course_subjects AS s
      WHERE c.course_subject_id = s.course_subject_id
      AND c.course_id = {$form_fields['course_id']}
END_QUERY;
    $result = mysql_query($course_type_query, $site_info['db_conn']);
    $course_type_id = mysql_result($result, 0);
    /* get students */
    $student_query = <<< END_QUERY
      SELECT COUNT(*) AS num_students, SUM(o.amount_paid) AS total_amount_paid
      FROM users_courses AS uc, orders AS o
      WHERE uc.user_course_id = o.product_id
      AND o.product_type_id = 1
      AND o.payment_status_id IN (1, 4)
      AND uc.user_role_id = 5
      AND uc.course_status_id = 7
      AND uc.course_id = {$form_fields['course_id']}
END_QUERY;
    $result = mysql_query($student_query, $site_info['db_conn']);
    $student_order_info = mysql_fetch_array($result);
    /* calculate facilitator stipend */
    if ($student_order_info['num_students'])
    {
      if ($course_type_id == 1)
      {
        if ($student_order_info['total_amount_paid'] / 2 > 10000) $total_stipend_amount = $student_order_info['total_amount_paid'] / 2;
        else $total_stipend_amount = 10000;
      }
      else
      {
        if ($student_order_info['num_students'] < 5) $total_stipend_amount = 15000;
        elseif ($student_order_info['num_students'] < 9) $total_stipend_amount = 20000;
        else $total_stipend_amount = 30000;
      }
    }
    else $total_stipend_amount = 0;
    $order_cost = floor($total_stipend_amount / $num_facilitators);
    $difference = $total_stipend_amount - ($order_cost * $num_facilitators);
    /* update queries */
    $i = 0;
    foreach ($facilitator_array as $key => $order)
    {
      /* skip facilitators that are being removed */
      if (isset($form_fields['facilitators'][$key]['remove'])) continue;
      $facilitator_order_cost = $order_cost;
      if ($i == 0 and $difference) $facilitator_order_cost += $difference;
      $i++;
      $amount_due = $facilitator_order_cost - $order['amount_paid'];
      /* update payment status */
      if ($order['amount_paid'] == 0)
      {
        /* no charge */
        if ($amount_due == 0) $payment_status_id = 1;
        /* not paid */
        else $payment_status_id = 2;
      }
      else
      {
        /* paid */
        if ($amount_due == 0) $payment_status_id = 4;
        /* partial payment */
        elseif ($amount_due > 0) $payment_status_id = 3;
        /* over payment */
        else $payment_status_id = 5;
      }
      /* update order */
      $update_query_array[] = 'UPDATE orders SET order_cost = '.$facilitator_order_cost.', amount_due = '.$amount_due.', payment_status_id = '.$payment_status_id.' WHERE order_id = '.$order['order_id'];
      $updated_users_array[] = $order['user_id'];
      $db_events_array[] = array(ORDERS_UPDATE, $order['order_id']);
    }
  }
  else
  {
    $total_stipend_amount = $order_cost_total;
    foreach ($form_fields['facilitators'] as $key => $order)
    {
      /* skip facilitators that are being removed */
      if (isset($order['remove'])) continue;
      if (is_numeric($order['order_cost']) and $order['order_cost'] > 0) $facilitator_order_cost = $order['order_cost'] * 100;
      else $facilitator_order_cost = 0;
      $amount_due = $facilitator_order_cost - $facilitator_array[$key]['amount_paid'];
      /* update payment status */
      if ($facilitator_array[$key]['amount_paid'] == 0)
      {
        /* no charge */
        if ($amount_due == 0) $payment_status_id = 1;
        /* not paid */
        else $payment_status_id = 2;
      }
      else
      {
        /* paid */
        if ($amount_due == 0) $payment_status_id = 4;
        /* partial payment */
        elseif ($amount_due > 0) $payment_status_id = 3;
        /* over payment */
        else $payment_status_id = 5;
      }
      /* update order */
      $update_query_array[] = 'UPDATE orders SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', order_cost = '.$facilitator_order_cost.', amount_due = '.$amount_due.', payment_status_id = '.$payment_status_id.' WHERE order_id = '.$facilitator_array[$key]['order_id'];
      $updated_users_array[] = $facilitator_array[$key]['user_id'];
      $db_events_array[] = array(ORDERS_UPDATE, $facilitator_array[$key]['order_id']);
    }
  }
  vlc_insert_events($db_events_array);
  $num_users_updated = count(array_unique($updated_users_array));
  foreach ($update_query_array as $update_query) $result = mysql_query($update_query, $site_info['db_conn']);
  $num_users_deleted = count(array_unique($deleted_users_array));
  foreach ($delete_query_array as $delete_query) $result = mysql_query($delete_query, $site_info['db_conn']);
  if ($num_users_updated or $num_users_deleted) vlc_exit_page($num_users_updated.' Facilitator Record(s) Updated, '.$num_users_deleted.' Facilitator(s) Removed.', 'success', $return_url);
  else vlc_exit_page('<li>No Changes Saved.</li>', 'error', $return_url);
}
elseif (isset($form_fields['students']))
{
  $return_url .= '?course='.$form_fields['course_id'];
  /* get existing users */
  $user_query = 'SELECT user_course_id, user_id, course_status_id, registration_type_id FROM users_courses WHERE course_id = '.$form_fields['course_id'];
  $result = mysql_query($user_query, $site_info['db_conn']);
  $user_array = $user_id_array = array();
  while ($record = mysql_fetch_array($result))
  {
    $user_array[$record['user_course_id']] = $record;
    $user_id_array[] = $record['user_id'];
  }
  /* get course cost */
  $course_cost_query = <<< END_QUERY
    SELECT t.partner_cost, t.non_partner_cost, t.undergraduate_cost, t.course_type_id
    FROM courses AS c, course_subjects AS s, course_types AS t
    WHERE c.course_subject_id = s.course_subject_id
    AND s.course_type_id = t.course_type_id
    AND c.course_id = {$form_fields['course_id']}
END_QUERY;
  $result = mysql_query($course_cost_query, $site_info['db_conn']);
  $course_details = mysql_fetch_array($result);
  $num_users_inserted = $num_users_updated = 0;
  $insert_query_array = $update_query_array = array();
  foreach ($form_fields['students'] as $key => $student)
  {
    if (is_numeric($key))
    {
      if ($student['course_status_id'] != $user_array[$key]['course_status_id'])
      {
        $update_query_array[] = 'UPDATE users_courses SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', course_status_id = '.$student['course_status_id'].' WHERE user_course_id = '.$key;
        $db_events_array[] = array(USERS_COURSES_UPDATE, $key);
        $num_users_updated++;
      }
    }
    elseif (is_numeric($student['user_id']) and !in_array($student['user_id'], $user_id_array))
    {
      $user_id_array[] = $student['user_id'];
      if ($student['user_role_id'] == 4)
      {
        $product_id = $form_fields['course_id'];
        $product_type_id = $customer_type_id = 3;
        $discount_type_id = $discount_id = 'NULL';
        $order_cost = 0;
      }
      else
      {
        $product_id = 'LAST_INSERT_ID()';
        $product_type_id = $customer_type_id = 1;
        if ($student['registration_type_id'] != 1)
        {
          $discount_type_id = $discount_id = 'NULL';
          $order_cost = $course_details['undergraduate_cost'];
        }
        elseif (is_numeric($student['discount_type_id']))
        {
          $discount_type_id = floor($student['discount_type_id'] / 10000);
          $discount_id = $student['discount_type_id'] % 10000;
          if ($discount_type_id == 1)
          {
            /* get payment code details */
            $discount_query = <<< END_QUERY
              SELECT partner_id, student_seminar_cost, partner_seminar_cost, student_course_cost, partner_course_cost
              FROM payment_codes
              WHERE payment_code_id = $discount_id
END_QUERY;
            $result = mysql_query($discount_query, $site_info['db_conn']);
            $discount_details = mysql_fetch_array($result);
            if ($course_details['course_type_id'] == 1) $order_cost = $discount_details['student_seminar_cost'];
            else $order_cost = $discount_details['student_course_cost'];
          }
          else
          {
            /* get partner discount details */
            $discount_query = <<< END_QUERY
              SELECT partner_id, student_seminar_cost, partner_seminar_cost, student_course_cost, partner_course_cost
              FROM partners
              WHERE partner_id = $discount_id
END_QUERY;
            $result = mysql_query($discount_query, $site_info['db_conn']);
            $discount_details = mysql_fetch_array($result);
            if (isset($discount_details['student_seminar_cost']))
            {
              if ($course_details['course_type_id'] == 1) $order_cost = $discount_details['student_seminar_cost'];
              else $order_cost = $discount_details['student_course_cost'];
            }
            else $order_cost = $course_details['partner_cost'];
          }
        }
        else
        {
          $discount_type_id = $discount_id = 'NULL';
          $order_cost = $course_details['non_partner_cost'];
        }
      }
      if ($order_cost == 0) $payment_status_id = 1;
      else $payment_status_id = 2;
      $insert_query_array[] = 'INSERT INTO users_courses (user_course_id, course_id, user_id, user_role_id, course_status_id, registration_type_id, certificate_date, notes, is_scored, score_level_id, facilitator_notes, UPDATED, UPDATEDBY, CREATED, CREATEDBY) VALUES (NULL, '.$form_fields['course_id'].', '.$student['user_id'].', '.$student['user_role_id'].', '.$student['course_status_id'].', '.$student['registration_type_id'].', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '.$user_info['user_id'].')';
      $insert_query_array[] = 'SELECT '.USERS_COURSES_CREATE.' AS event_type_id, @new_product_id := LAST_INSERT_ID() AS new_id';
      $insert_query_array[] = 'INSERT INTO orders VALUES (NULL, 1, 1, '.$payment_status_id.', CURDATE(), '.$product_type_id.', '.$product_id.', '.$customer_type_id.', '.$student['user_id'].', '.$discount_type_id.', '.$discount_id.', '.$order_cost.', 0, '.$order_cost.', NULL, NULL, NULL, '.$user_info['user_id'].')';
      $insert_query_array[] = 'SELECT '.ORDERS_CREATE.' AS event_type_id, LAST_INSERT_ID() AS new_id';
      $db_events_array[] = array(COURSES_ADD_USER, $form_fields['course_id']);
      $db_events_array[] = array(USERS_ADD_COURSE, $student['user_id']);
      $num_users_inserted++;
      /* new partner order */
      if (($discount_type_id == 1 and isset($discount_details['partner_id'])) or ($discount_type_id > 1 and isset($discount_details['student_seminar_cost'])))
      {
        if ($course_details['course_type_id'] == 1) $partner_order_cost = $discount_details['partner_seminar_cost'];
        else $partner_order_cost = $discount_details['partner_course_cost'];
        if ($partner_order_cost > 0)
        {
          $insert_query_array[] = 'INSERT INTO orders (CREATED, CREATEDBY, product_type_id, product_id, customer_type_id, customer_id, is_active, is_complete, payment_status_id, order_date, discount_type_id, discount_id, order_cost, amount_paid, amount_due) VALUES (NULL, '.$user_info['user_id'].', 1, @new_product_id, 2, '.$discount_details['partner_id'].', 1, 1, 2, CURDATE(), '.$discount_type_id.', '.$discount_id.', '.$partner_order_cost.', 0, '.$partner_order_cost.')';
          /* get new order id and event type id */
          $insert_query_array[] = 'SELECT '.ORDERS_CREATE.' AS event_type_id, LAST_INSERT_ID() AS new_id';
        }
      }
    }
  }
  if ($num_users_updated or $num_users_inserted)
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
    foreach ($update_query_array as $update_query) $result = mysql_query($update_query, $site_info['db_conn']);
    vlc_insert_events($db_events_array);
    vlc_exit_page($num_users_inserted.' User(s) Added, '.$num_users_updated.' User(s) Updated.', 'success', $return_url);
  }
  else vlc_exit_page('<li>No User ID(s) Entered or Duplicate User ID(s) Entered.</li><li>No Changes Saved.</li>', 'error', $return_url);
}
else
{
  /* check to see if required fields were filled in */
  if (!isset($form_fields['course_id']) and !is_numeric($form_fields['facilitator_id'])) $error_message .= '<li>Facilitator is required.</li>';
  if (!is_numeric($form_fields['cycle_id'])) $error_message .= '<li>Cycle is required.</li>';
  if (!is_numeric($form_fields['course_subject_id'])) $error_message .= '<li>Course Subject is required.</li>';
  if (!is_numeric($form_fields['section_id'])) $error_message .= '<li>Section is required.</li>';
  if (!is_numeric($form_fields['is_active'])) $error_message .= '<li>Course Status is required.</li>';
  if (!(strlen($form_fields['code'] = trim($form_fields['code'])))) $error_message .= '<li>Code is required.</li>';
  if (!(strlen($form_fields['description'] = trim($form_fields['description'])))) $error_message .= '<li>Description is required.</li>';
  if (!isset($form_fields['is_restricted'])) $form_fields['is_restricted'] = 0;
  if (!isset($form_fields['is_sample'])) $form_fields['is_sample'] = 0;
  /* if errors have occurred, go back to form */
  if (strlen($error_message) > 0)
  {
    $_SESSION['form_fields'] = $form_fields;
    vlc_exit_page($error_message, 'error', $return_url);
  }
  foreach ($form_fields as $key => $value)
  {
    if (is_string($value)) $form_fields[$key] = addslashes($value);
  }
  $form_fields['facilitator_start'] = $form_fields['facilitator_start_year'].'-'.$form_fields['facilitator_start_month'].'-'.$form_fields['facilitator_start_day'];
  $form_fields['facilitator_end'] = $form_fields['facilitator_end_year'].'-'.$form_fields['facilitator_end_month'].'-'.$form_fields['facilitator_end_day'];
  $form_fields['student_start'] = $form_fields['student_start_year'].'-'.$form_fields['student_start_month'].'-'.$form_fields['student_start_day'];
  $form_fields['student_end'] = $form_fields['student_end_year'].'-'.$form_fields['student_end_month'].'-'.$form_fields['student_end_day'];
  if (isset($form_fields['course_id']))
  {
    /* add course id to return url */
    $return_url .= '?course='.$form_fields['course_id'];
    /* update course details */
    $update_course_query = <<< END_QUERY
      UPDATE courses
      SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']}, cycle_id = {$form_fields['cycle_id']}, course_subject_id = {$form_fields['course_subject_id']}, section_id = {$form_fields['section_id']}, code = '{$form_fields['code']}', description = '{$form_fields['description']}', course_email = NULLIF('{$form_fields['course_email']}', ''), facilitator_start = '{$form_fields['facilitator_start']}', facilitator_end = '{$form_fields['facilitator_end']}', student_start = '{$form_fields['student_start']}', student_end = '{$form_fields['student_end']}', is_restricted = {$form_fields['is_restricted']}, is_sample = {$form_fields['is_sample']}, is_active = {$form_fields['is_active']}, registration_type_id = {$form_fields['registration_type_id']}
      WHERE course_id = {$form_fields['course_id']}
END_QUERY;
    $result = mysql_query($update_course_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "courses"');
    /* create success message */
    $success_message = '<p>Course Details Updated.</p>';
    $db_events_array[] = array(COURSES_UPDATE, $form_fields['course_id']);
  }
  else
  {
    /* insert new course */
    $insert_course_query = 'INSERT INTO courses VALUES (NULL, '.$form_fields['course_subject_id'].', '.$form_fields['cycle_id'].', '.$form_fields['section_id'].', \''.$form_fields['code'].'\', \''.$form_fields['description'].'\', \''.$form_fields['facilitator_start'].'\', \''.$form_fields['facilitator_end'].'\', \''.$form_fields['student_start'].'\', \''.$form_fields['student_end'].'\', NULLIF(\''.$form_fields['course_email'].'\', \'\'), NULL, '.$form_fields['is_restricted'].', '.$form_fields['is_sample'].', '.$form_fields['is_active'].', '.$form_fields['registration_type_id'].', NULL, NULL, NULL, '.$user_info['user_id'].')';
    $result = mysql_query($insert_course_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "courses"');
    /* get new course id */
    $course_id = mysql_insert_id();
    $db_events_array[] = array(COURSES_CREATE, $course_id);
    /* add new course id to return url */
    $return_url .= '?course='.$course_id;
    /* link facilitator to new course */
    $link_facilitator_query = 'INSERT INTO users_courses (user_course_id, course_id, user_id, user_role_id, course_status_id, registration_type_id, certificate_date, notes, is_scored, score_level_id, facilitator_notes, UPDATED, UPDATEDBY, CREATED, CREATEDBY) VALUES (NULL, '.$course_id.', '.$form_fields['facilitator_id'].', 4, 2, '.$form_fields['registration_type_id'].', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '.$user_info['user_id'].')';
    $result = mysql_query($link_facilitator_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "users_courses"');
    /* get new user course id */
    $user_course_id = mysql_insert_id();
    $db_events_array[] = array(USERS_COURSES_CREATE, $user_course_id);
    $db_events_array[] = array(COURSES_ADD_USER, $course_id);
    $db_events_array[] = array(USERS_ADD_COURSE, $form_fields['facilitator_id']);
    /* create facilitator stipend order */
    $facilitator_order_query = 'INSERT INTO orders VALUES (NULL, 1, 1, 1, CURDATE(), 3, '.$course_id.', 3, '.$form_fields['facilitator_id'].', NULL, NULL, 0, 0, 0, NULL, NULL, NULL, '.$user_info['user_id'].')';
    $result = mysql_query($facilitator_order_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "orders"');
    /* get new order id */
    $order_id = mysql_insert_id();
    $db_events_array[] = array(ORDERS_CREATE, $order_id);
    /* create success message */
    $success_message = '<p>Course Successfully Created.</p>';
  }
}
vlc_insert_events($db_events_array);
/* return to course details page */
vlc_exit_page($success_message, 'success', $return_url);
?>
