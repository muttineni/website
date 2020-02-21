<?php
$page_info['section'] = 'cms';
$page_info['login_required'] = 1;
$user_info = vlc_get_user_info($page_info['login_required']);
$lang = vlc_get_language();
/* return url */
$return_url = 'cms/transactions.php?'.$_SERVER['QUERY_STRING'];
/* check for valid upload */
if (isset($_FILES['webpay_report']) and is_uploaded_file($_FILES['webpay_report']['tmp_name']) and $_FILES['webpay_report']['size'] > 0) $webpay_report_details = $_FILES['webpay_report'];
else vlc_exit_page('<li>Invalid Report File.</li>', 'error', $return_url);
/* store uploaded report in "webpay" directory */
$temp_location = $webpay_report_details['tmp_name'];
$new_location = $site_info['webpay_reports_path'].$webpay_report_details['name'];
if (!move_uploaded_file($temp_location, $new_location)) trigger_error('UPLOAD FAILED: Unable to move uploaded file to "webpay" directory.');
/* insert file contents into database */
$transactions = file($new_location);
$transaction_types_array = array('PUR' => 1, 'CR' => 2, 'REV' => 3, 'VOID' => 4);
$insert_query_array = $inserts = $duplicates = $invalids = array();
foreach ($transactions as $transaction)
{
  /* skip the current record if it is a header row (contains the text "ID") or if it is blank */
  if (strlen($transaction = trim($transaction)) == 0) continue;
  if (strpos($transaction, 'ID') !== false)
  {
    $invalids[] = $transaction;
    continue;
  }
  $fields = preg_split('/\s*,\s*/', str_replace('"', '', $transaction));
  /* process standard report */
  if (count($fields) == 18)
  {
    $fields[4] = substr($fields[4], 6, 4).'-'.substr($fields[4], 0, 2).'-'.substr($fields[4], 3, 2);
    $fields[8] = substr($fields[8], 2, 2).'-'.substr($fields[8], 0, 2).'-01';
    $fields[10] *= 100;
    $transaction_id = $fields[15];
    $transaction_type_id = $transaction_types_array[$fields[3]];
    $transaction_amount = $fields[10];
    $transaction_date = $fields[4];
    $insert_query_template = "(NULL, %s, %s, %s, '%s', 1, %s, %s, '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', %s, '%s', '%s', %s, %s, %s, '%s', %s, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL)";
  }
  /* process error report */
  elseif (count($fields) == 21)
  {
    $fields[4] = substr($fields[4], 6, 4).'-'.substr($fields[4], 0, 2).'-'.substr($fields[4], 3, 2);
    $fields[7] = substr($fields[7], 2, 2).'-'.substr($fields[7], 0, 2).'-01';
    $fields[11] *= 100;
    if (strlen($fields[13]) == 0) $fields[13] = 'NULL';
    $transaction_id = $fields[17];
    $transaction_type_id = $transaction_types_array[$fields[3]];
    $transaction_amount = $fields[11];
    $transaction_date = $fields[4];
    $insert_query_template = "(NULL, %1\$s, %2\$s, %3\$s, '%4\$s', 0, %7\$s, %6\$s, '%5\$s', '%8\$s', '%9\$s', '%10\$s', NULL, '%11\$s', '%12\$s', '%13\$s', %16\$s, '%18\$s', '%19\$s', %20\$s, %21\$s, %22\$s, '%23\$s', %25\$s, '%14\$s', '%15\$s', '%17\$s', '%24\$s', NULL, NULL, NULL, NULL)";
  }
  /* process new upay report */
  elseif (count($fields) == 8)
  {
    $fields[4] = str_replace('\'', '\\\\\\\'', $fields[4]);
    $fields[7] *= 100;
    $transaction_id = $fields[2];
    $transaction_type_id = 1;
    $transaction_amount = $fields[7];
    $transaction_date = $fields[0];
    $insert_query_template = "(NULL, %1\$s, %2\$s, %3\$s, '%4\$s', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '%8\$s', %12\$s, '%10\$s', NULL, NULL, NULL, NULL, NULL, NULL, '%9\$s', NULL, NULL, NULL, NULL, NULL, NULL, NULL)";
  }
  else continue;
  array_unshift($fields, $transaction_id, $transaction_type_id, $transaction_amount, $transaction_date);
  $field_list = implode("', '", $fields);
  $insert_query_string = '';
  $sprintf_code = '$insert_query_string = sprintf($insert_query_template, \''.$field_list.'\');';
  eval($sprintf_code);
  $insert_query_string = str_replace(", ''", ", NULL", $insert_query_string);
  $insert_query_string = str_replace(", , ", ", NULL, ", $insert_query_string);
  $insert_query = "INSERT INTO transaction_reports VALUES $insert_query_string;";
  $result = @mysql_query($insert_query, $site_info['db_conn']);
  $mysql_error_num = mysql_errno();
  if ($mysql_error_num == 1062) $duplicates[] = $transaction;
  elseif ($mysql_error_num > 0) trigger_error('INSERT FAILED: transaction_reports');
  else $inserts[] = $transaction;
}
$num_inserts = count($inserts);
$num_duplicates = count($duplicates);
$num_invalids = count($invalids);
$success_message = "Transaction Report Successfully Uploaded. ($num_inserts Record(s) Inserted, $num_duplicates Duplicate Record(s), $num_invalids Invalid Record(s).)";
vlc_webpay_log($inserts, $duplicates, $invalids);
/* return to upload form */
vlc_exit_page($success_message, 'success', $return_url);
?>
