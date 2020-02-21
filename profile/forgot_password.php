<?php
$page_info['section'] = 'profile';
$page_info['page'] = 'forgot-password';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<div class="container">
    <h1><?php print $lang['profile']['forgot-password']['heading']['forgot-password'] ?></h1>
    <div class="card">
        <form method="post" action="forgot_password_action.php" onsubmit="return validate_form(this)">
            <div class="card-body">
                <div class="alert alert-info">
                    <?php print $lang['profile']['forgot-password']['content']['intro'] ?>
                </div>
                <div class="form-group">
                    <label for="username"><?php print $lang['profile']['forgot-password']['form-fields']['username'] ?></label>
                    <input type="text" class="form-field form-control" name="username" id="username" size="20" maxlength="6" />
                </div>
                <div class="form-group">
                    <label for="email"><?php print $lang['profile']['forgot-password']['form-fields']['email']?></label>
                    <input type="text" class="form-field form-control" name="email" id="email" size="30" />
                </div>
                <input type="submit" class="submit-button btn btn-vlc" name="submit" value="<?php print $lang['profile']['forgot-password']['form-fields']['send-password-button'] ?>" />
            </div>
            <div class="card-footer">
                <?php print $lang['profile']['forgot-password']['content']['contact-admin'] ?>
            </div>
        </form>
    </div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

