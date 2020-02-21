<?php
$page_info['section'] = 'payment';
$page_info['page'] = 'payment-errors';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* send environment variables for transaction return pages */
mail($site_info['webmaster_email'], 'Payment Return Variables', print_r($GLOBALS, true));
print $header;
?>
<!-- begin page content -->
<p><?php print $lang['payment']['payment-errors']['content'] ?></p>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
