<?php
$page_info['section'] = 'certificates';
$login_required = 1;
$lang = vlc_get_language();
$user_info = vlc_get_user_info($login_required, 1);
$db_events_array = array();
/* get form fields */
$form_fields = $_POST;
/* create application string */
$app_string = '';
/* create error string */
$error_message = '';
foreach ($form_fields as $key => $value)
{
  if (!is_numeric($key) and !is_numeric($value))
  {
    $key = ucwords(str_replace('_', ' ', $key));
    if (strlen(trim($value)) == 0) $error_message .= '<li>'.$key.' is required.</li>';
    $app_string .= "\n$key: $value\n";
  }
}
if (strlen($error_message))
{
  $_SESSION['form_fields'] = $form_fields;
  switch ($form_fields['cert_prog_id'])
  {
    case 1:
      $return_url = 'certificates/catechesis_lvl_i_app.php';
      break;
    case 2:
      $return_url = 'certificates/catechesis_lvl_ii_app.php';
      break;
    case 6:
      $return_url = 'certificates/adult_formation_app.php';
      break;
    case 7:
      $return_url = 'certificates/es_catechesis_lvl_i_app.php';
      break;
    case 8:
      $return_url = 'certificates/es_catechesis_lvl_ii_app.php';
      break;
    case 9:
      $return_url = 'certificates/social_justice_app.php';
      break;
    case 11:
      $return_url = 'certificates/flm_lvl_i_app.php';
      break;
    case 12:
      $return_url = 'certificates/flm_lvl_ii_app.php';
      break;
    case 13:
      $return_url = 'certificates/ym_app.php';
      break;
    case 15:
      $return_url = 'certificates/special_needs_app.php';
      break;    
    case 16:
      $return_url = 'certificates/es_ministerio_lvl_i_app.php';
      break;
    case 17:
      $return_url = 'certificates/es_ministerio_lvl_ii_app.php';
      break;
    default:
      $return_url = 'certificates/';
  }
  vlc_exit_page($error_message, 'error', $return_url);
}
$app_string_slashed = addslashes($app_string);


/* insert */
$insert_query = <<< END_QUERY
  INSERT INTO certs_users
  SET CREATED = NULL, 
  CREATEDBY = {$user_info['user_id']}, 
  cert_prog_id = {$form_fields['cert_prog_id']}, 
  user_id = {$user_info['user_id']}, 
  cert_status_id = 1, 
  application_notes = '$app_string_slashed'
END_QUERY;
$result = mysql_query($insert_query, $site_info['db_conn']);
if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "certs_users"');
$cert_user_id = mysql_insert_id();
$db_events_array[] = array(CERTS_USERS_CREATE, $cert_user_id);
$db_events_array[] = array(CERT_PROGS_ADD_USER, $form_fields['cert_prog_id']);
$db_events_array[] = array(USERS_ADD_CERT_PROG, $user_info['user_id']);


$order_cost_query = <<< END_QUERY
  SELECT partner_cost, non_partner_cost
  FROM cert_progs
  WHERE cert_prog_id = {$form_fields['cert_prog_id']}
END_QUERY;
$result = mysql_query($order_cost_query, $site_info['db_conn']);
$record = mysql_fetch_array($result);
if (is_numeric($form_fields['diocese_id']) and $form_fields['diocese_id'] > 0 and $form_fields['is_partner'] == 1)
{
  $form_fields['order_cost'] = $record['partner_cost'];
  $discount_id = $form_fields['diocese_id'];
  $discount_type_id = 3;
}
else
{
  $form_fields['order_cost'] = $record['non_partner_cost'];
  $discount_id = $discount_type_id = 'NULL';
}


/* insert student order */
$insert_student_order_query = 'INSERT INTO orders (CREATED, CREATEDBY, product_type_id, product_id, customer_type_id, customer_id, is_active, is_complete, payment_status_id, order_date, discount_type_id, discount_id, order_cost, amount_paid, amount_due) VALUES ';
$insert_student_order_query .= '(NULL, '.$user_info['user_id'].', 6, '.$cert_user_id.', 1, '.$user_info['user_id'].', 1, 1, 2, CURDATE(), '.$discount_type_id.', '.$discount_id.', '.$form_fields['order_cost'].', 0, '.$form_fields['order_cost'].')';
$result = mysql_query($insert_student_order_query, $site_info['db_conn']);
if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "orders"');
$form_fields['order_id'] = mysql_insert_id();
$db_events_array[] = array(ORDERS_CREATE, $form_fields['order_id']);


/* send message to user from administrator */
$from = 'From: "'.$lang['common']['misc']['vlcff-admin'].'" <'.$site_info['vlcff_email'].'>';
$to = $form_fields['email_address'];
$subject = $lang['certificates']['email']['cert-prog-app']['subject'];
$message = sprintf($lang['certificates']['email']['cert-prog-app']['message'], $form_fields['name'], $form_fields['certificate_program'], $form_fields['username'], $form_fields['email_address']);
mail($to, $subject, $message, $from);

/* send additional message to administrator from user */
$from = 'From: "'.$form_fields['name'].'" <'.$form_fields['email_address'].'>';
if ($lang['common']['misc']['current-language-id'] === 2){
    $to = $site_info['webmaster_email'] . ', ' . $site_info['spanish_curriculum_email'];
}else{
    $to = $site_info['webmaster_email'] . ', LFranklin1@udayton.edu';
}
$message .= "\n\n================================================================================\n\nApplication Details:\n$app_string";
mail($to, $subject, $message, $from);


vlc_insert_events($db_events_array);
/* store form field values in session variable */
$_SESSION['form_fields'] = $form_fields;
vlc_redirect('certificates/cert_app_success.php');