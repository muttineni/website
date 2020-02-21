<?php
  $connection = mysql_connect('localhost', 'ipi', 'InstPast') or die('<p>Could not connect to database.</p>'."\n");
  mysql_set_charset('utf8', $connection);
  mysql_select_db('vlc', $connection) or die('<p>Could not select database.</p>'."\n");
  /* retrieve course id list */
  $query_1 = <<< END
    SELECT c.course_id, c.code AS course_code, c.description AS course_name
    FROM courses AS c
    ORDER BY c.facilitator_start DESC, c.code DESC
END;
  $result_1 = mysql_query($query_1, $connection) or die('<p>Could not query database.</p>'."\n");
  $num_rows_1 = mysql_num_rows($result_1);
  $course_id_list = '';
  for($i=0; $i < $num_rows_1; $i++){
    $course_id_list .= '<option value="'.mysql_result($result_1, $i, 'course_id').'">'.mysql_result($result_1, $i, 'course_code').' - '.mysql_result($result_1, $i, 'course_name').'</option>'."\n";
  }
  /* retrieve cycle id list */
  $query_2 = <<< END
    SELECT cy.cycle_id, cy.code AS cycle_code, DATE_FORMAT(cy.cycle_start, '%b. %Y') AS start_date
    FROM cycles AS cy
    ORDER BY cy.cycle_start DESC
END;
  $result_2 = mysql_query($query_2, $connection) or die('<p>Could not query database.</p>'."\n");
  $num_rows_2 = mysql_num_rows($result_2);
  $cycle_id_list = '';
  for($i=0; $i < $num_rows_2; $i++){
    $cycle_id_list .= '<option value="'.mysql_result($result_2, $i, 'cycle_id').'">'.mysql_result($result_2, $i, 'cycle_code').' ('.mysql_result($result_2, $i, 'start_date').')</option>'."\n";
  }
  mysql_close($connection);
?>
<html>
<head>
<title>Cycle Preparation Generation</title>
<style>
<!--
table
{
  background: #eee;
}
//-->
</style>
<script language="JavaScript" type="text/javascript">
<!--
function select_and_copy(form)
{
  form.list_serv_review.focus();
  form.list_serv_review.select();
  if(document.execCommand) document.execCommand('copy');
  else alert('Unable to copy to clipboard.');
  return true;
}
//-->
</script>
</head>
<body>
<table border="1" cellpadding="10" cellspacing="0" width="100%">
<tr>
  <td width="50%"><h1>Cycle Preparation Generation</h1></td>
  <td align="right"><a href="help.html" target="_blank"><b>Help</b></a></td>
</tr>
</table>

<p>&nbsp;</p>

<table border="1" cellpadding="10" cellspacing="0" width="100%">
<form action="student_list_action.php" method="get">
<tr>
  <td><b>Student Registration Information</b> (Jewell)</td>
  <td>Select a Cycle:
    <select name="cycle_id">
<?php print $cycle_id_list ?>
    </select>
  </td>
  <td align="center">
    <input type="submit" value="Submit">
  </td>
</tr>
</form>
<form action="certificate_info_action.php" method="get">
<tr>
  <td><b>Student Certificate Information</b> (Jewell)</td>
  <td>Select a Cycle:
    <select name="cycle_id">
<?php print $cycle_id_list ?>
    </select>
  </td>
  <td align="center">
    <input type="submit" value="Submit">
  </td>
</tr>
</form>
<form action="cycle_prep_action.php" method="get">
<tr>
  <td><b>Facilitator Information</b> (Jewell)</td>
  <td>Select a Cycle:
    <select name="cycle_id">
<?php print $cycle_id_list ?>
    </select>
  </td>
  <td align="center">
    <input type="submit" value="Submit">
    <input type="hidden" name="action" value="facilitator_info.inc.php">
  </td>
</tr>
</form>
<form action="cycle_prep_action.php" method="get">
<tr>
  <td><b>Facilitator E-Mail Addresses</b> (Sr. AAZ)</td>
  <td>Select a Cycle:
    <select name="cycle_id">
<?php print $cycle_id_list ?>
    </select>
  </td>
  <td align="center">
    <input type="submit" value="Submit">
    <input type="hidden" name="action" value="facilitator_email.inc.php">
  </td>
</tr>
</form>
<form action="cycle_prep_action.php" method="get">
<tr>
  <td><b>Course Mailing List E-Mail Addresses</b> (Sr. AAZ)</td>
  <td>Select a Cycle:
    <select name="cycle_id">
<?php print $cycle_id_list ?>
    </select>
  </td>
  <td align="center">
    <input type="submit" value="Submit">
    <input type="hidden" name="action" value="all_lists.inc.php">
  </td>
</tr>
</form>
</table>

<p>&nbsp;</p>

<table border="1" cellpadding="10" cellspacing="0" width="100%">
<form action="cycle_prep_action.php" method="get">
<tr>
  <td><b>Cycle Preparation Checklist</b></td>
  <td>Select a Cycle:
    <select name="cycle_id">
<?php print $cycle_id_list ?>
    </select>
  </td>
  <td align="center">
    <input type="submit" value="Submit">
    <input type="hidden" name="action" value="checklist.inc.php">
  </td>
</tr>
</form>
<form action="cycle_prep_action.php" method="get">
<tr>
  <td><b>List Serv Add Commands</b></td>
  <td>Select a Cycle:
    <select name="cycle_id">
<?php print $cycle_id_list ?>
    </select>
  </td>
  <td align="center">
    <input type="submit" value="Submit">
    <input type="hidden" name="action" value="list_serv_add.inc.php">
  </td>
</tr>
</form>
<form action="cycle_prep_action.php" method="get">
<tr>
  <td><b>.htaccess Usernames and Passwords</b></td>
  <td>Select a Course:
    <select name="course_id">
<?php print $course_id_list ?>
    </select>
  </td>
  <td align="center">
    <input type="submit" value="Submit">
    <input type="hidden" name="action" value="htpasswd.inc.php">
  </td>
</tr>
</form>
</table>

<p>&nbsp;</p>

<table border="1" cellpadding="10" cellspacing="0" width="100%">
<form action="cycle_prep_action.php" method="post">
<tr>
  <td><b>List Serv Delete Commands</b></td>
  <td>
    <ul>
      <li>Send the following commands in an e-mail message to <a href="mailto:sympa@lists.udayton.edu">sympa@lists.udayton.edu</a></li>
      <li>Enter the entire results of the e-mail response into the text area and click "Submit".</li>
    </ul>
  </td>
  <td align="center">
    <input type="submit" value="Submit">
    <input type="hidden" name="action" value="list_serv_delete.inc.php">
  </td>
</tr>
<tr>
  <td colspan="3" align="center">
<p><input type="button" value="Select Text and Copy to Clipboard" onclick="select_and_copy(this.form)"></p>
<textarea name="list_serv_review" rows="20" cols="100">
review catechesis
review catechesis_b
review cathbeliefs
review cathbeliefs_b
review cathbeliefs_c
review church_history
review church_history_b
review church_history2
review church_history2_b
review conscience
review cst1
review cst1_b
review cst2
review cst2_b
review digital
review doctrine
review ecclesiology
review ecclesiology_b
review ecclesiology_adv1
review ecclesiology_adv1_b
review ecclesiology_adv2
review ecclesiology_adv2_b
review ecumenism
review ecumenism_b
review evangelization
review evangelization_b
review imagination
review imagination_b
review inclusion
review jesus
review jesus_b
review marianist_studies
review marianist_studies_b
review marianist_studies_c
review marianist_studies_d
review mary
review mary_b
review mediaed
review mediaed_b
review ohwb
review ohwb_b
review ohwb_c
review ohwb_d
review ohwb_e
review pwc
review pwc_b
review realfood
review realfood_b
review realfood2
review realfood2_b
review sacraments
review sacraments_b
quit
</textarea>
  </td>
</tr>
</form>
</table>

<p>&nbsp;</p>
<hr width="75%">
<p>&nbsp;</p>
<?php
  print <<< END
<pre>
SQL QUERIES USED:

$query_1

$query_2
</pre>
END;
?>
</body>
</html>

