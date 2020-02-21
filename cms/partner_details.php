<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'partner-details';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get url variables */
if (isset($_GET['partner'])) $form_fields['partner_id'] = intval($_GET['partner']);
if (isset($_GET['num'])) $num = $_GET['num'];
else $num = 1;
/* get form fields */
if (strlen($status_message) > 0 and isset($_SESSION['form_fields']))
{
  $form_fields = $_SESSION['form_fields'];
  $_SESSION['form_fields'] = null;
}
elseif (isset($form_fields['partner_id']))
{
  /* get partner details */
  $partner_details_query = <<< END_QUERY
    SELECT partner_id, is_partner, is_diocese,
      IFNULL(student_seminar_cost, '') AS student_seminar_cost, IFNULL(partner_seminar_cost, '') AS partner_seminar_cost,
      IFNULL(student_course_cost, '') AS student_course_cost, IFNULL(partner_course_cost, '') AS partner_course_cost,
      IFNULL(description, '') AS description, IFNULL(alternate_description, '') AS alternate_description,
      IFNULL(address_1, '') AS address_1, IFNULL(address_2, '') AS address_2,
      IFNULL(city, '') AS city, IFNULL(state_id, -1) AS state_id, IFNULL(zip, '') AS zip,
      IFNULL(country_id, -1) AS country_id,
      IFNULL(notes, '') AS notes, IFNULL(url, '') AS url, IFNULL(bishop, '') AS bishop
    FROM partners
    WHERE partner_id = {$form_fields['partner_id']}
END_QUERY;
  $result = mysql_query($partner_details_query, $site_info['db_conn']);
  $form_fields = mysql_fetch_array($result);
  /* format cost fields */
  if (is_numeric($form_fields['student_seminar_cost'])) $form_fields['student_seminar_cost'] = number_format($form_fields['student_seminar_cost'] / 100, 2);
  else $form_fields['student_seminar_cost'] = '';
  if (is_numeric($form_fields['partner_seminar_cost'])) $form_fields['partner_seminar_cost'] = number_format($form_fields['partner_seminar_cost'] / 100, 2);
  else $form_fields['partner_seminar_cost'] = '';
  if (is_numeric($form_fields['student_course_cost'])) $form_fields['student_course_cost'] = number_format($form_fields['student_course_cost'] / 100, 2);
  else $form_fields['student_course_cost'] = '';
  if (is_numeric($form_fields['partner_course_cost'])) $form_fields['partner_course_cost'] = number_format($form_fields['partner_course_cost'] / 100, 2);
  else $form_fields['partner_course_cost'] = '';
  /* get diocesan partner representatives */
  $form_fields['representatives'] = array();
  $representative_query = <<< END_QUERY
    SELECT up.user_id, u.last_name, u.first_name
    FROM users_partners AS up, users AS u
    WHERE up.user_id = u.user_id
    AND up.partner_id = {$form_fields['partner_id']}
    ORDER BY u.last_name, u.first_name
END_QUERY;
  $result = mysql_query($representative_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) $form_fields['representatives'][] = $record;
  /* get event details */
  $entity_id = $form_fields['partner_id'];
  $event_type_array = array(
    PARTNERS_CREATE,
    PARTNERS_UPDATE,
    PARTNERS_ADD_REP,
    PARTNERS_REMOVE_REP
  );
  $event_history = vlc_get_event_history($event_type_array, $entity_id);
}
else
{
  $form_fields = array
  (
    'is_partner' => 0,
    'is_diocese' => 0,
    'student_seminar_cost' => '',
    'partner_seminar_cost' => '',
    'student_course_cost' => '',
    'partner_course_cost' => '',
    'description' => '',
    'alternate_description' => '',
    'address_1' => '',
    'address_2' => '',
    'city' => '',
    'state_id' => -1,
    'zip' => '',
    'country_id' => -1,
    'notes' => '',
    'url' => '',
    'bishop' => ''
  );
}
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = htmlspecialchars($value);
}
/* build array for "state" select box */
$state_options_array = array();
$state_query = <<< END_QUERY
  SELECT state_id, code, description
  FROM states
  ORDER BY description
END_QUERY;
$result = mysql_query($state_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $state_options_array[$record['state_id']] = $record['description'].' ('.$record['code'].')';
/* build array for "country" select box */
$country_options_array = array();
$country_query = <<< END_QUERY
  SELECT country_id, code, description
  FROM countries
  ORDER BY description
END_QUERY;
$result = mysql_query($country_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $country_options_array[$record['country_id']] = $record['description'].' ('.$record['code'].')';
/* begin output variable */
$output = '';
/* return to previous page link */
if (isset($_SERVER['HTTP_REFERER']) and $return_url = strstr($_SERVER['HTTP_REFERER'], 'cms/')) $output .= '<p>'.vlc_internal_link('Return to Previous Page', $return_url).'</p>';
/* student/partner rate array */
for ($i = 0; $i <= 100; $i += 25) $student_partner_rate_array[$i] = $i.'%';
/* output */
$output .= '<form method="post" action="partner_action.php">';
$output .= '<p><b>Note:</b> Leave <b>&quot;Cost&quot;</b> fields blank unless there is a special arrangement in which the partner is required to pay part of the course fees. If that is the case, then all four <b>&quot;Cost&quot;</b> fields must be filled in.</p>';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
if (isset($form_fields['partner_id'])) $output .= '<tr><td><b>Partner ID:</b></td><td colspan="3">'.$form_fields['partner_id'].'<input type="hidden" name="partner_id" value="'.$form_fields['partner_id'].'"></td></tr>';
$output .= '<tr>';
$output .= '<td><b>Description:</b></td>';
$output .= '<td><input type="text" size="30" name="description" value="'.$form_fields['description'].'"></td>';
$output .= '<td><b>Alternate Description:</b></td>';
$output .= '<td><input type="text" size="30" name="alternate_description" value="'.$form_fields['alternate_description'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><nobr><b>Student Seminar Cost:</b></nobr></td>';
$output .= '<td>$&nbsp;<input type="text" size="10" name="student_seminar_cost" value="'.$form_fields['student_seminar_cost'].'">&nbsp;(For ex. "30" or "30.00")</td>';
$output .= '<td><b>Partner Seminar Cost:</b></td>';
$output .= '<td>$&nbsp;<input type="text" size="10" name="partner_seminar_cost" value="'.$form_fields['partner_seminar_cost'].'">&nbsp;(For ex. "30" or "30.00")</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><nobr><b>Student Course Cost:</b></nobr></td>';
$output .= '<td>$&nbsp;<input type="text" size="10" name="student_course_cost" value="'.$form_fields['student_course_cost'].'">&nbsp;(For ex. "30" or "30.00")</td>';
$output .= '<td><b>Partner Course Cost:</b></td>';
$output .= '<td>$&nbsp;<input type="text" size="10" name="partner_course_cost" value="'.$form_fields['partner_course_cost'].'">&nbsp;(For ex. "30" or "30.00")</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Partner:</b></td>';
$output .= '<td><input type="checkbox" name="is_partner" value="1"'.($form_fields['is_partner'] ? ' checked="checked"' : '').'></td>';
$output .= '<td><b>Diocese:</b></td>';
$output .= '<td><input type="checkbox" name="is_diocese" value="1"'.($form_fields['is_diocese'] ? ' checked="checked"' : '').'></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Address 1:</b></td>';
$output .= '<td colspan="3"><input type="text" size="30" name="address_1" value="'.$form_fields['address_1'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Address 2:</b></td>';
$output .= '<td colspan="3"><input type="text" size="30" name="address_2" value="'.$form_fields['address_2'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>City:</b></td>';
$output .= '<td colspan="3"><input type="text" size="30" name="city" value="'.$form_fields['city'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>State:</b></td>';
$output .= '<td colspan="3">'.vlc_select_box($state_options_array, 'array', 'state_id', $form_fields['state_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Country:</b></td>';
$output .= '<td colspan="3">'.vlc_select_box($country_options_array, 'array', 'country_id', $form_fields['country_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Zip:</b></td>';
$output .= '<td colspan="3"><input type="text" size="30" name="zip" value="'.$form_fields['zip'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Bishop:</b></td>';
$output .= '<td colspan="3"><input type="text" size="30" name="bishop" value="'.$form_fields['bishop'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>URL:</b></td>';
$output .= '<td colspan="3"><input type="text" size="30" name="url" value="'.$form_fields['url'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td valign="top"><b>Notes:</b></td>';
$output .= '<td colspan="3"><textarea rows="5" cols="60" name="notes">'.$form_fields['notes'].'</textarea></td>';
$output .= '</tr>';
$output .= '<tr><td colspan="4" align="center"><input type="submit" value="Submit"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
if (isset($form_fields['partner_id']))
{
  $output .= '<h3>Diocesan Partner Representatives (Still Under Development!):</h3>';
  $output .= '<p>The following users are linked to this partner.</p>';
  $output .= '<form method="post" action="partner_action.php">';
  $output .= '<input type="hidden" name="partner_id" value="'.$form_fields['partner_id'].'">';
  $output .= '<ul>';
  $output .= '<li>To view additional user details, click the <b>&quot;User ID&quot;</b> link.</li>';
  $output .= '<li>To add users to this partner, enter the <b>&quot;User ID&quot;</b> and click <b>&quot;Submit&quot;</b>: <input type="text" name="user_id" size="10"> <input type="submit" value="Submit"></li>';
  $output .= '</ul>';
  $output .= '</form>';
  $output .= '<form method="post" action="partner_action.php">';
  $output .= '<input type="hidden" name="partner_id" value="'.$form_fields['partner_id'].'">';
  $output .= '<table border="1" cellpadding="5" cellspacing="0">';
  $output .= '<tr><th>&nbsp;</th><th>User ID</th><th>Name</th><th>Remove</th></tr>';
  $i = 1;
  if (isset($form_fields['representatives']) and count($form_fields['representatives']))
  {
    foreach ($form_fields['representatives'] as $representative)
    {
      $output .= '<tr>';
      $output .= '<td>'.$i++.'.</td>';
      $output .= '<td align="center">'.vlc_internal_link($representative['user_id'], 'cms/user_details.php?user='.$representative['user_id']).'</td>';
      $output .= '<td>'.$representative['last_name'].', '.$representative['first_name'].'</td>';
      $output .= '<td align="center"><input type="checkbox" name="representatives['.$representative['user_id'].'][remove]" value="1"></td>';
      $output .= '</tr>';
    }
    $output .= '<tr><td colspan="4" align="center"><input type="submit" value="Submit"></td></tr>';
  }
  else $output .= '<tr><td colspan="4" align="center">No Diocesan Partner Representatives Found.</td></tr>';
  $output .= '</table>';
  $output .= '</form>';
  if (isset($event_history)) $output .= $event_history;
}
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
