<?php
$page_info['section'] = 'giftcert';
$lang = vlc_get_language();
$login_required = 0;
//$user_info = vlc_get_user_info($login_required, 0);

/* get form fields */
if (isset($_POST)) {
	$form_fields = $_POST;
}

$order_details_array = array();

/* the user clicked the "confirm" button on the gift cert order page - do the following, then go to the confirmation (index) page */
if (isset($form_fields['confirm']))
{

// Random Certificate number function ************************************************************
	 function crypto_rand_secure($min, $max)
{
    $range = $max - $min;
    if ($range < 1) return $min; // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd >= $range);
    return $min + $rnd;
}

function getToken($length)
{
    $token = "";
    $codeAlphabet = "BCDFGHJKLMNPQRSTVWXYZ";
    //$codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "23456789";
    $max = strlen($codeAlphabet) - 1;
    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[crypto_rand_secure(0, $max)];
    }
    return $token;
}
//**********************************************************************************


  //Get post values from giftcert_purchaser into session variable

	$_SESSION['giftcert']['quantity'] = $_POST['certificate_qty'];
	$_SESSION['giftcert']['transaction_amount'] = $_POST['certificate_qty'] * 50;
	$_SESSION['giftcert']['first_name'] = $_POST['first_name'];
	$_SESSION['giftcert']['last_name'] = $_POST['last_name'];
	$_SESSION['giftcert']['email'] = $_POST['email'];
	$_SESSION['giftcert']['phone'] = $_POST['phone'];
	$_SESSION['giftcert']['cert_code'] = getToken(8);
	
   //Put giftcert Session into form fields variable so I can easily use them on this page
	$form_fields = $_SESSION['giftcert'];
	  
	// Populate the gift_certificates table. Set the order status to 0 (not paid yet).
	$insert_giftcert_query = <<< END_QUERY
	INSERT INTO gift_certificates
	SET 
		order_status = 0
		,buyer_first_name = '{$form_fields['first_name']}'
		,buyer_last_name = '{$form_fields['last_name']}'
		,buyer_email = '{$form_fields['email']}'
		,buyer_phone = '{$form_fields['phone']}'
		,cert_code = '{$_SESSION['giftcert']['cert_code']}'
		,order_quantity = '{$form_fields['quantity']}'
		,transaction_amount = '{$form_fields['transaction_amount']}' * 100
		,payment_method = 3
		,remaining_value = 0
END_QUERY;
		
	 $result = mysql_query($insert_giftcert_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "transactions"');
	 $_SESSION['giftcert']['transaction_id'] = mysql_insert_id();
    $db_events_array[] = array(TRANSACTIONS_CREATE, $_SESSION['giftcert']['transaction_id']);
	 
	 
    $redirect_to = 'gift_certificates/';
}
/* the user clicked the "CANCEL" button on the payment entry page - go to "my start page" */
elseif (isset($form_fields['cancel']))
{
  /* clear out "form fields" and "order details" session variables */
  $_SESSION['form_fields'] = $_SESSION['order_details_array'] = null;
  $_SESSION['giftcert'] = null;
  vlc_exit_page($lang['payment']['status']['cancel-message'], 'success', '/');
}
/* the user clicked the "DONE" button on the payment success page - go to "my start page" */
elseif (isset($form_fields['done']))
{
  /* clear out "form fields" and "order details" session variables */
  $_SESSION['form_fields'] = $_SESSION['order_details_array'] = null;
  $redirect_to = '/';
}
/* the user clicked the "CANCEL" button on the online payment system - go to "my start page" */
elseif (isset($_GET['vlc_status']) and $_GET['vlc_status'] == 'cancel')
{
  /* clear out "form fields" and "order details" session variables */
  $_SESSION['form_fields'] = $_SESSION['order_details_array'] = null;
  vlc_exit_page($lang['payment']['status']['cancel-message'], 'success', '/');
}
/* the user encountered an ERROR on the online payment system - go to "my start page" */
elseif (isset($_GET['vlc_status']) and $_GET['vlc_status'] == 'error')
{
  /* send environment variables for errors */
  mail($site_info['webmaster_email'], 'Payment Error Variables', print_r($GLOBALS, true));
  /* clear out "form fields" and "order details" session variables */
  $_SESSION['form_fields'] = $_SESSION['order_details_array'] = null;
  $_SESSION['giftcert'] = null;
  vlc_exit_page($lang['payment']['status']['error-message'], 'success', '/');
}
/* the user is coming from the online payment system - determine whether the transaction was successful and either go to the payment success page with a "success" message or go to "my start page" with a "cancel" message  */
elseif (isset($_GET['vlc_status']) and $_GET['vlc_status'] == 'success')
{
  /* set transaction status */
  if ((isset($_SESSION['form_fields']['transaction_id']) and isset($_GET['EXT_TRANS_ID']) and $_SESSION['form_fields']['transaction_id'] == $_GET['EXT_TRANS_ID'])
    and (isset($_GET['UPAY_SITE_ID']) and $_GET['UPAY_SITE_ID'] == $site_info['UPAY_SITE_ID_giftcerts']))
  {
	 $order_details_array = $lang['giftcert']['order_details']['vlcff_gift_cert'] . '<br>' .
	 								$lang['giftcert']['order_details']['cert_number'] . $_SESSION['giftcert']['cert_code'] . '<br>' .
	  								$lang['giftcert']['order_details']['purchase_date'] . date("d M Y") . '<br>' .
	 								$lang['giftcert']['order_details']['cost'] .$_SESSION['giftcert']['transaction_amount'] . '<br>' .
									$lang['giftcert']['order_details']['initial_value'] . $_SESSION['giftcert']['quantity'] . '<br>' .
									$lang['giftcert']['order_details']['expiry'] . date("d M Y", strtotime("+1 years"));
	 
	 $form_fields['transaction_id'] = $_SESSION['form_fields']['transaction_id'];
	 
	 
	 $_SESSION['order_details_array'] = $order_details_array;
    $redirect_to = 'gift_certificates/payment_success.php';
  }
  else
  {
    /* clear out "form fields" and "order details" session variables */
    $_SESSION['form_fields'] = $_SESSION['order_details_array'] = null;
	 $_SESSION['giftcert'] = null;
    vlc_exit_page($lang['payment']['status']['cancel-message'], 'success', '/');
  }
}


// The user clicked Check Certificate on the giftcert_check.php page ******************************************
elseif (((isset($form_fields['certificate_number'])) and (!isset($form_fields['pay_with_giftcert']))) and (!isset($form_fields['back_to_payment_options'])))
{
	$giftcert_check_query = <<< END_QUERY
	SELECT remaining_value, date_ordered
	FROM gift_certificates
	WHERE cert_code = '{$form_fields['certificate_number']}'
END_QUERY;
	$result = mysql_query($giftcert_check_query, $site_info['db_conn']);
	$giftcert_check['remaining_value'] = mysql_result($result,0,0);
	
	$giftcert_check['date_ordered'] = mysql_result($result,0,1);
	$php_unix_date_ordered = strtotime($giftcert_check['date_ordered']);
	
	$giftcert_check['remaining_courses'] = intval($giftcert_check['remaining_value']/5000);
	
	$giftcert_check['expiration'] = date("d M Y", strtotime($giftcert_check['date_ordered'] . "+366 days"));
	$php_unix_date_expiration = $php_unix_date_ordered + (366 * 24 * 60 * 60);
	
	$formatted_cert_number = '<span style="letter-spacing: 2px;">' . $form_fields['certificate_number'] . '</span>';
	
	// Check for valid cert number
	if (empty($giftcert_check['date_ordered'])) {
		$giftcert_check_response = $lang['giftcert']['check_response']['invalid'];
	}
	// Cert Expired
	elseif ($php_unix_date_expiration < time()) {
		$giftcert_check_response =  $lang['giftcert']['check_response']['cert_number'] . $formatted_cert_number . '<br>' .
									$lang['giftcert']['check_response']['expired'];
	}
	
	// Cert courses are zero
	elseif ($giftcert_check['remaining_courses'] <= 0) { 
		$giftcert_check_response = $lang['giftcert']['check_response']['cert_number'] . $formatted_cert_number . '<br>' .
								   $lang['giftcert']['check_response']['no_courses'];
	}
	
	// Certificate is still good
	else {
		$giftcert_check_response =  $lang['giftcert']['check_response']['cert_number'] . $formatted_cert_number . '<br>' .
									$lang['giftcert']['check_response']['course_qty'] . $giftcert_check['remaining_courses'] . '<br>' .
									$lang['giftcert']['check_response']['expiration'] . $giftcert_check['expiration'];
	}
	
	$_SESSION['giftcert_check_response'] = $giftcert_check_response;
	
	unset($giftcert_check);
	$redirect_to = 'gift_certificates/check';
}



// The user clicked Pay with Gift Certificate on the gift_certificate/giftcert_use.php page ********************************
elseif (isset($form_fields['pay_with_giftcert']))
{
	// Get cert value information from database

	$giftcert_check_query = <<< END_QUERY
	SELECT remaining_value, date_ordered
	FROM gift_certificates
	WHERE cert_code = '{$form_fields['certificate_number']}'
END_QUERY;
	$result = mysql_query($giftcert_check_query, $site_info['db_conn']);
	$giftcert_check['remaining_value'] = mysql_result($result,0,0);
	
	$giftcert_check['date_ordered'] = mysql_result($result,0,1);
	$php_unix_date_ordered = strtotime($giftcert_check['date_ordered']);
	
	$giftcert_check['remaining_courses'] = intval($giftcert_check['remaining_value']/5000);
	
	$giftcert_check['expiration'] = date("d M Y", strtotime($giftcert_check['date_ordered'] . "+366 days"));
	$php_unix_date_expiration = $php_unix_date_ordered + (366 * 24 * 60 * 60);
	
	//Cert number spaced out for readability
	$formatted_cert_number = '<span style="letter-spacing: 2px;">' . $form_fields['certificate_number'] . '</span>';
	
	// Valid cert number?
	if (empty($giftcert_check['date_ordered'])) {
		$giftcert_check_response = $lang['giftcert']['check_response']['invalid'];
	}
	// Cert Expired?
	elseif ($php_unix_date_expiration < time()) {
		$giftcert_check_response =  $lang['giftcert']['check_response']['cert_number'] . $formatted_cert_number . '<br>' .
									$lang['giftcert']['check_response']['expired'];
	}
	
	// Zero course on Cert?
	elseif ($giftcert_check['remaining_courses'] <= 0) { 
		$giftcert_check_response = $lang['giftcert']['check_response']['cert_number'] . $formatted_cert_number . '<br>' .
								   $lang['giftcert']['check_response']['no_courses'];
	}
	
	// Check value of cert against cost of courses
	else {
	
		$discount_price_total = 0;
		$order_details = $_SESSION['form_fields']['order_details_array'];
	
		foreach ($order_details as $key => $value) {
			if (($value['amount_due']) and ($value['amount_due'] < 5000)) {
				$discount_price_total += $value['amount_due'];
			}
			else{
				$discount_price_total += 5000;
			}
		}	
		if ($giftcert_check['remaining_value'] < $discount_price_total) {
			$giftcert_check_response = $lang['giftcert']['check_response']['cert_number'] . $formatted_cert_number . '<br>' .
									   $lang['giftcert']['text']['not_enough_money'];
		}
		// Pay bill. Update cert table then redirect to giftcert_payment_return.php
		else {
			  /* update gift certificate transaction status */
			  $giftcert_check['remaining_value'] = $giftcert_check['remaining_value'] - $discount_price_total;
			  $update_gift_certificate_query = <<< END_QUERY
				UPDATE gift_certificates
				SET 
				  remaining_value = '{$giftcert_check['remaining_value']}'
				 ,date_updated = now()
				WHERE cert_code = '{$form_fields['certificate_number']}'
END_QUERY;

			  $result = mysql_query($update_gift_certificate_query, $site_info['db_conn']);
			  /* use mysql_affected_rows() to see if the query was executed successfully (even if no rows were updated) */
			  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "transactions"');
			  
			  /* track database update */
			  $db_events_array = array();
			  $db_events_array[] = array(TRANSACTIONS_UPDATE_STATUS_PMT, $form_fields['transaction_id']);
			  vlc_insert_events($db_events_array);
			
			  // This variable will be needed on the next page
			  $_SESSION['discount_price_total'] = $discount_price_total;

			  vlc_redirect('../payment/giftcert_payment_return.php'); 
		}
	}
	$_SESSION['giftcert_check_response'] = $giftcert_check_response;
	unset($giftcert_check);
	$redirect_to = 'gift_certificates/giftcert_use.php';
}

// The user clicked "back_to_payment_options"  ********************************************************
elseif (isset($form_fields['back_to_payment_options']))
{
$redirect_to = '../payment/';
}

/* if none of the above are true, then the user is trying to access the payment action page directly - go to "my start page" */
else
{
  /* clear out "form fields" and "order details" session variables */
  $_SESSION['form_fields'] = $_SESSION['order_details_array'] = null;
  $_SESSION['giftcert'] = null;
  vlc_exit_page($lang['payment']['status']['cancel-message'], 'success', '/');
}
/* continue to appropriate page */
vlc_redirect($redirect_to);
?>
