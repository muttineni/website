<?php
$page_info['section'] = 'profile';
$page_info['page'] = 'index';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
$page_content = '';
if ($user_info['logged_in'] == true)
{
  $sample_course_list = '';
  $current_course_list = '';
  $future_course_list = '';
  $unpaid_order_list = '';
  /* get sample courses */
  $sample_course_query = <<< END_QUERY
    SELECT course_id, description
    FROM courses
    WHERE is_sample = 1
    ORDER BY description
END_QUERY;
  $result = mysql_query($sample_course_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result)) $sample_course_list .= '<li>'.vlc_internal_link($record['description'], 'classes/?course='.$record['course_id']).'</li>';
  /* get course list */
  $course_details_query = <<< END_QUERY
    SELECT uc.user_course_id, uc.course_status_id, uc.user_role_id,
      c.course_id, c.code, c.description, y.cycle_start,
      UNIX_TIMESTAMP() AS current_unix_timestamp,
      UNIX_TIMESTAMP(c.facilitator_start) AS facilitator_start,
      UNIX_TIMESTAMP(c.facilitator_end + INTERVAL 1 DAY) AS facilitator_end,
      UNIX_TIMESTAMP(c.student_start) AS student_start,
      UNIX_TIMESTAMP(c.student_end + INTERVAL 1 DAY) AS student_end
    FROM users_courses AS uc, courses AS c, cycles AS y
    WHERE uc.course_id = c.course_id
      AND c.cycle_id = y.cycle_id
      AND c.is_active = 1
      AND uc.user_id = {$user_info['user_id']}
    ORDER BY c.description
END_QUERY;
  $result = mysql_query($course_details_query, $site_info['db_conn']);
  $course_details_array = array();
  $product_id_array = array();
  while ($record = mysql_fetch_array($result))
  {
    $course_details_array[$record['course_id']] = $record;
    $product_id_array[] = $record['user_course_id'];
    $ispd_course_id = $record['course_id'];
    $ispd_course_status = $record['course_status_id'];
  }
  $course_id_array = array_keys($course_details_array);
  /* get unread vlc-mail messages */
  if (count($course_id_array))
  {
    $course_id_list = join(', ', $course_id_array);
    $course_mail_query = <<< END_QUERY
      SELECT m.mail_id, m.course_id, CONCAT(f.first_name, ' ', f.last_name) AS from_name, m.subject, DATE_FORMAT(m.CREATED, '%c-%e-%Y %l:%i %p') AS create_date
      FROM users AS f, mail AS m, mail_users AS mu
      WHERE f.user_id = m.from_user_id
        AND m.mail_id = mu.mail_id
        AND mu.to_user_id = {$user_info['user_id']}
        AND mu.mail_status_id = 1
        AND m.course_id IN ($course_id_list)
      ORDER BY m.course_id ASC, m.CREATED DESC
END_QUERY;
    $result = mysql_query($course_mail_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result)) $course_details_array[$record['course_id']]['messages'][] = $record;
  }
  /* get unpaid orders */
  $where_clause = 'product_type_id = 6';
  if (count($product_id_array)) $where_clause = '(product_id IN ('.join(', ', $product_id_array).') OR '.$where_clause.')';
  $cert_prog_orders = array();
  $unpaid_order_query = <<< END_QUERY
    SELECT order_id, product_type_id, product_id, is_active, payment_status_id, amount_paid, amount_due
    FROM orders
    WHERE customer_id = {$user_info['user_id']}
    AND $where_clause
END_QUERY;
  $result = mysql_query($unpaid_order_query, $site_info['db_conn']);
  while ($record = mysql_fetch_array($result))
  {
    if ($record['product_type_id'] == 6) $cert_prog_orders[$record['product_id']] = $record;
    else $unpaid_order_array[$record['product_id']] = $record;
  }
  /* compile list of current courses, future courses, and unpaid orders */
  foreach ($course_details_array as $course)
  {
    /* only list courses that are "waiting list", "next cycle", "in progress", "incomplete", or "complete" */
    if (in_array($course['course_status_id'], array(1, 2, 3, 6, 7)))
    {
      /* future courses */
      if (($course['user_role_id'] == 4 and $course['current_unix_timestamp'] < $course['facilitator_start']) or ($course['user_role_id'] == 5 and $course['current_unix_timestamp'] < $course['student_start']))
      {
        /* cancel button for students (not facilitators) who have not paid */
        if ($course['user_role_id'] == 5 and isset($unpaid_order_array[$course['user_course_id']]) and $unpaid_order_array[$course['user_course_id']]['amount_paid'] == 0)
        {
          $cancel_button = '<form method="post" action="cancel_course_action.php">';
          $cancel_button .= '<input type="hidden" name="user_course_id" value="'.$course['user_course_id'].'">';
          $cancel_button .= '<input type="submit" name="cancel" value="'.$lang['profile']['index']['misc']['cancel-label'].'" class="submit-button btn btn-danger">';
          $cancel_button .= '</form>';
        }
        else
        {
          $cancel_button = '<form method="post" action="cancel_course_action.php" onsubmit="return false;">';
          $cancel_button .= '<input type="submit" name="cancel" value="'.$lang['profile']['index']['misc']['cancel-label'].'" class="submit-button-disabled btn btn-default" disabled>';
          $cancel_button .= '</form>';
        }
        $future_course_list .= '<tr><td><b>'.$course['description'].'</b></td><td>'.$course['code'].'</td><td>'.$course['cycle_start'].'</td><td>'.$lang['database']['course-status'][$course['course_status_id']].'</td><td align="center">'.$cancel_button.'</td></tr>';
      }
      /* current courses */
      elseif (($course['user_role_id'] == 4 and $course['current_unix_timestamp'] < $course['facilitator_end']) or ($course['user_role_id'] == 5 and $course['current_unix_timestamp'] < $course['student_end']))
      {
        $current_course_list .= '<li class="list-group-item">'.vlc_internal_link($course['description'], 'classes/?course='.$course['course_id']).' ('.$course['code'].')';
        /* unread vlc-mail messages */
        if (isset($course_details_array[$course['course_id']]['messages']))
        {
          $message_array = $course_details_array[$course['course_id']]['messages'];
          $message_count = count($message_array);
          $current_course_list .= ' ('.sprintf($lang['profile']['index']['misc']['unread-messages'], $message_count).')';
          $current_course_list .= '<ol>';
          /* show only first three new messages */
          if ($message_count > 3) $message_count = 3;
          for ($i = 0; $i < $message_count; $i++)
          {
            $message = $message_array[$i];
            $current_course_list .= '<li>'.$message['from_name'].' - '.vlc_internal_link($message['subject'], 'classes/mail_details.php?course='.$course['course_id'].'&mail='.$message['mail_id']).' ('.$message['create_date'].')</li>';
          }
          $current_course_list .= '</ol>';
        }
        $current_course_list .= '</li>';
      }
    }
    /* unpaid and partially paid active orders */
    if (isset($unpaid_order_array[$course['user_course_id']]) and $unpaid_order_array[$course['user_course_id']]['is_active'] and $unpaid_order_array[$course['user_course_id']]['amount_due'] > 0)
    {
      $amount_due = '$'.number_format($unpaid_order_array[$course['user_course_id']]['amount_due'] / 100, 2);
      $unpaid_order_list .= '<tr><td><b>'.$course['description'].'</b> ('.$course['code'].')</td><td class="text-right">'.$amount_due.'</td><td class="text-center"><label class="custom-control custom-checkbox mb-0"><input type="checkbox" name="order_id_array[]" value="'.$unpaid_order_array[$course['user_course_id']]['order_id'].'" class="custom-control-input" /><span class="custom-control-indicator"></span></label></td></tr>';
    }
  }
  if (isset($cert_prog_orders) and count($cert_prog_orders))
  {
    $prod_id_array = array_keys($cert_prog_orders);
    $prod_id_list = join(', ', $prod_id_array);
    $cert_prog_query = 'SELECT c.description, cu.cert_user_id FROM cert_progs AS c, certs_users AS cu WHERE c.cert_prog_id = cu.cert_prog_id AND cu.cert_user_id IN ('.$prod_id_list.')';
    $result = mysql_query($cert_prog_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result)) $cert_prog_details[$record['cert_user_id']] = $record['description'];
    foreach ($cert_prog_orders as $order)
    {
      if ($order['is_active'] and $order['amount_due'] > 0)
      {
        $amount_due = '$'.number_format($order['amount_due'] / 100, 2);
        $unpaid_order_list .= '<tr><td><b>'.$cert_prog_details[$order['product_id']].'</b></td><td align="right">'.$amount_due.'</td><td class="text-center"><label class="custom-control custom-checkbox mb-0"><input type="checkbox" name="order_id_array[]" value="'.$order['order_id'].'" class="custom-control-input" /><span class="custom-control-indicator"></span></label></td></tr>';
      }
    }
  }
  /* build page content */
  $page_content = '<h1>'.$lang['profile']['index']['heading']['start-page'].'</h1>';
  // user image and check for ISPD partner - BOB
  $user_image_query = <<< END_QUERY
    SELECT image, partner_id
    FROM user_info
    WHERE user_id = {$user_info['user_id']}
END_QUERY;
  $result = mysql_query($user_image_query, $site_info['db_conn']);
  $record = mysql_fetch_array($result);
  if (isset($record['image']) and strlen(trim($record['image']))) $user_image = '<img class="float-none float-md-right non_responsive profile-img ml-5" src="'.$site_info['images_url'].'users/'.$record['image'].'" width="125" height="175" alt="'.sprintf($lang['profile']['index']['misc']['picture-label'], $user_info['name']).'" title="'.sprintf($lang['profile']['index']['misc']['picture-label'], $user_info['name']).'">';
  else $user_image = '<img class="float-none float-md-right non_responsive profile-img ml-5" src="'.$site_info['images_url'].$lang['common']['misc']['current-language-code'].'/no_pic.gif" width="125" height="175" alt="'.$lang['profile']['index']['misc']['picture-not-available'].'" title="'.$lang['profile']['index']['misc']['picture-not-available'].'">';
  /* user profile */
  $page_content .= '<h3>'.$lang['profile']['index']['heading']['my-profile'].' ['.vlc_internal_link($lang['profile']['index']['misc']['edit-profile-link'], 'profile/profile.php').']</h3>';
  $page_content .= '<div class="container">';
  $page_content .= $user_image;
  $page_content .= '<ul class="list-group">';
    if (($record['partner_id'] == 3044)) 
  {
     $page_content .= '<li class="list-group-item"><b>'.vlc_internal_link('Register for an ISPD Course', 'register/ispd_index.php').'</b></li>';
     $page_content .= '<li class="list-group-item"><strong>'.vlc_internal_link('Register for a VLCFF Course', 'register/').'</strong></li>';
  }else{
  $page_content .= '<li class="list-group-item"><strong>'.vlc_internal_link($lang['profile']['index']['misc']['register-link'], 'register/').'</strong></li>';
  }
  $page_content .= '<li class="list-group-item">'.vlc_internal_link($lang['profile']['index']['misc']['cert-prog-link'], 'certificates/').'</li>';
  if (in_array(1, $user_info['user_roles']) or in_array(9, $user_info['user_roles'])) $page_content .= '<li class="list-group-item">'.vlc_internal_link($lang['profile']['index']['misc']['cms-link'], 'cms/').'</li>';
  if (in_array(1, $user_info['user_roles'])) $page_content .= '<li class="list-group-item">'.vlc_external_link($lang['profile']['index']['misc']['db-link'], 'http://vlc.udayton.edu/db/').'</li>';
  if (in_array(2, $user_info['user_roles'])) $page_content .= '<li class="list-group-item">'.vlc_internal_link($lang['profile']['index']['misc']['partner-link'], 'profile/partner.php').'</li>';
  if (in_array(4, $user_info['user_roles'])) $page_content .= '<li class="list-group-item">'.vlc_external_link($lang['profile']['index']['misc']['vlcff-admin-link'], 'http://www.udayton.edu/~ipi/vlcff/').'</li>';
  $page_content .= '<li class="list-group-item"><a href="#current-courses">'.$lang['profile']['index']['misc']['current-courses-link'].'</a></li>';
  $page_content .= '<li class="list-group-item">'.vlc_internal_link($lang['profile']['index']['misc']['course-history-link'], 'profile/course_history.php');
  if (in_array(4, $user_info['user_roles'])) $page_content .= $lang['profile']['index']['misc']['course-roster-note'];
  $page_content .= '</li>';
  $page_content .= '<li class="list-group-item">'.vlc_internal_link($lang['profile']['index']['misc']['cert-prog-history-link'], 'profile/cert_prog_history.php').'</li>';
  $page_content .= '<li class="list-group-item">'.vlc_internal_link($lang['profile']['index']['misc']['change-password-link'], 'profile/change_password.php').'</li>';
  $page_content .= '<li class="list-group-item">'.vlc_internal_link($lang['profile']['index']['misc']['upload-image-link'], 'profile/upload_image.php').'</li>';
  if (strlen($sample_course_list)) $page_content .= '<li class="list-group-item">'.$lang['profile']['index']['misc']['sample-course-link'].'<ul>'.$sample_course_list.'</ul></li>';
  //$page_content .= '<li class="list-group-item">'.vlc_internal_link($lang['profile']['index']['misc']['video-test-link'], 'misc/video.php').'</li>';
  $page_content .= '</ul>';
  $page_content .= '<br style="clear: right;"><div></div>';
  $page_content .= '</div>';
  /* unpaid orders */
  if (strlen($unpaid_order_list))
  {
    $page_content .= '<h3>'.$lang['profile']['index']['heading']['order-notice'].'</h3>';
    $page_content .= '<div class="container">';
    $page_content .= '<div>'.$lang['profile']['index']['content']['order-notice'].'</div>';
    $page_content .= '<form method="post" action="'.$site_info['home_url'].'payment/payment_action.php" class="mx-auto w-75">';
    $page_content .= '<table class="table table-striped my-3">';
    $page_content .= '<thead><tr><th>'.$lang['profile']['index']['misc']['desc-label'].'</th><th class="text-right">'.$lang['profile']['index']['misc']['amount-due-label'].'</th><th class="text-center"><label class="custom-control custom-checkbox mb-0"><input type="checkbox" name="check_all_checkbox" onclick="check_all(this, \'order_id_array[]\'); check_all(this, \'check_all_checkbox\');" class="custom-control-input" /><span class="custom-control-indicator"></span></label></th></tr></thead>';
    $page_content .= $unpaid_order_list;
    $page_content .= '</table>';
    $page_content .= '<div class="text-right mb-3"><input type="submit" name="pay_now" value="'.$lang['profile']['index']['misc']['pay-now-label'].'" class="submit-button btn btn-vlc"></div>';
    $page_content .= '</form>';
    $page_content .= '</div>';
    $page_content .= '<div>'.$lang['profile']['index']['content']['paid-by-check'].'</div>';
  }
  /* current courses */
  $page_content .= '<a name="current-courses"></a><h3 class="mt-4">'.$lang['profile']['index']['heading']['my-current-courses'].'</h3>';
  $page_content .= '<div class="container">';
  if (strlen($current_course_list))
  {
    $page_content .= '<ul class="list-group my-3">';
    $page_content .= $current_course_list;
    $page_content .= '</ul>';
    $page_content .= '</div><div>'.$lang['profile']['index']['content']['drop-note'].'</div>';
  }
  else $page_content .= '</div><div>'.$lang['profile']['index']['misc']['no-current-courses'].'</div>';

  /* future courses */
  $page_content .= '<h3 class="mt-4">'.$lang['profile']['index']['heading']['my-future-courses'].'</h3>';
  $page_content .= '<div class="container">';
  if (strlen($future_course_list))
  {
    $page_content .= '<h6>'.$lang['profile']['index']['content']['future-courses'].'</h6s>';
    $page_content .= '<table class="table table-striped">';
    $page_content .= '<thead><tr><th>'.$lang['profile']['index']['misc']['course-desc-label'].'</th><th>'.$lang['profile']['index']['misc']['course-code-label'].'</th><th>'.$lang['profile']['index']['misc']['course-date-label'].'</th><th>'.$lang['profile']['index']['misc']['course-status-label'].'</th><th>'.$lang['profile']['index']['misc']['cancel-label'].'</th></tr></thead>';
    $page_content .= $future_course_list;
    $page_content .= '</table>';
    $page_content .= '</div><div>'.$lang['profile']['index']['content']['cancel-note'].'</div>';
  }
  else $page_content .= '</div><div>'.$lang['profile']['index']['misc']['no-future-courses'].'</div>';
}
else
{
  $forgot_password = vlc_internal_link($lang['common']['sidebar']['misc']['forgot-password-link'], 'profile/forgot_password.php');
  $page_content = <<< LOGIN
    <h1>{$lang['profile']['index']['heading']['log-in']}</h1>
    <div class="card">
      <form action="login_action.php" method="post" onsubmit="return validate_form(this)">
        <div class="card-body">
          <div class="form-group>
            <label for="username">{$lang['profile']['index']['form-fields']['username']}</label>
            <input type="text" size="20" maxlength="6" class="form-field form-control" name="username" id="username" value="{$user_info['username']}" required="true" message="{$lang['profile']['index']['status']['username-required']}" />
          </div>
          <div class="form-group">
            <label for="password">{$lang['profile']['index']['form-fields']['password']}</label>
            <input type="password" size="20" maxlength="6" class="form-field form-control" name="password" id="password" required="true" message="{$lang['profile']['index']['status']['password-required']}" aria-describedby="forgot-password" />
            <small id="forgot-password" class="form-text">$forgot_password</small>
          </div>
          <input type="submit" class="submit-button btn btn-vlc" name="submit" value="{$lang['profile']['index']['form-fields']['log-in-button']}" />
        </div>
      </form>
    </div>
LOGIN;
}
print $header;
?>
<!-- begin page content -->
<div class="container mb-5">
  <?php print $page_content ?>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
