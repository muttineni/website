<?php
  $cycle_id = $_GET['cycle_id'];
  $connection = mysql_connect('localhost', 'ipi', 'InstPast') or die('<p>Could not connect to database.</p>'."\n");
  mysql_set_charset('utf8', $connection);
  mysql_select_db('vlc', $connection) or die('<p>Could not select database.</p>'."\n");
  $query_1 = <<< END
    SELECT c.course_email, uc.user_role_id, ui.primary_email, IFNULL(ui.secondary_email, '') AS secondary_email
    FROM courses AS c, users_courses AS uc, users AS u, user_info AS ui
    WHERE c.course_id = uc.course_id
    AND uc.user_id = u.user_id
    AND u.user_id = ui.user_id
    AND c.cycle_id = $cycle_id
    ORDER BY uc.user_role_id, c.course_email, ui.primary_email
END;
  $result_1 = mysql_query($query_1, $connection) or die('<p>Could not query database.</p>'."\n");
  $num_rows = mysql_num_rows($result_1);
  for($i=0; $i < $num_rows; $i++){
    $course_email = mysql_result($result_1, $i, 'course_email');
    if (strlen($course_email)){
      $user_role_id = mysql_result($result_1, $i, 'user_role_id');
      $primary_email = mysql_result($result_1, $i, 'primary_email');
      $secondary_email = mysql_result($result_1, $i, 'secondary_email');
      print 'add '.$course_email.' '.$primary_email."\n";
      if ($user_role_id == 4 and strlen($secondary_email)) print 'add '.$course_email.' '.$secondary_email."\n";
    }
  }
  mysql_close($connection);
  print 'quit';
?>

