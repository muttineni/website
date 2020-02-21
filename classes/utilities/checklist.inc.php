<?php
  $cycle_id = $_GET['cycle_id'];
  $connection = mysql_connect('localhost', 'ipi', 'InstPast') or die('<p>Could not connect to database.</p>'."\n");
  mysql_set_charset('utf8', $connection);
  mysql_select_db('vlc', $connection) or die('<p>Could not select database.</p>'."\n");
  $query_1 = <<< END
    SELECT
      DATE_FORMAT(cy.cycle_start, '%b. %e') AS sess_1_start,
      DATE_FORMAT(cy.cycle_start + INTERVAL 6 DAY, '%b. %e') AS sess_1_end,
      DATE_FORMAT(cy.cycle_start + INTERVAL 7 DAY, '%b. %e') AS sess_2_start,
      DATE_FORMAT(cy.cycle_start + INTERVAL 13 DAY, '%b. %e') AS sess_2_end,
      DATE_FORMAT(cy.cycle_start + INTERVAL 14 DAY, '%b. %e') AS sess_3_start,
      DATE_FORMAT(cy.cycle_start + INTERVAL 20 DAY, '%b. %e') AS sess_3_end,
      DATE_FORMAT(cy.cycle_start + INTERVAL 21 DAY, '%b. %e') AS sess_4_start,
      DATE_FORMAT(cy.cycle_start + INTERVAL 27 DAY, '%b. %e') AS sess_4_end,
      DATE_FORMAT(cy.cycle_start + INTERVAL 28 DAY, '%b. %e') AS sess_5_start,
      DATE_FORMAT(cy.cycle_start + INTERVAL 34 DAY, '%b. %e') AS sess_5_end,
      DATE_FORMAT(cy.cycle_start + INTERVAL 35 DAY, '%b. %e') AS sess_6_start,
      DATE_FORMAT(cy.cycle_start + INTERVAL 41 DAY, '%b. %e') AS sess_6_end,
      DATE_FORMAT(cy.cycle_start - INTERVAL 6 DAY, '%a. %b. %e') AS mon_before,
      DATE_FORMAT(cy.cycle_start + INTERVAL 1 DAY, '%a. %b. %e') AS mon_after
    FROM cycles AS cy
    WHERE cy.cycle_id = $cycle_id
END;
  $result_1 = mysql_query($query_1, $connection) or die('<p>Could not query database.</p>'."\n");
  $dates = mysql_fetch_array($result_1);
  print <<< END

/* NOTE: */
/* Cycle codes should be modified to fit the current cycle throughout this document. */
/* Search for "?" */

===============================================================================

CYCLE PREPARATION CHECKLIST FOR: Cycle ?, 2004 (?04)

Week before cycle starts (Week of {$dates[12]})
========================
 o Change session dates (/usr/users/vlc/html/classes/utilities/session_dates.inc.php) to:
  1 => '{$dates[0]} - {$dates[1]}',
  2 => '{$dates[2]} - {$dates[3]}',
  3 => '{$dates[4]} - {$dates[5]}',
  4 => '{$dates[6]} - {$dates[7]}',
  5 => '{$dates[8]} - {$dates[9]}',
  6 => '{$dates[10]} - {$dates[11]}'
 o Upload new student and facilitator images to images directory (/usr/users/vlc/html/images/users/)
 o Clear all old list-serv e-mail addresses
 o Add all new list-serv e-mail addresses
 o Replace old usernames/passwords with new usernames/passwords (.htpasswd)
 o Change course_id for each course (common.php3)

Monday after cycle starts ({$dates[13]})
=========================
 o Update course status in the database [course_status_id in braces]:
   - For all students and facilitators in CURRENT cycle,
     status changes from "Next Cycle" [1] to "In Progress" [2]
   - For all facilitators in PREVIOUS cycle,
     status changes from "In Progress" [2] to "Complete" [3]
 o Update registration dates on homepage announcement

===============================================================================

Courses being offered:


END;
  $query_2 = <<< END
    SELECT c.course_id, c.code AS course_code, c.description AS course_name, u.first_name, u.last_name, ui.primary_email, IFNULL(ui.secondary_email, '') AS secondary_email
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
    $count = $i + 1;
    $course_id = mysql_result($result_2, $i, 'course_id');
    $course_code = mysql_result($result_2, $i, 'course_code');
    $course_name = mysql_result($result_2, $i, 'course_name');
    $facilitator_name = mysql_result($result_2, $i, 'first_name').' '.mysql_result($result_2, $i, 'last_name');
    $facilitator_email = mysql_result($result_2, $i, 'primary_email');
    $secondary_email = mysql_result($result_2, $i, 'secondary_email');
    if (strlen($secondary_email)) $facilitator_email .= ', '.$secondary_email;
    print <<< END

$count. $course_code $course_name: $facilitator_name ($facilitator_email) - $course_id;

END;
  }
  print <<< END

===============================================================================

END;
  mysql_close($connection);
?>

