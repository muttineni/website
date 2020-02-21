<?php
$page_info['section'] = 'profile';
$login_required = 1; /* user must be logged in to access this page */
$lang = vlc_get_language();
$user_info = vlc_get_user_info($login_required, 1);


/* check for valid upload */
if (isset($_FILES['user_image']) and is_uploaded_file($_FILES['user_image']['tmp_name']) and $_FILES['user_image']['size'] > 0) $user_image_details = $_FILES['user_image'];
else vlc_exit_page('<li>'.$lang['profile']['upload-image']['status']['invalid-image'].'</li>', 'error', 'profile/upload_image.php');


/* store uploaded image in "uploads" directory */
$temp_location = $user_image_details['tmp_name'];
$user_image_extension = strrchr($user_image_details['name'], '.');
$new_filename = $user_info['username'].'_'.time().$user_image_extension;
$new_location = $site_info['uploaded_images_path'].$new_filename;
$user_image_url = 'http://'.$_SERVER['HTTP_HOST'].$site_info['images_url'].'uploads/'.$new_filename;
if (!move_uploaded_file($temp_location, $new_location)) trigger_error('UPLOAD FAILED: Unable to move uploaded file to "uploads" directory.');


/* send notification message to user from administrator */
$from = 'From: "'.$lang['common']['misc']['vlcff-admin'].'" <'.$site_info['vlcff_email'].'>';
$to = $user_info['email'];
$subject = $lang['profile']['email']['upload-image']['subject'];
$message = sprintf($lang['profile']['email']['upload-image']['message'], $user_info['name'], $site_info['vlcff_email'], $user_info['username'], $user_info['email']);
mail($to, $subject, $message, $from);


/* send additional message to administrator from user */
$from = 'From: "'.$user_info['full_name'].'" <'.$user_info['email'].'>';
$to = $site_info['webmaster_email'].', '.$site_info['support_email'];
$message .= "\n\nImage Path: $new_location\nImage URL: $user_image_url";
mail($to, $subject, $message, $from);
vlc_exit_page($lang['profile']['upload-image']['status']['upload-image-success'], 'success', 'profile/upload_image.php');
?>
