<?php
  $cycle_id = $_GET['cycle_id'];
  $connection = mysql_connect('localhost', 'ipi', 'InstPast') or die('<p>Could not connect to database.</p>'."\n");
  mysql_set_charset('utf8', $connection);
  mysql_select_db('vlc', $connection) or die('<p>Could not select database.</p>'."\n");

  $query_2 = <<< END
SELECT ui.primary_email
FROM courses AS c, users_courses AS uc, users AS u, user_info AS ui
WHERE c.course_id = uc.course_id
AND uc.user_id = u.user_id
AND u.user_id = ui.user_id
AND uc.user_role_id = 4
AND c.cycle_id = $cycle_id
ORDER BY ui.primary_email
END;
  $result_2 = mysql_query($query_2, $connection) or die('<p>Could not query database.</p>'."\n");
  $num_rows = mysql_num_rows($result_2);
  for($i=0; $i < $num_rows; $i++){
    if($i>0) print ', ';
    $facilitator_email = mysql_result($result_2, $i, 'primary_email');
    print $facilitator_email;
  }

  mysql_close($connection);
?>
