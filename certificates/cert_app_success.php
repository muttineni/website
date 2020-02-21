<?php
$page_info['section'] = 'certificates';
$page_info['page'] = 'success';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
if (isset($_SESSION['form_fields'])) $form_fields = $_SESSION['form_fields'];
else vlc_redirect('certificates/');
$output = '<h1>'.$lang['certificates']['success']['page-title'].'</h1>';
$output .= '<div class="alert alert-info">'.$lang['certificates']['success']['content']['junk-mail-notice'].'<hr/>';
$output .= $lang['certificates']['success']['content']['payment-message'].'</div>';
$output .= '<table class="table table-striped mx-auto w-75">';
$output .= '<thead><tr><td scope="row"><strong>'.$lang['certificates']['success']['content']['cert-prog'].'</strong></td><td>'.$form_fields['certificate_program'].'</td></tr>';
$output .= '<tr><td scope="row"><strong>'.$lang['certificates']['success']['content']['order-cost'].'</strong></td><td>'.'$'.number_format($form_fields['order_cost'] / 100, 2).'</td></tr>';
$output .= '<tr><td scope="row"><strong>'.$lang['certificates']['success']['content']['diocese'].'</strong></td><td>'.$form_fields['diocese'].'</td></tr></thead>';
$output .= '</table>';
$output .= '<form method="post" action="'.$site_info['home_url'].'payment/payment_action.php" class="mx-auto w-75">';
$output .= '<input type="hidden" name="order_id_array[]" value="'.$form_fields['order_id'].'">';
$output .= '<input type="submit" name="pay_later" value="'.$lang['certificates']['success']['form-fields']['pay-later-button'].'" class="submit-button btn btn-default m-3">';
$output .= '<input type="submit" name="pay_now" value="'.$lang['certificates']['success']['form-fields']['pay-now-button'].'" class="submit-button btn btn-vlc m-3">';
$output .= '</form>';

print $header;
?>
<!-- begin page content -->
<div class="container">
    <?php print $output ?>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
