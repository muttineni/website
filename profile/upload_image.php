<?php
$page_info['section'] = 'profile';
$page_info['page'] = 'upload-image';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>

<!-- begin page content -->

<div class="container">
    <h1><?php print $lang['profile']['upload-image']['heading']['upload-image'] ?></h1>
    <div class="return-link">
        <i class="fa fa-arrow-left"></i>
        <?php echo vlc_internal_link($lang['profile']['shared']['return-link'], 'profile/') ?>
    </div>
    <div class="card">
        <form method="post" action="upload_image_action.php" enctype="multipart/form-data">
            <div class="card-body">
                <div class="alert alert-info">
                    <?php print $lang['profile']['upload-image']['content']['intro'] ?>
                </div>
                <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
                <div class="form-group">
                    <label for="user_image" class="sr-only">Select file to upload</label>
                    <input type="file" size="40" name="user_image" id="user_image" class="form-control-file">
                </div>
                <input type="submit" class="submit-button btn btn-vlc" name="submit" value="<?php print $lang['profile']['upload-image']['form-fields']['upload-image-button'] ?>"></p>
            </div>
        </form>
    </div>
</div>

<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>