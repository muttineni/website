<?php
$page_info['section'] = 'cms';
$page_info['login_required'] = 1;
$user_info = vlc_get_user_info($page_info['login_required']);
$lang = vlc_get_language();
/* get form fields from posted variables */
$form_fields = $_POST;
/* initialize success message array */
$success_message_array = array();
$db_events_array = array();
/*******************************************************************************
** editing multiple transactions (coming from transactions.php)
*/
if (isset($form_fields['transaction_id_array']))
{
  /* build update queries */
  if (is_array($form_fields['transaction_id_array']) and count($form_fields['transaction_id_array']))
  {
    foreach ($form_fields['transaction_id_array'] as $transaction_id)
    {
      $transaction = $form_fields['transactions'][$transaction_id];
      /* check to see if user selected "update all checked records" */
      if (is_numeric($form_fields['update_all_transaction_status'])) $transaction['transaction_status'] = $form_fields['update_all_transaction_status'];
      if ($transaction['transaction_status'] != $transaction['previous_transaction_status'])
      {
        /* update transaction status */
        $update_transactions_query = 'UPDATE transactions SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', transaction_status = '.$transaction['transaction_status'].' WHERE transaction_id = '.$transaction_id;
        $result = mysql_query($update_transactions_query, $site_info['db_conn']);
        if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "transactions"');
        $db_events_array[] = array(TRANSACTIONS_UPDATE_STATUS_CMS, $transaction_id);
        /* update order(s) */
        $order_details_query = 'SELECT o.order_id, o.order_cost, o.amount_paid, ot.order_transaction_amount FROM orders AS o, orders_transactions AS ot WHERE o.order_id = ot.order_id AND ot.transaction_id = '.$transaction_id;
        $result = mysql_query($order_details_query, $site_info['db_conn']);
        while ($record = mysql_fetch_array($result))
        {
          /* calculate updated amount paid and amount due */
          if ($transaction['transaction_status'])
          {
            $amount_paid = $record['amount_paid'] + $record['order_transaction_amount'];
            $amount_due = $record['order_cost'] - $amount_paid;
          }
          else
          {
            $amount_paid = $record['amount_paid'] - $record['order_transaction_amount'];
            $amount_due = $record['order_cost'] - $amount_paid;
          }
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
          $update_order_query = 'UPDATE orders SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', payment_status_id = '.$payment_status_id.', amount_paid = '.$amount_paid.', amount_due = '.$amount_due.' WHERE order_id = '.$record['order_id'];
          $update_result = mysql_query($update_order_query, $site_info['db_conn']);
          if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "orders"');
          /* add order to success message */
          $success_message_array['orders'][$record['order_id']] = vlc_internal_link($record['order_id'], 'cms/order_details.php?order='.$record['order_id']);
          $db_events_array[] = array(ORDERS_UPDATE_AMOUNT_PAID, $record['order_id']);
        }
        /* add transaction to success message */
        $success_message_array['transactions'][$transaction_id] = '<a href="#transaction-'.$transaction_id.'">'.$transaction_id.'</a>';
      }
    }
  }
  /* success message */
  if (count($success_message_array))
  {
    $success_message = '<p>The following changes were saved:</p><ul>';
    if (isset($success_message_array['transactions'])) $success_message .= '<li>Update Transaction Status ('.count($success_message_array['transactions']).'): '.join(', ', $success_message_array['transactions']).'</li>';
    if (isset($success_message_array['orders'])) $success_message .= '<li>Update Related Orders - Amount Paid / Amount Due ('.count($success_message_array['orders']).'): '.join(', ', $success_message_array['orders']).'</li>';
    $success_message .= '</ul>';
  }
  else $success_message = '<p>No changes were saved.</p>';
  vlc_insert_events($db_events_array);
  /* return to search results */
  vlc_exit_page($success_message, 'success', 'cms/transactions.php?'.$_SERVER['QUERY_STRING']);
}
/*******************************************************************************
** editing a single transaction (coming from transaction_details.php)
*/
/* return url */
$return_url = 'cms/transaction_details.php?transaction='.$form_fields['transaction_id'];
/* get transaction details */
$transaction_details_query = <<< END_QUERY
  SELECT t.transaction_status, t.transaction_amount, t.payment_method_id,
    t.customer_type_id, t.customer_id,
    c.credit_id, c.credit_amount AS customer_credit_amount
  FROM transactions AS t, credits AS c
  WHERE t.customer_id = c.customer_id
  AND t.customer_type_id = c.customer_type_id
  AND t.transaction_id = {$form_fields['transaction_id']}
END_QUERY;
$result = mysql_query($transaction_details_query, $site_info['db_conn']);
$transaction_details = mysql_fetch_array($result);
/* process order updates */
if (isset($form_fields['orders']))
{
  if ($transaction_details['transaction_status'])
  {
    $order_transaction_total = 0;
    $orders = array();
    foreach ($form_fields['orders'] as $key => $order)
    {
      if (is_numeric($key) and !isset($orders[$key])) $orders[$key] = $order;
      elseif (is_numeric($order['order_id']) and is_numeric($order['order_transaction_amount']) and $order['order_transaction_amount'] > 0 and !isset($orders[$order['order_id']])) $orders[$order['order_id']] = $order;
    }
    /* get order details */
    $order_id_list = implode(', ', array_keys($orders));
    $order_details_query = <<< END_QUERY
      SELECT o.order_id, o.customer_id, o.order_cost, o.amount_paid, o.amount_due,
        IF(ot.transaction_id = {$form_fields['transaction_id']}, ot.order_transaction_id, NULL) AS order_transaction_id
      FROM orders AS o LEFT JOIN orders_transactions AS ot ON o.order_id = ot.order_id
      WHERE o.order_id IN ($order_id_list)
      ORDER BY order_transaction_id
END_QUERY;
    $result = mysql_query($order_details_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result)) $orders[$record['order_id']] = array_merge($orders[$record['order_id']], $record);
    $num_orders_updated = $num_orders_added = $num_orders_removed = 0;
    $update_query_array = $insert_query_array = $delete_query_array = array();
    foreach ($orders as $order_id => $order)
    {
      $update_order_details = $remove_order = 0;
      if (isset($order['previous_order_transaction_amount']))
      {
        $credit_issue_date = 'credit_issue_date';
        $refund_issue_date = 'refund_issue_date';
        if (is_numeric($order['order_transaction_amount']) and $order['order_transaction_amount'] > 0) $order['order_transaction_amount'] *= 100;
        else $order['order_transaction_amount'] = 0;
        $order['previous_order_transaction_amount'] *= 100;
        $order_transaction_total += $order['order_transaction_amount'];
        /* see if order should be removed from transaction */
        if ($order['order_transaction_amount'] == 0)
        {
          $update_order_details = $remove_order = 1;
          $amount_paid = $order['amount_paid'] - $order['previous_order_transaction_amount'];
          $amount_due = $order['order_cost'] - $amount_paid;
        }
        /* see if a credit should be issued */
        elseif (isset($order['issue_credit']) and !isset($order['credit_issued']) and $order['order_transaction_amount'] > 0)
        {
          /* get credit details */
          $credit_details_query = 'SELECT credit_id FROM credits WHERE customer_id = '.$transaction_details['customer_id'].' AND customer_type_id = '.$transaction_details['customer_type_id'];
          $result = mysql_query($credit_details_query, $site_info['db_conn']);
          if (mysql_num_rows($result))
          {
            $credit_details = mysql_fetch_array($result);
            $update_order_details = 1;
            $amount_paid = $order['amount_paid'] - $order['previous_order_transaction_amount'];
            $amount_due = $order['order_cost'] - $amount_paid;
            $credit_issue_date = 'CURDATE()';
            $customer_credit_amount = 'credit_amount + '.$order['order_transaction_amount'];
            $update_query_array[] = 'UPDATE credits SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', credit_amount = '.$customer_credit_amount.' WHERE credit_id = '.$credit_details['credit_id'];
            $db_events_array[] = array(ORDERS_TRANSACTIONS_ISSUE_CREDIT, $order['order_transaction_id']);
            $db_events_array[] = array(CREDITS_UPDATE, $credit_details['credit_id']);
          }
        }
        /* see if a credit should be reversed */
        elseif (isset($order['credit_issued']) and !isset($order['issue_credit']) and $transaction_details['customer_credit_amount'] >= $order['order_transaction_amount'])
        {
          /* get credit details */
          $credit_details_query = 'SELECT credit_id FROM credits WHERE customer_id = '.$transaction_details['customer_id'].' AND customer_type_id = '.$transaction_details['customer_type_id'];
          $result = mysql_query($credit_details_query, $site_info['db_conn']);
          if (mysql_num_rows($result))
          {
            $credit_details = mysql_fetch_array($result);
            $update_order_details = 1;
            $amount_paid = $order['amount_paid'] + $order['order_transaction_amount'];
            $amount_due = $order['order_cost'] - $amount_paid;
            $credit_issue_date = 'NULL';
            $customer_credit_amount = 'credit_amount - '.$order['order_transaction_amount'];
            $update_query_array[] = 'UPDATE credits SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', credit_amount = '.$customer_credit_amount.' WHERE credit_id = '.$credit_details['credit_id'];
            $db_events_array[] = array(ORDERS_TRANSACTIONS_REVERSE_CREDIT, $order['order_transaction_id']);
            $db_events_array[] = array(CREDITS_UPDATE, $credit_details['credit_id']);
          }
        }
        /* see if a refund should be issued */
        elseif (isset($order['issue_refund']) and !isset($order['refund_issued']) and $order['order_transaction_amount'] > 0)
        {
          $update_order_details = 1;
          $amount_paid = $order['amount_paid'] - $order['previous_order_transaction_amount'];
          $amount_due = $order['order_cost'] - $amount_paid;
          $refund_issue_date = 'CURDATE()';
          $db_events_array[] = array(ORDERS_TRANSACTIONS_ISSUE_REFUND, $order['order_transaction_id']);
        }
        /* see if a refund should be reversed */
        elseif (isset($order['refund_issued']) and !isset($order['issue_refund']))
        {
          $update_order_details = 1;
          $amount_paid = $order['amount_paid'] + $order['order_transaction_amount'];
          $amount_due = $order['order_cost'] - $amount_paid;
          $refund_issue_date = 'NULL';
          $db_events_array[] = array(ORDERS_TRANSACTIONS_REVERSE_REFUND, $order['order_transaction_id']);
        }
        /* see if order transaction amount has been updated */
        elseif ($order['order_transaction_amount'] != $order['previous_order_transaction_amount'])
        {
          $update_order_details = 1;
          $amount_paid = $order['amount_paid'] - $order['previous_order_transaction_amount'] + $order['order_transaction_amount'];
          $amount_due = $order['order_cost'] - $amount_paid;
        }
        /* update order details if necessary */
        if ($update_order_details)
        {
          /* get order-transaction id */
          $order_transaction_query = 'SELECT order_transaction_id FROM orders_transactions WHERE order_id = '.$order_id.' AND transaction_id = '.$form_fields['transaction_id'].' LIMIT 1';
          $result = mysql_query($order_transaction_query, $site_info['db_conn']);
          $order_transaction_id = mysql_result($result, 0);
          if ($remove_order)
          {
            $delete_query_array[] = 'DELETE FROM orders_transactions WHERE order_transaction_id = '.$order_transaction_id;
            $db_events_array[] = array(ORDERS_TRANSACTIONS_DELETE, $order_transaction_id);
            $db_events_array[] = array(TRANSACTIONS_REMOVE_ORDER, $form_fields['transaction_id']);
            $db_events_array[] = array(ORDERS_REMOVE_TRANSACTION, $order_id);
            $num_orders_removed++;
          }
          else
          {
            $update_query_array[] = 'UPDATE orders_transactions SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', order_transaction_amount = '.$order['order_transaction_amount'].', credit_issue_date = '.$credit_issue_date.', refund_issue_date = '.$refund_issue_date.' WHERE order_transaction_id = '.$order_transaction_id;
            $db_events_array[] = array(ORDERS_TRANSACTIONS_UPDATE, $order_transaction_id);
            $num_orders_updated++;
          }
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
          $update_query_array[] = 'UPDATE orders SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', payment_status_id = '.$payment_status_id.', amount_paid = '.$amount_paid.', amount_due = '.$amount_due.' WHERE order_id = '.$order_id;
          $db_events_array[] = array(ORDERS_UPDATE_AMOUNT_PAID, $order_id);
        }
      }
      elseif (isset($order['customer_id']))
      {
        $order['order_transaction_amount'] *= 100;
        $order_transaction_total += $order['order_transaction_amount'];
        $amount_paid = $order['amount_paid'] + $order['order_transaction_amount'];
        $amount_due = $order['order_cost'] - $amount_paid;
        $insert_query_array[] = 'INSERT INTO orders_transactions SET CREATED = NULL, CREATEDBY = '.$user_info['user_id'].', order_id = '.$order_id.', transaction_id = '.$form_fields['transaction_id'].', order_transaction_amount = '.$order['order_transaction_amount'];
        $db_events_array[] = array(TRANSACTIONS_ADD_ORDER, $form_fields['transaction_id']);
        $db_events_array[] = array(ORDERS_ADD_TRANSACTION, $order_id);
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
        $update_query_array[] = 'UPDATE orders SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', payment_status_id = '.$payment_status_id.', amount_paid = '.$amount_paid.', amount_due = '.$amount_due.' WHERE order_id = '.$order_id;
        $db_events_array[] = array(ORDERS_UPDATE_AMOUNT_PAID, $order_id);
        $num_orders_added++;
      }
    }
    if ($transaction_details['transaction_amount'] != $order_transaction_total) vlc_exit_page('<li>Transaction Amount must be equal to the sum of the Amount Paid for each order listed below.</li>', 'error', $return_url);
    if ($num_orders_updated or $num_orders_added or $num_orders_removed)
    {
      foreach ($update_query_array as $update_query)
      {
        $result = mysql_query($update_query, $site_info['db_conn']);
        if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED');
      }
      foreach ($delete_query_array as $delete_query)
      {
        $result = mysql_query($delete_query, $site_info['db_conn']);
        if (mysql_affected_rows() < 1) trigger_error('DELETE FAILED');
      }
      foreach ($insert_query_array as $insert_query)
      {
        $result = mysql_query($insert_query, $site_info['db_conn']);
        if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED');
        else $db_events_array[] = array(ORDERS_TRANSACTIONS_CREATE, mysql_insert_id());
      }
      vlc_insert_events($db_events_array);
      vlc_exit_page($num_orders_added.' Order(s) Added, '.$num_orders_updated.' Order(s) Updated, '.$num_orders_removed.' Order(s) Removed.', 'success', $return_url);
    }
    else vlc_exit_page('<li>No updates were made or updates were invalid.</li>', 'error', $return_url);
  }
  else vlc_exit_page('<li>Orders related to this transaction cannot be edited because Transaction Status is &quot;Unsuccessful&quot;.</li>', 'error', $return_url);
}
/* get transaction details */
if ($form_fields['payment_method_id'] == 2) $check_number = $form_fields['check_number'];
else $check_number = '';
$num_orders_updated = 0;
$update_query_array = array();
/* see if transaction status has been updated */
if ($form_fields['transaction_status'] != $transaction_details['transaction_status'])
{
  $order_details_query = <<< END_QUERY
    SELECT o.order_id, o.order_cost, o.amount_paid, ot.order_transaction_amount
    FROM orders AS o, orders_transactions AS ot
    WHERE o.order_id = ot.order_id
    AND ot.transaction_id = {$form_fields['transaction_id']}
END_QUERY;
  $result = mysql_query($order_details_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result))
  {
    if ($form_fields['transaction_status'])
    {
      $amount_paid = $record['amount_paid'] + $record['order_transaction_amount'];
      $amount_due = $record['order_cost'] - $amount_paid;
    }
    else
    {
      $amount_paid = $record['amount_paid'] - $record['order_transaction_amount'];
      $amount_due = $record['order_cost'] - $amount_paid;
    }
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
    $update_query_array[] = 'UPDATE orders SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', payment_status_id = '.$payment_status_id.', amount_paid = '.$amount_paid.', amount_due = '.$amount_due.' WHERE order_id = '.$record['order_id'];
    $num_orders_updated++;
    $db_events_array[] = array(ORDERS_UPDATE_AMOUNT_PAID, $record['order_id']);
  }
  $db_events_array[] = array(TRANSACTIONS_UPDATE_STATUS_CMS, $form_fields['transaction_id']);
}
/* see if payment method has been updated */
if ($form_fields['payment_method_id'] != $transaction_details['payment_method_id'] and $transaction_details['payment_method_id'] == 4) $update_query_array[] = 'UPDATE credits SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', credit_amount = credit_amount + '.$transaction_details['transaction_amount'].' WHERE customer_id = '.$transaction_details['customer_id'].' AND customer_type_id = '.$transaction_details['customer_type_id'];
/* process update queries */
foreach ($update_query_array as $update_query)
{
  $result = mysql_query($update_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED');
}
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = addslashes($value);
}
/* update transaction details */
$update_transaction_query = <<< END_QUERY
  UPDATE transactions
  SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']},
    transaction_status = {$form_fields['transaction_status']},
    payment_method_id = {$form_fields['payment_method_id']},
    check_number = NULLIF('$check_number', ''),
    notes = NULLIF('{$form_fields['notes']}', '')
  WHERE transaction_id = {$form_fields['transaction_id']}
END_QUERY;
$result = mysql_query($update_transaction_query, $site_info['db_conn']);
if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "transactions"');
$db_events_array[] = array(TRANSACTIONS_UPDATE, $form_fields['transaction_id']);
vlc_insert_events($db_events_array);
/* create success message */
$success_message = '<p>Transaction Details Updated.</p>';
if ($num_orders_updated) $success_message .= '<p>'.$num_orders_updated.' Order(s) Updated.</p>';
/* return to transaction details page */
vlc_exit_page($success_message, 'success', $return_url);
?>
