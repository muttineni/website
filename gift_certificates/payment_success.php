<?php
$page_info['section'] = 'giftcert';
$page_info['page'] = 'payment-success';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);

/* get order details from session variable */
if (isset($_SESSION['order_details_array'])) $order_details_array = $_SESSION['order_details_array'];
else vlc_redirect('profile/');

/* build page content */
$page_content = '<h2>'.$lang['payment']['payment-success']['heading']['success'].'</h2>';
$page_content .= $lang['payment']['payment-success']['content']['success-message'];
$page_content .= '<div>';
$page_content .= '<div style="text-align:left; width:300px; margin:20px auto;">';
$page_content .= $_SESSION['order_details_array'];
$page_content .= '</div>';
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
