<?php
  $cycle_id = $_GET['cycle_id'];
  $connection = mysql_connect('localhost', 'ipi', 'InstPast') or die('<p>Could not connect to database.</p>'."\n");
  mysql_set_charset('utf8', $connection);
  mysql_select_db('vlc', $connection) or die('<p>Could not select database.</p>'."\n");
  $query_1 = <<< END
    SELECT c.course_email
    FROM courses AS c
    WHERE c.cycle_id = $cycle_id
    ORDER BY c.course_email
END;
  $result_1 = mysql_query($query_1, $connection) or die('<p>Could not query database.</p>'."\n");
  $num_rows = mysql_num_rows($result_1);
  $email_list = '';
  for($i=0; $i < $num_rows; $i++){
    $course_email = mysql_result($result_1, $i, 'course_email');
    if(strlen($course_email)){
      if(strlen($email_list)) $email_list .= ', ';
      $email_list .= $course_email.'@lists.udayton.edu';
    }
  }
  print $email_list;
  mysql_close($connection);
?>

