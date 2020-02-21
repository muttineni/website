<?php
$page_info['section'] = 'cms';
$page_info['login_required'] = 1;
$user_info = vlc_get_user_info($page_info['login_required']);
$lang = vlc_get_language();
/* get form fields from posted variables */
$form_fields = $_POST;
/* return url */
if (isset($form_fields['order_id'])) $return_url = 'cms/order_details.php?order='.$form_fields['order_id'];
elseif (isset($form_fields['is_stu_reg_order'])) $return_url = 'cms/student_orders.php?'.$_SERVER['QUERY_STRING'];
else $return_url = 'cms/orders.php?'.$_SERVER['QUERY_STRING'];
/* initialize success message array and query array */
$orders = $success_message_array = $db_events_array = $insert_query_array = $update_query_array = array();
/* update orders */
if (isset($form_fields['order_id_array']) and is_array($form_fields['order_id_array']) and count($form_fields['order_id_array']))
{
  /* get order details */
  $order_id_list = join(', ', $form_fields['order_id_array']);
  if (isset($form_fields['is_stu_reg_order']))
  {
    $order_details_query = <<< END_QUERY
    SELECT o.order_id, o.is_active AS previous_order_status, IF(o.discount_type_id IS NULL, 'NULL', o.discount_type_id * 10000 + o.discount_id) AS previous_discount_type_id,
      o.product_type_id, o.product_id, o.customer_type_id, o.customer_id, o.order_cost, o.amount_paid,
      uc.course_id AS previous_course_id, uc.course_status_id AS previous_course_status_id
    FROM orders AS o, users_courses AS uc
    WHERE o.product_id = uc.user_course_id
    AND o.product_type_id = 1
    AND o.customer_id = uc.user_id
    AND o.customer_type_id = 1
    AND o.order_id IN ($order_id_list)
END_QUERY;
  }
  else
  {
    $order_details_query = <<< END_QUERY
      SELECT order_id, is_active AS previous_order_status, order_cost, amount_paid,
        product_type_id, product_id, customer_type_id, customer_id,
        IF(discount_type_id IS NULL, 'NULL', discount_type_id * 10000 + discount_id) AS previous_discount_type_id
      FROM orders
      WHERE order_id IN ($order_id_list)
END_QUERY;
  }
  $result = mysql_query($order_details_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) $orders[$record['order_id']] = array_merge($form_fields['orders'][$record['order_id']], $record);
  $course_details = array();
  if (isset($form_fields['is_stu_reg_order']))
  {
    $course_id_array = array();
    foreach ($orders as $order)
    {
      $course_id_array[] = $order['course_id'];
      $course_id_array[] = $order['previous_course_id'];
    }
    $course_id_list = join(', ', array_unique($course_id_array));
    $course_type_query = <<< END_QUERY
      SELECT c.course_id, t.course_type_id, t.partner_cost, t.non_partner_cost
      FROM courses AS c, course_subjects AS s, course_types AS t
      WHERE c.course_subject_id = s.course_subject_id
        AND s.course_type_id = t.course_type_id
        AND c.course_id IN ($course_id_list)
END_QUERY;
    $result = mysql_query($course_type_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result)) $course_details[$record['course_id']] = $record;
  }
  else
  {
    $user_course_id_array = array();
    foreach ($orders as $order)
    {
      if ($order['product_type_id'] == 1) $user_course_id_array[] = $order['product_id'];
    }
    if (count($user_course_id_array))
    {
      $user_course_id_list = join(', ', array_unique($user_course_id_array));
      $course_type_query = <<< END_QUERY
        SELECT c.course_id, t.course_type_id, t.partner_cost, t.non_partner_cost
        FROM users_courses AS uc, courses AS c, course_subjects AS s, course_types AS t
        WHERE uc.course_id = c.course_id
          AND c.course_subject_id = s.course_subject_id
          AND s.course_type_id = t.course_type_id
          AND uc.user_course_id IN ($user_course_id_list)
END_QUERY;
      $result = mysql_query($course_type_query, $site_info['db_conn']);
      while ($record = mysql_fetch_array($result)) $course_details[$record['course_id']] = $record;
    }
  }
  foreach ($orders as $order_id => $order)
  {
    $amount_paid = $order['amount_paid'];
    /* check to see if user selected "update all checked records" */
    if (isset($form_fields['update_all_order_status']) and is_numeric($form_fields['update_all_order_status'])) $order['order_status'] = $form_fields['update_all_order_status'];
    if (isset($form_fields['update_all_course_status_id']) and is_numeric($form_fields['update_all_course_status_id'])) $order['course_status_id'] = $form_fields['update_all_course_status_id'];
    if (isset($form_fields['update_all_course_id']) and is_numeric($form_fields['update_all_course_id'])) $order['course_id'] = $form_fields['update_all_course_id'];
    if (isset($form_fields['update_all_discount_type_id']) and is_numeric($form_fields['update_all_discount_type_id'])) $order['discount_type_id'] = $form_fields['update_all_discount_type_id'];
    /* update order status */
    if (isset($order['order_status']))
    {
      if ($order['order_status'] != $order['previous_order_status'])
      {
        $update_orders_query = 'UPDATE orders SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', is_active = '.$order['order_status'].' WHERE order_id = '.$order_id;
        $success_message_array['order-status'][$order_id] = '<a href="#order-'.$order_id.'">'.$order_id.'</a>';
        $db_events_array[] = array(ORDERS_UPDATE_ORDER_STATUS, $order_id);
      }
    }
    else $order['order_status'] = 'is_active';
    /* process credits */
    if (isset($order['transactions']))
    {
      $update_order_details = 0;
      /* get transaction details */
      $transaction_details_query = <<< END_QUERY
        SELECT ot.order_transaction_id, ot.transaction_id, ot.order_transaction_amount,
          t.customer_type_id, t.customer_id,
          c.credit_id, c.credit_amount AS customer_credit_amount
        FROM orders_transactions AS ot, transactions AS t, credits AS c
        WHERE ot.transaction_id = t.transaction_id
        AND t.customer_type_id = c.customer_type_id
        AND t.customer_id = c.customer_id
        AND ot.order_id = $order_id
END_QUERY;
      $result = mysql_query($transaction_details_query, $site_info['db_conn']);
      while ($record = mysql_fetch_array($result))
      {
        if (isset($order['transactions'][$record['transaction_id']])) $transaction = $order['transactions'][$record['transaction_id']];
        else continue;
        $credit_issue_date = 'credit_issue_date';
        $refund_issue_date = 'refund_issue_date';
        $update_credit = $update_refund = 0;
        /* issue credit */
        if (isset($transaction['issue_credit']) and !isset($transaction['credit_issued']) and $record['order_transaction_amount'] > 0)
        {
          $customer_credit_amount = 'credit_amount + '.$record['order_transaction_amount'];
          $credit_issue_date = 'CURDATE()';
          $amount_paid -= $record['order_transaction_amount'];
          $update_credit = 1;
          $db_events_array[] = array(ORDERS_TRANSACTIONS_ISSUE_CREDIT, $record['order_transaction_id']);
          $db_events_array[] = array(CREDITS_UPDATE, $record['credit_id']);
          $success_message_array['issue-credit'][$order_id] = '<a href="#order-'.$order_id.'">'.$order_id.'</a>';
        }
        /* reverse credit */
        elseif (isset($transaction['credit_issued']) and !isset($transaction['issue_credit']) and $record['customer_credit_amount'] >= $record['order_transaction_amount'])
        {
          $customer_credit_amount = 'credit_amount - '.$record['order_transaction_amount'];
          $credit_issue_date = 'NULL';
          $amount_paid += $record['order_transaction_amount'];
          $update_credit = 1;
          $db_events_array[] = array(ORDERS_TRANSACTIONS_REVERSE_CREDIT, $record['order_transaction_id']);
          $db_events_array[] = array(CREDITS_UPDATE, $record['credit_id']);
          $success_message_array['reverse-credit'][$order_id] = '<a href="#order-'.$order_id.'">'.$order_id.'</a>';
        }
        /* issue refund */
        elseif (isset($transaction['issue_refund']) and !isset($transaction['refund_issued']) and $record['order_transaction_amount'] > 0)
        {
          $refund_issue_date = 'CURDATE()';
          $amount_paid -= $record['order_transaction_amount'];
          $update_refund = 1;
          $db_events_array[] = array(ORDERS_TRANSACTIONS_ISSUE_REFUND, $record['order_transaction_id']);
          $success_message_array['issue-refund'][$order_id] = '<a href="#order-'.$order_id.'">'.$order_id.'</a>';
        }
        /* reverse refund */
        elseif (isset($transaction['refund_issued']) and !isset($transaction['issue_refund']))
        {
          $refund_issue_date = 'NULL';
          $amount_paid += $record['order_transaction_amount'];
          $update_refund = 1;
          $db_events_array[] = array(ORDERS_TRANSACTIONS_REVERSE_REFUND, $record['order_transaction_id']);
          $success_message_array['reverse-refund'][$order_id] = '<a href="#order-'.$order_id.'">'.$order_id.'</a>';
        }
        if ($update_credit or $update_refund)
        {
          $update_order_details = 1;
          $update_query_array[] = 'UPDATE orders_transactions SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', credit_issue_date = '.$credit_issue_date.', refund_issue_date = '.$refund_issue_date.' WHERE order_transaction_id = '.$record['order_transaction_id'];
          if ($update_credit) $update_query_array[] = 'UPDATE credits SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', credit_amount = '.$customer_credit_amount.' WHERE credit_id = '.$record['credit_id'];
        }
      }
      if ($update_order_details)
      {
        /* calculate updated amount paid and amount due */
        $amount_due = $order['order_cost'] - $amount_paid;
        /* update payment status */
        if ($amount_paid == 0)
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
        /* update orders table */
        $update_orders_query = 'UPDATE orders SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', is_active = '.$order['order_status'].', payment_status_id = '.$payment_status_id.', amount_paid = '.$amount_paid.', amount_due = '.$amount_due.' WHERE order_id = '.$order_id;
        $db_events_array[] = array(ORDERS_UPDATE_AMOUNT_PAID, $order_id);
      }
    }
    /* process new transactions */
    if (isset($order['transaction_amount']) and is_numeric($order['transaction_amount']) and $order['transaction_amount'] > 0)
    {
      $transaction_is_valid = 1;
      $order['transaction_amount'] = $order['transaction_amount'] * 100;
      if ($order['payment_method_id'] != 2) $order['check_number'] = '';
      if ($order['payment_method_id'] == 4)
      {
        /* get user's credit details */
        $credit_details_query = 'SELECT credit_id, credit_amount FROM credits WHERE customer_type_id = '.$order['customer_type_id'].' AND customer_id = '.$order['customer_id'];
        $result = mysql_query($credit_details_query, $site_info['db_conn']);
        if (mysql_num_rows($result)) $credit_details = mysql_fetch_array($result);
        else $credit_details = array('credit_amount' => 0);
        /* if transaction amount is more than the user's credit amount, do not process the transaction */
        if ($credit_details['credit_amount'] >= $order['transaction_amount'])
        {
          $update_query_array[] = 'UPDATE credits SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', credit_amount = credit_amount - '.$order['transaction_amount'].' WHERE credit_id = '.$credit_details['credit_id'];
          $db_events_array[] = array(CREDITS_UPDATE, $credit_details['credit_id']);
        }
        else $transaction_is_valid = 0;
      }
      if ($transaction_is_valid)
      {
        /* insert transaction */
        $insert_query_array[] = <<< END_QUERY
          INSERT INTO transactions
          SET CREATED = NULL, CREATEDBY = {$user_info['user_id']},
            customer_type_id = {$order['customer_type_id']}, customer_id = {$order['customer_id']},
            transaction_status = 1, transaction_date = CURDATE(), transaction_amount = {$order['transaction_amount']},
            payment_method_id = {$order['payment_method_id']}, check_number = NULLIF('{$order['check_number']}', '')
END_QUERY;
        $insert_query_array[] = 'SELECT '.TRANSACTIONS_CREATE.' AS event_type_id, LAST_INSERT_ID() AS new_id';
        $insert_query_array[] = 'SELECT '.TRANSACTIONS_ADD_ORDER.' AS event_type_id, LAST_INSERT_ID() AS new_id';
        /* link transaction to order */
        $insert_query_array[] = <<< END_QUERY
          INSERT INTO orders_transactions
          SET CREATED = NULL, CREATEDBY = {$user_info['user_id']},
            order_id = $order_id,
            transaction_id = {LAST_INSERT_ID},
            order_transaction_amount = {$order['transaction_amount']}
END_QUERY;
        $insert_query_array[] = 'SELECT '.ORDERS_TRANSACTIONS_CREATE.' AS event_type_id, LAST_INSERT_ID() AS new_id';
        $db_events_array[] = array(ORDERS_ADD_TRANSACTION, $order_id);
        /* calculate amount paid and amount due */
        $amount_paid += $order['transaction_amount'];
        $amount_due = $order['order_cost'] - $amount_paid;
        /* update payment status */
        if ($amount_paid == 0)
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
        /* update orders table (this query will be overwritten below if discount type has been updated) */
        $update_orders_query = 'UPDATE orders SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', is_active = '.$order['order_status'].', payment_status_id = '.$payment_status_id.', amount_paid = '.$amount_paid.', amount_due = '.$amount_due.' WHERE order_id = '.$order_id;
        $success_message_array['transaction'][$order_id] = '<a href="#order-'.$order_id.'">'.$order_id.'</a>';
        $db_events_array[] = array(ORDERS_UPDATE_AMOUNT_PAID, $order_id);
      }
    }
    /* update users_courses if course status has changed or if course has changed */
    $update_order_cost = 0;
    if (isset($form_fields['is_stu_reg_order']) and (($order['course_status_id'] != $order['previous_course_status_id']) or ($order['course_id'] != $order['previous_course_id'])))
    {
      $update_query_array[] = 'UPDATE users_courses SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', course_id = '.$order['course_id'].', course_status_id = '.$order['course_status_id'].' WHERE user_course_id = '.$order['product_id'];
      /* add order to success message */
      $success_message_array['users-courses'][$order_id] = '<a href="#order-'.$order_id.'">'.$order_id.'</a>';
      $db_events_array[] = array(USERS_COURSES_UPDATE, $order['product_id']);
      /* if either the new course or the old course is a seminar, update order cost (in other words, course changed from course to seminar or seminar to course and order cost should be updated) */
      if ($course_details[$order['course_id']]['course_type_id'] == 1 xor $course_details[$order['previous_course_id']]['course_type_id'] == 1)
      {
        $update_order_cost = 1;
      }
    }
    /* update discount type only for course registrations where customer is student */
    if (((isset($order['discount_type_id']) and $order['discount_type_id'] != $order['previous_discount_type_id']) or ($update_order_cost)) and $order['product_type_id'] == 1 and $order['customer_type_id'] == 1)
    {
      /* get new discount rate/cost */
      if (is_numeric($order['discount_type_id']) and $order['discount_type_id'] % 10000 > 0)
      {
        $discount_type_id = floor($order['discount_type_id'] / 10000);
        $discount_id = $order['discount_type_id'] % 10000;
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
          if ($course_details[$order['course_id']]['course_type_id'] == 1) $order_cost = $discount_details['student_seminar_cost'];
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
            if ($course_details[$order['course_id']]['course_type_id'] == 1) $order_cost = $discount_details['student_seminar_cost'];
            else $order_cost = $discount_details['student_course_cost'];
          }
          else $order_cost = $course_details[$order['course_id']]['partner_cost'];
        }
      }
      else
      {
        $discount_type_id = $discount_id = 'NULL';
        $order_cost = $course_details[$order['course_id']]['non_partner_cost'];
      }
      /* calculate amount due */
      $amount_due = $order_cost - $amount_paid;
      /* update payment status */
      if ($amount_paid == 0)
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
      /* previous partner order */
      if (is_numeric($order['previous_discount_type_id']) and $order['previous_discount_type_id'] % 10000 > 0)
      {
        $previous_discount_type_id = floor($order['previous_discount_type_id'] / 10000);
        $previous_discount_id = $order['previous_discount_type_id'] % 10000;
        if ($previous_discount_type_id == 1)
        {
          $previous_discount_details_query = "SELECT IFNULL(partner_id, 'NULL') AS partner_id FROM payment_codes WHERE payment_code_id = $previous_discount_id";
          $result = mysql_query($previous_discount_details_query, $site_info['db_conn']);
          $previous_discount_details = mysql_fetch_array($result);
          if (is_numeric($previous_discount_details['partner_id'])) $previous_partner_id = $previous_discount_details['partner_id'];
        }
        else $previous_partner_id = $previous_discount_id;
        /* get previous partner order id */
        if (isset($previous_partner_id))
        {
          $partner_order_query = 'SELECT order_id FROM orders WHERE customer_type_id = 2 AND customer_id = '.$previous_partner_id.' AND product_type_id = '.$order['product_type_id'].' AND product_id = '.$order['product_id'].' AND is_active = 1';
          $result = mysql_query($partner_order_query, $site_info['db_conn']);
          if (mysql_num_rows($result))
          {
            $partner_order_id_array = array();
            while ($record = mysql_fetch_array($result))
            {
              $partner_order_id_array[] = $record['order_id'];
              $db_events_array[] = array(ORDERS_UPDATE_ORDER_STATUS, $record['order_id']);
            }
            $update_query_array[] = 'UPDATE orders SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', is_active = 0 WHERE order_id IN ('.join(', ', $partner_order_id_array).')';
            $success_message_array['update-partner-order'][$order_id] = '<a href="#order-'.$order_id.'">'.$order_id.'</a>';
          }
        }
      }
      /* new partner order */
      if (($discount_type_id == 1 and isset($discount_details['partner_id'])) or ($discount_type_id > 1 and isset($discount_details['student_seminar_cost'])))
      {
        if ($course_details[$order['course_id']]['course_type_id'] == 1) $order['partner_cost'] = $discount_details['partner_seminar_cost'];
        else $order['partner_cost'] = $discount_details['partner_course_cost'];
        if ($order['partner_cost'] > 0)
        {
          $insert_query_array[] = 'INSERT INTO orders (CREATED, CREATEDBY, product_type_id, product_id, customer_type_id, customer_id, is_active, is_complete, payment_status_id, order_date, discount_type_id, discount_id, order_cost, amount_paid, amount_due) VALUES (NULL, '.$user_info['user_id'].', '.$order['product_type_id'].', '.$order['product_id'].', 2, '.$discount_details['partner_id'].', 1, 1, 2, CURDATE(), '.$discount_type_id.', '.$discount_id.', '.$order['partner_cost'].', 0, '.$order['partner_cost'].')';
          /* get new order id and event type id */
          $insert_query_array[] = 'SELECT '.ORDERS_CREATE.' AS event_type_id, LAST_INSERT_ID() AS new_id';
          $success_message_array['insert-partner-order'][$order_id] = '<a href="#order-'.$order_id.'">'.$order_id.'</a>';
        }
      }
      /* update order query */
      $update_orders_query = 'UPDATE orders SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', is_active = '.$order['order_status'].', payment_status_id = '.$payment_status_id.', discount_type_id = '.$discount_type_id.', discount_id = '.$discount_id.', order_cost = '.$order_cost.', amount_paid = '.$amount_paid.', amount_due = '.$amount_due.' WHERE order_id = '.$order_id;
      $success_message_array['discount-type'][$order_id] = '<a href="#order-'.$order_id.'">'.$order_id.'</a>';
      $db_events_array[] = array(ORDERS_UPDATE_DISCOUNT_TYPE, $order_id);
    }
    /* update orders table if necessary */
    if (isset($update_orders_query))
    {
      $update_query_array[] = $update_orders_query;
      $db_events_array[] = array(ORDERS_UPDATE, $order_id);
    }
  }
  /* execute insert queries */
  $last_insert_id = '';
  foreach ($insert_query_array as $insert_query)
  {
    $insert_query = str_replace('{LAST_INSERT_ID}', $last_insert_id, $insert_query);
    $result = mysql_query($insert_query, $site_info['db_conn']);
    if (is_resource($result))
    {
      $record = mysql_fetch_array($result);
      $last_insert_id = $record['new_id'];
      $db_events_array[] = array($record['event_type_id'], $last_insert_id);
    }
    elseif (mysql_affected_rows() < 1) trigger_error('INSERT FAILED');
  }
  /* execute update queries */
  foreach ($update_query_array as $update_query) $result = mysql_query($update_query, $site_info['db_conn']);
  vlc_insert_events($db_events_array);
}
/* return message */
if (count($success_message_array))
{
  $success_message = '<p>The following changes were saved:</p><ul>';
  if (isset($success_message_array['order-status'])) $success_message .= '<li>Update Order Status ('.count($success_message_array['order-status']).'): '.join(', ', $success_message_array['order-status']).'</li>';
  if (isset($success_message_array['issue-credit'])) $success_message .= '<li>Issue Credit ('.count($success_message_array['issue-credit']).'): '.join(', ', $success_message_array['issue-credit']).'</li>';
  if (isset($success_message_array['issue-refund'])) $success_message .= '<li>Issue Refund ('.count($success_message_array['issue-refund']).'): '.join(', ', $success_message_array['issue-refund']).'</li>';
  if (isset($success_message_array['reverse-credit'])) $success_message .= '<li>Reverse Credit ('.count($success_message_array['reverse-credit']).'): '.join(', ', $success_message_array['reverse-credit']).'</li>';
  if (isset($success_message_array['reverse-refund'])) $success_message .= '<li>Reverse Refund ('.count($success_message_array['reverse-refund']).'): '.join(', ', $success_message_array['reverse-refund']).'</li>';
  if (isset($success_message_array['transaction'])) $success_message .= '<li>Enter Transaction ('.count($success_message_array['transaction']).'): '.join(', ', $success_message_array['transaction']).'</li>';
  if (isset($success_message_array['discount-type'])) $success_message .= '<li>Update Discount Type ('.count($success_message_array['discount-type']).'): '.join(', ', $success_message_array['discount-type']).'</li>';
  if (isset($success_message_array['update-partner-order'])) $success_message .= '<li>Update Partner Order ('.count($success_message_array['update-partner-order']).'): '.join(', ', $success_message_array['update-partner-order']).'</li>';
  if (isset($success_message_array['insert-partner-order'])) $success_message .= '<li>Create Partner Order ('.count($success_message_array['insert-partner-order']).'): '.join(', ', $success_message_array['insert-partner-order']).'</li>';
  if (isset($success_message_array['users-courses'])) $success_message .= '<li>Update Course or Course Status ('.count($success_message_array['users-courses']).'): '.join(', ', $success_message_array['users-courses']).'</li>';
  $success_message .= '</ul>';
}
else $success_message = '<p>No changes were saved.</p>';
/* return */
vlc_exit_page($success_message, 'success', $return_url);
?>
