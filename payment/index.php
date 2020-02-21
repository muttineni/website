<?php
$page_info['section'] = 'payment';
$page_info['page'] = 'index';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get form fields and order details from session variable */
if (isset($_SESSION['form_fields']))
{
  $form_fields = $_SESSION['form_fields'];
  $order_details_array = $form_fields['order_details_array'];
  $payment_base_url = 'http://'.$_SERVER['HTTP_HOST'].$site_info['home_url'].'payment/';
  $hidden_fields = '<input type="hidden" name="EXT_TRANS_ID_LABEL" value="transaction_id">';
  $hidden_fields .= '<input type="hidden" name="EXT_TRANS_ID" value="'.$form_fields['transaction_id'].'">';
  $hidden_fields .= '<input type="hidden" name="AMT" value="'.number_format($form_fields['transaction_amount'] / 100, 2).'">';
  $hidden_fields .= '<input type="hidden" name="UPAY_SITE_ID" value="'.$site_info['UPAY_SITE_ID'].'">';
  $hidden_fields .= '<input type="hidden" name="posting_key" value="payment">';
  $hidden_fields .= '<input type="hidden" name="SUCCESS_LINK" value="'.$payment_base_url.'payment_action.php?vlc_status=success">';
  $hidden_fields .= '<input type="hidden" name="ERROR_LINK" value="'.$payment_base_url.'payment_action.php?vlc_status=error">';
  $hidden_fields .= '<input type="hidden" name="CANCEL_LINK" value="'.$payment_base_url.'payment_action.php?vlc_status=cancel">';
}
else vlc_redirect('profile/');
/* build page content */
$page_content = '<h1>'.$lang['payment']['index']['heading']['payment-system'].'</h1>';
$page_content .= '<div>'.$lang['payment']['index']['content']['refund-policy'].'</div>';
$page_content .= '<div class="alert alert-info">'.$lang['payment']['index']['content']['payment-instructions'].'</div>';
$page_content .= '<div align="center">';
$page_content .= '<table class="table table-striped w-75 mx-auto">';
$page_content .= '<thead><tr><th>'.$lang['payment']['common']['misc']['desc-label'].'</th><th class="text-right">'.$lang['payment']['common']['misc']['amount-due-label'].'</th></tr></thead>';
/* format order details */
$total_amount_due = 0;
$i = 0;
foreach ($order_details_array as $order_id => $order)
{
  $page_content .= '<tr><td>'.$order['product'].'</td><td align="right">$'.number_format($order['amount_due'] / 100, 2).'</td></tr>';
  $total_amount_due += $order['amount_due'];
  $i++;
}
$total_amount_due = '$'.number_format($total_amount_due / 100, 2);
$page_content .= '<tr><td align="right"><b>'.$lang['payment']['common']['misc']['total-label'].':</b></td><td align="right"><b>'.$total_amount_due.'</b></td></tr>';
$page_content .= '</table>';
$page_content .= '<p class="center">';
$page_content .= '<form method="post" action="payment_action.php" class="d-inline-block">';
$page_content .= '<input type="submit" name="cancel" value="'.$lang['payment']['common']['form-fields']['cancel-button'].'" class="submit-button btn btn-danger mx-3">';
$page_content .= '</form>';
$page_content .= '<form method="post" action="'.$site_info['payment_action'].'" class="d-inline-block">';
$page_content .= $hidden_fields;
$page_content .= '<input type="submit" name="begin" value="'.$lang['payment']['common']['form-fields']['begin-button'].'" class="submit-button btn btn-vlc mx-3">';
$page_content .= '</form>';
$page_content .= '<form method="post" action="../gift_certificates/giftcert_use.php" class="d-inline-block">';
$page_content .= $hidden_fields;
$page_content .= '<input type="submit" name="giftcert_use" value="'.$lang['payment']['common']['form-fields']['giftcert_button'].'" class="submit-button btn btn-vlc mx-3">';
$page_content .= '</form>';
$page_content .= '</p>';
$page_content .= '</div>';
print $header;
?>
<!-- begin page content -->
<div class="container">
  <?php print $page_content ?>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
