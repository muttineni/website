<?php
header('Content-type: text/plain');
if (isset($_GET['query']) and strlen($_GET['query']) and isset($_GET['field']) and strlen($_GET['field']))
{
  $query_string = addslashes($_GET['query']);
  $field_string = $_GET['field'];
  switch ($field_string)
  {
    /* users */
    case 'user_id':
      $query = "SELECT user_id, CONCAT(first_name, ' ', last_name) FROM users WHERE first_name LIKE '%$query_string%' OR last_name LIKE '%$query_string%' OR user_id LIKE '%$query_string%' LIMIT 10";
      break;
    case 'user_id_with_diocese_id':
      $query = "SELECT u.user_id, CONCAT(u.first_name, ' ', u.last_name), i.diocese_id FROM users AS u, user_info AS i WHERE u.user_id = i.user_id AND (u.first_name LIKE '%$query_string%' OR u.last_name LIKE '%$query_string%' OR u.user_id LIKE '%$query_string%') LIMIT 10";
      break;
    case 'first_name':
      $query = "SELECT first_name, user_id FROM users WHERE first_name LIKE '%$query_string%' LIMIT 10";
      break;
    case 'last_name':
      $query = "SELECT last_name, user_id FROM users WHERE last_name LIKE '%$query_string%' LIMIT 10";
      break;
    case 'username':
      $query = "SELECT username, user_id FROM users WHERE username LIKE '%$query_string%' LIMIT 10";
      break;
    case 'username_available':
      $query = "SELECT COUNT(*) FROM users WHERE BINARY username = '$query_string'";
      break;
    case 'primary_email':
      $query = "SELECT primary_email, user_id FROM user_info WHERE primary_email LIKE '%$query_string%' LIMIT 10";
      break;
    /* payment codes */
    case 'payment_code_id':
      $query = "SELECT payment_code_id, CONCAT(code, ': ', description) FROM payment_codes WHERE code LIKE '%$query_string%' OR description LIKE '%$query_string%' OR payment_code_id LIKE '%$query_string%' LIMIT 10";
      break;
    case 'payment_code':
      $query = "SELECT code, payment_code_id FROM payment_codes WHERE code LIKE '%$query_string%' LIMIT 10";
      break;
    case 'payment_code_desc':
      $query = "SELECT description, payment_code_id FROM payment_codes WHERE description LIKE '%$query_string%' LIMIT 10";
      break;
    /* cycles */
    case 'cycle_id':
      $query = "SELECT cycle_id, CONCAT(code, ': ', description) FROM cycles WHERE code LIKE '%$query_string%' OR description LIKE '%$query_string%' OR cycle_id LIKE '%$query_string%' LIMIT 10";
      break;
    case 'cycle_code':
      $query = "SELECT code, cycle_id FROM cycles WHERE code LIKE '%$query_string%' LIMIT 10";
      break;
    case 'cycle_desc':
      $query = "SELECT description, cycle_id FROM cycles WHERE description LIKE '%$query_string%' LIMIT 10";
      break;
    /* courses */
    case 'course_id':
      $query = "SELECT course_id, CONCAT(code, ': ', description) FROM courses WHERE code LIKE '%$query_string%' OR description LIKE '%$query_string%' OR course_id LIKE '%$query_string%' LIMIT 10";
      break;
    case 'course_code':
      $query = "SELECT code, course_id FROM courses WHERE code LIKE '%$query_string%' LIMIT 10";
      break;
    case 'course_desc':
      $query = "SELECT description, course_id FROM courses WHERE description LIKE '%$query_string%' LIMIT 10";
      break;
    /* partners */
    case 'partner_id':
      $query = "SELECT partner_id, description FROM partners WHERE description LIKE '%$query_string%' OR partner_id LIKE '%$query_string%' LIMIT 10";
      break;
    case 'partner_desc':
      $query = "SELECT description, partner_id FROM partners WHERE description LIKE '%$query_string%' LIMIT 10";
      break;
  }
  if (strlen($query))
  {
    $result = mysql_query($query, $site_info['db_conn']);
    while ($record = mysql_fetch_row($result))
    {
      print join("\t", $record)."\n";
    }
  }
}
?>
