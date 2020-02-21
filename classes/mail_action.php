<?php
$page_info['section'] = 'classes';
$page_info['login_required'] = 1;
$page_info['course_id'] = vlc_get_url_variable($site_info, 'course', true);
$page_info['folder_id'] = vlc_get_url_variable($site_info, 'folder', false, $page_info['course_id']);
$lang = vlc_get_language();
$user_info = vlc_get_user_info($page_info['login_required'], 1);
$course_info = vlc_get_course_info($site_info, $page_info['course_id'], 1);
/* get action */
if (isset($_GET['action']) and is_numeric($_GET['action'])) $page_info['action_id'] = $_GET['action'];
elseif (isset($_POST['action']) and is_numeric($_POST['action'])) $page_info['action_id'] = $_POST['action'];
else trigger_error('INVALID ACTION ID');
/* set folder id default value if it has not been set */
if (!isset($page_info['folder_id'])) $page_info['folder_id'] = 1;
/* process based on action variable */
switch ($page_info['action_id'])
{
  /* send to trash */
  case 1:
    if (isset($_GET['mail']) and is_numeric($_GET['mail'])) $mail_id_array = array($_GET['mail']);
    elseif (isset($_POST['mail_id_array']) and is_array($_POST['mail_id_array']) and count($_POST['mail_id_array']) > 0) $mail_id_array = $_POST['mail_id_array'];
    else vlc_exit_page('<li>'.$lang['classes']['mail']['status']['no-messages-selected'].'</li>', 'error', 'classes/mail.php?course='.$page_info['course_id'].'&folder='.$page_info['folder_id']);
    $mail_id_list = implode(', ', $mail_id_array);
    $send_to_trash_query = <<< END_QUERY
      UPDATE mail_users
      SET mail_status_id = 3
      WHERE to_user_id = {$user_info['user_id']}
      AND mail_id IN ($mail_id_list)
END_QUERY;
    $result = mysql_query($send_to_trash_query, $site_info['db_conn']);
    $success_message = $lang['classes']['mail']['status']['message-successfully-trashed'];
    break;
  /* mark read */
  case 2:
    if (isset($_POST['mail_id_array']) and is_array($_POST['mail_id_array']) and count($_POST['mail_id_array']) > 0) $mail_id_array = $_POST['mail_id_array'];
    else vlc_exit_page('<li>'.$lang['classes']['mail']['status']['no-messages-selected'].'</li>', 'error', 'classes/mail.php?course='.$page_info['course_id'].'&folder='.$page_info['folder_id']);
    $mail_id_list = implode(', ', $mail_id_array);
    $mark_mail_read_query = <<< END_QUERY
      UPDATE mail_users
      SET mail_status_id = 2
      WHERE to_user_id = {$user_info['user_id']}
      AND mail_id IN ($mail_id_list)
END_QUERY;
    $result = mysql_query($mark_mail_read_query, $site_info['db_conn']);
    $success_message = $lang['classes']['mail']['status']['message-successfully-marked-read'];
    break;
  /* mark unread */
  case 3:
    if (isset($_POST['mail_id_array']) and is_array($_POST['mail_id_array']) and count($_POST['mail_id_array']) > 0) $mail_id_array = $_POST['mail_id_array'];
    else vlc_exit_page('<li>'.$lang['classes']['mail']['status']['no-messages-selected'].'</li>', 'error', 'classes/mail.php?course='.$page_info['course_id'].'&folder='.$page_info['folder_id']);
    $mail_id_list = implode(', ', $mail_id_array);
    $mark_mail_unread_query = <<< END_QUERY
      UPDATE mail_users
      SET mail_status_id = 1
      WHERE to_user_id = {$user_info['user_id']}
      AND mail_id IN ($mail_id_list)
END_QUERY;
    $result = mysql_query($mark_mail_unread_query, $site_info['db_conn']);
    $success_message = $lang['classes']['mail']['status']['message-successfully-marked-unread'];
    break;
  /* send */
  case 4:
    $form_fields = $_POST;
    $error_message = '';
    if (!isset($form_fields['recipient_id_array']) or !is_array($form_fields['recipient_id_array']) or count($form_fields['recipient_id_array']) == 0) $error_message .= '<li>'.$lang['classes']['mail']['status']['recipient-required'].'</li>';
    if (!isset($form_fields['subject']) or strlen(trim($form_fields['subject'])) == 0) $error_message .= '<li>'.$lang['classes']['mail']['status']['subject-required'].'</li>';
    if (isset($form_fields['message_rte']) and strlen(trim($form_fields['message_rte']))) $form_fields['message'] = vlc_convert_html($form_fields['message_rte']);
    elseif (isset($form_fields['message']) and strlen(trim($form_fields['message']))) $form_fields['message'] = str_replace("\n", "[br]\n", stripslashes(trim($form_fields['message'])));
    else $error_message .= '<li>'.$lang['classes']['mail']['status']['message-required'].'</li>';
    if (strlen($error_message) > 0)
    {
      $_SESSION['form_fields'] = $form_fields;
      vlc_exit_page($error_message, 'error', 'classes/mail_form.php?course='.$page_info['course_id'].'&action=1');
    }
    /* prepare message for database query */
    $form_fields['subject'] = addslashes($form_fields['subject']);
    $form_fields['message'] = addslashes($form_fields['message']);
    $insert_mail_query = <<< END_QUERY
      INSERT INTO mail (CREATED, course_id, from_user_id, subject, message)
      VALUES (NULL, {$page_info['course_id']}, {$user_info['user_id']}, '{$form_fields['subject']}', '{$form_fields['message']}')
END_QUERY;
    $result = mysql_query($insert_mail_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "mail"');
    else $last_insert_id = mysql_insert_id();
    $insert_mail_users_query = 'INSERT INTO mail_users (CREATED, mail_id, to_user_id, mail_status_id) VALUES ';
    foreach ($form_fields['recipient_id_array'] as $recipient_id)
    {
      $values_array[] = '(NULL, '.$last_insert_id.', '.$recipient_id.', 1)';
    }
    $insert_mail_users_query .= implode(', ', $values_array);
    $result = mysql_query($insert_mail_users_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "mail_users"');
    $success_message = $lang['classes']['mail']['status']['message-successfully-sent'];
    break;
  /* restore */
  case 5:
    if (isset($_GET['mail']) and is_numeric($_GET['mail'])) $mail_id_array = array($_GET['mail']);
    elseif (isset($_POST['mail_id_array']) and is_array($_POST['mail_id_array']) and count($_POST['mail_id_array']) > 0) $mail_id_array = $_POST['mail_id_array'];
    else vlc_exit_page('<li>'.$lang['classes']['mail']['status']['no-messages-selected'].'</li>', 'error', 'classes/mail.php?course='.$page_info['course_id'].'&folder='.$page_info['folder_id']);
    $mail_id_list = implode(', ', $mail_id_array);
    $restore_mail_query = <<< END_QUERY
      UPDATE mail_users
      SET mail_status_id = 1
      WHERE to_user_id = {$user_info['user_id']}
      AND mail_id IN ($mail_id_list)
END_QUERY;
    $result = mysql_query($restore_mail_query, $site_info['db_conn']);
    $success_message = $lang['classes']['mail']['status']['message-successfully-restored'];
    break;
  /* empty trash */
  case 6:
    $empty_trash_query = <<< END_QUERY
      UPDATE mail_users
      SET mail_status_id = 4
      WHERE to_user_id = {$user_info['user_id']}
      AND mail_status_id = 3
END_QUERY;
    $result = mysql_query($empty_trash_query, $site_info['db_conn']);
    $success_message = $lang['classes']['mail']['status']['trash-successfully-emptied'];
    break;
  default:
    trigger_error('INVALID ACTION ID: '.$page_info['action_id']);
}
vlc_exit_page($success_message, 'success', 'classes/mail.php?course='.$page_info['course_id'].'&folder='.$page_info['folder_id']);
?>

