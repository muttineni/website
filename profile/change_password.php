<?php
$page_info['section'] = 'profile';
$page_info['page'] = 'change-password';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<div class="container">
    <h1><?php echo $lang['profile']['change-password']['heading']['change-password'] ?></h1>
    <div class="return-link">
        <i class="fa fa-arrow-left"></i>
        <?php echo vlc_internal_link($lang['profile']['shared']['return-link'], 'profile/') ?>
    </div>
    <div class="card">
        <form method="post" action="change_password_action.php" onsubmit="return validate_form(this)">
            <div class="card-body">
                <div class="alert alert-info">
                    <?php echo $lang['profile']['change-password']['content']['intro'] ?>
                </div>
                <div class="form-group">
                    <label for="current_password"><?php echo $lang['profile']['change-password']['form-fields']['current-password']?></label>
                    <input type="password" class="form-field form-control" name="current_password" id="current_password" size="20" maxlength="6" required="true" message="<?php echo $lang['profile']['change-password']['status']['current-password-required'] ?>" />
                </div>
                <div class="form-group">
                    <label for="new_password"><?php echo $lang['profile']['change-password']['form-fields']['new-password'] ?></label>
                    <input type="password" class="form-field form-control" name="new_password" id="new_password" size="20" maxlength="6" required="true" message="<?php echo $lang['profile']['change-password']['status']['new-password-required'] ?>" />
                </div>
                <div class="form-group">
                    <label for="verify_password"><?php echo $lang['profile']['change-password']['form-fields']['verify-new-password'] ?></label>
                    <input type="password" class="form-field form-control" name="verify_password" id="verify_password" 
                        size="20" maxlength="6" required="true" message="<?php print $lang['profile']['change-password']['status']['verify-password'] ?>" />
                    <input type="hidden" name="link_fields" value="new_password:verify_password" 
                        message="<?php echo $lang['profile']['change-password']['status']['new-password-must-match'] ?>">
                </div>
                <input type="submit" class="submit-button btn btn-vlc" name="submit" value="<?php print $lang['profile']['change-password']['form-fields']['change-password-button'] ?>" />

            </div>
        </form>
    </div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

