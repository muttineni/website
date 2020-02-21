<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'order-details';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get order id */
$order_id = intval($_GET['order']);
/* begin output variable */
$output = '';
/* return to previous page link */
if (isset($_SERVER['HTTP_REFERER']) and $return_url = strstr($_SERVER['HTTP_REFERER'], 'cms/')) $output .= '<p>'.vlc_internal_link('Return to Previous Page', $return_url).'</p>';
/* get order details */
$order_query = <<< END_QUERY
  SELECT o.order_id, o.is_active AS order_status, y.description AS payment_status,
    IFNULL(o.discount_type_id, 0) AS discount_type_id, IFNULL(o.discount_id, 0) AS discount_id,
    UNIX_TIMESTAMP(o.order_date) AS order_date_timestamp,
    o.order_cost, o.amount_paid, o.amount_due, e.credit_amount,
    o.product_type_id, t.description AS product_type, o.product_id,
    CASE o.product_type_id
      WHEN 1 THEN CONCAT(u.first_name, ' ', u.last_name, ' - ', c.description)
      WHEN 2 THEN r.title
      WHEN 3 THEN n.description
      WHEN 4 THEN b.description
      WHEN 6 THEN CONCAT(h.first_name, ' ', h.last_name, ' - ', g.description)
    END AS product,
    o.customer_type_id, s.description AS customer_type, o.customer_id,
    IF(o.customer_type_id = 2, p.description, CONCAT(m.first_name, ' ', m.last_name)) AS customer,
    uc.course_id
  FROM orders AS o
    LEFT JOIN users_courses AS uc ON o.product_id = uc.user_course_id
    LEFT JOIN users AS u ON uc.user_id = u.user_id
    LEFT JOIN courses AS c ON uc.course_id = c.course_id
    LEFT JOIN users AS m ON o.customer_id = m.user_id
    LEFT JOIN courses AS n ON o.product_id = n.course_id
    LEFT JOIN resources AS r ON o.product_id = r.resource_id
    LEFT JOIN course_subjects AS b ON o.product_id = b.course_subject_id
    LEFT JOIN partners AS p ON o.customer_id = p.partner_id
    LEFT JOIN credits AS e ON o.customer_id = e.customer_id AND o.customer_type_id = e.customer_type_id
    LEFT JOIN certs_users AS cu ON o.product_id = cu.cert_user_id
    LEFT JOIN cert_progs AS g ON cu.cert_prog_id = g.cert_prog_id
    LEFT JOIN users AS h ON cu.user_id = h.user_id,
    customer_types AS s, product_types AS t, payment_status AS y
  WHERE o.customer_type_id = s.customer_type_id
  AND o.product_type_id = t.product_type_id
  AND o.payment_status_id = y.payment_status_id
  AND o.order_id = $order_id
END_QUERY;
$result = mysql_query($order_query, $site_info['db_conn']);
$order_details = mysql_fetch_array($result);
/* get transactions linked to this order */
$transaction_query = <<< END_QUERY
  SELECT t.transaction_id, t.transaction_status, m.description AS payment_method, IFNULL(t.check_number, '&nbsp;') AS check_number,
    IF(t.transaction_status, 'Successful', 'Unsuccessful') AS transaction_status_desc,
    UNIX_TIMESTAMP(t.transaction_date) AS transaction_date,
    ot.order_transaction_id, ot.order_transaction_amount,
    UNIX_TIMESTAMP(ot.credit_issue_date) AS credit_issue_date,
    UNIX_TIMESTAMP(ot.refund_issue_date) AS refund_issue_date,
    IF(t.customer_type_id = 2, p.description, CONCAT(c.first_name, ' ', c.last_name)) AS customer
  FROM orders_transactions AS ot, transactions AS t
      LEFT JOIN users AS c ON t.customer_id = c.user_id
      LEFT JOIN partners AS p ON t.customer_id = p.partner_id
      LEFT JOIN credits AS e ON t.customer_id = e.customer_id AND t.customer_type_id = e.customer_type_id,
    payment_methods AS m
  WHERE ot.transaction_id = t.transaction_id
  AND t.payment_method_id = m.payment_method_id
  AND ot.order_id = $order_id
END_QUERY;
$result = mysql_query($transaction_query, $site_info['db_conn']);
$transaction_details = array();
$ot_id_array = array();
while ($record = mysql_fetch_array($result))
{
  $transaction_details[$record['transaction_id']] = $record;
  $ot_id_array[] = $record['order_transaction_id'];
}
/* get event details */
$event_type_array_1 = array(
  ORDERS_CREATE,
  ORDERS_UPDATE,
  ORDERS_ADD_TRANSACTION,
  ORDERS_REMOVE_TRANSACTION,
  ORDERS_UPDATE_ORDER_STATUS,
  ORDERS_UPDATE_AMOUNT_PAID,
  ORDERS_UPDATE_DISCOUNT_TYPE
);
$event_type_list_1 = join(', ', $event_type_array_1);
$where_clause = "e.event_type_id IN ($event_type_list_1) AND e.entity_id = $order_id";
if (count($ot_id_array))
{
  $ot_id_list = join(', ', $ot_id_array);
  $event_type_array_2 = array(
    ORDERS_TRANSACTIONS_CREATE,
    ORDERS_TRANSACTIONS_UPDATE,
    ORDERS_TRANSACTIONS_DELETE,
    ORDERS_TRANSACTIONS_ISSUE_CREDIT,
    ORDERS_TRANSACTIONS_REVERSE_CREDIT,
    ORDERS_TRANSACTIONS_ISSUE_REFUND,
    ORDERS_TRANSACTIONS_REVERSE_REFUND
  );
  $event_type_list_2 = join(', ', $event_type_array_2);
  $where_clause = "(($where_clause) OR (e.event_type_id IN ($event_type_list_2) AND e.entity_id IN ($ot_id_list)))";
}
$event_details_query = <<< END_QUERY
  SELECT v.description,
    CONCAT(u.first_name, ' ', u.last_name) AS CREATEDBY,
    DATE_FORMAT(e.CREATED, '%c/%e/%Y %l:%i:%s %p') AS CREATED
  FROM events AS e, event_types AS v, users AS u
  WHERE e.event_type_id = v.event_type_id
  AND e.CREATEDBY = u.user_id
  AND $where_clause
  ORDER BY e.event_id
END_QUERY;
$result = mysql_query($event_details_query, $site_info['db_conn']);
$event_details_array = array();
while ($record = mysql_fetch_array($result)) $event_details_array[] = $record;
/* build array for "discount type" select box */
$discount_options_array = array();
$discount_options_array[1]['label'] = 'Payment Codes';
$discount_options_array[2]['label'] = 'Partnering Organizations';
$discount_options_array[3]['label'] = 'Partnering Dioceses';
/* get payment codes */
$payment_codes_query = <<< END_QUERY
  SELECT payment_code_id, code AS payment_code, description
  FROM payment_codes
  ORDER BY code
END_QUERY;
$result = mysql_query($payment_codes_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $discount_options_array[1]['options'][10000 + $record['payment_code_id']] = $record['payment_code'].' - '.$record['description'];
/* get dioceses and partners */
$partner_query = <<< END_QUERY
  SELECT p.partner_id, p.description, p.is_diocese, IFNULL(s.code, c.description) AS state_country
  FROM partners AS p LEFT JOIN states AS s USING (state_id), countries AS c
  WHERE p.is_partner = 1
  AND p.country_id = c.country_id
  ORDER BY p.description
END_QUERY;
$result = mysql_query($partner_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result))
{
  if ($record['is_diocese']) $discount_options_array[3]['options'][30000 + $record['partner_id']] = $record['description'].' ('.$record['state_country'].')';
  else $discount_options_array[2]['options'][20000 + $record['partner_id']] = $record['description'];
}
/* order status values */
$order_status_options_array = array(1 => 'Active', 0 => 'Inactive');
/* product details link */
switch ($order_details['product_type_id'])
{
  case 1:
    $product_details_link = vlc_internal_link($order_details['product_id'], 'cms/user_course_details.php?user_course='.$order_details['product_id']);
    break;
  case 2:
    $product_details_link = vlc_internal_link($order_details['product_id'], 'cms/resource_details.php?resource='.$order_details['product_id']);
    break;
  case 3:
    $product_details_link = vlc_internal_link($order_details['product_id'], 'cms/course_details.php?course='.$order_details['product_id']);
    break;
  case 4:
    $product_details_link = vlc_internal_link($order_details['product_id'], 'cms/course_subject_details.php?course_subject='.$order_details['product_id']);
    break;
  case 6:
    $product_details_link = vlc_internal_link($order_details['product_id'], 'cms/cert_user_details.php?cert_user='.$order_details['product_id']);
    break;
}
/* customer details link and discount type */
$discount_type_id = $order_details['discount_type_id'] * 10000 + $order_details['discount_id'];
if ($discount_type_id == 0) $discount_type_id = 'NULL';
/* do not allow users to edit discount type for partner orders */
if ($order_details['customer_type_id'] == 2)
{
  $customer_details_link = vlc_internal_link($order_details['customer_id'], 'cms/partner_details.php?partner='.$order_details['customer_id']);
  $discount_type_options = $discount_options_array[$order_details['discount_type_id']]['options'][$discount_type_id];
  $discount_type_options .= '<input type="hidden" name="orders['.$order_id.'][discount_type_id]" value="'.$discount_type_id.'">';
}
else
{
  $customer_details_link = vlc_internal_link($order_details['customer_id'], 'cms/user_details.php?user='.$order_details['customer_id']);
  $discount_type_options = vlc_select_box($discount_options_array, 'array', 'orders['.$order_id.'][discount_type_id]', $discount_type_id, false);
}
/* add course id field if product type is course registration */
if (isset($order_details['course_id'])) $course_id_field = '<input type="hidden" name="orders['.$order_id.'][course_id]" value="'.$order_details['course_id'].'">';
else $course_id_field = '';
/* add order details to output */
$output .= '<form method="post" action="order_action.php">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
$output .= '<tr>';
$output .= '<td><b>Order ID:</b></td><td>'.$order_id.'<input type="hidden" name="order_id" value="'.$order_id.'"><input type="hidden" name="order_id_array[]" value="'.$order_id.'"></td>';
$output .= '<td><b>Date:</b></td><td>'.date('n/j/Y', $order_details['order_date_timestamp']).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Order Status:</b></td><td>'.vlc_select_box($order_status_options_array, 'array', 'orders['.$order_id.'][order_status]', $order_details['order_status'], true).'</td>';
$output .= '<td><b>Payment Status:</b></td><td>'.$order_details['payment_status'].'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Product Type:</b></td><td colspan="3">'.$order_details['product_type'].'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Product:</b></td><td>'.$order_details['product'].' ('.$product_details_link.')'.$course_id_field.'</td>';
$output .= '<td><b>Order Cost:</b></td><td align="right">$'.number_format($order_details['order_cost'] / 100, 2).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Customer Type:</b></td><td>'.$order_details['customer_type'].'</td>';
$output .= '<td><b>Amount Paid:</b></td><td align="right">$'.number_format($order_details['amount_paid'] / 100, 2).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Customer:</b></td><td>'.$order_details['customer'].' ('.$customer_details_link.')</td>';
$output .= '<td><b>Amount Due:</b></td><td align="right">$'.number_format($order_details['amount_due'] / 100, 2).'</td>';
$output .= '</tr>';
$output .= '<tr><td><b>Discount Type:</b></td><td colspan="3">'.$discount_type_options.'</td></tr>';
$output .= '<tr><td colspan="4" align="center"><input type="submit" value="Submit"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
/* transactions */
$output .= '<h3>Transactions:</h3>';
$output .= '<p>The following transactions are linked to this order.</p><ul><li><b>&quot;Amount Paid&quot;</b> indicates how much of the transaction was applied to this order.</li><li>To view additional transaction details, click the <b>&quot;Transaction ID&quot;</b> link.</li></ul>';
$output .= '<form method="post" action="order_action.php">';
$output .= '<input type="hidden" name="order_id" value="'.$order_id.'">';
$output .= '<input type="hidden" name="order_id_array[]" value="'.$order_id.'">';
$output .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
$output .= '<tr><th>&nbsp;</th><th>Transaction ID</th><th>Transaction Status</th><th>Customer</th><th>Transaction Date</th><th>Amount Paid</th><th>Payment Method</th><th>Check Number</th><th>VLCFF Credit</th><th>Refund</th></tr>';
$payment_method_options_array = array(1 => 'Cash'
									, 2 => 'Check'
									, 3 => 'Credit Card'
									, 7 => 'Lump Sum CC'
									, 4 => 'VLCFF Credit'
									, 5 => 'Prepaid Funds'
									, 6 => 'Wire Transfer'
									, 8 => 'ACTA Fund'
									, 9 => 'Connor Trust'
									, 10 => 'Marianist'
									, 11 => 'Bosco Schlrshp'
									, 12 => 'Spc Needs Schlrshp'
									, 13 => 'Gift Certificate'
);
if (count($transaction_details))
{
  $i = 1;
  foreach ($transaction_details as $transaction)
  {
    $credit_checked = $credit_disabled = $credit_issued_field = $credit_issue_date = '';
    $refund_checked = $refund_disabled = $refund_issued_field = $refund_issue_date = '';
    if ($transaction['transaction_status'] == 0)
    {
      $order_transaction_amount = '<span class="inactive">($'.number_format($transaction['order_transaction_amount'] / 100, 2).')</span>';
      $credit_disabled = ' disabled';
      $refund_disabled = ' disabled';
    }
    elseif (isset($transaction['credit_issue_date']))
    {
      $order_transaction_amount = '<span class="inactive">($'.number_format($transaction['order_transaction_amount'] / 100, 2).')</span><input type="hidden" name="orders['.$order_id.'][transactions]['.$transaction['transaction_id'].'][order_transaction_amount]" value="'.number_format($transaction['order_transaction_amount'] / 100, 2).'"><input type="hidden" name="orders['.$order_id.'][transactions]['.$transaction['transaction_id'].'][previous_order_transaction_amount]" value="'.number_format($transaction['order_transaction_amount'] / 100, 2).'">';
      if ($order_details['credit_amount'] >= $transaction['order_transaction_amount'])
      {
        $credit_checked = ' checked id="transaction-'.$transaction['transaction_id'].'"';
        $credit_issue_date = ' <label for="transaction-'.$transaction['transaction_id'].'">'.date('n/j/Y', $transaction['credit_issue_date']).'</label>';
        $credit_issued_field = '<input type="hidden" name="orders['.$order_id.'][transactions]['.$transaction['transaction_id'].'][credit_issued]" value="1">';
      }
      else
      {
        $credit_checked = ' checked';
        $credit_disabled = ' disabled';
        $credit_issue_date = ' <span class="inactive">('.date('n/j/Y', $transaction['credit_issue_date']).')</span>';
      }
      $refund_disabled = ' disabled';
    }
    elseif (isset($transaction['refund_issue_date']))
    {
      $order_transaction_amount = '<span class="inactive">($'.number_format($transaction['order_transaction_amount'] / 100, 2).')</span><input type="hidden" name="orders['.$order_id.'][transactions]['.$transaction['transaction_id'].'][order_transaction_amount]" value="'.number_format($transaction['order_transaction_amount'] / 100, 2).'"><input type="hidden" name="orders['.$order_id.'][transactions]['.$transaction['transaction_id'].'][previous_order_transaction_amount]" value="'.number_format($transaction['order_transaction_amount'] / 100, 2).'">';
      $refund_checked = ' checked id="transaction-'.$transaction['transaction_id'].'"';
      $refund_issue_date = ' <label for="transaction-'.$transaction['transaction_id'].'">'.date('n/j/Y', $transaction['refund_issue_date']).'</label>';
      $refund_issued_field = '<input type="hidden" name="orders['.$order_id.'][transactions]['.$transaction['transaction_id'].'][refund_issued]" value="1">';
      $credit_disabled = ' disabled';
    }
    else
    {
      $order_transaction_amount = '$'.number_format($transaction['order_transaction_amount'] / 100, 2);
      if ($transaction['order_transaction_amount'] == 0)
      {
        $credit_disabled = ' disabled';
        $refund_disabled = ' disabled';
      }
    }
    $issue_credit_field = '<input type="checkbox" name="orders['.$order_id.'][transactions]['.$transaction['transaction_id'].'][issue_credit]" value="1"'.$credit_checked.$credit_disabled.'>'.$credit_issued_field;
    $issue_refund_field = '<input type="checkbox" name="orders['.$order_id.'][transactions]['.$transaction['transaction_id'].'][issue_refund]" value="1"'.$refund_checked.$refund_disabled.'>'.$refund_issued_field;
    $output .= '<tr>';
    $output .= '<td>'.$i++.'.</td>';
    $output .= '<td align="center">'.vlc_internal_link($transaction['transaction_id'], 'cms/transaction_details.php?transaction='.$transaction['transaction_id']).'</td>';
    $output .= '<td align="center">'.$transaction['transaction_status_desc'].'</td>';
    $output .= '<td align="center"><nobr>'.$transaction['customer'].'</nobr></td>';
    $output .= '<td align="center">'.date('n/j/Y', $transaction['transaction_date']).'</td>';
    $output .= '<td align="center"><nobr>'.$order_transaction_amount.'</nobr></td>';
    $output .= '<td align="center">'.$transaction['payment_method'].'</td>';
    $output .= '<td align="center">'.$transaction['check_number'].'</td>';
    $output .= '<td align="center"><nobr>'.$issue_credit_field.$credit_issue_date.'</nobr></td>';
    $output .= '<td align="center"><nobr>'.$issue_refund_field.$refund_issue_date.'</nobr></td>';
    $output .= '</tr>';
  }
}
else $output .= '<tr><td colspan="10" align="center">No Transactions Found.</td></tr>';
if (isset($order_details['credit_amount']) and $order_details['credit_amount'] > 0) $payment_method_options_array[4] .= ' ($'.number_format($order_details['credit_amount'] / 100, 2).')';
else unset($payment_method_options_array[4]);
$output .= '<tr><td colspan="5" align="right"><b>New Transaction:</b></td><td align="center"><nobr>$ <input type="text" size="10" name="orders['.$order_id.'][transaction_amount]" style="text-align:right"></nobr></td><td align="center">'.vlc_select_box($payment_method_options_array, 'array', 'orders['.$order_id.'][payment_method_id]', -1, true).'</td><td align="center"><input type="text" size="10" name="orders['.$order_id.'][check_number]" style="text-align:right"></td><td colspan="2">&nbsp;</td></tr>';
$output .= '<tr><td colspan="10" align="center"><input type="submit" value="Submit"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
if (count($event_details_array))
{
  $output .= '<h3>Database Record Update History</h3>';
  $output .= '<ul><li><a href="javascript:show_hide_content(\'event-history\')">[+/-] Show/Hide Database Record Update History</a></li></ul>';
  $output .= '<div id="event-history" style="display: none;">';
  $output .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
  $output .= '<tr><th>Date</th><th>User</th><th>Event</th></tr>';
  foreach ($event_details_array as $event)
  {
    $output .= '<tr>';
    $output .= '<td>'.$event['CREATED'].'</td>';
    $output .= '<td>'.$event['CREATEDBY'].'</td>';
    $output .= '<td>'.$event['description'].'</td>';
    $output .= '</tr>';
  }
  $output .= '</table>';
  $output .= '</div>';
}
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
