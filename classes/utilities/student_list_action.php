<?php
$cycle_id = $_GET['cycle_id'];
$connection = mysql_connect('localhost', 'ipi', 'InstPast') or die('<p>Could not connect to database.</p>'."\n");
mysql_set_charset('utf8', $connection);
mysql_select_db('vlc', $connection) or die('<p>Could not select database.</p>'."\n");
/* get course information */
$course_query = <<< END_COURSE_QUERY
  SELECT c.course_id, c.description AS course_name, c.code AS course_code, f.user_id, f.username, f.password, CONCAT(f.first_name, ' ', f.last_name) AS facilitator_name
  FROM courses AS c, users_courses AS fc, users AS f
  WHERE c.course_id = fc.course_id
  AND fc.user_id = f.user_id
  AND fc.user_role_id = 4
  AND fc.course_status_id IN (2, 3)
  AND c.cycle_id = $cycle_id
  ORDER BY c.course_id
END_COURSE_QUERY;
$result = mysql_query($course_query, $connection) or die('<p>Could not query database.</p>'."\n");
while ($record = mysql_fetch_array($result))
{
  $courses[$record['course_id']]['course_details'] = $record;
  $courses[$record['course_id']]['facilitators'][] = 'Facilitator: '.$record['facilitator_name'].' (<code>User ID: '.$record['user_id'].', Username: '.$record['username'].', Password: '.$record['password'].'</code>)';
}
/* get student information */
$student_query = <<< END_STUDENT_QUERY
  SELECT c.course_id, u.user_id, u.username, u.password, i.primary_email, i.primary_phone,
    CONCAT(u.last_name, ', ', u.first_name, ' ', IFNULL(u.middle_name, '')) AS student_name,
    IFNULL(d.description, '(None)') AS diocese,
    IF(i.diocese_id IS NULL, '', IF(d.is_partner, '(Partner)', '(Non-Partner)')) AS diocese_partner_status,
    IFNULL(p.description, '(None)') AS partner,
    IF(i.partner_id IS NULL, '', IF(p.is_partner, '(Partner)', '(Non-Partner)')) AS partner_partner_status,
    i.address_1, i.address_2, i.city, IFNULL(t.code, '--') AS state, i.zip, y.description AS country,
    o.payment_status_id, m.description AS payment_status, s.description AS course_status
  FROM courses AS c, users_courses AS uc, users AS u,
    user_info AS i
      LEFT JOIN partners AS d ON i.diocese_id = d.partner_id
      LEFT JOIN partners AS p ON i.partner_id = p.partner_id
      LEFT JOIN countries AS y ON i.country_id = y.country_id
      LEFT JOIN states AS t ON i.state_id = t.state_id,
    course_status AS s, orders AS o, payment_status AS m
  WHERE c.course_id = uc.course_id
    AND uc.course_status_id = s.course_status_id
    AND uc.user_course_id = o.product_id
    AND o.payment_status_id = m.payment_status_id
    AND uc.user_id = u.user_id
    AND u.user_id = i.user_id
    AND uc.user_role_id = 5
    AND o.product_type_id = 1
    AND o.customer_type_id = 1
    AND uc.course_status_id NOT IN (1,4)
    AND c.cycle_id = $cycle_id
  ORDER BY c.description, c.section_id, u.last_name, u.first_name
END_STUDENT_QUERY;
$result = mysql_query($student_query, $connection) or die('<p>Could not query database.</p>'."\n");
$total_students = mysql_num_rows($result);
$i = 1;
$prev_course_id = 0;
$course_list = '';
while ($record = mysql_fetch_array($result))
{
  $curr_course_id = $record['course_id'];
  if ($curr_course_id != $prev_course_id)
  {
    $course_info = $courses[$curr_course_id]['course_details']['course_name'].' (Course Code: '.$courses[$curr_course_id]['course_details']['course_code'].', Course ID: '.$curr_course_id.')<br>'.join('<br>', $courses[$curr_course_id]['facilitators']);
    $course_list .= '<tr bgcolor="#cccccc">';
    $course_list .= '<th>'.$course_info.'</th>';
    $course_list .= '</tr>';
    $i++;
  }
  if (in_array($record['payment_status_id'], array(1, 4))) $table_row_tag = '<tr>';
  else $table_row_tag = '<tr bgcolor="#eeeeee">';
  $student_info = '<b>'.$record['student_name'].'</b> (<code>User ID: '.$record['user_id'].', Username: '.$record['username'].', Password: '.$record['password'].'</code>)<br>';
  $student_info .= '&nbsp;&nbsp;&nbsp;&nbsp;Payment Status: <b>'.$record['payment_status'].'</b><br>';
  $student_info .= '&nbsp;&nbsp;&nbsp;&nbsp;Course Status: <b>'.$record['course_status'].'</b><br>';
  $student_info .= '&nbsp;&nbsp;&nbsp;&nbsp;Diocese: '.$record['diocese'].' <b>'.$record['diocese_partner_status'].'</b><br>';
  $student_info .= '&nbsp;&nbsp;&nbsp;&nbsp;Non-Diocesan Organization: '.$record['partner'].' <b>'.$record['partner_partner_status'].'</b><br>';
  $student_info .= '&nbsp;&nbsp;&nbsp;&nbsp;Address: '.$record['address_1'].', '.$record['address_2'].', '.$record['city'].', '.$record['state'].', '.$record['zip'].' ('.$record['country'].')<br>';
  $student_info .= '&nbsp;&nbsp;&nbsp;&nbsp;Phone: '.$record['primary_phone'].', E-Mail: <a href="mailto:'.$record['primary_email'].'">'.$record['primary_email'].'</a>';
  $course_list .= $table_row_tag;
  $course_list .= '<td>'.$student_info.'</td>';
  $course_list .= '</tr>';
  $prev_course_id = $curr_course_id;
}
mysql_close($connection);
?>
<html>
<head>
<title>Student Registration Information</title>
</head>
<body>
<table border="1" cellpadding="5" cellspacing="0">
<tr><td><h1 align="center">Student Registration Information</h1><h2 align="center">(Cycle ID: <?php print $cycle_id ?>)</h2><h3 align="center">Total Number of Students: <?php print $total_students ?></h3></td></tr>
<?php print $course_list ?>
</table>
</body>
</html>
