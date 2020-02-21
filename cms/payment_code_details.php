<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'payment-code-details';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get url variables */
if (isset($_GET['code'])) $form_fields['payment_code_id'] = intval($_GET['code']);
if (isset($_GET['num'])) $num = $_GET['num'];
else $num = 1;
/* get form fields */
if (strlen($status_message) > 0 and isset($_SESSION['form_fields']))
{
  $form_fields = $_SESSION['form_fields'];
  $_SESSION['form_fields'] = null;
}
elseif (isset($form_fields['payment_code_id']))
{
  /* get payment code details */
  $payment_code_details_query = <<< END_QUERY
    SELECT payment_code_id, IFNULL(code, '') AS code, IFNULL(description, '') AS description, IFNULL(partner_id, -1) AS partner_id,
      IFNULL(student_seminar_cost, '') AS student_seminar_cost, IFNULL(partner_seminar_cost, '') AS partner_seminar_cost,
      IFNULL(student_course_cost, '') AS student_course_cost, IFNULL(partner_course_cost, '') AS partner_course_cost,
      IFNULL(MONTH(active_start), -1) AS active_start_month,
      IFNULL(DAYOFMONTH(active_start), -1) AS active_start_day,
      IFNULL(YEAR(active_start), -1) AS active_start_year,
      IFNULL(MONTH(active_end), -1) AS active_end_month,
      IFNULL(DAYOFMONTH(active_end), -1) AS active_end_day,
      IFNULL(YEAR(active_end), -1) AS active_end_year
    FROM payment_codes
    WHERE payment_code_id = {$form_fields['payment_code_id']}
END_QUERY;
  $result = mysql_query($payment_code_details_query, $site_info['db_conn']);
  $form_fields = mysql_fetch_array($result);
  if (is_numeric($form_fields['student_seminar_cost'])) $form_fields['student_seminar_cost'] = number_format($form_fields['student_seminar_cost'] / 100, 2);
  if (is_numeric($form_fields['partner_seminar_cost'])) $form_fields['partner_seminar_cost'] = number_format($form_fields['partner_seminar_cost'] / 100, 2);
  if (is_numeric($form_fields['student_course_cost'])) $form_fields['student_course_cost'] = number_format($form_fields['student_course_cost'] / 100, 2);
  if (is_numeric($form_fields['partner_course_cost'])) $form_fields['partner_course_cost'] = number_format($form_fields['partner_course_cost'] / 100, 2);
}
else
{
  $form_fields = array
  (
    'code' => '',
    'description' => '',
    'partner_id' => -1,
    'student_course_cost' => '',
    'partner_course_cost' => '',
    'student_seminar_cost' => '',
    'partner_seminar_cost' => '',
    'active_start_month' => date('n'),
    'active_start_day' => date('j'),
    'active_start_year' => date('Y'),
    'active_end_month' => date('n'),
    'active_end_day' => date('j'),
    'active_end_year' => date('Y') + 10
  );
}
if (isset($form_fields['payment_code_id']))
{
  /* get event details */
  $entity_id = $form_fields['payment_code_id'];
  $event_type_array = array(
    PAYMENT_CODES_CREATE,
    PAYMENT_CODES_UPDATE
  );
  $event_history = vlc_get_event_history($event_type_array, $entity_id);
}
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = htmlspecialchars($value);
}
/* build array for "partner" select box */
$partner_options_array = array();
$partner_query = <<< END_QUERY
  SELECT partner_id, description
  FROM partners
  WHERE country_id = 222
    OR is_partner = 1
    OR is_diocese = 0
  ORDER BY description
END_QUERY;
$result = mysql_query($partner_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $partner_options_array[$record['partner_id']] = $record['description'];
/* begin output variable */
$output = '';
/* return to previous page link */
if (isset($_SERVER['HTTP_REFERER']) and $return_url = strstr($_SERVER['HTTP_REFERER'], 'cms/')) $output .= '<p>'.vlc_internal_link('Return to Previous Page', $return_url).'</p>';
/* month, day, year arrays */
for ($i = 1; $i <= 12; $i++) $months_array[$i] = date('F', mktime(0,0,0,$i,1,0));
for ($i = 1; $i <= 31; $i++) $days_array[$i] = $i;
for ($i = 2000; $i <= 2025; $i++) $years_array[$i] = $i;
/* student/partner rate array */
for ($i = 0; $i <= 100; $i += 25) $student_partner_rate_array[$i] = $i.'%';
/* output */
$output .= '<form method="post" action="payment_code_action.php">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
if (isset($form_fields['payment_code_id'])) $output .= '<tr><td><b>Payment Code ID:</b></td><td colspan="3">'.$form_fields['payment_code_id'].'<input type="hidden" name="payment_code_id" value="'.$form_fields['payment_code_id'].'"></td></tr>';
$output .= '<tr>';
$output .= '<td><b>Active Start:</b></td>';
$output .= '<td><nobr>'.vlc_select_box($months_array, 'array', 'active_start_month', $form_fields['active_start_month'], true).' '.vlc_select_box($days_array, 'array', 'active_start_day', $form_fields['active_start_day'], true).' '.vlc_select_box($years_array, 'array', 'active_start_year', $form_fields['active_start_year'], true).' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].active_start_year,document.forms[0].active_start_month,document.forms[0].active_start_day,false,false,this);"></nobr></td>';
$output .= '<td><b>Active End:</b></td>';
$output .= '<td><nobr>'.vlc_select_box($months_array, 'array', 'active_end_month', $form_fields['active_end_month'], true).' '.vlc_select_box($days_array, 'array', 'active_end_day', $form_fields['active_end_day'], true).' '.vlc_select_box($years_array, 'array', 'active_end_year', $form_fields['active_end_year'], true).' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].active_end_year,document.forms[0].active_end_month,document.forms[0].active_end_day,false,false,this);"></nobr></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Code:</b></td>';
$output .= '<td><input type="text" size="30" name="code" value="'.$form_fields['code'].'"></td>';
$output .= '<td><b>Description:</b></td>';
$output .= '<td><input type="text" size="30" name="description" value="'.$form_fields['description'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Partner:</b></td>';
$output .= '<td colspan="3">'.vlc_select_box($partner_options_array, 'array', 'partner_id', $form_fields['partner_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Student Seminar Cost:</b></td>';
$output .= '<td>$ <input type="text" size="10" name="student_seminar_cost" value="'.$form_fields['student_seminar_cost'].'"></td>';
$output .= '<td><b>Partner Seminar Cost:</b></td>';
$output .= '<td>$ <input type="text" size="10" name="partner_seminar_cost" value="'.$form_fields['partner_seminar_cost'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Student Course Cost:</b></td>';
$output .= '<td>$ <input type="text" size="10" name="student_course_cost" value="'.$form_fields['student_course_cost'].'"></td>';
$output .= '<td><b>Partner Course Cost:</b></td>';
$output .= '<td>$ <input type="text" size="10" name="partner_course_cost" value="'.$form_fields['partner_course_cost'].'"></td>';
$output .= '</tr>';
$output .= '<tr><td colspan="4" align="center"><input type="submit" value="Submit"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
if (isset($form_fields['payment_code_id'])) $output .= $event_history;
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
