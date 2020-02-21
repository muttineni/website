

<?php

/*
if (isset($_SESSION['giftcert']))
{
  $form_fields = $_SESSION['giftcert'];
  $order_details_array = $form_fields['order_details_array'];
  $payment_base_url = 'https://'.$_SERVER['HTTP_HOST'].$site_info['home_url'].'payment/';
  $hidden_fields = '<input type="hidden" name="EXT_TRANS_ID_LABEL" value="transaction_id">';
  $hidden_fields .= '<input type="hidden" name="EXT_TRANS_ID" value="'.$form_fields['transaction_id'].'">';
  $hidden_fields .= '<input type="hidden" name="AMT" value="'.number_format($form_fields['transaction_amount'], 2).'">';
  $hidden_fields .= '<input type="hidden" name="UPAY_SITE_ID" value="'.$site_info['UPAY_SITE_ID'].'">';
  $hidden_fields .= '<input type="hidden" name="posting_key" value="payment">';
  $hidden_fields .= '<input type="hidden" name="SUCCESS_LINK" value="'.$payment_base_url.'payment_action.php?vlc_status=success">';
  $hidden_fields .= '<input type="hidden" name="ERROR_LINK" value="'.$payment_base_url.'payment_action.php?vlc_status=error">';
  $hidden_fields .= '<input type="hidden" name="CANCEL_LINK" value="'.$payment_base_url.'payment_action.php?vlc_status=cancel">';
}

*/ 

if (isset($_POST)) $form_fields = $_POST;

/*

<form action="payment_return.php" method="post">
   First Name: <input type="text" name="EXT_TRANS_ID" value="<?php print $user_info['first_name'] ?>" ><br>
   Last Name: <input type="text" name="pmt_amt" value="<?php print $user_info['last_name'] ?>" ><br>
   E-mail: <input type="text" name="pmt_date" value="<?php print $user_info['email'] ?>" ><br>
   Phone: <input type="text" name="name_on_acct" value="<?php print $user_info['phone'] ?>" ><br><br />
   <input type="submit" name="done" value="Done">
</form>

*/



  // get transaction id from "EXT_TRANS_ID" 
  $form_fields['transaction_id'] = $form_fields['EXT_TRANS_ID'];
  $form_fields['pmt_amt'] = $form_fields['AMT'];
  $form_fields['pmt_date'] = date("m-d-Y");
  $form_fields['name_on_acct'] = ($form_fields['name_on_acct']);




	
	$page_content .= '<form method="post" action="payment_return.php">';
	$page_content .= '<input type="text" name="pmt_status" value="success"><br>';
	$page_content .= 'Trans ID <input type="text" name="EXT_TRANS_ID" value="' . $form_fields['transaction_id'] . '"><br>';
	$page_content .= 'Amount $<input type="text" name="pmt_amt" value="' . $form_fields['pmt_amt'] . '"><br>';
	$page_content .= 'Date <input type="text" name="pmt_date" value="' . $form_fields['pmt_date'] . '"><br>';
	$page_content .= 'Name <input type="text" name="name_on_acct" value="' . $form_fields['name_on_acct'] . '"><br>';	
	$page_content .= '<input type="submit" name="cancel" value="TEST_RETURN">';
	$page_content .= '</form>';
	

print $page_content;




?>