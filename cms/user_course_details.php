<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'user-course-details';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get user-course id */
$user_course_id = intval($_GET['user_course']);
/* begin output variable */
$output = '';
/* return to previous page link */
if (isset($_SERVER['HTTP_REFERER']) and $return_url = strstr($_SERVER['HTTP_REFERER'], 'cms/')) $output .= '<p>'.vlc_internal_link('Return to Previous Page', $return_url).'</p>';
/* get user-course details */
$user_course_query = <<< END_QUERY
  SELECT uc.course_id, uc.user_id, uc.course_status_id, uc.registration_type_id, uc.user_role_id, r.description AS user_role,
    IFNULL(uc.notes, '') AS notes, u.first_name, u.last_name,
    uc.is_scored, IFNULL(uc.score_level_id, -1) AS score_level_id, IFNULL(uc.facilitator_notes, '') AS facilitator_notes,
    IFNULL(MONTH(uc.certificate_date), -1) AS certificate_date_month,
    IFNULL(DAYOFMONTH(uc.certificate_date), -1) AS certificate_date_day,
    IFNULL(YEAR(uc.certificate_date), -1) AS certificate_date_year
  FROM users_courses AS uc, users AS u, courses AS c, user_roles AS r
  WHERE uc.user_id = u.user_id
  AND uc.course_id = c.course_id
  AND uc.user_role_id = r.user_role_id
  AND uc.user_course_id = $user_course_id
END_QUERY;
$result = mysql_query($user_course_query, $site_info['db_conn']);
$user_course_details = mysql_fetch_array($result);
foreach ($user_course_details as $key => $value)
{
  //if (is_string($value)) $user_course_details[$key] = htmlspecialchars($value);
}
/* get orders linked to this registration */
if ($user_course_details['user_role_id'] == 4)
{
  $order_query = <<< END_QUERY
    SELECT o.order_id, IF(o.is_active, 'Active', 'Inactive') AS order_status, s.description AS payment_status,
      o.product_id, o.product_type_id, d.description AS product_type,
      o.customer_id, o.customer_type_id, m.description AS customer_type,
      UNIX_TIMESTAMP(o.order_date) AS order_date_timestamp,
      CONCAT(u.first_name, ' ', u.last_name) AS customer
    FROM users_courses AS uc, orders AS o, users AS u,
      product_types AS d, customer_types AS m, payment_status AS s
    WHERE uc.course_id = o.product_id
      AND uc.user_id = o.customer_id
      AND o.customer_id = u.user_id
      AND o.customer_type_id = 3
      AND o.product_type_id = 3
      AND o.customer_type_id = m.customer_type_id
      AND o.product_type_id = d.product_type_id
      AND o.payment_status_id = s.payment_status_id
      AND uc.user_course_id = $user_course_id
    ORDER BY o.order_date, o.order_id
END_QUERY;
}
else
{
  $order_query = <<< END_QUERY
    SELECT o.order_id, IF(o.is_active, 'Active', 'Inactive') AS order_status, s.description AS payment_status,
      o.product_id, o.product_type_id, d.description AS product_type,
      o.customer_id, o.customer_type_id, m.description AS customer_type,
      UNIX_TIMESTAMP(o.order_date) AS order_date_timestamp,
      IF(o.customer_type_id = 2, p.description, CONCAT(u.first_name, ' ', u.last_name)) AS customer
    FROM users_courses AS uc, orders AS o
        LEFT JOIN users AS u ON o.customer_id = u.user_id AND o.customer_type_id = 1
        LEFT JOIN partners AS p ON o.customer_id = p.partner_id AND o.customer_type_id = 2,
      product_types AS d, customer_types AS m, payment_status AS s
    WHERE uc.user_course_id = o.product_id
      AND o.product_type_id = 1
      AND o.customer_type_id = m.customer_type_id
      AND o.product_type_id = d.product_type_id
      AND o.payment_status_id = s.payment_status_id
      AND uc.user_course_id = $user_course_id
    ORDER BY o.order_date, o.order_id
END_QUERY;
}
$result = mysql_query($order_query, $site_info['db_conn']);
$order_details = array();
while ($record = mysql_fetch_array($result)) $order_details[$record['order_id']] = $record;
/* get event details */
$entity_id = $user_course_id;
$event_type_array = array(
  USERS_COURSES_CREATE,
  USERS_COURSES_UPDATE
);
$event_history = vlc_get_event_history($event_type_array, $entity_id);
/* build array for "course" select box */
$course_options_array = array();
$course_options_query = <<< END_QUERY
  SELECT c.course_id, c.code AS course_code, c.description, y.cycle_id, y.code AS cycle_code, UNIX_TIMESTAMP(y.cycle_start) AS cycle_start
  FROM courses AS c, cycles AS y
  WHERE c.cycle_id = y.cycle_id
  ORDER BY y.cycle_start DESC, c.code
END_QUERY;
$result = mysql_query($course_options_query, $site_info['db_conn']);
$previous_cycle = 0;
while ($record = mysql_fetch_array($result))
{
  $current_cycle = $record['cycle_id'];
  if ($current_cycle != $previous_cycle)
  {
    $previous_cycle = $current_cycle;
    $course_options_array[$current_cycle]['label'] = $record['cycle_code'].' - '.date('M. d, Y', $record['cycle_start']).' ('.$record['cycle_id'].')';
  }
  $course_options_array[$current_cycle]['options'][$record['course_id']] = $record['course_code'].' - '.$record['description'].' ('.$record['course_id'].')';
}
/* build array for "course status" select box */
$course_status_options_array = array();
$course_status_query = <<< END_QUERY
  SELECT course_status_id, description
  FROM course_status
  ORDER BY course_status_id
END_QUERY;
$result = mysql_query($course_status_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $course_status_options_array[$record['course_status_id']] = $record['description'];
/* get registration type options */
$registration_type_query = <<< END_QUERY
  SELECT registration_type_id, description
  FROM registration_types
  ORDER BY registration_type_id
END_QUERY;
$result = mysql_query($registration_type_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $registration_type_options_array[$record['registration_type_id']] = $record['description'];
/* get scoring level options */
$score_level_query = <<< END_QUERY
  SELECT score_level_id, description
  FROM score_levels
  ORDER BY score_level_id
END_QUERY;
$result = mysql_query($score_level_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $score_level_options_array[$record['score_level_id']] = $record['description'];
/* build array for "user role" select box */
$user_role_options_array = array(4 => 'Facilitator', 5 => 'Student');
/* month, day, year arrays */
for ($i = 1; $i <= 12; $i++) $months_array[$i] = date('F', mktime(0,0,0,$i,1,0));
for ($i = 1; $i <= 31; $i++) $days_array[$i] = $i;
for ($i = 2000; $i <= 2020; $i++) $years_array[$i] = $i;
/* add user-course details to output */
$output .= '<form method="post" action="user_course_action.php">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
$output .= '<tr><td><b>Course Registration ID:</b></td><td colspan="3">'.$user_course_id.'<input type="hidden" name="user_course_id" value="'.$user_course_id.'"></td></tr>';
$output .= '<tr>';
$output .= '<td><b>User:</b></td>';
$output .= '<td>'.$user_course_details['first_name'].' '.$user_course_details['last_name'].' ('.vlc_internal_link($user_course_details['user_id'], 'cms/user_details.php?user='.$user_course_details['user_id']).')</td>';
$output .= '<td><b>Course:</b></td>';
$output .= '<td>'.vlc_select_box($course_options_array, 'array', 'course_id', $user_course_details['course_id'], true).' ('.vlc_internal_link($user_course_details['course_id'], 'cms/course_details.php?course='.$user_course_details['course_id']).')</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>User Role:</b></td>';
$output .= '<td>'.$user_course_details['user_role'].'</td>';
$output .= '<td><b>Course Status:</b></td>';
$output .= '<td>'.vlc_select_box($course_status_options_array, 'array', 'course_status_id', $user_course_details['course_status_id'], true).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Is Scored:</b></td>';
$output .= '<td><input type="checkbox" name="is_scored" value="1"'.($user_course_details['is_scored'] ? ' checked="checked"' : '').'></td>';
$output .= '<td><b>Score:</b></td>';
$output .= '<td>'.vlc_select_box($score_level_options_array, 'array', 'score_level_id', $user_course_details['score_level_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr><td valign="top"><b>Facilitator Notes:</b></td><td colspan="3"><textarea rows="5" cols="80" name="facilitator_notes">'.$user_course_details['facilitator_notes'].'</textarea></td></tr>';
$output .= '<tr>';
$output .= '<td><b>Registration Type:</b></td>';
$output .= '<td colspan="3">'.vlc_select_box($registration_type_options_array, 'array', 'registration_type_id', $user_course_details['registration_type_id'], true).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Certificate Date:</b></td>';
$output .= '<td colspan="3">';
$output .= '<nobr>';
$output .= vlc_select_box($months_array, 'array', 'certificate_date_month', $user_course_details['certificate_date_month'], false).' ';
$output .= vlc_select_box($days_array, 'array', 'certificate_date_day', $user_course_details['certificate_date_day'], false).' ';
$output .= vlc_select_box($years_array, 'array', 'certificate_date_year', $user_course_details['certificate_date_year'], false).' ';
$output .= '<img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].certificate_date_year,document.forms[0].certificate_date_month,document.forms[0].certificate_date_day,false,false,this);">';
$output .= '</nobr>';
$output .= '</td>';
$output .= '</tr>';
$output .= '<tr><td valign="top"><b>Notes:</b></td><td colspan="3"><textarea rows="5" cols="80" name="notes">'.$user_course_details['notes'].'</textarea></td></tr>';
$output .= '<tr><td colspan="4" align="center"><input type="submit" value="Submit"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
$output .= '<h3>Orders:</h3>';
$output .= '<p>The following orders are linked to this course registration.</p><ul><li>To view additional order details, click the <b>&quot;Order ID&quot;</b> link.</li></ul>';
$output .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
$output .= '<tr><th>&nbsp;</th><th>Order ID</th><th>Order Status</th><th>Order Date</th><th>Payment Status</th><th>Customer</th><th>Customer Type</th><th>Product Type</th></tr>';
if (count($order_details))
{
  $i = 1;
  foreach ($order_details as $order)
  {
    if ($order['customer_type_id'] == 2) $customer_id_link = vlc_internal_link($order['customer_id'], 'cms/partner_details.php?partner='.$order['customer_id']);
    else $customer_id_link = vlc_internal_link($order['customer_id'], 'cms/user_details.php?user='.$order['customer_id']);
    $output .= '<tr>';
    $output .= '<td>'.$i++.'.</td>';
    $output .= '<td align="center">'.vlc_internal_link($order['order_id'], 'cms/order_details.php?order='.$order['order_id']).'</td>';
    $output .= '<td align="center">'.$order['order_status'].'</td>';
    $output .= '<td align="center">'.date('n/j/Y', $order['order_date_timestamp']).'</td>';
    $output .= '<td align="center">'.$order['payment_status'].'</td>';
    $output .= '<td>'.$order['customer'].' ('.$customer_id_link.')</td>';
    $output .= '<td align="center">'.$order['customer_type'].'</td>';
    $output .= '<td align="center">'.$order['product_type'].'</td>';
    $output .= '</tr>';
  }
}
else $output .= '<tr><td colspan="8" align="center">No Orders Found.</td></tr>';
$output .= '</table>';
$output .= $event_history;
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
