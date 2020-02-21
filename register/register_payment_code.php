<?php
$page_info['section'] = 'register';
$page_info['page'] = 'payment-code';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get form field values from session variable */
if (isset($_SESSION['form_fields'])) $form_fields = $_SESSION['form_fields'];
else vlc_redirect('register/');
/* set default payment code value if it is not set */
if (!isset($form_fields['payment_code'])) $form_fields['payment_code'] = '';
/* build page content */
$hidden_fields = '<input type="hidden" name="action_id" value="'.$form_fields['action_id'].'">';
$hidden_fields .= '<input type="hidden" name="course_id" value="'.$form_fields['course_id'].'">';
$hidden_fields .= '<input type="hidden" name="registration_type_id" value="'.$form_fields['registration_type_id'].'">';
$hidden_fields .= '<input type="hidden" name="discount_type_id" value="1">';

$page_content .= '<input type="submit" name="cancel" value="'.$lang['register']['common']['form-fields']['cancel-button'].'" class="submit-button">';
$page_content .= '&nbsp;<input type="submit" name="next" value="'.$lang['register']['common']['form-fields']['next-button'].'" class="submit-button">';
$page_content .= '</p>';
$page_content .= '</form>';
print $header;
?>
<!-- begin page content -->
<div class="container">
    <h1><?php echo $lang['register']['payment-code']['heading']['payment-code'] ?></h1>
    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <?php echo sprintf($lang['register']['payment-code']['content']['intro'], $lang['register']['common']['misc']['cancel-message']) ?>
            </div>
            <form method="post" action="register_action.php">
                <?php echo $hidden_fields ?>
                <div class="form-group">
                    <label for="payment_code" class="sr-only">Payment Code</label>
                    <input type="text" name="payment_code" id="payment_code" value="<?php echo htmlspecialchars($form_fields['payment_code'])?>" size="20" maxlength="20" class="form-field form-control" onkeypress="if (event.keyCode && event.keyCode == 13) return false; else return true;" />
                </div>
                <input type="submit" name="cancel" value="<?php echo $lang['register']['common']['form-fields']['cancel-button'] ?>" class="submit-button btn btn-danger" />
                <input type="submit" name="next" value="<?php echo $lang['register']['common']['form-fields']['next-button'] ?>" class="submit-button btn btn-vlc" />
            </form>
        </div>
    </div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
