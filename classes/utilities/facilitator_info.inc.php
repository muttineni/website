<?php
  $cycle_id = $_GET['cycle_id'];
  $connection = mysql_connect('localhost', 'ipi', 'InstPast') or die('<p>Could not connect to database.</p>'."\n");
  mysql_set_charset('utf8', $connection);
  mysql_select_db('vlc', $connection) or die('<p>Could not select database.</p>'."\n");

  $query_2 = <<< END
    SELECT c.code AS course_code, c.description AS course_name, u.first_name, u.last_name, u.username, u.password, ui.primary_email
    FROM courses AS c, users_courses AS uc, users AS u, user_info AS ui
    WHERE c.course_id = uc.course_id
    AND uc.user_id = u.user_id
    AND u.user_id = ui.user_id
    AND c.cycle_id = $cycle_id
    AND uc.user_role_id = 4
    ORDER BY c.description
END;
  $result_2 = mysql_query($query_2, $connection) or die('<p>Could not query database.</p>'."\n");
  $num_rows = mysql_num_rows($result_2);
  for($i=0; $i < $num_rows; $i++){
    $course_id = mysql_result($result_2, $i, 'course_code');
    $course_name = mysql_result($result_2, $i, 'course_name');
    $facilitator_name = mysql_result($result_2, $i, 'first_name').' '.mysql_result($result_2, $i, 'last_name');
    $facilitator_email = mysql_result($result_2, $i, 'primary_email');
    $facilitator_userid = mysql_result($result_2, $i, 'username');
    $facilitator_passwd = mysql_result($result_2, $i, 'password');
    print <<< END

Course: $course_name ($course_id)
Facilitator: $facilitator_name ($facilitator_email)
Username/Password: $facilitator_userid/$facilitator_passwd

END;
  }

  mysql_close($connection);
?>
