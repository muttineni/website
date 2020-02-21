<?php
$page_info['section'] = 'cms';
$page_info['login_required'] = 1;
$user_info = vlc_get_user_info($page_info['login_required']);
$lang = vlc_get_language();
/* get form fields from posted variables */
$form_fields = $_POST;
/* initialize success message array */
$success_message_array = array();
$db_events_array = array();
/*******************************************************************************
** editing multiple records (coming from users_courses.php)
*/
/* build update queries */
if (isset($form_fields['user_course_id_array']))
{
  if (is_array($form_fields['user_course_id_array']) and count($form_fields['user_course_id_array']))
  {
    $update_query_array = array();
    foreach ($form_fields['user_course_array'] as $user_course_id => $user_course_record)
    {
      /* check to see if user selected "update all checked records" */
      if (is_numeric($form_fields['update_all_course_status_id'])) $user_course_record['course_status_id'] = $form_fields['update_all_course_status_id'];
      /* update course status */
      if ($user_course_record['course_status_id'] != $user_course_record['previous_course_status_id'])
      {
        $update_query_array[] = 'UPDATE users_courses SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', course_status_id = '.$user_course_record['course_status_id'].' WHERE user_course_id = '.$user_course_id;
        /* add record to success message */
        $success_message_array['users_courses'][$user_course_id] = '<a href="#user-course-'.$user_course_id.'">'.$user_course_id.'</a>';
        $db_events_array[] = array(USERS_COURSES_UPDATE, $user_course_id);
      }
    }
    /* execute update queries */
    foreach ($update_query_array as $update_query)
    {
      $result = mysql_query($update_query, $site_info['db_conn']);
      if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "users_courses"');
    }
  }
  /* success message */
  if (count($success_message_array))
  {
    $success_message = '<p>The following changes were saved:</p>';
    $success_message .= '<ul>';
    if (isset($success_message_array['users_courses'])) $success_message .= '<li>Update Course Status ('.count($success_message_array['users_courses']).'): '.join(', ', $success_message_array['users_courses']).'</li>';
    $success_message .= '</ul>';
  }
  else $success_message = '<p>No changes were saved.</p>';
  vlc_insert_events($db_events_array);
  /* return to search results */
  vlc_exit_page($success_message, 'success', 'cms/users_courses.php?'.$_SERVER['QUERY_STRING']);
}
/*******************************************************************************
** editing a single record (coming from user_course_details.php)
*/
/* return url */
$return_url = 'cms/user_course_details.php?user_course='.$form_fields['user_course_id'];
/* query arrays */
$update_query_array = $insert_query_array = array();
/* get course registration details */
$users_courses_query = <<< END_QUERY
  SELECT course_id, user_role_id, course_status_id, registration_type_id
  FROM users_courses
  WHERE user_course_id = {$form_fields['user_course_id']}
END_QUERY;
$result = mysql_query($users_courses_query, $site_info['db_conn']);
$user_course_details = mysql_fetch_array($result);
$course_changed = $form_fields['course_id'] != $user_course_details['course_id'];
$reg_type_changed = false;
$is_ceu = true;
if ($form_fields['registration_type_id'] != $user_course_details['registration_type_id'])
{
  if ($form_fields['registration_type_id'] == 1)
  {
    $reg_type_changed = true;
  }
  elseif ($user_course_details['registration_type_id'] == 1)
  {
    $reg_type_changed = true;
    $is_ceu = false;
  }
  else
  {
    $is_ceu = false;
  }
}
elseif ($form_fields['registration_type_id'] != 1)
{
  $is_ceu = false;
}
/* process updates */
if (($course_changed and $is_ceu) or $reg_type_changed)
{
  /* get course details */
  $course_info_query = <<< END_QUERY
    SELECT c.course_id, t.course_type_id, t.partner_cost, t.non_partner_cost, t.undergraduate_cost
    FROM courses AS c, course_subjects AS s, course_types AS t
    WHERE c.course_subject_id = s.course_subject_id
    AND s.course_type_id = t.course_type_id
    AND c.course_id IN ({$form_fields['course_id']}, {$user_course_details['course_id']})
END_QUERY;
  $result = mysql_query($course_info_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) $course_details_array[$record['course_id']] = $record;
  /* if course type is different, update order details based on new course type cost */
  $course_type_changed = $course_details_array[$user_course_details['course_id']]['course_type_id'] != $course_details_array[$form_fields['course_id']]['course_type_id'];
  if (($course_type_changed and $is_ceu) or $reg_type_changed)
  {
    /* get order details (all orders linked to the course registration) */
    $order_details_query = <<< END_QUERY
      SELECT order_id, is_active, customer_type_id, IFNULL(discount_type_id, 'NULL') AS discount_type_id, IFNULL(discount_id, 'NULL') AS discount_id, order_cost, amount_paid
      FROM orders
      WHERE product_type_id = 1
        AND product_id = {$form_fields['user_course_id']}
END_QUERY;
    $result = mysql_query($order_details_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result))
    {
      $order_status = 1;
      $update_order = 0;
      $new_order_cost = $record['order_cost'];
      /* if there is a discount applied to the original order */
      if (isset($record['discount_type_id']) and is_numeric($record['discount_type_id']))
      {
        /* if discount type is payment code */
        if ($record['discount_type_id'] == 1)
        {
          $payment_code_query = 'SELECT student_seminar_cost, partner_seminar_cost, student_course_cost, partner_course_cost FROM payment_codes WHERE payment_code_id = '.$record['discount_id'];
          $payment_code_result = mysql_query($payment_code_query, $site_info['db_conn']);
          $payment_code_details = mysql_fetch_array($payment_code_result);
          /* if student order */
          if ($record['customer_type_id'] == 1)
          {
            /* undergraduate/graduate */
            if (!$is_ceu)
            {
              $new_order_cost = $course_details_array[$form_fields['course_id']]['undergraduate_cost'];
              $record['discount_id'] = $record['discount_type_id'] = 'NULL';
            }
            /* seminar */
            elseif ($course_details_array[$form_fields['course_id']]['course_type_id'] == 1) $new_order_cost = $payment_code_details['student_seminar_cost'];
            /* course */
            else $new_order_cost = $payment_code_details['student_course_cost'];
          }
          /* else partner order */
          else
          {
            /* undergraduate/graduate */
            if (!$is_ceu or $record['is_active'] == 0) $order_status = 0;
            /* seminar */
            elseif ($course_details_array[$form_fields['course_id']]['course_type_id'] == 1) $new_order_cost = $payment_code_details['partner_seminar_cost'];
            /* course */
            else $new_order_cost = $payment_code_details['partner_course_cost'];
          }
          $update_order = 1;
        }
        /* else discount type is diocese or partner */
        else
        {
          $partner_query = 'SELECT student_seminar_cost, partner_seminar_cost, student_course_cost, partner_course_cost FROM partners WHERE partner_id = '.$record['discount_id'];
          $partner_result = mysql_query($partner_query, $site_info['db_conn']);
          $partner_details = mysql_fetch_array($partner_result);
          /* special partnership arrangement */
          if (isset($partner_details['student_seminar_cost']) and is_numeric($partner_details['student_seminar_cost']))
          {
            /* if student order */
            if ($record['customer_type_id'] == 1)
            {
              /* undergraduate/graduate */
              if (!$is_ceu)
              {
                $new_order_cost = $course_details_array[$form_fields['course_id']]['undergraduate_cost'];
                $record['discount_id'] = $record['discount_type_id'] = 'NULL';
              }
              /* seminar */
              elseif ($course_details_array[$form_fields['course_id']]['course_type_id'] == 1) $new_order_cost = $partner_details['student_seminar_cost'];
              /* course */
              else $new_order_cost = $partner_details['student_course_cost'];
            }
            /* else partner order */
            else
            {
              /* undergraduate/graduate */
              if (!$is_ceu or $record['is_active'] == 0) $order_status = 0;
              /* seminar */
              elseif ($course_details_array[$form_fields['course_id']]['course_type_id'] == 1) $new_order_cost = $partner_details['partner_seminar_cost'];
              /* course */
              else $new_order_cost = $partner_details['partner_course_cost'];
            }
          }
          /* standard partner cost */
          else
          {
            /* undergraduate/graduate */
            if (!$is_ceu)
            {
              $new_order_cost = $course_details_array[$form_fields['course_id']]['undergraduate_cost'];
              $record['discount_id'] = $record['discount_type_id'] = 'NULL';
            }
            /* ceu */
            else
            {
              $new_order_cost = $course_details_array[$form_fields['course_id']]['partner_cost'];
            }
          }
          $update_order = 1;
        }
      }
      /* else there is no discount type - non partner */
      else
      {
        /* undergraduate/graduate */
        if (!$is_ceu)
        {
          $new_order_cost = $course_details_array[$form_fields['course_id']]['undergraduate_cost'];
          $record['discount_id'] = $record['discount_type_id'] = 'NULL';
        }
        /* ceu */
        else
        {
          $new_order_cost = $course_details_array[$form_fields['course_id']]['non_partner_cost'];
        }
        $update_order = 1;
      }
      if ($update_order)
      {
        $amount_due = $new_order_cost - $record['amount_paid'];
        if ($record['amount_paid'] == 0)
        {
          if ($amount_due == 0) $payment_status_id = 1;
          else $payment_status_id = 2;
        }
        else
        {
          if ($amount_due == 0) $payment_status_id = 4;
          elseif ($amount_due > 0) $payment_status_id = 3;
          else $payment_status_id = 5;
        }
        $update_query_array[] = 'UPDATE orders SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', is_active = '.$order_status.', discount_type_id = '.$record['discount_type_id'].', discount_id = '.$record['discount_id'].', payment_status_id = '.$payment_status_id.', order_cost = '.$new_order_cost.', amount_due = '.$amount_due.' WHERE order_id = '.$record['order_id'];
        $db_events_array[] = array(ORDERS_UPDATE, $record['order_id']);
      }
    }
  }
}
if (is_numeric($form_fields['certificate_date_year']) and is_numeric($form_fields['certificate_date_month']) and is_numeric($form_fields['certificate_date_day'])) $certificate_date = $form_fields['certificate_date_year'].'-'.$form_fields['certificate_date_month'].'-'.$form_fields['certificate_date_day'];
else $certificate_date = '';
if (!isset($form_fields['is_scored'])) $form_fields['is_scored'] = 0;
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = addslashes($value);
}
$update_query_array[] = 'UPDATE users_courses SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', course_id = '.$form_fields['course_id'].', course_status_id = '.$form_fields['course_status_id'].', registration_type_id = '.$form_fields['registration_type_id'].', certificate_date = NULLIF(\''.$certificate_date.'\', \'\'), notes = NULLIF(\''.trim($form_fields['notes']).'\', \'\'), is_scored = '.$form_fields['is_scored'].', score_level_id = '.$form_fields['score_level_id'].', facilitator_notes = NULLIF(\''.trim($form_fields['facilitator_notes']).'\', \'\') WHERE user_course_id = '.$form_fields['user_course_id'];
foreach ($update_query_array as $update_query)
{
  $result = mysql_query($update_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED');
}
/* create success message */
$success_message = '<p>Course Registration Details Updated.</p>';
$db_events_array[] = array(USERS_COURSES_UPDATE, $form_fields['user_course_id']);
vlc_insert_events($db_events_array);
/* return to order details page */
vlc_exit_page($success_message, 'success', $return_url);
?>
