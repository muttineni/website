<?php // add partner and non-partner cost fields...
$page_info['section'] = 'cms';
$page_info['page'] = 'cert-prog-details';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get url variables */
if (isset($_GET['cert_prog'])) $form_fields['cert_prog_id'] = intval($_GET['cert_prog']);
if (isset($_GET['num'])) $num = $_GET['num'];
else $num = 1;
/* get form fields */
if (strlen($status_message) > 0 and isset($_SESSION['form_fields']))
{
  $form_fields = $_SESSION['form_fields'];
  $_SESSION['form_fields'] = null;
}
if (isset($form_fields['cert_prog_id']))
{
  if (!isset($form_fields['description']))
  {
    /* get cert prog details */
    $cert_prog_query = <<< END_QUERY
      SELECT cert_prog_id, cert_level_id, IFNULL(description, '') AS description, display_order
      FROM cert_progs
      WHERE cert_prog_id = {$form_fields['cert_prog_id']}
END_QUERY;
    $result = mysql_query($cert_prog_query, $site_info['db_conn']);
    $cert_prog_details = mysql_fetch_array($result);
    $form_fields = array_merge($form_fields, $cert_prog_details);
  }
  if (!isset($form_fields['course_subjects'])) $form_fields['course_subjects'] = array();
  /* get course subjects */
  $course_subject_query = <<< END_QUERY
    SELECT course_subject_id, IFNULL(description, course_subject_id) AS description
    FROM course_subjects
    ORDER BY description
END_QUERY;
  $result = mysql_query($course_subject_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) $course_subject_options_array[$record['course_subject_id']] = $record['description'];
  /* get certificate categories */
  $cert_cat_query = <<< END_QUERY
    SELECT cert_cat_id, IFNULL(description, cert_cat_id) AS description
    FROM cert_cats
    ORDER BY display_order
END_QUERY;
  $result = mysql_query($cert_cat_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) $cert_cat_options_array[$record['cert_cat_id']] = $record['description'];
}
elseif (!isset($form_fields['description']))
{
  $form_fields = array
  (
    'cert_level_id' => -1, 'description' => '', 'display_order' => -1
  );
}
if (isset($form_fields['cert_prog_id']))
{
  /* get course subjects linked to this certificate program */
  $course_subject_query = <<< END_QUERY
    SELECT ps.course_subject_id, s.description, IFNULL(ps.cert_cat_id, 0) AS cert_cat_id, ps.display_order
    FROM certs_courses AS ps LEFT JOIN cert_cats AS c ON ps.cert_cat_id = c.cert_cat_id, course_subjects AS s
    WHERE ps.course_subject_id = s.course_subject_id
    AND ps.cert_prog_id = {$form_fields['cert_prog_id']}
    ORDER BY c.display_order, ps.display_order
END_QUERY;
  $result = mysql_query($course_subject_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) $course_subject_details[$record['course_subject_id']] = $record;
  /* get students linked to this certificate program */
  $form_fields['students'] = array();
  $student_query = <<< END_QUERY
    SELECT cu.cert_user_id, cu.user_id, p.description AS payment_status,
      cu.cert_status_id, u.last_name, u.first_name, o.order_id,
      CASE o.discount_type_id
        WHEN 1 THEN CONCAT(b.code, ' - ', b.description)
        WHEN 2 THEN a.description
        WHEN 3 THEN a.description
        ELSE '&nbsp;'
      END AS discount
    FROM certs_users AS cu, users AS u, payment_status AS p,
      orders AS o
        LEFT JOIN partners AS a ON o.discount_id = a.partner_id
        LEFT JOIN payment_codes AS b ON o.discount_id = b.payment_code_id
        LEFT JOIN discount_types AS d ON o.discount_type_id = d.discount_type_id
    WHERE cu.user_id = u.user_id
    AND cu.user_id = o.customer_id
    AND cu.cert_user_id = o.product_id
    AND o.payment_status_id = p.payment_status_id
    AND o.product_type_id = 6
    AND o.customer_type_id = 1
    AND cu.cert_prog_id = {$form_fields['cert_prog_id']}
    ORDER BY cu.CREATED DESC
    LIMIT 10
END_QUERY;
  $result = mysql_query($student_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) $form_fields['students'][] = $record;
  /* get event details */
  $entity_id = $form_fields['cert_prog_id'];
  $event_type_array = array(
    CERT_PROGS_CREATE,
    CERT_PROGS_UPDATE,
    CERT_PROGS_ADD_COURSE,
    CERT_PROGS_REMOVE_COURSE,
    CERT_PROGS_ADD_USER
  );
  $event_history = vlc_get_event_history($event_type_array, $entity_id);
}
/* build array for "certificate level" select box */
$cert_level_options_array = array();
$cert_level_query = <<< END_QUERY
  SELECT cert_level_id, description
  FROM cert_levels
  ORDER BY display_order
END_QUERY;
$result = mysql_query($cert_level_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $cert_level_options_array[$record['cert_level_id']] = $record['description'];
/* get cert status options */
$cert_status_options_array = array();
$cert_status_query = <<< END_QUERY
  SELECT cert_status_id, description
  FROM cert_status
  ORDER BY cert_status_id
END_QUERY;
$result = mysql_query($cert_status_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $cert_status_options_array[$record['cert_status_id']] = $record['description'];
/* build array for "discount type" select box */
$discount_type_options_array = array();
$discount_type_options_array[2]['label'] = 'Partnering Organizations';
$discount_type_options_array[3]['label'] = 'Partnering Dioceses';
/* get dioceses and partners */
$partner_query = <<< END_QUERY
  SELECT p.partner_id, p.description, p.is_diocese, IFNULL(s.code, c.description) AS state_country
  FROM partners AS p LEFT JOIN states AS s USING (state_id), countries AS c
  WHERE p.is_partner = 1
  AND p.country_id = c.country_id
  ORDER BY p.description
END_QUERY;
$result = mysql_query($partner_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result))
{
  if ($record['is_diocese']) $discount_type_options_array[3]['options'][30000 + $record['partner_id']] = $record['description'].' ('.$record['state_country'].')';
  else $discount_type_options_array[2]['options'][20000 + $record['partner_id']] = $record['description'];
}
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = $value;
}
/* begin output variable */
$output = '';
/* return to previous page link */
if (isset($_SERVER['HTTP_REFERER']) and $return_url = strstr($_SERVER['HTTP_REFERER'], 'cms/')) $output .= '<p>'.vlc_internal_link('Return to Previous Page', $return_url).'</p>';
/* num courses array */
for ($i = 5; $i <= 50; $i += 5) $num_courses_array[$i] = $i;
/* display order array */
for ($i = 1; $i <= 20; $i++) $display_order_options_array[$i] = $i;
/* add cycle details to output */
$output .= '<form method="post" action="cert_prog_action.php">';
$output .= '<table border="1" cellpadding="5" cellspacing="0">';
if (isset($form_fields['cert_prog_id'])) $output .= '<tr><td><b>Certificate Program ID:</b></td><td>'.$form_fields['cert_prog_id'].'<input type="hidden" name="cert_prog_id" value="'.$form_fields['cert_prog_id'].'"></td></tr>';
$output .= '<tr><td><b>Description:</b></td><td><input type="text" size="50" name="description" value="'.$form_fields['description'].'"></td></tr>';
$output .= '<tr><td><b>Certificate Level:</b></td><td>'.vlc_select_box($cert_level_options_array, 'array', 'cert_level_id', $form_fields['cert_level_id'], true).'</td></tr>';
$output .= '<tr><td><b>Display Order:</b></td><td>'.vlc_select_box($display_order_options_array, 'array', 'display_order', $form_fields['display_order'], true).'</td></tr>';
$output .= '<tr><td colspan="2" align="center"><input type="submit" value="Submit"> <input type="reset" value="Reset"></td></tr>';
$output .= '</table>';
$output .= '</form>';
$css_output = $js_output = '';
if (isset($form_fields['cert_prog_id']))
{
  /* course subjects linked to this certificate program */
  $output .= '<a name="course-subjects"></a><h2>Course Subjects:</h2>';
  $output .= '<form method="get" action="cert_prog_details.php#course-subjects">';
  $output .= '<input type="hidden" name="cert_prog" value="'.$form_fields['cert_prog_id'].'">';
  $output .= '<p>The following course subjects are linked to this certificate program.</p>';
  $output .= '<ul>';
  $output .= '<li>To add courses to this certificate program, select the number of course subjects to add and click <b>&quot;Submit&quot;</b>: '.vlc_select_box($num_courses_array, 'array', 'num', -1, true).' <input type="submit" value="Submit"></li>';
  $output .= '</ul>';
  $output .= '</form>';
  $output .= '<form method="post" action="cert_prog_action.php">';
  $output .= '<input type="hidden" name="cert_prog_id" value="'.$form_fields['cert_prog_id'].'">';
  $output .= '<table border="1" cellpadding="5" cellspacing="0">';
  $output .= '<tr><th>&nbsp;</th><th>Course Subject</th><th>Category</th><th>Display Order<br>(Within Category)</th><th>Remove</th></tr>';
  $i = 1;
  if (isset($course_subject_details) and is_array($course_subject_details) and count($course_subject_details))
  {
    foreach ($course_subject_details as $course_subject)
    {
      $remove = '';
      if (isset($form_fields['course_subjects'][$course_subject['course_subject_id']]))
      {
        $course_subject = array_merge($course_subject, $form_fields['course_subjects'][$course_subject['course_subject_id']]);
        unset($form_fields['course_subjects'][$course_subject['course_subject_id']]);
        if (isset($course_subject['remove'])) $remove = ' checked';
      }
      $output .= '<tr>';
      $output .= '<td>'.$i++.'.</td>';
      $output .= '<td>'.$course_subject['description'].'<input type="hidden" name="course_subjects['.$course_subject['course_subject_id'].'][course_subject_id]" value="'.$course_subject['course_subject_id'].'"></td>';
      $output .= '<td>'.vlc_select_box($cert_cat_options_array, 'array', 'course_subjects['.$course_subject['course_subject_id'].'][cert_cat_id]', $course_subject['cert_cat_id'], false).'</td>';
      $output .= '<td>'.vlc_select_box($display_order_options_array, 'array', 'course_subjects['.$course_subject['course_subject_id'].'][display_order]', $course_subject['display_order'], true).'</td>';
      $output .= '<td align="center"><input type="checkbox" name="course_subjects['.$course_subject['course_subject_id'].'][remove]" value="1"'.$remove.'></td>';
      $output .= '</tr>';
    }
  }
  else $output .= '<tr><td colspan="5" align="center">No Course Subjects Found.</td></tr>';
  if (count($form_fields['course_subjects']) == 0)
  {
    $az_array = array();
    for ($j = 97; $j < 123; $j++) $az_array[] = chr($j);
    for ($j = 65; $j < 91; $j++) $az_array[] = chr($j);
    for ($k = 0; $k < $num; $k++) $form_fields['course_subjects'][$az_array[$k]] = array('course_subject_id' => -1, 'cert_cat_id' => -1, 'display_order' => -1);
  }
  foreach ($form_fields['course_subjects'] as $key => $course_subject)
  {
    $output .= '<tr>';
    $output .= '<td>'.$i++.'.</td>';
    $output .= '<td>'.vlc_select_box($course_subject_options_array, 'array', 'course_subjects['.$key.'][course_subject_id]', $course_subject['course_subject_id'], false).'</td>';
    $output .= '<td>'.vlc_select_box($cert_cat_options_array, 'array', 'course_subjects['.$key.'][cert_cat_id]', $course_subject['cert_cat_id'], false).'</td>';
    $output .= '<td>'.vlc_select_box($display_order_options_array, 'array', 'course_subjects['.$key.'][display_order]', $course_subject['display_order'], false).'</td>';
    $output .= '<td>&nbsp;</td>';
    $output .= '</tr>';
  }
  $output .= '<tr><td colspan="5" align="center"><input type="submit" value="Submit"></td></tr>';
  $output .= '</table>';
  $output .= '</form>';
  /* students linked to this certificate program */
  $output .= '<a name="students"></a><h3>Students:</h3>';
  $output .= '<p>The following records are the most recent student registrations for this certificate program.</p>';
  $output .= '<form method="get" action="cert_prog_details.php#students">';
  $output .= '<input type="hidden" name="cert_prog" value="'.$form_fields['cert_prog_id'].'">';
  $output .= '<ul>';
  $output .= '<li>To view additional order details, click the <b>&quot;Order ID&quot;</b> link.</li>';
  $output .= '<li>To view additional certificate registration details, click the <b>&quot;Certificate Registration ID&quot;</b> link.</li>';
  $output .= '<li>To view additional user details, click the <b>&quot;User ID&quot;</b> link.</li>';
  $output .= '<li>To add students to this certificate program, select the number of students to add and click <b>&quot;Submit&quot;</b>: '.vlc_select_box($num_courses_array, 'array', 'num', -1, true).' <input type="submit" value="Submit"></li>';
  $output .= '</ul>';
  $output .= '</form>';
  $output .= '<form method="post" action="cert_prog_action.php">';
  $output .= '<input type="hidden" name="cert_prog_id" value="'.$form_fields['cert_prog_id'].'">';
  $output .= '<table border="1" cellpadding="5" cellspacing="0">';
  $output .= '<tr><th><nobr>&nbsp;</nobr></th><th><nobr>Order ID</nobr></th><th>Certificate Registration ID</th><th><nobr>User ID</nobr></th><th><nobr>Name</nobr></th><th><nobr>Certificate Status</nobr></th><th><nobr>Partner Discount</nobr></th><th><nobr>Payment Status</nobr></th></tr>';
  $i = 1;
  if (count($form_fields['students']))
  {
    foreach ($form_fields['students'] as $student)
    {
      $output .= '<tr>';
      $output .= '<td>'.$i++.'.</td>';
      $output .= '<td align="center">'.vlc_internal_link($student['order_id'], 'cms/order_details.php?order='.$student['order_id']).'</td>';
      $output .= '<td align="center">'.vlc_internal_link($student['cert_user_id'], 'cms/cert_user_details.php?cert_user='.$student['cert_user_id']).'</td>';
      $output .= '<td align="center">'.vlc_internal_link($student['user_id'], 'cms/user_details.php?user='.$student['user_id']).'</td>';
      $output .= '<td><nobr>'.$student['last_name'].', '.$student['first_name'].'</nobr></td>';
      $output .= '<td align="center">'.vlc_select_box($cert_status_options_array, 'array', 'students['.$student['cert_user_id'].'][cert_status_id]', $student['cert_status_id'], true).'</td>';
      $output .= '<td>'.$student['discount'].'</td>';
      $output .= '<td align="center">'.$student['payment_status'].'</td>';
      $output .= '</tr>';
    }
  }
  else $output .= '<tr><td colspan="8" align="center">No Students Found.</td></tr>';
  $az_array = array();
  for ($j = 97; $j < 123; $j++) $az_array[] = chr($j);
  for ($j = 65; $j < 91; $j++) $az_array[] = chr($j);
  for ($j = 0; $j < $num; $j++)
  {
    $output .= '<tr>';
    $output .= '<td>'.$i++.'.</td>';
    $output .= '<td colspan="4"><div class="yui-skin-sam"><div id="ac_user_id_'.$az_array[$j].'" class="autocomplete"><input type="text" name="students['.$az_array[$j].'][user_id]" id="ac_user_id_'.$az_array[$j].'_field" value=""><div id="ac_user_id_'.$az_array[$j].'_container"></div></div></div></td>';
    $output .= '<td align="center">'.vlc_select_box($cert_status_options_array, 'array', 'students['.$az_array[$j].'][cert_status_id]', 1, true).'</td>';
    $output .= '<td>'.vlc_select_box($discount_type_options_array, 'array', 'students['.$az_array[$j].'][discount_type_id]', -1, false, '', '', 'discount_type_'.$az_array[$j]).'</td>';
    $output .= '<td>&nbsp;</td>';
    $output .= '</tr>';
    $css_output .= '#ac_user_id_'.$az_array[$j].' { z-index:'.(9000 - $j).'; }'."\n";
    $js_output .= 'this.ac_user_id_'.$az_array[$j].'_data_source = new YAHOO.widget.DS_XHR(\'xhr.php\', ["\n", "\t"]); this.ac_user_id_'.$az_array[$j].'_data_source.responseType = YAHOO.widget.DS_XHR.TYPE_FLAT; this.ac_user_id_'.$az_array[$j].'_data_source.scriptQueryAppend = \'field=user_id_with_diocese_id\'; this.ac_user_id_'.$az_array[$j].'_widget = new YAHOO.widget.AutoComplete(\'ac_user_id_'.$az_array[$j].'_field\', \'ac_user_id_'.$az_array[$j].'_container\', this.ac_user_id_'.$az_array[$j].'_data_source); this.ac_user_id_'.$az_array[$j].'_widget.related_dropdown = \'discount_type_'.$az_array[$j].'\'; this.ac_user_id_'.$az_array[$j].'_widget.itemSelectEvent.subscribe(select_handler); this.ac_user_id_'.$az_array[$j].'_widget.formatResult = function(result_array, query_string) { var result_id = result_array[0]; var result_string = result_array[1]; return format_result(result_id, result_string, query_string); };'."\n";
  }
  $output .= '<tr><td colspan="8" align="center"><input type="submit" value="Submit"></td></tr>';
  $output .= '</table>';
  $output .= '</form>';
  $output .= $event_history;
}
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<style type="text/css">
<?php print $css_output ?>
</style>
<script type="text/javascript">
YAHOO.example.ACFlatData = new function() {
    function format_result(result_id, result_string, query_string) {
      var result_id_offset = result_id.indexOf(query_string);
      if (result_id_offset != -1) {
        var result_id_begin = result_id.substr(0, result_id_offset);
        var result_id_match = result_id.substr(result_id_offset, query_string.length);
        var result_id_end = result_id.substr(result_id_offset + query_string.length);
        result_id = result_id_begin + '<span class="result-match">' + result_id_match + '</span>' + result_id_end;
      }
      var result_string_offset = result_string.toLowerCase().indexOf(query_string.toLowerCase());
      if (result_string_offset != -1) {
        var result_string_begin = result_string.substr(0, result_string_offset);
        var result_string_match = result_string.substr(result_string_offset, query_string.length);
        var result_string_end = result_string.substr(result_string_offset + query_string.length);
        result_string = result_string_begin + '<span class="result-match">' + result_string_match + '</span>' + result_string_end;
      }
      var html_string = '<div class="sample-result"><div class="result-id">' + result_id + '</div>' + result_string + '</div>';
      return html_string;
    };
    function select_handler(event_type, arg_array) {
      var data_array = arg_array[2];
      var discount_type_id = 30000 + parseInt(data_array[2]);
      var select_box = document.getElementById(arg_array[0].related_dropdown);
      select_box.options[0].selected = true;
      for (var i = 0; i < select_box.length; i++) {
        if (select_box.options[i].value == discount_type_id) {
          select_box.options[i].selected = true;
          return;
        }
      }
    };
<?php print $js_output ?>
};
</script>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
