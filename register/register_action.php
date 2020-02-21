<?php
$page_info['section'] = 'register';
$login_required = 1;
$lang = vlc_get_language();
$user_info = vlc_get_user_info($login_required, 1);
/* get form fields */
$form_fields = $_POST;
$db_events_array = array();
/*******************************************************************************
** determine which button was clicked
*/
/* the user clicked the "register" button on the main registration page - show blank payment code form or info page for undergraduate credit courses */
if (isset($form_fields['register']))
{
  if ($form_fields['registration_type_id'] == 1)
  {
    $form_to_show = 1;
    $form_fields['payment_code'] = '';
    $form_fields['discount_type_id'] = 1;
  }
  /* undergraduate credit */
  else $form_to_show = 6;
}
/* the user clicked the "previous" button */
elseif (isset($form_fields['previous']))
{
  $form_to_show = $form_fields['discount_type_id'] = $form_fields['previous_discount_type_id'];
}
/* the user clicked the "cancel" button */
elseif (isset($form_fields['cancel']))
{
  /* clear out "form fields" and "course details" session variables */
  $_SESSION['form_fields'] = $_SESSION['course_details'] = null;
  vlc_exit_page($lang['register']['status']['cancel-message'], 'success', 'register/');
}
/* the user clicked the "next" button */
elseif (isset($form_fields['next']))
{
  $form_fields['previous_discount_type_id'] = $form_fields['discount_type_id'];
  switch ($form_fields['discount_type_id'])
  {
    case 1:
      $form_fields['payment_code'] = trim($form_fields['payment_code']);
      /* the user entered a payment code - show the confirm form with the payment code discount */
      if (strlen($form_fields['payment_code'])) $form_to_show = 4;
      /* the user did not enter a payment code (or entered all spaces) - show the diocese form (with no diocese selected) */
      else
      {
        $form_to_show = $form_fields['discount_type_id'] = 3;
        $diocese_query = <<< END_QUERY
          SELECT diocese_id
          FROM user_info
          WHERE user_id = {$user_info['user_id']}
END_QUERY;
        $result = mysql_query($diocese_query, $site_info['db_conn']);
        $record = mysql_fetch_array($result);
        if (isset($record['diocese_id'])) $form_fields['partner_id'] = $record['diocese_id'];
        else $form_fields['partner_id'] = -1;
      }
      break;
    case 3:
      /* the user selected a diocese - show the confirm form with the diocese discount */
      if (is_numeric($form_fields['partner_id'])) $form_to_show = 4;
      /* the user did not select a diocese - show the confirm form with no discount */
      else
      {
        $form_to_show = $form_fields['discount_type_id'] = 4;
      }
      break;
  }
}
/* the user clicked the "confirm" button on the confirmation page - show the success form */
elseif (isset($form_fields['confirm'])) $form_to_show = 5;
/* the user clicked the "done" button on the waiting list success page */
elseif (isset($form_fields['done']))
{
  /* clear out "form fields" and "course details" session variables */
  $_SESSION['form_fields'] = $_SESSION['course_details'] = null;
  vlc_redirect('profile/');
}
/*******************************************************************************
** determine which form to show
*/
switch ($form_to_show)
{
  /* payment code form */
  case 1:
    $redirect_to = 'register/register_payment_code.php';
    break;
  /* diocese form */
  case 3:
    $redirect_to = 'register/register_diocese.php';
    break;
  /* confirm form */
  case 4:
  /* success form */
  case 5:
    /* see if student is enrolled in related certificate program */
    $cert_prog_query = <<< END_QUERY
      SELECT cu.cert_user_id
      FROM certs_users AS cu, certs_courses AS cc, courses AS c
      WHERE cu.cert_prog_id = cc.cert_prog_id
      AND cc.course_subject_id = c.course_subject_id
      AND cu.cert_status_id != 4
      AND cu.user_id = {$user_info['user_id']}
      AND c.course_id = {$form_fields['course_id']}
END_QUERY;
    $result = mysql_query($cert_prog_query, $site_info['db_conn']);
    if (mysql_num_rows($result)) $form_fields['scoring_required'] = 1;
    else $form_fields['scoring_required'] = 0;
    /* get course details */
    $course_details_query = <<< END_QUERY
      SELECT c.code, c.description, UNIX_TIMESTAMP(y.cycle_start - INTERVAL 2 DAY) AS email_date, UNIX_TIMESTAMP(y.cycle_start) AS cycle_start,
        s.course_subject_id, s.course_level_id, s.course_type_id, s.amazon_link,
        t.ceu, t.credit, t.partner_cost, t.non_partner_cost, t.undergraduate_cost,
        u.first_name, u.last_name
      FROM courses AS c, cycles AS y, course_subjects AS s, course_types AS t, users_courses AS uc, users AS u
      WHERE c.cycle_id = y.cycle_id
      AND c.course_subject_id = s.course_subject_id
      AND s.course_type_id = t.course_type_id
      AND c.course_id = uc.course_id
      AND uc.user_id = u.user_id
      AND uc.user_role_id = 4
      AND CURDATE() >= y.registration_start
      AND CURDATE() <= y.registration_end
      AND c.course_id = {$form_fields['course_id']}
END_QUERY;
    $result = mysql_query($course_details_query, $site_info['db_conn']);
    $course_details = mysql_fetch_array($result);
    /* get course materials */
    $course_materials_query = <<< END_QUERY
      SELECT r.resource_type_id, r.title, r.content, IFNULL(d.author, '') AS author, IFNULL(d.source, '') AS source, d.resource_format_id
      FROM resources AS r LEFT JOIN resource_details AS d ON r.resource_id = d.resource_id
      WHERE r.resource_type_id IN (54, 55)
      AND r.course_subject_id = {$course_details['course_subject_id']}
      ORDER BY r.resource_type_id, r.display_order
END_QUERY;
    $result = mysql_query($course_materials_query, $site_info['db_conn']);
    $course_details['materials'] = array();
    $course_materials_email = "\n";
    $num_required = 0;
    while ($record = mysql_fetch_array($result))
    {
      $course_material_html = $course_material_txt = array();
      /* compile course material details for both html display and text display */
      if (isset($record['author']))
      {
        $course_material_html[] = $record['author'];
        $course_material_txt[] = $record['author'];
      }
      if (isset($record['title']))
      {
        $course_material_html[] = '<i>'.$record['title'].'</i>';
        $course_material_txt[] = $record['title'];
      }
      if (isset($record['source']))
      {
        $course_material_html[] = $record['source'];
        $course_material_txt[] = $record['source'];
      }
      if (isset($record['isbn']))
      {
        $course_material_html[] = '<b>ISBN:</b> '.$record['isbn'];
        $course_material_txt[] = '(ISBN: '.$record['isbn'].')';
      }
      if (isset($record['content']))
      {
        $course_material_html[] = '<ul><li>'.vlc_convert_code($record['content']).'</li></ul>';
        $course_material_txt[] = "\n    - ".preg_replace('/\[url=|\]Order here\[\/url\]/', '', $record['content']) . "\n";
        //$course_material_txt[] = "\n    - ".$record['content'];
      }
      /* determine whether course material is optional or required */
      if ($record['resource_type_id'] == 54)
      {
        $material_label = sprintf($lang['register']['course-details']['misc']['required-materials'], $lang['database']['resource-formats'][$record['resource_format_id']]);
        if ($record['resource_format_id'] == 1) $num_required++;
      }
      elseif ($record['resource_type_id'] == 55) $material_label = sprintf($lang['register']['course-details']['misc']['optional-materials'], $lang['database']['resource-formats'][$record['resource_format_id']]);
      /* combine course material label and content for both html and text */
      $course_details['materials'][] = '<b>'.$material_label.':</b> '.join(' ', $course_material_html);
      $course_materials_email .= "\n * $material_label: ".join('; ', $course_material_txt);
      $course_materials_email = utf8_encode($course_materials_email);
    }
    $course_details['num_required'] = $num_required;
    if ($course_details['num_required'] == 0) $course_materials_email .= "\n * ".$lang['register']['course-details']['misc']['all-materials-online'];
    else
    {
      if (isset($course_details['amazon_link'])) $amazon_url = $course_details['amazon_link'];
      else $amazon_url = 'http://www.amazon.com/';
      $course_materials_email .= sprintf($lang['register']['email']['register-details']['where-to-buy'], $amazon_url);
    }
    /* get cycle start date */
    $course_details['cycle_start'] = date('w|n|j|Y', $course_details['cycle_start']);
    $start_date_array = explode('|', $course_details['cycle_start']);
    $start_date_array[0] = $lang['common']['days']['full'][$start_date_array[0]+1];
    $start_date_array[1] = $lang['common']['months']['full'][$start_date_array[1]];
    array_unshift($start_date_array, $lang['common']['misc']['long-date-format']);
    $course_details['cycle_start'] = call_user_func_array('sprintf', $start_date_array);
    /* get next e-mail notification date */
    $course_details['email_date'] = date('n|j', $course_details['email_date']);
    $start_date_array = explode('|', $course_details['email_date']);
    $start_date_array[0] = $lang['common']['months']['abbr'][$start_date_array[0]];
    array_unshift($start_date_array, $lang['common']['misc']['shorter-date-format']);
    $course_details['email_date'] = call_user_func_array('sprintf', $start_date_array);
    /* initialize order details array */
    $order_details = array();
    /* determine course cost */
    if ($form_fields['registration_type_id'] != 1)
    {
      $order_details['student']['order_cost'] = $course_details['undergraduate_cost'];
    }
    else
    {
      switch ($form_fields['discount_type_id'])
      {
        /* payment code */
        case 1:
          $payment_code_with_slashes = addslashes($form_fields['payment_code']);
          $payment_code_query = <<< END_QUERY
            SELECT payment_code_id, code, description, partner_id, student_seminar_cost, partner_seminar_cost, student_course_cost, partner_course_cost
            FROM payment_codes
            WHERE CURDATE() >= active_start
            AND CURDATE() <= active_end
            AND code = '$payment_code_with_slashes'
END_QUERY;
          $result = mysql_query($payment_code_query, $site_info['db_conn']);
          if (mysql_num_rows($result))
          {
            $record = mysql_fetch_array($result);
            $form_fields['discount_id'] = $form_fields['payment_code_id'] = $record['payment_code_id'];
            $form_fields['hidden_field'] = '<input type="hidden" name="payment_code" value="'.htmlspecialchars($record['code']).'">';
            $course_details['discount_type_description'] = $lang['database']['discount-types'][$form_fields['discount_type_id']];
            $course_details['discount_description'] = $record['description'].' ('.$record['code'].')';
            /* calculate student cost based on the "student cost" fields and course type */
            if ($course_details['course_type_id'] == 1) $order_details['student']['order_cost'] = $record['student_seminar_cost'];
            else $order_details['student']['order_cost'] = $record['student_course_cost'];
            $course_details['student_cost'] = $order_details['student']['order_cost'] / 100;
            $course_details['student_cost'] = '$'.number_format($course_details['student_cost'], 2);
            /* if the code is linked to a partner, then calculate partner cost */
            if (isset($record['partner_id']))
            {
              $form_fields['partner_id'] = $record['partner_id'];
              /* calculate partner cost based on the "partner cost" fields and course type */
              if ($course_details['course_type_id'] == 1) $order_details['partner']['order_cost'] = $record['partner_seminar_cost'];
              else $order_details['partner']['order_cost'] = $record['partner_course_cost'];
            }
          }
          else
          {
            /* store form field values in session variable */
            $_SESSION['form_fields'] = $form_fields;
            vlc_exit_page($lang['register']['status']['invalid-payment-code'], 'error', 'register/register_payment_code.php');
          }
          break;
        /* special partner */
        case 2:
        /* diocese */
        case 3:
          $course_details['discount_type_description'] = $lang['database']['discount-types'][$form_fields['discount_type_id']];
          $course_details['discount_description'] = $lang['database']['partners'][$form_fields['partner_id']];
          $form_fields['discount_id'] = $form_fields['partner_id'];
          $form_fields['hidden_field'] = '<input type="hidden" name="partner_id" value="'.$form_fields['partner_id'].'">';
          $partner_query = <<< END_QUERY
            SELECT is_partner, student_seminar_cost, partner_seminar_cost, student_course_cost, partner_course_cost
            FROM partners
            WHERE partner_id = {$form_fields['partner_id']}
END_QUERY;
          $result = mysql_query($partner_query, $site_info['db_conn']);
          $record = mysql_fetch_array($result);
          /* calculate student cost */
          if (isset($record['student_seminar_cost']))
          {
            if ($course_details['course_type_id'] == 1) $order_details['student']['order_cost'] = $record['student_seminar_cost'];
            else $order_details['student']['order_cost'] = $record['student_course_cost'];
            if ($course_details['course_type_id'] == 1) $order_details['partner']['order_cost'] = $record['partner_seminar_cost'];
            else $order_details['partner']['order_cost'] = $record['partner_course_cost'];
          }
          else
          {
            if ($record['is_partner']) $order_details['student']['order_cost'] = $course_details['partner_cost'];
            else $order_details['student']['order_cost'] = $course_details['non_partner_cost'];
          }
          $course_details['student_cost'] = $order_details['student']['order_cost'] / 100;
          $course_details['student_cost'] = '$'.number_format($course_details['student_cost'], 2);
          break;
        /* none of the discount types above apply */
        case 4:
          $course_details['discount_type_description'] = $course_details['discount_description'] = 'N/A';
          $order_details['student']['order_cost'] = $course_details['non_partner_cost'];
          $course_details['student_cost'] = $order_details['student']['order_cost'] / 100;
          $course_details['student_cost'] = '$'.number_format($course_details['student_cost'], 2);
          break;
      }
    }
    /* confirm form */
    if ($form_to_show == 4) $redirect_to = 'register/register_confirm.php';
    /* success form */
    else
    {
      /* check for errors */
      $error_message = '';
      /* see how many students are registered for this course */
      $student_count_query = <<< END_QUERY
        SELECT COUNT(*) AS num_students
        FROM users_courses
        WHERE course_id = {$form_fields['course_id']}
        AND user_role_id = 5
        AND course_status_id = 2
END_QUERY;
      $result = mysql_query($student_count_query, $site_info['db_conn']);
      $num_students = mysql_result($result, 0);
      /* cannot register because course is full */
      if ($form_fields['action_id'] == 1 and $num_students >= 15) $error_message = '<li>'.$lang['register']['status']['course-full'].'</li>';
      /* cannot put on waiting list because course is not full */
      if ($form_fields['action_id'] == 2 and $num_students < 15) $error_message = '<li>'.$lang['register']['status']['course-not-full'].'</li>';
      /* see which courses and how many courses the user has registered for as a student in the current registration period */
      $user_course_query = <<< END_QUERY
        SELECT uc.course_id, uc.course_status_id
        FROM users_courses AS uc, courses AS c, cycles AS y
        WHERE uc.course_id = c.course_id
        AND c.cycle_id = y.cycle_id
        AND CURDATE() >= y.registration_start
        AND CURDATE() <= y.registration_end
        AND uc.user_id = {$user_info['user_id']}
        AND uc.user_role_id = 5
        AND uc.course_status_id IN (1, 2)
END_QUERY;
      $result = mysql_query($user_course_query, $site_info['db_conn']);
      $num_courses = mysql_num_rows($result);
      $course_id_array = array();
      while ($record = mysql_fetch_array($result)) $course_id_array[] = $record['course_id'];
      /* student is already registered for this course */
      if (in_array($form_fields['course_id'], $course_id_array)) $error_message = '<li>'.$lang['register']['status']['already-registered'].'</li>';
      /* student is already registered for two courses; limit is two */
      if ($num_courses >= 2) $error_message = '<li>'.$lang['register']['status']['course-limit'].'</li>';
      /* user is not a student */
      if (in_array(5, $user_info['user_roles']) == false) $error_message = '<li>'.$lang['register']['status']['students-only'].'</li>';
      /* exit if errors have occurred */
      if (strlen($error_message)) vlc_exit_page($error_message, 'error', 'register/');
      /* payment status is "no charge" if order cost is 0 */
      if ($order_details['student']['order_cost'] == 0)
      {
        $order_details['student']['payment_status_id'] = 1;
        $course_cost_message = sprintf($lang['register']['email']['register']['no-charge-message'], $lang['database']['course-types'][$course_details['course_type_id']]);
      }
      else
      {
        $order_details['student']['payment_status_id'] = 2;
        $course_cost_message = sprintf($lang['register']['email']['register']['course-cost-message'], $lang['database']['course-types'][$course_details['course_type_id']], $course_details['student_cost'], $course_details['code']);
      }
      /* action = register */
      $order_status = 1;
      if ($form_fields['action_id'] == 1)
      {
        $course_status_id = 2;
        $facilitator_name = $course_details['first_name'].' '.$course_details['last_name'];
        if ($form_fields['registration_type_id'] == 1)
        {
          $subject = $lang['register']['email']['register']['subject'];
          $message = sprintf($lang['register']['email']['register']['message'], $user_info['name'], utf8_encode($course_details['description']), $course_details['code'], $course_cost_message, $site_info['vlcff_email'], $user_info['username'], $user_info['email']);
          $course_details_subject = $lang['register']['email']['register-details']['subject'];
          $course_details_message = sprintf($lang['register']['email']['register-details']['message'], $course_details['email_date'], $user_info['name'], utf8_encode($course_details['description']), $course_details['code'], $lang['register']['email']['register-details']['ceu-invoice-notice'], $lang['register']['email']['register-details']['start-video'], $course_details['cycle_start'], $facilitator_name, $course_materials_email, $site_info['vlcff_email'], $user_info['username'], $user_info['email']);
        }
        else
        {
          $course_details_subject = $lang['register']['email']['register-details']['subject'].' ('.$lang['register']['common']['misc']['undergraduate-credit'].')';
          $course_details_message = sprintf($lang['register']['email']['register-details']['message'], $course_details['email_date'], $user_info['name'], utf8_encode($course_details['description']), $course_details['code'], $lang['register']['email']['register-details']['credit-invoice-notice'], $lang['register']['email']['register-details']['start-video'], $course_details['cycle_start'], $facilitator_name, $course_materials_email, $site_info['vlcff_email'], $user_info['username'], $user_info['email']);
        }
      }
      /* action = waiting list */
      elseif ($form_fields['action_id'] == 2)
      {
        $course_status_id = 1;
        $order_status = 0;
        if ($lang['common']['misc']['current-language-id'] == 2) $also_send_to = 'romerojo@notes.udayton.edu';
        $subject = $lang['register']['email']['wait']['subject'];
        $message = sprintf($lang['register']['email']['wait']['message'], $user_info['name'], utf8_encode($course_details['description']), $course_details['code'], $site_info['vlcff_email'], $user_info['username'], $user_info['email']);
      }
      /* process registration or waiting list */
      $register_query = <<< END_QUERY
        INSERT INTO users_courses
        SET CREATED = NULL, CREATEDBY = {$user_info['user_id']}, course_id = {$form_fields['course_id']}, user_id = {$user_info['user_id']}, user_role_id = 5, course_status_id = $course_status_id, registration_type_id = {$form_fields['registration_type_id']}, is_scored = {$form_fields['is_scored']}
END_QUERY;
      $result = mysql_query($register_query, $site_info['db_conn']);
      if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "users_courses"');
      $user_course_id = mysql_insert_id();
      $db_events_array[] = array(USERS_COURSES_CREATE, $user_course_id);
      $db_events_array[] = array(COURSES_ADD_USER, $form_fields['course_id']);
      $db_events_array[] = array(USERS_ADD_COURSE, $user_info['user_id']);
      $form_fields['amount_due'] = $order_details['student']['order_cost'];
      /* update discount type and discount entity if no discount was applied */
      if (!isset($form_fields['discount_type_id']) or $form_fields['discount_type_id'] == 4) $form_fields['discount_id'] = $form_fields['discount_type_id'] = 'NULL';
      /* insert student order */
      $insert_student_order_query = 'INSERT INTO orders (CREATED, CREATEDBY, product_type_id, product_id, customer_type_id, customer_id, is_active, is_complete, payment_status_id, order_date, discount_type_id, discount_id, order_cost, amount_paid, amount_due) VALUES ';
      $insert_student_order_query .= '(NULL, '.$user_info['user_id'].', 1, '.$user_course_id.', 1, '.$user_info['user_id'].', '.$order_status.', 1, '.$order_details['student']['payment_status_id'].', CURDATE(), '.$form_fields['discount_type_id'].', '.$form_fields['discount_id'].', '.$order_details['student']['order_cost'].', 0, '.$order_details['student']['order_cost'].')';
      $result = mysql_query($insert_student_order_query, $site_info['db_conn']);
      if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "orders"');
      $form_fields['order_id'] = mysql_insert_id();
      $db_events_array[] = array(ORDERS_CREATE, $form_fields['order_id']);
      /* insert partner order (if applicable) */
      if (isset($order_details['partner']['order_cost']) and $order_details['partner']['order_cost'] > 0)
      {
        $insert_partner_order_query = 'INSERT INTO orders (CREATED, CREATEDBY, product_type_id, product_id, customer_type_id, customer_id, is_active, is_complete, payment_status_id, order_date, discount_type_id, discount_id, order_cost, amount_paid, amount_due) VALUES ';
        $insert_partner_order_query .= '(NULL, '.$user_info['user_id'].', 1, '.$user_course_id.', 2, '.$form_fields['partner_id'].', '.$order_status.', 1, 2, CURDATE(), '.$form_fields['discount_type_id'].', '.$form_fields['discount_id'].', '.$order_details['partner']['order_cost'].', 0, '.$order_details['partner']['order_cost'].')';
        $result = mysql_query($insert_partner_order_query, $site_info['db_conn']);
        if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "orders"');
        $db_events_array[] = array(ORDERS_CREATE, mysql_insert_id());
      }
      /* send message to user from administrator */
      $to = $user_info['email'];
      $from_name = $lang['common']['misc']['vlcff-admin'];
      $from_email = $site_info['vlcff_email'];
      $reply_email = $site_info['vlcff_email'];
      
      if (isset($subject)) vlc_utf8_mail($to, $subject, $message, $from_name, $from_email, $reply_email);
      if (isset($course_details_subject)) vlc_utf8_mail($to, $course_details_subject, $course_details_message, $from_name, $from_email, $reply_email);
      
      
      /* send additional message to administrator from user */
      $to = $site_info['webmaster_email'].', '.$site_info['support_email'] . ', ' . $site_info['register_email'];
      $from_name = $user_info['full_name'];
      $from_email = $user_info['email'];
      $reply_email = $user_info['email'];
      
      if ($form_fields['registration_type_id'] != 1) $to .= ', '.$site_info['billing_email'];
      if (isset($also_send_to)) $to .= ', '.$also_send_to;
      if (isset($subject)) vlc_utf8_mail($to, $subject, $message, $from_name, $from_email, $reply_email);
      if (isset($course_details_subject)) vlc_utf8_mail($to, $course_details_subject, $course_details_message, $from_name, $from_email, $reply_email);
      $redirect_to = 'register/register_success.php';
    }
    /* store course details in session variable */
    $_SESSION['course_details'] = $course_details;
    break;
  /* undergraduate credit info form */
  case 6:
    /* get course details */
    $course_details_query = <<< END_QUERY
      SELECT code, description
      FROM courses
      WHERE course_id = {$form_fields['course_id']}
END_QUERY;
    $result = mysql_query($course_details_query, $site_info['db_conn']);
    $course_details = mysql_fetch_array($result);
    $redirect_to = 'register/register_credit.php';
    /* store course details in session variable */
    $_SESSION['course_details'] = $course_details;
    break;
}
vlc_insert_events($db_events_array);
/* store form field values in session variable */
$_SESSION['form_fields'] = $form_fields;
vlc_redirect($redirect_to);
