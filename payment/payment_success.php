<?php
$page_info['section'] = 'payment';
$page_info['page'] = 'payment-success';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get order details from session variable */
if (isset($_SESSION['order_details_array'])) $order_details_array = $_SESSION['order_details_array'];
else vlc_redirect('profile/');
/* build page content */
$page_content = '<h2>'.$lang['payment']['payment-success']['heading']['success'].'</h2>';
$page_content .= $lang['payment']['payment-success']['content']['success-message'];
$page_content .= '<div align="center">';
$page_content .= '<table border="0" cellpadding="5" cellspacing="0">';
$page_content .= '<tr bgcolor="#eeeeee"><th>'.$lang['payment']['common']['misc']['desc-label'].'</th><th>'.$lang['payment']['common']['misc']['amount-paid-label'].'</th></tr>';
$total_amount_paid = 0;
$i = 0;
foreach ($order_details_array as $order_id => $order)
{
  if ($i % 2 == 0) $row_background = '';
  else $row_background = ' bgcolor="#eeeeee"';
  $page_content .= '<tr'.$row_background.'><td>'.$order['product'].'</td><td align="right">$'.number_format($order['amount_paid'] / 100, 2).'</td></tr>';
  $total_amount_paid += $order['amount_paid'];
  $i++;
}
$total_amount_paid = '$'.number_format($total_amount_paid / 100, 2);
$page_content .= '<tr><td align="right"><b>'.$lang['payment']['common']['misc']['total-label'].':</b></td><td align="right"><b>'.$total_amount_paid.'</b></td></tr>';
$page_content .= '</table>';
$page_content .= '</div>';
$page_content .= '<form method="post" action="payment_action.php">';
$page_content .= '<p class="center"><input type="submit" name="done" value="'.$lang['payment']['common']['form-fields']['done-button'].'" class="submit-button"></p>';
$page_content .= '</form>';
print $header;
?>
<!-- begin page content -->
<?php print $page_content ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
