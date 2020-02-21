 <?php
$page_info['section'] = 'giftcert';
$lang = vlc_get_language();

/* get form fields from "post" vars coming from uPAY */
$uPay = $_POST;
if ($uPay['pmt_status'] == 'success')
{  
  // Needed to remove commas from number string. PHP won't multiply numbers properly with commas.
  $uPay['pmt_amt'] = str_replace(",", "", $uPay['pmt_amt']);
  /* get transaction id from "EXT_TRANS_ID" */
  $uPay['transaction_id'] = $uPay['EXT_TRANS_ID'];
  $uPay['pmt_amt'] = $uPay['pmt_amt'] * 100;
  $uPay['pmt_date'] = substr($uPay['pmt_date'], 6, 4).'-'.substr($uPay['pmt_date'], 0, 2).'-'.substr($uPay['pmt_date'], 3, 2);
  $uPay['name_on_acct'] = ($uPay['name_on_acct']);
  
  /* insert transaction report data */
  $insert_transaction_report_query = <<< END_QUERY
    INSERT INTO gift_certificate_transaction_reports
    SET 
	transaction_id = '{$uPay['transaction_id']}'
	, transaction_type_id = 1
	, transaction_amount = '{$uPay['pmt_amt']}'
	, transaction_date = '{$uPay['pmt_date']}'
	, transaction_report_status = 1
	, CARD_TYPE = '{$uPay['card_type']}'
	, AMOUNT = '{$uPay['pmt_amt']}'
	, REFERENCE_ID = '{$uPay['tpg_trans_id']}'
	, CARD_NAME = '{$uPay['name_on_acct']}'
END_QUERY;

  $result = mysql_query($insert_transaction_report_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "transaction_reports"');
  
  
  /* update gift certificate transaction status */
  $update_gift_certificate_query = <<< END_QUERY
    UPDATE gift_certificates
    SET 
	 order_status = 1
	 ,remaining_value = '{$uPay['pmt_amt']}'
	 ,date_updated = now()
    WHERE order_transaction_id = {$uPay['transaction_id']}
END_QUERY;

  $result = mysql_query($update_gift_certificate_query, $site_info['db_conn']);
  /* use mysql_affected_rows() to see if the query was executed successfully (even if no rows were updated) */
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "transactions"');
  
  /* track database update */
  $db_events_array = array();
  $db_events_array[] = array(TRANSACTIONS_UPDATE_STATUS_PMT, $uPay['transaction_id']);
  vlc_insert_events($db_events_array);
  
  
	/* Get data from gift cert table to insert into emails */
	$get_data_from_gift_certificate = <<< END_QUERY
		SELECT
		CONCAT(gc.buyer_first_name,' ',gc.buyer_last_name) AS name
		,gc.buyer_email
		,gc.cert_code
		,gc.date_ordered
		FROM
		gift_certificates AS gc
		WHERE
		gc.order_transaction_id = '{$uPay['transaction_id']}'
END_QUERY;

  $result = mysql_query($get_data_from_gift_certificate, $site_info['db_conn']);
  /* use mysql_affected_rows() to see if the query was executed successfully (even if no rows were updated) */
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "transactions"');
  // Create array of database query results
  $giftcert_info = mysql_fetch_array($result);
  
  // If user is logged in, get username and email address from database to place at bottom of confirmation email.
  $user_info = vlc_get_user_info(0);

 // Prepare variables for emails
  
  $cert_buyer_name = $giftcert_info['name'];
  $cert_code = $giftcert_info['cert_code'];
  $cert_value = $uPay['pmt_amt']/4000;
  $course_s = ($cert_value > 1)? 'courses' : 'course';
  $cert_expiry = date('d M Y', strtotime($giftcert_info['date_ordered'] . ' + 1 year'));
  $cert_payer_name = $uPay['name_on_acct'];
  $cert_payment = $uPay['pmt_amt']/100;
  $cert_class_es = ($cert_value > 1)? 'classes' : 'class';
  $cert_username = ($user_info['logged_in'])? $user_info['username'] : 'None';
  $cert_email = ($user_info['logged_in'])? $user_info['email'] : $giftcert_info['buyer_email'];
  $cert_check_link = 'https://vlcff.udayton.edu/gift_certificates/check';
  $cert_share_link = 'https://vlcff.udayton.edu/gift_certificates/share';
     
 
// EMAILS ***************************************
  
  /* Prepare e-mail messages */
  $subject = $lang['giftcert']['email']['success']['subject'] . ' ' . $cert_code;
  $message = sprintf($lang['giftcert']['email']['success']['message']
  				,$cert_buyer_name
				,$cert_code
				,$cert_value
				,$course_s
				,$cert_expiry
				,$cert_payer_name
				,$cert_payment
				,$cert_check_link
				,$cert_share_link
				,$cert_class_es
				,$cert_username
				,$cert_email
				);
  
  
  // send message to user from administrator 
  $from = 'From: "'.$lang['common']['misc']['vlcff-admin'].'" <'.$site_info['vlcff_email'].'>';
  $to = $cert_email;
  
  mail($to, $subject, $message, $from);
  
  // send additional message to administrator from user 
  $from = 'From: "'.$cert_buyer_name.'" <'.$cert_email.'>';
  $to = 'rstewart1@udayton.edu';
  mail($to, $subject, $message, $from);
  
  // send environment variables for approved/successful transactions 
  mail('rstewart1@udayton.edu', 'Payment Action Variables', print_r($GLOBALS, true));
}
else
{
  mail($site_info['webmaster_email'], 'Payment Status Variables', print_r($GLOBALS, true));
}


echo '<pre>';
echo 'TEST<br>';
echo print_r($giftcert_info);
echo print_r($user_info);
echo '<br>' . $cert_username;
echo '<br><a href="https://vlcff.udayton.edu> HOME</a>';
echo '</pre>';

/* exit */

//exit;
//vlc_redirect('gift_certificates/payment_action.php?vlc_status=success&UPAY_SITE_ID=59&EXT_TRANS_ID=' . $uPay['transaction_id']);
?>
