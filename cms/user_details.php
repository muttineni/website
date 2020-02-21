<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'user-details';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get url variables */
if (isset($_GET['user'])) $form_fields['user_id'] = intval($_GET['user']);
if (isset($_GET['num'])) $num = $_GET['num'];
else $num = 1;
/* get form fields */
if (strlen($status_message) > 0 and isset($_SESSION['form_fields']))
{
  $form_fields = $_SESSION['form_fields'];
  $_SESSION['form_fields'] = null;
}
if (isset($form_fields['user_id']))
{
  if (!isset($form_fields['username']))
  {
    /* get user details */
    $user_details_query = <<< END_QUERY
      SELECT u.user_id, IFNULL(u.username, '') AS username, IFNULL(u.username, '') AS original_username, IFNULL(u.password, '') AS password,
        IFNULL(u.first_name, '') AS first_name, IFNULL(u.middle_name, '') AS middle_name, IFNULL(u.last_name, '') AS last_name,
        IFNULL(u.prefix, '') AS prefix, IFNULL(u.suffix, '') AS suffix, IFNULL(u.nickname, '') AS nickname,
        IFNULL(MONTH(u.active_start), -1) AS active_start_month,
        IFNULL(DAYOFMONTH(u.active_start), -1) AS active_start_day,
        IFNULL(YEAR(u.active_start), -1) AS active_start_year,
        IFNULL(MONTH(u.active_end), -1) AS active_end_month,
        IFNULL(DAYOFMONTH(u.active_end), -1) AS active_end_day,
        IFNULL(YEAR(u.active_end), -1) AS active_end_year,
        IFNULL(MONTH(i.birth_date), -1) AS birth_date_month,
        IFNULL(DAYOFMONTH(i.birth_date), -1) AS birth_date_day,
        IFNULL(YEAR(i.birth_date), -1) AS birth_date_year,
        IFNULL(i.is_us_citizen, -1) AS is_us_citizen,
        IFNULL(i.marital_status_id, -1) AS marital_status_id, IFNULL(i.religion_id, -1) AS religion_id,
        IFNULL(i.gender_type_id, -1) AS gender_type_id, IFNULL(i.race_type_id, -1) AS race_type_id, IFNULL(i.occupation_id, -1) AS occupation_id,
        IFNULL(i.partner_id, -1) AS partner_id, IFNULL(i.diocese_id, -1) AS diocese_id, IFNULL(i.diocese, '') AS diocese,
		  IFNULL(i.parish, '') AS parish,
        IFNULL(i.address_1, '') AS address_1, IFNULL(i.address_2, '') AS address_2,
        IFNULL(i.city, '') AS city, IFNULL(i.state_id, -1) AS state_id, IFNULL(i.zip, '') AS zip,
        IFNULL(i.country_id, 222) AS country_id, IFNULL(i.international_address, '') AS international_address,
        IFNULL(i.primary_phone, '') AS primary_phone, IFNULL(i.secondary_phone, '') AS secondary_phone, IFNULL(i.fax, '') AS fax,
        IFNULL(i.primary_email, '') AS primary_email, IFNULL(i.secondary_email, '') AS secondary_email,
        IFNULL(i.url, '') AS url, IFNULL(i.title, '') AS title, IFNULL(i.image, '') AS image, 
		  IFNULL(i.biography, '') AS biography,
        i.send_email_notification
      FROM users AS u, user_info AS i
      WHERE u.user_id = i.user_id
      AND u.user_id = {$form_fields['user_id']}
END_QUERY;
    $result = mysql_query($user_details_query, $site_info['db_conn']);
    $form_fields = mysql_fetch_array($result);
	  
    /* get user role(s) */
    $user_role_query = 'SELECT user_role_id FROM users_roles WHERE user_id = '.$form_fields['user_id'];
    $result = mysql_query($user_role_query, $site_info['db_conn']);
    $form_fields['user_roles'] = array();
    while ($record = mysql_fetch_array($result)) $form_fields['user_roles'][] = $record['user_role_id'];
  }
  if (!isset($form_fields['courses']))
  {
    $form_fields['courses'] = array();
    /* get courses linked to this user */
    $course_query = <<< END_QUERY
      SELECT uc.user_course_id, uc.course_id, o.order_id, c.code, c.description AS course_name, p.description AS pay_status,
        UNIX_TIMESTAMP(y.cycle_start) AS cycle_start_timestamp, r.description AS user_role, s.description AS course_status
      FROM users_courses AS uc, courses AS c, cycles AS y, user_roles AS r, course_status AS s, orders AS o, payment_status AS p
      WHERE uc.course_id = c.course_id
      AND c.cycle_id = y.cycle_id
      AND uc.user_role_id = r.user_role_id
      AND uc.course_status_id = s.course_status_id
      AND p.payment_status_id = o.payment_status_id
      AND
      (
        (uc.user_course_id = o.product_id AND uc.user_id = o.customer_id AND o.product_type_id = 1 AND o.customer_type_id = 1)
        OR
        (uc.course_id = o.product_id AND uc.user_id = o.customer_id AND o.product_type_id = 3 AND o.customer_type_id = 3)
      )
      AND uc.user_id = {$form_fields['user_id']}
      ORDER BY y.cycle_start, c.code
END_QUERY;
    $result = mysql_query($course_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result)) $form_fields['courses'][] = $record;
  }
  /* get credit amount */
  $credit_amount_query = <<< END_QUERY
    SELECT credit_id, credit_amount
    FROM credits
    WHERE customer_type_id = 1
    AND customer_id = {$form_fields['user_id']}
END_QUERY;
  $result = mysql_query($credit_amount_query, $site_info['db_conn']);
  $credit_details = mysql_fetch_array($result);
  /* build array for "course" select box */
  $course_options_array = array();
  $course_options_query = <<< END_QUERY
    SELECT c.course_id, c.code AS course_code, c.description, y.cycle_id, y.code AS cycle_code, UNIX_TIMESTAMP(y.cycle_start) AS cycle_start
    FROM courses AS c, cycles AS y
    WHERE c.cycle_id = y.cycle_id
    ORDER BY y.cycle_start DESC, c.code
END_QUERY;
  $result = mysql_query($course_options_query, $site_info['db_conn']);
  $previous_cycle = 0;
  while ($record = mysql_fetch_array($result))
  {
    $current_cycle = $record['cycle_id'];
    if ($current_cycle != $previous_cycle)
    {
      $previous_cycle = $current_cycle;
      $course_options_array[$current_cycle]['label'] = $record['cycle_code'].' - '.date('M. d, Y', $record['cycle_start']).' ('.$record['cycle_id'].')';
    }
    $course_options_array[$current_cycle]['options'][$record['course_id']] = $record['course_code'].' - '.$record['description'].' ('.$record['course_id'].')';
  }
  /* get event details */
  $event_type_array_1 = array(
    USERS_CREATE,
    USERS_UPDATE,
    USERS_ADD_COURSE,
    USERS_REMOVE_COURSE,
    USERS_ADD_TO_PARTNER,
    USERS_REMOVE_FROM_PARTNER
  );
  $event_type_list_1 = join(', ', $event_type_array_1);
  $event_type_array_2 = array(
    CREDITS_UPDATE
  );
  $event_type_list_2 = join(', ', $event_type_array_2);
  $event_details_query = <<< END_QUERY
    SELECT v.description,
      CONCAT(u.first_name, ' ', u.last_name) AS CREATEDBY,
      DATE_FORMAT(e.CREATED, '%c/%e/%Y %l:%i:%s %p') AS CREATED
    FROM events AS e, event_types AS v, users AS u
    WHERE e.event_type_id = v.event_type_id
    AND e.CREATEDBY = u.user_id
    AND
    (
      (e.event_type_id IN ($event_type_list_1) AND e.entity_id = {$form_fields['user_id']})
        OR
      (e.event_type_id IN ($event_type_list_2) AND e.entity_id = {$credit_details['credit_id']})
    )
    ORDER BY e.event_id
END_QUERY;
  $result = mysql_query($event_details_query, $site_info['db_conn']);
  $event_details_array = array();
  while ($record = mysql_fetch_array($result)) $event_details_array[] = $record;
}
else
{
  if (!isset($form_fields['username']))
  {
    $form_fields = array
    (
      'username' => '', 'original_username' => '', 'password' => '',
      'first_name' => '', 'middle_name' => '', 'last_name' => '',
      'prefix' => '', 'suffix' => '', 'nickname' => '',
      'active_start_month' => date('n'),
      'active_start_day' => date('j'),
      'active_start_year' => date('Y'),
      'active_end_month' => date('n'),
      'active_end_day' => date('j'),
      'active_end_year' => date('Y') + 10,
      'birth_date_month' => -1,
      'birth_date_day' => -1,
      'birth_date_year' => -1,
      'is_us_citizen' => -1,
      'marital_status_id' => -1, 'religion_id' => -1,
      'gender_type_id' => -1, 'race_type_id' => -1, 'occupation_id' => -1,
      'partner_id' => -1, 'diocese_id' => -1, 'diocese' => '',
		'parish' => '',
      'address_1' => '', 'address_2' => '',
      'city' => '', 'state_id' => -1, 'zip' => '',
      'country_id' => 222, 'international_address' => '',
      'primary_phone' => '', 'secondary_phone' => '', 'fax' => '',
      'primary_email' => '', 'secondary_email' => '',
      'url' => '', 'title' => '', 'image' => '', 'biography' => '',
      'send_email_notification' => 0
    );
    $form_fields['user_roles'] = array(5);
  }
}
foreach ($form_fields as $key => $value)
{
  //if (is_string($value)) $form_fields[$key] = htmlspecialchars($value); commented out by bob 4/28/2015 to fix hidden data issue
}
/* get user_roles */
$user_role_query = <<< END_QUERY
  SELECT user_role_id, IFNULL(description, user_role_id) AS description
  FROM user_roles
  ORDER BY description
END_QUERY;
$result = mysql_query($user_role_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $user_role_options_array[$record['user_role_id']] = $record['description'];
/* get marital_status */
$marital_status_query = <<< END_QUERY
  SELECT marital_status_id, IFNULL(description, marital_status_id) AS description
  FROM marital_status
  ORDER BY description
END_QUERY;
$result = mysql_query($marital_status_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $marital_status_options_array[$record['marital_status_id']] = $record['description'];
/* get religions */
$religion_query = <<< END_QUERY
  SELECT religion_id, IFNULL(description, religion_id) AS description
  FROM religions
  ORDER BY description
END_QUERY;
$result = mysql_query($religion_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $religion_options_array[$record['religion_id']] = $record['description'];
/* get gender_types */
$gender_type_query = <<< END_QUERY
  SELECT gender_type_id, IFNULL(description, gender_type_id) AS description
  FROM gender_types
  ORDER BY description
END_QUERY;
$result = mysql_query($gender_type_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $gender_type_options_array[$record['gender_type_id']] = $record['description'];
/* get race_types */
$race_type_query = <<< END_QUERY
  SELECT race_type_id, IFNULL(description, race_type_id) AS description
  FROM race_types
  ORDER BY description
END_QUERY;
$result = mysql_query($race_type_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $race_type_options_array[$record['race_type_id']] = $record['description'];
/* get occupations */
$occupation_query = <<< END_QUERY
  SELECT occupation_id, IFNULL(description, occupation_id) AS description
  FROM occupations
  ORDER BY description
END_QUERY;
$result = mysql_query($occupation_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $occupation_options_array[$record['occupation_id']] = $record['description'];
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
/* build array for "diocese" select box */
$diocese_options_array = array();
$diocese_query = <<< END_QUERY
  SELECT p.partner_id, p.description, p.is_partner, IFNULL(s.code, c.description) AS state_country
  FROM partners AS p LEFT JOIN states AS s USING (state_id), countries AS c
  WHERE p.is_diocese = 1
  AND (p.is_partner = 1 OR p.country_id = 222 OR p.partner_id = 2658)
  AND p.country_id = c.country_id
  ORDER BY p.description
END_QUERY;
$result = mysql_query($diocese_query, $site_info['db_conn']);
$diocese_options_array[1]['label'] = 'Partner';
$diocese_options_array[0]['label'] = 'Non-Partner';
while ($record = mysql_fetch_array($result))
{
  if ($record['is_partner']) $diocese_options_array[1]['options'][$record['partner_id']] = $record['description'].' ('.$record['state_country'].')';
  else $diocese_options_array[0]['options'][$record['partner_id']] = $record['description'].' ('.$record['state_country'].')';
}
/* build array for "special partner" select box */
$partner_options_array = array();
$partner_query = <<< END_QUERY
  SELECT partner_id, description, is_partner
  FROM partners
  WHERE is_diocese = 0
  ORDER BY description
END_QUERY;
$result = mysql_query($partner_query, $site_info['db_conn']);
$partner_options_array[1]['label'] = 'Partner';
$partner_options_array[0]['label'] = 'Non-Partner';
while ($record = mysql_fetch_array($result))
{
  if ($record['is_partner']) $partner_options_array[1]['options'][$record['partner_id']] = $record['description'];
  else $partner_options_array[0]['options'][$record['partner_id']] = $record['description'];
}
/* build array for "citizen" select box */
$citizen_options_array = array(1 => 'Yes', 0 => 'No');
/* begin output variable */
$output = '';
/* return to previous page link */
if (isset($_SERVER['HTTP_REFERER']) and $return_url = strstr($_SERVER['HTTP_REFERER'], 'cms/')) $output .= '<p>'.vlc_internal_link('Return to Previous Page', $return_url).'</p>';
/* month, day, year arrays */
for ($i = 1; $i <= 12; $i++) $months_array[$i] = date('F', mktime(0,0,0,$i,1,0));
for ($i = 1; $i <= 31; $i++) $days_array[$i] = $i;
for ($i = 2000; $i <= 2030; $i++) $years_array[$i] = $i;
for ($i = 1900; $i <= 2005; $i++) $birth_years_array[$i] = $i;
/* num courses array */
for ($i = 5; $i <= 50; $i += 5) $num_courses_array[$i] = $i;
/* output */
$output .= '<form method="post" action="user_action.php">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
if (isset($form_fields['user_id'])) $output .= '<tr><td><b>User ID:</b></td><td>'.$form_fields['user_id'].'<input type="hidden" name="user_id" value="'.$form_fields['user_id'].'"></td><td><b>Credit Amount:</b></td><td>$'.number_format($credit_details['credit_amount'] / 100, 2).'</td></tr>';
$output .= '<tr>';
$output .= '<td width="25%"><b>Active Start:</b></td>';
$output .= '<td width="25%"><nobr>'.vlc_select_box($months_array, 'array', 'active_start_month', $form_fields['active_start_month'], true).' '.vlc_select_box($days_array, 'array', 'active_start_day', $form_fields['active_start_day'], true).' '.vlc_select_box($years_array, 'array', 'active_start_year', $form_fields['active_start_year'], true).' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].active_start_year,document.forms[0].active_start_month,document.forms[0].active_start_day,false,false,this);"></nobr></td>';
$output .= '<td width="25%"><b>Active End:</b></td>';
$output .= '<td width="25%"><nobr>'.vlc_select_box($months_array, 'array', 'active_end_month', $form_fields['active_end_month'], true).' '.vlc_select_box($days_array, 'array', 'active_end_day', $form_fields['active_end_day'], true).' '.vlc_select_box($years_array, 'array', 'active_end_year', $form_fields['active_end_year'], true).' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].active_end_year,document.forms[0].active_end_month,document.forms[0].active_end_day,false,false,this);"></nobr></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td valign="top"><b>User Role(s):</b></td>';
$output .= '<td colspan="3">';
$output .= '<select multiple size="3" name="user_roles[]">';
foreach ($user_role_options_array as $user_role_id => $user_role)
{
  if (in_array($user_role_id, $form_fields['user_roles'])) $selected = ' selected';
  else $selected = '';
  $output .= '<option value="'.$user_role_id.'"'.$selected.'>'.$user_role.'</option>';
}
$output .= '</select> (Note: Use CTRL or SHIFT to select multiple options.)';
$output .= '</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Username:</b></td>';
$output .= '<td>';
$output .= '<input type="text" size="30" name="username" value="'.$form_fields['username'].'" onblur="ajax_request(this.value);">';
$output .= '<br><span id="validate-username">Enter username and hit tab to check for availability.</span>';
$output .= '<input type="hidden" name="original_username" value="'.$form_fields['original_username'].'">';
$output .= '</td>';
$output .= '<td><b>Password:</b></td>';
$output .= '<td><input type="text" size="30" name="password" value="'.$form_fields['password'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Prefix:</b></td>';
$output .= '<td colspan="3"><input type="text" size="10" name="prefix" value="'.$form_fields['prefix'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Name:</b></td>';
$output .= '<td colspan="3"><input type="text" size="30" name="first_name" value="'.$form_fields['first_name'].'"> <input type="text" size="10" name="middle_name" value="'.$form_fields['middle_name'].'"> <input type="text" size="30" name="last_name" value="'.$form_fields['last_name'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Suffix:</b></td>';
$output .= '<td colspan="3"><input type="text" size="10" name="suffix" value="'.$form_fields['suffix'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Nickname:</b></td>';
$output .= '<td colspan="3"><input type="text" size="30" name="nickname" value="'.$form_fields['nickname'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Birth Date:</b></td>';
$output .= '<td colspan="3"><nobr>'.vlc_select_box($months_array, 'array', 'birth_date_month', $form_fields['birth_date_month'], false).' '.vlc_select_box($days_array, 'array', 'birth_date_day', $form_fields['birth_date_day'], false).' '.vlc_select_box($birth_years_array, 'array', 'birth_date_year', $form_fields['birth_date_year'], false).' <img src="'.$site_info['js_url'].'calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == \'block\') closeCalendar(); else displayCalendarSelectBox(document.forms[0].birth_date_year,document.forms[0].birth_date_month,document.forms[0].birth_date_day,false,false,this);"></nobr></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>U.S. Citizen:</b></td>';
$output .= '<td>'.vlc_select_box($citizen_options_array, 'array', 'is_us_citizen', $form_fields['is_us_citizen'], false).'</td>';
$output .= '<td><b>Marital Status:</b></td>';
$output .= '<td>'.vlc_select_box($marital_status_options_array, 'array', 'marital_status_id', $form_fields['marital_status_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Gender:</b></td>';
$output .= '<td>'.vlc_select_box($gender_type_options_array, 'array', 'gender_type_id', $form_fields['gender_type_id'], false).'</td>';
$output .= '<td><b>Religion:</b></td>';
$output .= '<td>'.vlc_select_box($religion_options_array, 'array', 'religion_id', $form_fields['religion_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Race:</b></td>';
$output .= '<td colspan="3">'.vlc_select_box($race_type_options_array, 'array', 'race_type_id', $form_fields['race_type_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Occupation:</b></td>';
$output .= '<td colspan="3">'.vlc_select_box($occupation_options_array, 'array', 'occupation_id', $form_fields['occupation_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Partner:</b></td>';
$output .= '<td colspan="3">'.vlc_select_box($partner_options_array, 'array', 'partner_id', $form_fields['partner_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Diocese:</b></td>';
$output .= '<td colspan="3">'.vlc_select_box($diocese_options_array, 'array', 'diocese_id', $form_fields['diocese_id'], false).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Diocese (Text):</b></td>';
$output .= '<td colspan="3"><input type="text" size="30" name="diocese" value="'.$form_fields['diocese'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Local Parish / Organization:</b></td>';
$output .= '<td colspan="3"><input type="text" size="30" name="parish" value="'.$form_fields['parish'].'"></td>';
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
$output .= '<td><b>Zip:</b></td>';
$output .= '<td colspan="3"><input type="text" size="30" name="zip" value="'.$form_fields['zip'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Country:</b></td>';
$output .= '<td colspan="3">'.vlc_select_box($country_options_array, 'array', 'country_id', $form_fields['country_id'], true).'</td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td valign="top"><b>International Address:</b></td>';
$output .= '<td colspan="3"><textarea rows="5" cols="60" name="international_address">'.$form_fields['international_address'].'</textarea></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Phone 1:</b></td>';
$output .= '<td><input type="text" size="30" name="primary_phone" value="'.$form_fields['primary_phone'].'"></td>';
$output .= '<td><b>Phone 2:</b></td>';
$output .= '<td><input type="text" size="30" name="secondary_phone" value="'.$form_fields['secondary_phone'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Fax:</b></td>';
$output .= '<td colspan="3"><input type="text" size="30" name="fax" value="'.$form_fields['fax'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>E-Mail 1:</b></td>';
$output .= '<td><input type="text" size="30" name="primary_email" value="'.$form_fields['primary_email'].'"></td>';
$output .= '<td><b>E-Mail 2:</b></td>';
$output .= '<td><input type="text" size="30" name="secondary_email" value="'.$form_fields['secondary_email'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>URL:</b></td>';
$output .= '<td colspan="3"><input type="text" size="30" name="url" value="'.$form_fields['url'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Title:</b></td>';
$output .= '<td colspan="3"><input type="text" size="30" name="title" value="'.$form_fields['title'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>Image:</b></td>';
$output .= '<td colspan="3"><input type="text" size="30" name="image" value="'.$form_fields['image'].'"></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td valign="top"><b>Biography:</b></td>';
$output .= '<td colspan="3"><textarea rows="5" cols="60" name="biography">'.$form_fields['biography'].'</textarea></td>';
$output .= '</tr>';
$output .= '<tr>';
$output .= '<td><b>VLC-Mail Notification:</b></td>';
$output .= '<td colspan="3"><input type="checkbox" name="send_email_notification" value="1"'.($form_fields['send_email_notification'] ? ' checked="checked"' : '').'></td>';
$output .= '</tr>';
$output .= '<tr><td colspan="4" align="center"><input type="submit" value="Submit"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
if (isset($form_fields['user_id']))
{
  if ($credit_details['credit_amount'])
  {
    $output .= '<h3>Credit / Raincheck:</h3>';
    $output .= '<p>This user has a credit of <b>$'.number_format($credit_details['credit_amount'] / 100, 2).'</b>.</p>';
    $output .= '<form method="post" action="user_action.php">';
    $output .= '<input type="hidden" name="user_id" value="'.$form_fields['user_id'].'">';
    $output .= '<ul><li>';
    $output .= 'To transfer credit to another user, enter the user ID and the amount to transfer and click <b>&quot;Submit&quot;</b>:';
    $output .= '<ul><li>';
    $output .= '<b>Transfer User ID:</b> <input type="text" size="10" name="transfer_user_id" value="" style="text-align:right"> ';
    $output .= '<b>Transfer Amount:</b> $ <input type="text" size="10" name="transfer_amount" value="" style="text-align:right"> ';
    $output .= '<input type="submit" value="Submit">';
    $output .= '</li></ul>';
    $output .= '</li></ul>';
    $output .= '</form>';
  }
  $evaluation_options_array = array(1 => 'Grouped by Question (HTML)', 'Grouped by Student (HTML)', 'Export to Excel (CSV)', 'Printable Table (PDF - Portrait)', 'Printable Table (PDF - Landscape)');
  $output .= '<h3>Course Evaluations:</h3>';
  $output .= '<form method="get" action="evaluations.php">';
  $output .= '<input type="hidden" name="user" value="'.$form_fields['user_id'].'">';
  $output .= '<p>Select a Format: '.vlc_select_box($evaluation_options_array, 'array', 'format', -1, true).' <input type="submit" value="Go"></p>';
  $output .= '</form>';
  $output .= '<h3>Courses:</h3>';
  $output .= '<form method="get" action="user_details.php#courses">';
  $output .= '<input type="hidden" name="user" value="'.$form_fields['user_id'].'">';
  $output .= '<p>The following courses are linked to this user.</p>';
  $output .= '<ul>';
  $output .= '<li>To view additional order details, click the <b>&quot;Order ID&quot;</b> link.</li>';
  $output .= '<li>To view additional course registration details, click the <b>&quot;Course Registration ID&quot;</b> link.</li>';
  $output .= '<li>To view additional course details, click the <b>&quot;Course ID&quot;</b> link.</li>';
  $output .= '<li>To transfer course registrations to another user, enter the user ID, check the boxes below, and click <b>&quot;Submit&quot;</b>:</li>';
  $output .= '<li>To add courses to this user, select the number of courses to add and click <b>&quot;Submit&quot;</b>: '.vlc_select_box($num_courses_array, 'array', 'num', -1, true).' <input type="submit" value="Submit"></li>';
  $output .= '</ul>';
  $output .= '</form>';
  if (count($form_fields['courses'])) $disabled = '';
  else $disabled = ' disabled';
  $output .= '<form method="post" action="user_action.php">';
  $output .= '<input type="hidden" name="user_id" value="'.$form_fields['user_id'].'">';
  $output .= '<table border="1" cellpadding="5" cellspacing="0">';
  $output .= '<tr><th><input type="checkbox" name="check_all_checkbox" onclick="check_all(this, \'user_course_id_array[]\');" checked'.$disabled.'></th><th>Order ID</th><th>Course Registration ID</th><th>Course Name</th><th>Course Date</th><th>User Role</th><th>Course Status</th><th>Pay Status</th></tr>';
  $i = 1;
  if (count($form_fields['courses']))
  {
    foreach ($form_fields['courses'] as $course)
    {
      $output .= '<tr>';
      $output .= '<td><nobr><input type="checkbox" name="user_course_id_array[]" value="'.$course['user_course_id'].'" id="'.$course['user_course_id'].'" checked> <label for="'.$course['user_course_id'].'">'.$i++.'.</label></nobr></td>';
      $output .= '<td align="center">'.vlc_internal_link($course['order_id'], 'cms/order_details.php?order='.$course['order_id']).'</td>';
      $output .= '<td align="center">'.vlc_internal_link($course['user_course_id'], 'cms/user_course_details.php?user_course='.$course['user_course_id']).'</td>';
      $output .= '<td>'.$course['code'].' - '.$course['course_name'].' ('.vlc_internal_link($course['course_id'], 'cms/course_details.php?course='.$course['course_id']).')</td>';
      $output .= '<td align="right">'.date('n/j/Y', $course['cycle_start_timestamp']).'</td>';
      $output .= '<td align="center">'.$course['user_role'].'</td>';
      $output .= '<td align="center">'.$course['course_status'].'</td>';
      $output .= '<td align="center">'.$course['pay_status'].'</td>';
      $output .= '</tr>';
    }
  }
  else $output .= '<tr><td colspan="8" align="center">No Courses Found.</td></tr>';
  $output .= '<tr><td colspan="8" align="center"><b>Transfer course history to user ID:</b> <input type="text" size="10" name="transfer_user_id" value=""'.$disabled.'> <input type="submit" value="Submit"'.$disabled.'></td></tr>';
  $output .= '</table>';
  $output .= '</form>';
  $output .= '<a name="courses"></a><h3>Add Courses:</h3>';
  $output .= '<form method="post" action="user_action.php">';
  $output .= '<p>Select the course(s) to add and click <b>&quot;Submit&quot;</b>:</p>';
  $output .= '<input type="hidden" name="user_id" value="'.$form_fields['user_id'].'">';
  $output .= '<ol>';
  for ($j = 0; $j < $num; $j++) $output .= '<li>'.vlc_select_box($course_options_array, 'array', 'courses[][course_id]', -1, false).'</li>';
  $output .= '</ol>';
  $output .= '<p><input type="submit" value="Submit"></p>';
  $output .= '</form>';
  if (count($event_details_array))
  {
    $output .= '<h3>Database Record Update History</h3>';
    $output .= '<ul><li><a href="javascript:show_hide_content(\'event-history\')">[+/-] Show/Hide Database Record Update History</a></li></ul>';
    $output .= '<div id="event-history" style="display: none;">';
    $output .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
    $output .= '<tr><th>Date</th><th>User</th><th>Event</th></tr>';
    foreach ($event_details_array as $event)
    {
      $output .= '<tr>';
      $output .= '<td>'.$event['CREATED'].'</td>';
      $output .= '<td>'.$event['CREATEDBY'].'</td>';
      $output .= '<td>'.$event['description'].'</td>';
      $output .= '</tr>';
    }
    $output .= '</table>';
    $output .= '</div>';
  }
}
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<script type="text/javascript">
/* create ajax object */
function ajax_create()
{
  if (window.XMLHttpRequest) return new XMLHttpRequest();
  else if (window.ActiveXObject) return new ActiveXObject('Microsoft.XMLHTTP');
  else return null;
}
var http = ajax_create();

/* send ajax request */
function ajax_request(username)
{
  http.open('get', 'xhr.php?field=username_available&query='+username);
  http.onreadystatechange = ajax_response;
  http.send(null);
}

/* get ajax response */
function ajax_response()
{
  if (http.readyState == 4)
  {
    if (parseInt(http.responseText) == 0)
    {
      document.getElementById('validate-username').innerHTML = '<b>Username Available</b>';
    }
    else
    {
      document.getElementById('validate-username').innerHTML = '<b>Username Already Taken</b>';
    }
  }
}
</script>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
