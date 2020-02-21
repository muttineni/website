<?php
$page_info['section'] = 'payment';
$lang = vlc_get_language();
/* get form fields from "post" vars */
$form_fields = $_POST;
if ($form_fields['pmt_status'] == 'success')
{
  /* get transaction id from "EXT_TRANS_ID" */
  $form_fields['transaction_id'] = $form_fields['EXT_TRANS_ID'];
  $form_fields['pmt_amt'] = $form_fields['pmt_amt'] * 100;
  $form_fields['pmt_date'] = substr($form_fields['pmt_date'], 6, 4).'-'.substr($form_fields['pmt_date'], 0, 2).'-'.substr($form_fields['pmt_date'], 3, 2);
  $form_fields['name_on_acct'] = addslashes($form_fields['name_on_acct']);
  /* insert transaction report data */
  $insert_transaction_report_query = <<< END_QUERY
    INSERT INTO transaction_reports
    SET transaction_id = {$form_fields['transaction_id']}, transaction_type_id = 1, transaction_amount = {$form_fields['pmt_amt']}, transaction_date = '{$form_fields['pmt_date']}', transaction_report_status = 1,
      CARD_TYPE = '{$form_fields['card_type']}', AMOUNT = {$form_fields['pmt_amt']}, REFERENCE_ID = '{$form_fields['tpg_trans_id']}', CARD_NAME = '{$form_fields['name_on_acct']}',
      CREATEDBY = NULL
END_QUERY;
  $result = mysql_query($insert_transaction_report_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "transaction_reports"');
  /* update transaction status */
  $update_transaction_query = <<< END_QUERY
    UPDATE transactions
    SET transaction_status = 1
    WHERE transaction_id = {$form_fields['transaction_id']}
END_QUERY;
  $result = mysql_query($update_transaction_query, $site_info['db_conn'])or die(mysql_error());
  /* use mysql_affected_rows() to see if the query was executed successfully (even if no rows were updated) */
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "transactions"');
  /* track database update */
  $db_events_array = array();
  $db_events_array[] = array(TRANSACTIONS_UPDATE_STATUS_PMT, $form_fields['transaction_id']);
  vlc_insert_events($db_events_array);
  /* get user details - assuming customer type is student or facilitator */
  $user_details_query = <<< END_QUERY
    SELECT u.first_name, u.last_name, u.username, i.primary_email
    FROM transactions AS t, users AS u, user_info AS i
    WHERE t.customer_id = u.user_id
    AND u.user_id = i.user_id
    AND t.transaction_id = {$form_fields['transaction_id']}
END_QUERY;
  $result = mysql_query($user_details_query, $site_info['db_conn']);
  $user_details = mysql_fetch_array($result);
  /* get order details */
  $order_details_query = <<< END_QUERY
    SELECT ot.order_transaction_amount, o.order_id, o.order_cost, o.amount_paid, o.amount_due,
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
  /* update order details - a single transaction can be applied to multiple orders */
  $order_summary_array = array();
  $total_amount_paid = 0;
  while ($record = mysql_fetch_array($result))
  {
    /* update amount paid and amount due */
    $record['amount_paid'] += $record['order_transaction_amount'];
    $record['amount_due'] -= $record['order_transaction_amount'];
    /* update payment status */
    if ($record['amount_paid'] == 0)
    {
      /* no charge */
      if ($record['amount_due'] == 0) $payment_status_id = 1;
      /* not paid */
      else $payment_status_id = 2;
    }
    else
    {
      /* paid */
      if ($record['amount_due'] == 0) $payment_status_id = 4;
      /* partial payment */
      elseif ($record['amount_due'] > 0) $payment_status_id = 3;
      /* over payment */
      else $payment_status_id = 5;
    }
    $update_order_query = <<< END_QUERY
      UPDATE orders
      SET payment_status_id = $payment_status_id, amount_paid = {$record['amount_paid']}, amount_due = {$record['amount_due']}
      WHERE order_id = {$record['order_id']}
END_QUERY;
    $update_result = mysql_query($update_order_query, $site_info['db_conn']);
    $order_summary_array[] = $record['product'].' ... $'.number_format($record['order_transaction_amount'] / 100, 2);
    $total_amount_paid += $record['order_transaction_amount'];
    $order_details_array[$record['order_id']] = $record;
    $order_details_array[$record['order_id']]['amount_paid'] = $record['order_transaction_amount'];
  }
  /* format order total */
  $total_amount_paid = '$'.number_format($total_amount_paid / 100, 2);
  $order_summary_array[] = '----------------';
  $order_summary_array[] = $lang['payment']['common']['misc']['total-label'].': '.$total_amount_paid;
  $order_summary_list = join("\n", $order_summary_array);
  /* send e-mail messages */
  $subject = $lang['payment']['email']['success']['subject'];
  $message = sprintf($lang['payment']['email']['success']['message'], $user_details['first_name'], $order_summary_list, $user_details['username'], $user_details['primary_email']);
  /* send message to user from administrator */
  $from = 'From: "'.$lang['common']['misc']['vlcff-admin'].'" <'.$site_info['vlcff_email'].'>';
  $to = $user_details['primary_email'];
  mail($to, $subject, $message, $from);
  /* send additional message to administrator from user */
  $from = 'From: "'.$user_details['first_name'].' '.$user_details['last_name'].'" <'.$user_details['primary_email'].'>';
  $to = $site_info['webmaster_email'].', '.$site_info['billing_email'];
  mail($to, $subject, $message, $from);
  /* send environment variables for approved/successful transactions */
  mail($site_info['webmaster_email'], 'Payment Action Variables', print_r($GLOBALS, true));
}
else
{
  mail($site_info['webmaster_email'], 'Payment Status Variables', print_r($GLOBALS, true));
}
/* exit */
exit;
?>
