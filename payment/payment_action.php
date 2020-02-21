<?php
$page_info['section'] = 'payment';
$lang = vlc_get_language();
$login_required = 1;
$user_info = vlc_get_user_info($login_required, 1);
/* get form fields */
if (isset($_POST)) $form_fields = $_POST;
$order_details_array = array();
/* the user clicked the "pay later" button on the register success page - go to "my start page" */
if (isset($form_fields['pay_later']))
{
  /* clear out "form fields" and "course details" session variables */
  $_SESSION['form_fields'] = $_SESSION['course_details'] = null;
  $redirect_to = 'profile/';
}
/* the user clicked the "pay now" button on either the register success page or the profile page - go to the payment entry page */
elseif (isset($form_fields['pay_now']))
{
  /* clear out "course details" session variable */
  $_SESSION['course_details'] = null;
  if (isset($form_fields['order_id_array']) and is_array($form_fields['order_id_array']) and count($form_fields['order_id_array']))
  {
    $db_events_array = array();
    $order_id_list = join(', ', $form_fields['order_id_array']);
    /* get amount due for new transaction */
    $order_amount_query = <<< END_QUERY
      SELECT order_id, amount_due
      FROM orders
      WHERE order_id IN ($order_id_list)
END_QUERY;
    $result = mysql_query($order_amount_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result)) $order_amount_array[$record['order_id']] = $record['amount_due'];
    /* calculate total transaction amount */
    $form_fields['transaction_amount'] = array_sum($order_amount_array);
	
    /* insert a new transaction (marked "unsuccessful") */
    $insert_transaction_query = <<< END_QUERY
      INSERT INTO transactions
      SET CREATEDBY = {$user_info['user_id']}, customer_type_id = 1, customer_id = {$user_info['user_id']}, payment_method_id = 3, transaction_status = 0, transaction_amount = {$form_fields['transaction_amount']}, transaction_date = CURDATE()
END_QUERY;
    $result = mysql_query($insert_transaction_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "transactions"');
    $form_fields['transaction_id'] = mysql_insert_id();
    $db_events_array[] = array(TRANSACTIONS_CREATE, $form_fields['transaction_id']);
    /* link the new transaction to the order(s) */
    foreach ($order_amount_array as $order_id => $order_amount)
    {
      $orders_transactions_query = 'INSERT INTO orders_transactions (CREATEDBY, order_id, transaction_id, order_transaction_amount) VALUES ('.$user_info['user_id'].', '.$order_id.', '.$form_fields['transaction_id'].', '.$order_amount.')';
      $result = mysql_query($orders_transactions_query, $site_info['db_conn']);
      if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "orders_transactions"');
      $db_events_array[] = array(ORDERS_TRANSACTIONS_CREATE, mysql_insert_id());
      $db_events_array[] = array(ORDERS_ADD_TRANSACTION, $order_id);
      $db_events_array[] = array(TRANSACTIONS_ADD_ORDER, $form_fields['transaction_id']);
    }
    vlc_insert_events($db_events_array);
    /* get order details to display on page */
    $order_details_query = <<< END_QUERY
      SELECT o.amount_due, o.order_id,
        CASE o.product_type_id
          WHEN 1 THEN CONCAT(c.description, ' (', c.code, ')')
          WHEN 6 THEN g.description
        END AS product
      FROM orders AS o
        LEFT JOIN users_courses AS uc ON o.product_id = uc.user_course_id
        LEFT JOIN courses AS c ON uc.course_id = c.course_id
        LEFT JOIN certs_users AS cu ON o.product_id = cu.cert_user_id
        LEFT JOIN cert_progs AS g ON cu.cert_prog_id = g.cert_prog_id
      WHERE o.order_id IN ($order_id_list)
      AND o.is_active = 1
      AND o.payment_status_id IN (2, 3)
END_QUERY;
    $result = mysql_query($order_details_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result)) $order_details_array[$record['order_id']] = $record;
    $form_fields['order_details_array'] = $order_details_array;
    /* store form fields and order details in session variable */
    $_SESSION['form_fields'] = $form_fields;
    $redirect_to = 'payment/';
  }
  else vlc_exit_page('<li>'.$lang['payment']['status']['no-order-selected'].'</li>', 'error', 'profile/');
}
/* the user clicked the "cancel" button on the payment entry page - go to "my start page" */
elseif (isset($form_fields['cancel']))
{
  /* clear out "form fields" and "order details" session variables */
  $_SESSION['form_fields'] = $_SESSION['order_details_array'] = null;
  vlc_exit_page($lang['payment']['status']['cancel-message'], 'success', 'profile/');
}
/* the user clicked the "done" button on the payment success page - go to "my start page" */
elseif (isset($form_fields['done']))
{
  /* clear out "form fields" and "order details" session variables */
  $_SESSION['form_fields'] = $_SESSION['order_details_array'] = null;
  $redirect_to = 'profile/';
}
/* the user clicked the "cancel" button on the online payment system - go to "my start page" */
elseif (isset($_GET['vlc_status']) and $_GET['vlc_status'] == 'cancel')
{
  /* clear out "form fields" and "order details" session variables */
  $_SESSION['form_fields'] = $_SESSION['order_details_array'] = null;
  vlc_exit_page($lang['payment']['status']['cancel-message'], 'success', 'profile/');
}
/* the user encountered an error on the online payment system - go to "my start page" */
elseif (isset($_GET['vlc_status']) and $_GET['vlc_status'] == 'error')
{
  /* send environment variables for errors */
  mail($site_info['webmaster_email'], 'Payment Error Variables', print_r($GLOBALS, true));
  /* clear out "form fields" and "order details" session variables */
  $_SESSION['form_fields'] = $_SESSION['order_details_array'] = null;
  vlc_exit_page($lang['payment']['status']['error-message'], 'success', 'profile/');
}
/* the user is coming from the online payment system - determine whether the transaction was successful and either go to the payment success page with a "success" message or go to "my start page" with a "cancel" message  */
elseif (isset($_GET['vlc_status']) and $_GET['vlc_status'] == 'success')
{
  /* set transaction status */
  if ((isset($_SESSION['form_fields']['transaction_id']) and isset($_GET['EXT_TRANS_ID']) and $_SESSION['form_fields']['transaction_id'] == $_GET['EXT_TRANS_ID'])
    and (isset($_GET['UPAY_SITE_ID']) and $_GET['UPAY_SITE_ID'] == $site_info['UPAY_SITE_ID']))
  {
    $form_fields['transaction_id'] = $_SESSION['form_fields']['transaction_id'];
    /* get order details to display on payment success page */
    $order_details_query = <<< END_QUERY
      SELECT ot.order_transaction_amount, o.order_id,
        CASE o.product_type_id
          WHEN 1 THEN CONCAT(c.description, ' (', c.code, ')')
          WHEN 6 THEN g.description
        END AS product
      FROM orders_transactions AS ot, orders AS o
        LEFT JOIN users_courses AS uc ON o.product_id = uc.user_course_id
        LEFT JOIN courses AS c ON uc.course_id = c.course_id
        LEFT JOIN certs_users AS cu ON o.product_id = cu.cert_user_id
        LEFT JOIN cert_progs AS g ON cu.cert_prog_id = g.cert_prog_id
      WHERE ot.order_id = o.order_id
      AND ot.transaction_id = {$form_fields['transaction_id']}
END_QUERY;
    $result = mysql_query($order_details_query, $site_info['db_conn']);
    /* get order details */
    while ($record = mysql_fetch_array($result))
    {
      $order_details_array[$record['order_id']] = $record;
      $order_details_array[$record['order_id']]['amount_paid'] = $record['order_transaction_amount'];
    }
    /* store course details in session variable */
    $_SESSION['order_details_array'] = $order_details_array;
    $redirect_to = 'payment/payment_success.php';
  }
  else
  {
    /* clear out "form fields" and "order details" session variables */
    $_SESSION['form_fields'] = $_SESSION['order_details_array'] = null;
    vlc_exit_page($lang['payment']['status']['cancel-message'], 'success', 'profile/');
  }
}
/* if none of the above are true, then the user is trying to access the payment action page directly - go to "my start page" */
else
{
  /* clear out "form fields" and "order details" session variables */
  $_SESSION['form_fields'] = $_SESSION['order_details_array'] = null;
  vlc_exit_page($lang['payment']['status']['cancel-message'], 'success', 'profile/');
}
/* continue to appropriate page */
vlc_redirect($redirect_to);
?>
