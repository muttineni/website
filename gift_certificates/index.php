<?php
$page_info['section'] = 'giftcert';
$page_info['page'] = 'index';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);

/* get form fields */
if (isset($_POST)) $form_fields = $_POST;
$order_details_array = array();

/* get form fields and order details from session variable */
// Insert transaction ID and sale amount into hidden fields

if (isset($_SESSION['giftcert']))
{
  $form_fields = $_SESSION['giftcert'];
  $order_details_array = $form_fields['order_details_array'];
  $giftcert_base_url = 'https://'.$_SERVER['HTTP_HOST'].$site_info['home_url'].'gift_certificates/';
  $hidden_fields = '<input type="hidden" name="EXT_TRANS_ID_LABEL" value="transaction_id">';
  $hidden_fields .= '<input type="hidden" name="EXT_TRANS_ID" value="'.$form_fields['transaction_id'].'">';
  $hidden_fields .= '<input type="hidden" name="AMT" value="'.number_format($form_fields['transaction_amount'], 2).'">';
  $hidden_fields .= '<input type="hidden" name="UPAY_SITE_ID" value="'.$site_info['UPAY_SITE_ID_giftcerts'].'">';
  $hidden_fields .= '<input type="hidden" name="posting_key" value="payment">';
  $hidden_fields .= '<input type="hidden" name="SUCCESS_LINK" value="'.$giftcert_base_url.'payment_action.php?vlc_status=success">';
  $hidden_fields .= '<input type="hidden" name="ERROR_LINK" value="'.$giftcert_base_url.'payment_action.php?vlc_status=error">';
  $hidden_fields .= '<input type="hidden" name="CANCEL_LINK" value="'.$giftcert_base_url.'payment_action.php?vlc_status=cancel">';
}
else vlc_redirect('profile/');

// Form buttons

	
	$page_content .= '&nbsp;&nbsp;<form method="post" action="payment_action.php">';
	$page_content .= '<input type="submit" name="cancel" value="'.$lang['giftcert']['button']['cancel'].'">';
	$page_content .= '</form>';
	
	$page_content .= '&nbsp;&nbsp;<form method="post" action="'.$site_info['payment_action'].'">';
	$page_content .= $hidden_fields;
	$page_content .= '<input type="submit" name="begin" value="'. $lang['giftcert']['button']['enter_payment'].'">';
	$page_content .= '</form>';
$page_content .= '</div>';

$page_content .= '</div>';

print $header;
?>
<!-- begin page content -->
<div class="container">
	<h1><?php echo $lang['giftcert']['heading']['order_confirm_details'] ?></h1>
	<div class="card">
		<!-- Order Details -->
		<div class="card-header">
			<h3><?php echo $lang['giftcert']['heading']['order_details'] ?></h3>
		</div>
		<div class="card-body">
			<div class="float-right">
				<img src="../images/gift_cert_01.jpg" width="150" class="non_responsive d-none d-sm-block" />
			</div>
			<div>
				<div class="review-item">
					<span class="review-label"><?php echo $lang['giftcert']['label']['course_qty'] ?></span>: <span class="review-value"><?php echo $form_fields['quantity'] ?></span>
				</div>
				<div class="review-item">
					<span class="review-label"><?php echo $lang['giftcert']['label']['total_cost'] ?></span>: <span class="review-item"><?php echo $form_fields['transaction_amount'] ?></span>
				</div>
			</div>
		</div>
		<div class="card-header card-footer">
			<h3><?php echo $lang['giftcert']['heading']['buyer_details'] ?></h3>
		</div>
		<div class="card-body">				
			<div class="review-item">
				<span class="review-label"><?php echo $lang['giftcert']['label']['buyer_first_name'] ?></span>: <span class="review-value"><?php echo $form_fields['first_name'] ?></span>
			</div>
			<div class="review-item">
				<span class="review-label"><?php echo $lang['giftcert']['label']['buyer_last_name'] ?></span>: <span class="review-item"><?php echo $form_fields['last_name'] ?></span>
			</div>				
			<div class="review-item">
				<span class="review-label"><?php echo $lang['giftcert']['label']['buyer_email'] ?></span>: <span class="review-value"><?php echo $form_fields['email'] ?></span>
			</div>
			<div class="review-item">
				<span class="review-label"><?php echo $lang['giftcert']['label']['buyer_phone']?></span>: <span class="review-item"><?php echo $form_fields['phone'] ?></span>
			</div>
			<div class="alert alert-info mt-3">
				<?php echo $lang['giftcert']['text']['payment_instructions'] ?>
			</div>
			<div class="btn-container">
				<form method="post" action="giftcert_order.php" class="d-md-inline m-2">
					<input type="submit" name="back" class="btn btn-default" value="<?php echo $lang['giftcert']['button']['back'] ?>" />
				</form>
				<form method="post" action="payment_action.php" class="d-md-inline m-2">
					<input type="submit" name="cancel" class="btn btn-danger" value="<?php echo $lang['giftcert']['button']['cancel'] ?>" />
				</form>
				<form method="post" action="<?php echo $site_info['payment_action'] ?>" class="d-md-inline m-2">
					<?php echo $hidden_fields ?>
					<input type="submit" name="begin" class="btn btn-vlc" value="<?php echo $lang['giftcert']['button']['enter_payment'] ?>">
				</form>
			</div>
		</div>
	</div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
