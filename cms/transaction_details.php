<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'transaction-details';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get transaction id */
$transaction_id = intval($_GET['transaction']);
/* get number of orders to add */
if (isset($_GET['num'])) $num = $_GET['num'];
else $num = 1;
/* begin output variable */
$output = '';
/* return to previous page link */
if (isset($_SERVER['HTTP_REFERER']) and $return_url = strstr($_SERVER['HTTP_REFERER'], 'cms/')) $output .= '<p>'.vlc_internal_link('Return to Previous Page', $return_url).'</p>';
/* get transaction details */
$transaction_query = <<< END_QUERY
  SELECT t.transaction_status, t.transaction_amount, t.payment_method_id,
    UNIX_TIMESTAMP(t.transaction_date) AS transaction_date_timestamp,
    IFNULL(t.check_number, '') AS check_number, IFNULL(t.notes, '') AS notes,
    r.transaction_report_id, r.transaction_type_id, r.transaction_report_status,
    t.customer_type_id, t.customer_id,
    c.description AS customer_type,
    IF(t.customer_type_id = 2, p.description, CONCAT(m.first_name, ' ', m.last_name)) AS customer,
    IFNULL(e.credit_amount, 0) AS credit_amount
  FROM transactions AS t
      LEFT JOIN transaction_reports AS r ON t.transaction_id = r.transaction_id
      LEFT JOIN users AS m ON t.customer_id = m.user_id
      LEFT JOIN partners AS p ON t.customer_id = p.partner_id
      LEFT JOIN credits AS e ON t.customer_id = e.customer_id AND t.customer_type_id = e.customer_type_id,
    customer_types AS c
  WHERE t.customer_type_id = c.customer_type_id
  AND t.transaction_id = $transaction_id
  GROUP BY t.transaction_id
END_QUERY;
$result = mysql_query($transaction_query, $site_info['db_conn']);
$transaction_details = mysql_fetch_array($result);
foreach ($transaction_details as $key => $value)
{
  if (is_string($value)) $transaction_details[$key] = htmlspecialchars($value);
}
/* get orders linked to this transaction */
$order_query = <<< END_QUERY
  SELECT o.order_id, o.is_active AS order_status, UNIX_TIMESTAMP(o.order_date) AS order_date,
    ot.order_transaction_id, ot.order_transaction_amount,
    UNIX_TIMESTAMP(ot.credit_issue_date) AS credit_issue_date,
    UNIX_TIMESTAMP(ot.refund_issue_date) AS refund_issue_date,
    CASE o.product_type_id
      WHEN 1 THEN CONCAT(u.first_name, ' ', u.last_name, ' - ', c.description)
      WHEN 2 THEN r.title
      WHEN 3 THEN n.description
      WHEN 4 THEN b.description
      WHEN 6 THEN CONCAT(h.first_name, ' ', h.last_name, ' - ', g.description)
    END AS product
  FROM orders_transactions AS ot, orders AS o
    LEFT JOIN users_courses AS uc ON o.product_id = uc.user_course_id
    LEFT JOIN users AS u ON uc.user_id = u.user_id
    LEFT JOIN courses AS c ON uc.course_id = c.course_id
    LEFT JOIN resources AS r ON o.product_id = r.resource_id
    LEFT JOIN courses AS n ON o.product_id = n.course_id
    LEFT JOIN course_subjects AS b ON o.product_id = b.course_subject_id
    LEFT JOIN certs_users AS cu ON o.product_id = cu.cert_user_id
    LEFT JOIN cert_progs AS g ON cu.cert_prog_id = g.cert_prog_id
    LEFT JOIN users AS h ON cu.user_id = h.user_id
  WHERE o.order_id = ot.order_id
  AND ot.transaction_id = $transaction_id
  ORDER BY o.order_id
END_QUERY;
$result = mysql_query($order_query, $site_info['db_conn']);
$credit_issued = $refund_issued = 0;
$ot_id_array = array();
while ($record = mysql_fetch_array($result))
{
  $order_details[$record['order_id']] = $record;
  if (isset($record['credit_issue_date'])) $credit_issued = 1;
  if (isset($record['refund_issue_date'])) $refund_issued = 1;
  $ot_id_array[] = $record['order_transaction_id'];
}
$ot_id_list = join(', ', $ot_id_array);
/* get transaction report details */
$transaction_report_query = <<< END_QUERY
  SELECT r.transaction_report_id, t.description AS transaction_type, r.transaction_amount,
    UNIX_TIMESTAMP(r.transaction_date) AS transaction_date_timestamp,
    IFNULL(r.REFERENCE_ID, '&nbsp;') AS REFERENCE_ID,
    IFNULL(r.APPROVAL_CODE, '&nbsp;') AS APPROVAL_CODE,
    IFNULL(r.RETURN_CODE, '&nbsp;') AS RETURN_CODE,
    IFNULL(r.REF_NUM, '&nbsp;') AS REF_NUM
  FROM transaction_reports AS r, transaction_types AS t
  WHERE r.transaction_type_id = t.transaction_type_id
  AND r.transaction_id = $transaction_id
  ORDER BY r.transaction_date
END_QUERY;
$result = mysql_query($transaction_report_query, $site_info['db_conn']);
$transaction_report_array = array();
while ($record = mysql_fetch_array($result)) $transaction_report_array[] = $record;
/* get event details */
$event_type_array_1 = array(
  TRANSACTIONS_CREATE,
  TRANSACTIONS_UPDATE,
  TRANSACTIONS_ADD_ORDER,
  TRANSACTIONS_REMOVE_ORDER,
  TRANSACTIONS_UPDATE_STATUS_CMS,
  TRANSACTIONS_UPDATE_STATUS_PMT,
  TRANSACTIONS_UPDATE_STATUS_RPT
);
$event_type_list_1 = join(', ', $event_type_array_1);
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
$event_details_query = <<< END_QUERY
  SELECT v.description,
    CONCAT(u.first_name, ' ', u.last_name) AS CREATEDBY,
    DATE_FORMAT(e.CREATED, '%c/%e/%Y %l:%i:%s %p') AS CREATED
  FROM events AS e LEFT JOIN users AS u ON e.CREATEDBY = u.user_id, event_types AS v
  WHERE e.event_type_id = v.event_type_id
  AND
  (
    (e.event_type_id IN ($event_type_list_1) AND e.entity_id = $transaction_id)
      OR
    (e.event_type_id IN ($event_type_list_2) AND e.entity_id IN ($ot_id_list))
  )
  ORDER BY e.event_id
END_QUERY;
$result = mysql_query($event_details_query, $site_info['db_conn']);
$event_details_array = array();
while ($record = mysql_fetch_array($result)) $event_details_array[] = $record;
/* transaction status values */
$transaction_status_array = array(1 => 'Successful', 0 => 'Unsuccessful');
/* payment method values */
$payment_method_array = 
        array(
                1 => 'Cash',
                2 => 'Check',
                3 => 'Credit Card',
                8 => 'ACTA Fund',
                11 => 'Bosco Sch',
                9 => 'Connor Trust',
                7 => 'Lump Sum CC',
                10 => 'Marianist',
                5 => 'Prepaid Funds',
                12 => 'Sp Needs Sch',
                6 => 'Wire Transfer'
              ); 
if ($transaction_details['payment_method_id'] == 4) $payment_method_array[4] = 'VLCFF Credit';
/* order status values */
$order_status_array = array(1 => 'Active', 0 => 'Inactive');
/* get transaction status */
if ($credit_issued or $refund_issued) $transaction_status = $transaction_status_array[$transaction_details['transaction_status']].'<input type="hidden" name="transaction_status" value="'.$transaction_details['transaction_status'].'">';
else $transaction_status = vlc_select_box($transaction_status_array, 'array', 'transaction_status', $transaction_details['transaction_status'], true);
/* get transaction report status */
if ($transaction_details['payment_method_id'] == 3)
{
  if (isset($transaction_details['transaction_report_id']))
  {
    if ($transaction_details['transaction_report_status'])
    {
      if ($transaction_details['transaction_type_id'] == 1) $transaction_report_status = 'Successful';
      else $transaction_report_status = 'Refund';
    }
    else $transaction_report_status = 'Error';
  }
  else $transaction_report_status = 'Unsuccessful';
}
else $transaction_report_status = '<span class="inactive">(N/A)</span>';
/* num orders array */
for ($i = 5; $i <= 50; $i += 5) $num_orders_array[$i] = $i;
/* customer details link */
if ($transaction_details['customer_type_id'] == 2) $customer_details_link = vlc_internal_link($transaction_details['customer_id'], 'cms/partner_details.php?partner='.$transaction_details['customer_id']);
else $customer_details_link = vlc_internal_link($transaction_details['customer_id'], 'cms/user_details.php?user='.$transaction_details['customer_id']);
/* add transaction details to output */
$output .= '<form method="post" action="transaction_action.php">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
$output .= '<tr><td><b>Transaction ID:</b></td><td colspan="3">'.$transaction_id.'<input type="hidden" name="transaction_id" value="'.$transaction_id.'"></td></tr>';
$output .= '<tr>';
$output .= '<td><b>Amount:</b></td><td>$'.number_format($transaction_details['transaction_amount'] / 100, 2).'</td>';
$output .= '<td><b>Date:</b></td><td>'.date('n/j/Y', $transaction_details['transaction_date_timestamp']).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Customer:</b></td><td>'.$transaction_details['customer'].' ('.$customer_details_link.')</td>';
$output .= '<td><b>Customer Type:</b></td><td>'.$transaction_details['customer_type'].'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Payment Method:</b></td><td>'.vlc_select_box($payment_method_array, 'array', 'payment_method_id', $transaction_details['payment_method_id'], true).'</td>';
$output .= '<td><b>Check Number:</b></td><td><input type="text" size="10" name="check_number" value="'.$transaction_details['check_number'].'" style="text-align:right"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Transaction Status:</b></td><td>'.$transaction_status.'</td>';
$output .= '<td><b>Transaction Report Status:</b></td><td>'.$transaction_report_status.'</td>';
$output .= '</tr>';
$output .= '<tr><td valign="top"><b>Notes / Comments:</b></td><td colspan="3"><textarea rows="5" cols="60" name="notes">'.$transaction_details['notes'].'</textarea></td></tr>';
$output .= '<tr><td colspan="4" align="center"><input type="submit" value="Submit"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
if (count($transaction_report_array))
{
  $output .= '<h3>Transaction Report Details</h3>';
  $output .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
  $output .= '<tr><th>Transaction Report ID</th><th>Transaction ID</th><th>Transaction Type</th><th>Amount</th><th>Date</th><th>REFERENCE ID</th><th>APPROVAL CODE</th><th>RETURN CODE</th><th>REF NUM</th></tr>';
  foreach ($transaction_report_array as $record)
  {
    $output .= '<tr>';
    $output .= '<td>'.$record['transaction_report_id'].'</td>';
    $output .= '<td>'.$transaction_id.'</td>';
    $output .= '<td>'.$record['transaction_type'].'</td>';
    $output .= '<td>$'.number_format($record['transaction_amount'] / 100, 2).'</td>';
    $output .= '<td>'.date('n/j/Y', $record['transaction_date_timestamp']).'</td>';
    $output .= '<td>'.$record['REFERENCE_ID'].'</td>';
    $output .= '<td>'.$record['APPROVAL_CODE'].'</td>';
    $output .= '<td>'.$record['RETURN_CODE'].'</td>';
    $output .= '<td>'.$record['REF_NUM'].'</td>';
    $output .= '</tr>';
  }
  $output .= '</table>';
}
$output .= '<a name="orders"></a><h3>Orders:</h3>';
$output .= '<form method="get" action="transaction_details.php#orders">';
$output .= '<input type="hidden" name="transaction" value="'.$transaction_id.'">';
$output .= '<p>The following orders are linked to this transaction.</p>';
$output .= '<ul>';
$output .= '<li><b>&quot;Amount Paid&quot;</b> indicates how much of this transaction was applied to the order.</li>';
$output .= '<li>To view additional order details, click the <b>&quot;Order ID&quot;</b> link.</li>';
$output .= '<li>To add orders to this transaction, select the number of orders to add and click <b>&quot;Submit&quot;</b>: '.vlc_select_box($num_orders_array, 'array', 'num', -1, true).' <input type="submit" value="Submit"></li>';
$output .= '</ul>';
$output .= '</form>';
$output .= '<form method="post" action="transaction_action.php">';
$output .= '<input type="hidden" name="transaction_id" value="'.$transaction_id.'">';
$output .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
$output .= '<tr><th>&nbsp;</th><th>Order ID</th><th>Product</th><th>Order Status</th><th>Order Date</th><th>Amount Paid</th><th>VLCFF Credit</th><th>Refund</th></tr>';
$i = 1;
foreach ($order_details as $order)
{
  $credit_checked = $credit_disabled = $credit_issued_field = $credit_issue_date = '';
  $refund_checked = $refund_disabled = $refund_issued_field = $refund_issue_date = '';
  if ($transaction_details['transaction_status'] == 0)
  {
    $order_transaction_amount = '<span class="inactive">($'.number_format($order['order_transaction_amount'] / 100, 2).')</span>';
    $credit_disabled = ' disabled';
    $refund_disabled = ' disabled';
  }
  elseif (isset($order['credit_issue_date']))
  {
    $order_transaction_amount = '<span class="inactive">($'.number_format($order['order_transaction_amount'] / 100, 2).')</span><input type="hidden" name="orders['.$order['order_id'].'][order_transaction_amount]" value="'.number_format($order['order_transaction_amount'] / 100, 2).'"><input type="hidden" name="orders['.$order['order_id'].'][previous_order_transaction_amount]" value="'.number_format($order['order_transaction_amount'] / 100, 2).'">';
    if ($transaction_details['credit_amount'] >= $order['order_transaction_amount'])
    {
      $credit_checked = ' checked';
      $credit_issued_field = '<input type="hidden" name="orders['.$order['order_id'].'][credit_issued]" value="1">';
    }
    else
    {
      $credit_checked = ' checked';
      $credit_disabled = ' disabled';
    }
    $credit_issue_date = ' ('.date('n/j/Y', $order['credit_issue_date']).')';
    $refund_disabled = ' disabled';
  }
  elseif (isset($order['refund_issue_date']))
  {
    $order_transaction_amount = '<span class="inactive">($'.number_format($order['order_transaction_amount'] / 100, 2).')</span><input type="hidden" name="orders['.$order['order_id'].'][order_transaction_amount]" value="'.number_format($order['order_transaction_amount'] / 100, 2).'"><input type="hidden" name="orders['.$order['order_id'].'][previous_order_transaction_amount]" value="'.number_format($order['order_transaction_amount'] / 100, 2).'">';
    $credit_disabled = ' disabled';
    $refund_checked = ' checked';
    $refund_issued_field = '<input type="hidden" name="orders['.$order['order_id'].'][refund_issued]" value="1">';
    $refund_issue_date = ' ('.date('n/j/Y', $order['refund_issue_date']).')';
  }
  else
  {
    $order_transaction_amount = '$&nbsp;<input type="text" size="10" name="orders['.$order['order_id'].'][order_transaction_amount]" value="'.number_format($order['order_transaction_amount'] / 100, 2).'" style="text-align:right"><input type="hidden" name="orders['.$order['order_id'].'][previous_order_transaction_amount]" value="'.number_format($order['order_transaction_amount'] / 100, 2).'">';
    if ($order['order_transaction_amount'] == 0)
    {
      $credit_disabled = ' disabled';
      $refund_disabled = ' disabled';
    }
  }
  $issue_credit_field = '<input type="checkbox" name="orders['.$order['order_id'].'][issue_credit]" value="1"'.$credit_checked.$credit_disabled.'>'.$credit_issued_field;
  $issue_refund_field = '<input type="checkbox" name="orders['.$order['order_id'].'][issue_refund]" value="1"'.$refund_checked.$refund_disabled.'>'.$refund_issued_field;
  $output .= '<tr>';
  $output .= '<td>'.$i++.'.</td>';
  $output .= '<td align="center">'.vlc_internal_link($order['order_id'], 'cms/order_details.php?order='.$order['order_id']).'</td>';
  $output .= '<td align="center"><nobr>'.$order['product'].'</nobr></td>';
  $output .= '<td align="center">'.$order_status_array[$order['order_status']].'</td>';
  $output .= '<td align="center">'.date('n/j/Y', $order['order_date']).'</td>';
  $output .= '<td align="center"><nobr>'.$order_transaction_amount.'</nobr></td>';
  $output .= '<td align="center"><nobr>'.$issue_credit_field.$credit_issue_date.'</nobr></td>';
  $output .= '<td align="center"><nobr>'.$issue_refund_field.$refund_issue_date.'</nobr></td>';
  $output .= '</tr>';
}
$az_array = array();
for ($j = 97; $j < 123; $j++) $az_array[] = chr($j);
for ($j = 65; $j < 91; $j++) $az_array[] = chr($j);
for ($j = 0; $j < $num; $j++)
{
  $output .= '<tr>';
  $output .= '<td>'.$i++.'.</td>';
  $output .= '<td align="center"><input type="text" size="10" name="orders['.$az_array[$j].'][order_id]" value="" style="text-align:center"></td>';
  $output .= '<td>&nbsp;</td>';
  $output .= '<td>&nbsp;</td>';
  $output .= '<td>&nbsp;</td>';
  $output .= '<td align="center"><nobr>$ <input type="text" size="10" name="orders['.$az_array[$j].'][order_transaction_amount]" value="" style="text-align:right"></nobr></td>';
  $output .= '<td>&nbsp;</td>';
  $output .= '<td>&nbsp;</td>';
  $output .= '</tr>';
}
$output .= '<tr><td colspan="8" align="center"><input type="submit" value="Submit"> <input type="reset" value="Reset"></td></tr>';
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
    $output .= '<td>'.(isset($event['CREATEDBY']) ? $event['CREATEDBY'] : '<i>unknown</i>').'</td>';
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
