<?php
$page_info['section'] = 'giftcert';
$lang = vlc_get_language();
/* get form fields from "post" vars */
$form_fields = $_POST;
if ($form_fields['pmt_status'] == 'success')
{
  /* get transaction id from "EXT_TRANS_ID" */
  
  // Needed to remove commas from number string. PHP won't multiply numbers properly with commas.
  $form_fields['pmt_amt'] = str_replace(",", "", $form_fields['pmt_amt']);
  
  $form_fields['transaction_id'] = $form_fields['EXT_TRANS_ID'];
  $form_fields['pmt_amt'] = $form_fields['pmt_amt'] * 100;
  $form_fields['pmt_date'] = substr($form_fields['pmt_date'], 6, 4).'-'.substr($form_fields['pmt_date'], 0, 2).'-'.substr($form_fields['pmt_date'], 3, 2);
  $form_fields['name_on_acct'] = ($form_fields['name_on_acct']);
  
  /* insert transaction report data */
  $insert_transaction_report_query = <<< END_QUERY
    INSERT INTO gift_certificate_transaction_reports
    SET 
	transaction_id = '{$form_fields['transaction_id']}'
	, transaction_type_id = 1
	, transaction_amount = '{$form_fields['pmt_amt']}'
	, transaction_date = '{$form_fields['pmt_date']}'
	, transaction_report_status = 1
	, CARD_TYPE = '{$form_fields['card_type']}'
	, AMOUNT = '{$form_fields['pmt_amt']}'
	, REFERENCE_ID = '{$form_fields['tpg_trans_id']}'
	, CARD_NAME = '{$form_fields['name_on_acct']}'
END_QUERY;

  $result = mysql_query($insert_transaction_report_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "transaction_reports"');
  
  
  /* update gift certificate transaction status */
  $update_gift_certificate_query = <<< END_QUERY
    UPDATE gift_certificates
    SET 
	 order_status = 1
	 ,cert_code = '{$_SESSION['giftcert']['cert_code']}'
	 ,remaining_value = '{$form_fields['pmt_amt']}'
	 ,date_updated = now()
    WHERE order_transaction_id = {$form_fields['transaction_id']}
END_QUERY;

  $result = mysql_query($update_gift_certificate_query, $site_info['db_conn']);
  /* use mysql_affected_rows() to see if the query was executed successfully (even if no rows were updated) */
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "transactions"');
  
  /* track database update */
  $db_events_array = array();
  $db_events_array[] = array(TRANSACTIONS_UPDATE_STATUS_PMT, $form_fields['transaction_id']);
  vlc_insert_events($db_events_array);
  


 // Prepare variables for emails
  
  $cert_buyer_name = $_SESSION['giftcert']['first_name'] . ' ' . $_SESSION['giftcert']['last_name'];
  $cert_code = $_SESSION['giftcert']['cert_code'];
  $cert_value = intval($form_fields['pmt_amt']/5000);
  $course_s = ($cert_value > 1)? 'courses' : 'course';
  $cert_expiry = date_add($form_fields['pmt_date'],date_interval_create_from_date_string('1 year'));
  $cert_payer_name = $form_fields['name_on_acct'];
  $cert_payment = $form_fields['pmt_amt']/100;
  $cert_class_es = ($cert_value > 1)? 'classes' : 'class';
  $cert_email = $_SESSION['giftcert']['email'];
  $cert_username = (isset($_SESSION['user_info']['username']))? $_SESSION['user_info']['username'] : 'None';
  $cert_check_link = 'https://vlcff.udayton.edu/gift_certificates/check.php';
  $cert_share_link = 'https://vlcff.udayton.edu/gift_certificates/share';
     
 
// EMAILS ***************************************
  
  /* Prepare e-mail messages */
  $subject = $lang['giftcert']['email']['success']['subject'];
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
  
}

//FOR TESTING ONLY - output email then click button to go to payment_action, which should send you to success
  echo $subject . '<br><br>' . $message . '<br>';
  $_SESSION['form_fields']['transaction_id'] = $form_fields['transaction_id'];
?>

<form method="get" action="payment_action.php">
	<input type="text" name="UPAY_SITE_ID" value="9" />
	<input type="text" name="vlc_status" value="success" />
	<input type="text" name="EXT_TRANS_ID" value="<?php echo $form_fields['transaction_id'] ?>" />   
	<input type="submit" name="button" value="submit" />
</form>
  
  <?php echo $BOBTEST1 . '<br>'; ?>
  <?php echo $BOBTEST2 . '<br>'; ?>
  <?php echo $BOBTEST3 . '<br>'; ?>
  
  <?php
  /*
  // send message to user from administrator 
  $from = 'From: "'.$lang['common']['misc']['vlcff-admin'].'" <'.$site_info['vlcff_email'].'>';
  $to = $cert_email;
  
  mail($to, $subject, $message, $from);
  
  // send additional message to administrator from user 
  $from = 'From: "'.$cert_buyer_name.'" <'.$cert_email.'>';
  $to = $site_info['webmaster_email'].', '.$site_info['billing_email'];
  mail($to, $subject, $message, $from);
  
  // send environment variables for approved/successful transactions 
  mail($site_info['webmaster_email'], 'Payment Action Variables', print_r($GLOBALS, true));
}
else
{
  mail($site_info['webmaster_email'], 'Payment Status Variables', print_r($GLOBALS, true));
}
/* exit */

//exit;
?>
