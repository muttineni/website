<?php
$page_info['section'] = 'profile';
$login_required = 1;
$lang = vlc_get_language();
$user_info = vlc_get_user_info($login_required, 1);
/* get form fields */
$form_fields = $_POST;
$db_events_array = array();
/* update course registration */
$cancel_course_query = <<< END_QUERY
  UPDATE users_courses
  SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']},
    course_status_id = 4
  WHERE user_course_id = {$form_fields['user_course_id']}
END_QUERY;
$result = mysql_query($cancel_course_query, $site_info['db_conn']);
if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "users_courses"');
$db_events_array[] = array(USERS_COURSES_UPDATE, $form_fields['user_course_id']);
/* update order(s) */
$update_orders_query = <<< END_QUERY
  UPDATE orders
  SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']},
    is_active = 0
  WHERE product_type_id = 1
  AND product_id = {$form_fields['user_course_id']}
END_QUERY;
$result = mysql_query($update_orders_query, $site_info['db_conn']);
if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "orders"');
$order_id_query = 'SELECT order_id FROM orders WHERE product_type_id = 1 AND product_id = '.$form_fields['user_course_id'];
$result = mysql_query($order_id_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $db_events_array[] = array(ORDERS_UPDATE, $record['order_id']);

// issue rainchecks?

/* get course details */
$course_details_query = <<< END_QUERY
  SELECT c.description, c.code
  FROM courses AS c, users_courses AS uc
  WHERE c.course_id = uc.course_id
  AND uc.user_course_id = {$form_fields['user_course_id']}
END_QUERY;
$result = mysql_query($course_details_query, $site_info['db_conn']);
$course_details = mysql_fetch_array($result);
/* mail subject and mail message */
$subject = $lang['profile']['email']['cancel-course']['subject'];
$message = sprintf($lang['profile']['email']['cancel-course']['message'], $user_info['name'], $course_details['description'], $course_details['code'], $site_info['vlcff_email'], $user_info['username'], $user_info['email']);
/* send message to user from administrator */
$from = 'From: "'.$lang['common']['misc']['vlcff-admin'].'" <'.$site_info['vlcff_email'].'>';
$to = $user_info['email'];
mail($to, $subject, $message, $from);
/* send additional message to administrator from user */
$from = 'From: "'.$user_info['full_name'].'" <'.$user_info['email'].'>';
$to = $site_info['webmaster_email'].', '.$site_info['support_email'].', '.$site_info['billing_email'];
mail($to, $subject, $message, $from);
/* exit and show success message */
vlc_insert_events($db_events_array);
vlc_exit_page($lang['profile']['index']['status']['cancel-course-success'], 'success', 'profile/');
?>
