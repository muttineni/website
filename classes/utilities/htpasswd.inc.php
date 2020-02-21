<?php
  $course_id = $_GET['course_id'];
  $connection = mysql_connect('localhost', 'ipi', 'InstPast') or die('<p>Could not connect to database.</p>'."\n");
  mysql_set_charset('utf8', $connection);
  mysql_select_db('vlc', $connection) or die('<p>Could not select database.</p>'."\n");
  $query_1 = <<< END
    SELECT uc.user_role_id, u.username, u.password
    FROM users AS u, users_courses AS uc
    WHERE u.user_id = uc.user_id
    AND uc.course_id = $course_id
    ORDER BY uc.user_role_id, u.username
END;
  $result_1 = mysql_query($query_1, $connection) or die('<p>Could not query database.</p>'."\n");
  $num_rows = mysql_num_rows($result_1);
  for($i=0; $i < $num_rows; $i++){
      $user_role_id = mysql_result($result_1, $i, 'user_role_id');
      $id = mysql_result($result_1, $i, 'username');
      $crypt_pass = crypt(mysql_result($result_1, $i, 'password'));
      print $id.':'.$crypt_pass;
      if ($user_role_id == 4) print "\n\n";
      else print "\n";
  }
  mysql_close($connection);
?>

