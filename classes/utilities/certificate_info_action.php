<?php
$cycle_id = $_GET['cycle_id'];
$connection = mysql_connect('localhost', 'ipi', 'InstPast') or die('<p>Could not connect to database.</p>'."\n");
mysql_set_charset('utf8', $connection);
mysql_select_db('vlc', $connection) or die('<p>Could not select database.</p>'."\n");
/* get certificate information */
$certificate_info_query = <<< END_CERTIFICATE_INFO_QUERY
  SELECT IFNULL(p.description, '') AS partner, IF(p.is_partner, 'Partner', 'Non-Partner') AS partner_partner_status,
    IFNULL(d.description, '') AS diocese, IF(d.is_partner, 'Partner', 'Non-Partner') AS diocese_partner_status,
    IFNULL(j.description, '') AS course_name, IFNULL(c.code, '') AS course_code,
    IFNULL(DATE_FORMAT(y.cycle_start, '%c/%e/%Y'), '') AS course_date, FORMAT(t.ceu, 1) AS ceu,
    CONCAT(IFNULL(u.first_name, ''), ' ', IFNULL(u.middle_name, ''), ' ', IFNULL(u.last_name, '')) AS student_name,
    CONCAT(IFNULL(i.address_1, ''), IF(IFNULL(i.address_2, '') = '', '', CONCAT(', ', i.address_2))) AS address,
    IFNULL(i.city, '') AS city, IFNULL(s.code, '') AS state, IFNULL(i.zip, '') AS zip, IF(i.country_id = 222, '', IFNULL(o.description, '')) AS country
  FROM cycles AS y, course_subjects AS j, course_types AS t, courses AS c, users_courses AS uc, users AS u, states AS s, countries AS o,
    user_info AS i LEFT JOIN partners AS p ON (i.partner_id = p.partner_id) LEFT JOIN partners AS d ON (i.diocese_id = d.partner_id)
  WHERE c.cycle_id = y.cycle_id
  AND c.course_subject_id = j.course_subject_id
  AND j.course_type_id = t.course_type_id
  AND c.course_id = uc.course_id
  AND uc.user_id = u.user_id
  AND u.user_id = i.user_id
  AND i.state_id = s.state_id
  AND i.country_id = o.country_id
  AND y.cycle_id = $cycle_id
  AND uc.user_role_id = 5
  AND uc.course_status_id = 7
  ORDER BY d.is_partner DESC, d.description, p.is_partner DESC, p.description, j.description, u.last_name, u.first_name
END_CERTIFICATE_INFO_QUERY;
$result = mysql_query($certificate_info_query, $connection) or die('<p>Could not query database.</p>'."\n");
$student_list = '';
while ($record = mysql_fetch_array($result))
{
  $student_list .= '<tr>';
  $student_list .= '<td>'.$record['diocese'].'&nbsp;</td>';
  $student_list .= '<td>'.$record['diocese_partner_status'].'&nbsp;</td>';
  $student_list .= '<td>'.$record['partner'].'&nbsp;</td>';
  $student_list .= '<td>'.$record['partner_partner_status'].'&nbsp;</td>';
  $student_list .= '<td>'.$record['course_name'].'&nbsp;</td>';
  $student_list .= '<td>'.$record['course_code'].'&nbsp;</td>';
  $student_list .= '<td>'.$record['course_date'].'&nbsp;</td>';
  $student_list .= '<td>'.$record['ceu'].'&nbsp;</td>';
  $student_list .= '<td>'.$record['student_name'].'&nbsp;</td>';
  $student_list .= '<td>'.$record['address'].'&nbsp;</td>';
  $student_list .= '<td>'.$record['city'].'&nbsp;</td>';
  $student_list .= '<td>'.$record['state'].'&nbsp;</td>';
  $student_list .= '<td>'.$record['zip'].'&nbsp;</td>';
  $student_list .= '<td>'.$record['country'].'&nbsp;</td>';
  $student_list .= '</tr>';
}
mysql_close($connection);
?>
<html>
<head>
<title>Student Certificate Information</title>
</head>
<body>
<h1 align="center">Student Certificate Information</h1>
<h2 align="center">(Cycle ID: <?php print $cycle_id ?>)</h2>
<table border="1" cellpadding="5" cellspacing="0">
<tr><th>Diocese</th><th>Diocese Partner Status</th><th>Partner</th><th>Partner Partner Status</th><th>Course Name</th><th>Course Code</th><th>Course Date</th><th>CEU</th><th>Student Name</th><th>Address</th><th>City</th><th>State</th><th>Zip</th><th>Country</th></tr>
<?php print $student_list ?>
</table>
</body>
</html>
