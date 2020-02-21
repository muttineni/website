<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'cert-user-details';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get cert-user id */
$cert_user_id = intval($_GET['cert_user']);
/* begin output variable */
$output = '';
/* return to previous page link */
if (isset($_SERVER['HTTP_REFERER']) and $return_url = strstr($_SERVER['HTTP_REFERER'], 'cms/')) $output .= '<p>'.vlc_internal_link('Return to Previous Page', $return_url).'</p>';
/* get cert-user details */
$cert_user_query = <<< END_QUERY
  SELECT cu.cert_prog_id, c.description, cu.user_id, cu.cert_status_id,
    IFNULL(cu.notes, '') AS notes, u.first_name, u.last_name,
    IFNULL(cu.score_level_id, -1) AS score_level_id, IFNULL(cu.application_notes, '') AS application_notes,
    IFNULL(MONTH(cu.certificate_date), -1) AS certificate_date_month,
    IFNULL(DAYOFMONTH(cu.certificate_date), -1) AS certificate_date_day,
    IFNULL(YEAR(cu.certificate_date), -1) AS certificate_date_year
  FROM certs_users AS cu, users AS u, cert_progs AS c
  WHERE cu.user_id = u.user_id
  AND cu.cert_prog_id = c.cert_prog_id
  AND cu.cert_user_id = $cert_user_id
END_QUERY;
$result = mysql_query($cert_user_query, $site_info['db_conn']);
$cert_user_details = mysql_fetch_array($result);
foreach ($cert_user_details as $key => $value)
{
  //if (is_string($value)) $cert_user_details[$key] = htmlspecialchars($value); 2014/04/29 commented out by Bob to correct missing extended characters issue in cms
}
/* cert prog history - init array variables */
$categories = $course_subjects = $course_subject_id_array = $student_course_history = $cert_prog_req = array();
/* get cert prog courses */
$cert_prog_courses_query = <<< END_QUERY
  SELECT IFNULL(r.cert_cat_id, 0) AS cert_cat_id, r.description AS category, s.course_subject_id, s.description AS course_subject
  FROM certs_courses AS cc LEFT JOIN cert_cats AS r ON cc.cert_cat_id = r.cert_cat_id, course_subjects AS s
  WHERE cc.course_subject_id = s.course_subject_id
  AND cc.cert_prog_id = {$cert_user_details['cert_prog_id']}
  ORDER BY r.display_order, cc.display_order
END_QUERY;
$result = mysql_query($cert_prog_courses_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result))
{
  $course_subject_id_array[] = $record['course_subject_id'];
  $categories[$record['cert_cat_id']] = $record['category'];
  $course_subjects[$record['cert_cat_id']][$record['course_subject_id']] = $record['course_subject'];
}
if (count($categories) == 0) {
    $categories[] = '';
}
if (count($course_subject_id_array))
{
  $course_subject_id_list = join(', ', $course_subject_id_array);
  /* get student course record */
  $student_course_query = <<< END_QUERY
    SELECT c.course_subject_id, c.course_id, y.cycle_start, uc.user_course_id, uc.course_status_id, s.description AS course_status, uc.is_scored, uc.score_level_id, l.description AS score_level
    FROM users_courses AS uc LEFT JOIN score_levels AS l ON uc.score_level_id = l.score_level_id,
      courses AS c, cycles AS y, course_status AS s
    WHERE c.cycle_id = y.cycle_id
    AND c.course_id = uc.course_id
    AND s.course_status_id = uc.course_status_id
    AND c.course_subject_id IN ($course_subject_id_list)
    AND uc.user_id = {$cert_user_details['user_id']}
    ORDER BY c.course_subject_id, uc.created
END_QUERY;
  $result = mysql_query($student_course_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) {
        $student_course_history[$record['course_subject_id']] = $record;
    }
    /* get cert prog req records. This gets any documented course info for the student that is out of the ordinary */
  $cert_prog_req_query = <<< END_QUERY
    SELECT r.cert_prog_req_id, r.course_subject_id, r.description, r.notes
    FROM cert_prog_reqs AS r
    WHERE r.course_subject_id IN ($course_subject_id_list)
    AND r.cert_user_id = $cert_user_id
END_QUERY;
  $result = mysql_query($cert_prog_req_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) {
        $cert_prog_req[$record['course_subject_id']] = $record;
    }
}
/* course list */
$cert_prog_record = '<form method="post" action="cert_user_action.php">';
$cert_prog_record .= '<input type="hidden" name="cert_user_id" value="'.$cert_user_id.'">';
foreach ($categories as $cert_cat_id => $category)
{
  if ($cert_cat_id > 0) $cert_prog_record .= '<p><b>'.$category.'</b></p>';
  $cert_prog_record .= '<ol>';
  foreach ($course_subjects[$cert_cat_id] as $course_subject_id => $course_subject)
  {
    if (isset($cert_prog_req[$course_subject_id]))
    {
      $student_details = $cert_prog_req[$course_subject_id];
      $course_details = '<b>Requirement Completed Outside of VLCFF</b>';
      $course_details .= '<input type="hidden" name="cert_prog_reqs['.$course_subject_id.'][cert_prog_req_id]" value="'.$student_details['cert_prog_req_id'].'">';
      $course_details .= '<ul>';
      $course_details .= '<li><nobr><input type="checkbox" name="cert_prog_reqs['.$course_subject_id.'][remove]" value="1" id="'.$course_subject_id.'"> <label for="'.$course_subject_id.'">Remove this completed requirement.</label></nobr></li>';
      $course_details .= '<li>Description: <input type="text" name="cert_prog_reqs['.$course_subject_id.'][description]" value="'.$student_details['description'].'"></li>';
      $course_details .= '<li>Notes:<br><textarea rows="5" cols="40" name="cert_prog_reqs['.$course_subject_id.'][notes]">'.$student_details['notes'].'</textarea></li>';
      $course_details .= '</ul>';
    }
    elseif (isset($student_course_history[$course_subject_id]))
    {
      $student_details = $student_course_history[$course_subject_id];
      if ($student_details['is_scored'])
      {
        if (isset($student_details['score_level_id'])) $score = $student_details['score_level'];
        else $score = 'Not Yet Available';
        $scoring = 'Score: '.$score;
      }
      else $scoring = 'Not Scored';
      $course_subject .= ' ('.vlc_internal_link($student_details['user_course_id'], 'cms/user_course_details.php?user_course='.$student_details['user_course_id']).')';
      $course_details = '<b>'.$student_details['course_status'].' ('.$student_details['cycle_start'].') - '.$scoring.'</b>';
    }
    else
    {
      $course_details = '<b>Not Registered</b>';
      $course_details .= '<ul>';
      $course_details .= '<li><nobr><input type="checkbox" name="cert_prog_reqs['.$course_subject_id.'][add]" value="1" id="'.$course_subject_id.'"> <label for="'.$course_subject_id.'">Mark this requirement completed outside of VLCFF.</label></nobr></li>';
      $course_details .= '<li>Description: <input type="text" name="cert_prog_reqs['.$course_subject_id.'][description]"></li>';
      $course_details .= '<li>Notes:<br><textarea rows="5" cols="40" name="cert_prog_reqs['.$course_subject_id.'][notes]"></textarea></li>';
      $course_details .= '</ul>';
    }
    $cert_prog_record .= '<li>'.$course_subject.' - '.$course_details.'</li>';
  }
  $cert_prog_record .= '</ol>';
}
$cert_prog_record .= '<p><input type="submit" value="Submit">';
$cert_prog_record .= '</form>';
$cert_prog_record .= '<hr width="50%">';
/* get orders linked to this registration */
$order_query = <<< END_QUERY
  SELECT o.order_id, IF(o.is_active, 'Active', 'Inactive') AS order_status, s.description AS payment_status,
    o.product_id, o.product_type_id, d.description AS product_type,
    o.customer_id, o.customer_type_id, m.description AS customer_type,
    UNIX_TIMESTAMP(o.order_date) AS order_date_timestamp,
    CONCAT(u.first_name, ' ', u.last_name) AS customer
  FROM certs_users AS cu, orders AS o, users AS u,
    product_types AS d, customer_types AS m, payment_status AS s
  WHERE cu.cert_user_id = o.product_id
    AND cu.user_id = o.customer_id
    AND o.customer_id = u.user_id
    AND o.customer_type_id = 1
    AND o.product_type_id = 6
    AND o.customer_type_id = m.customer_type_id
    AND o.product_type_id = d.product_type_id
    AND o.payment_status_id = s.payment_status_id
    AND cu.cert_user_id = $cert_user_id
  ORDER BY o.order_date, o.order_id
END_QUERY;
$result = mysql_query($order_query, $site_info['db_conn']);
$order_details = array();
while ($record = mysql_fetch_array($result)) $order_details[$record['order_id']] = $record;
/* get event details */
$entity_id = $cert_user_id;
$event_type_array = array(
  CERTS_USERS_CREATE,
  CERTS_USERS_UPDATE,
  CERT_PROG_REQS_ADD_REQ,
  CERT_PROG_REQS_UPDATE_REQ,
  CERT_PROG_REQS_REMOVE_REQ
);
$event_history = vlc_get_event_history($event_type_array, $entity_id);
/* build array for "cert status" select box */
$cert_status_options_array = array();
$cert_status_query = <<< END_QUERY
  SELECT cert_status_id, description
  FROM cert_status
  ORDER BY cert_status_id
END_QUERY;
$result = mysql_query($cert_status_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $cert_status_options_array[$record['cert_status_id']] = $record['description'];
/* get scoring level options */
$score_level_query = <<< END_QUERY
  SELECT score_level_id, description
  FROM score_levels
  ORDER BY score_level_id
END_QUERY;
$result = mysql_query($score_level_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $score_level_options_array[$record['score_level_id']] = $record['description'];
/* month, day, year arrays */
for ($i = 1; $i <= 12; $i++) $months_array[$i] = date('F', mktime(0,0,0,$i,1,0));
for ($i = 1; $i <= 31; $i++) $days_array[$i] = $i;
for ($i = 2000; $i <= 2020; $i++) $years_array[$i] = $i;
/* add cert-user details to output */
$output .= '<form method="post" action="cert_user_action.php">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
$output .= '<tr><td><b>Cert Prog Reg ID:</b></td><td colspan="3">'.$cert_user_id.'<input type="hidden" name="cert_user_id" value="'.$cert_user_id.'"></td></tr>';
$output .= '<tr>';
$output .= '<td><b>User:</b></td>';
$output .= '<td>'.$cert_user_details['first_name'].' '.$cert_user_details['last_name'].' ('.vlc_internal_link($cert_user_details['user_id'], 'cms/user_details.php?user='.$cert_user_details['user_id']).')</td>';
$output .= '<td><b>Cert Prog:</b></td>';
$output .= '<td>'.$cert_user_details['description'].' ('.vlc_internal_link($cert_user_details['cert_prog_id'], 'cms/cert_prog_details.php?cert_prog='.$cert_user_details['cert_prog_id']).')</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Cert Prog Status:</b></td>';
$output .= '<td>'.vlc_select_box($cert_status_options_array, 'array', 'cert_status_id', $cert_user_details['cert_status_id'], true).'</td>';
$output .= '<td><b>Score:</b></td>';
$output .= '<td>'.vlc_select_box($score_level_options_array, 'array', 'score_level_id', $cert_user_details['score_level_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Certificate Date:</b></td>';
$output .= '<td colspan="3">';
$output .= '<nobr>';
$output .= vlc_select_box($months_array, 'array', 'certificate_date_month', $cert_user_details['certificate_date_month'], false).' ';
$output .= vlc_select_box($days_array, 'array', 'certificate_date_day', $cert_user_details['certificate_date_day'], false).' ';
$output .= vlc_select_box($years_array, 'array', 'certificate_date_year', $cert_user_details['certificate_date_year'], false).' ';
$output .= '<img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].certificate_date_year,document.forms[0].certificate_date_month,document.forms[0].certificate_date_day,false,false,this);">';
$output .= '</nobr>';
$output .= '</td>';
$output .= '</tr>';
$output .= '<tr><td valign="top"><b>Notes:</b></td><td colspan="3"><textarea rows="5" cols="80" name="notes">'.$cert_user_details['notes'].'</textarea></td></tr>';
$output .= '<tr><td valign="top"><b>Application Notes:</b></td><td colspan="3"><textarea rows="5" cols="80" name="application_notes">'.$cert_user_details['application_notes'].'</textarea></td></tr>';
$output .= '<tr><td colspan="4" align="center"><input type="submit" value="Submit"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
$output .= '<h3>Course History:</h3>';
$output .= $cert_prog_record;
$output .= '<h3>Order:</h3>';
$output .= '<p>The following order is linked to this certificate program registration.</p><ul><li>To view additional order details, click the <b>&quot;Order ID&quot;</b> link.</li></ul>';
$output .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
$output .= '<tr><th>&nbsp;</th><th>Order ID</th><th>Order Status</th><th>Order Date</th><th>Payment Status</th><th>Customer</th><th>Customer Type</th><th>Product Type</th></tr>';
if (count($order_details))
{
  $i = 1;
  foreach ($order_details as $order)
  {
    $customer_id_link = vlc_internal_link($order['customer_id'], 'cms/user_details.php?user='.$order['customer_id']);
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
