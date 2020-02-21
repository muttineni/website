<?php
$page_info['section'] = 'classes';
$page_info['login_required'] = 1;
$page_info['course_id'] = vlc_get_url_variable($site_info, 'course', true);
$page_info['session_id'] = vlc_get_url_variable($site_info, 'session', false, $page_info['course_id']);
$page_info['resource_id'] = vlc_get_url_variable($site_info, 'resource', true, $page_info['course_id']);
$lang = vlc_get_language();
$user_info = vlc_get_user_info($page_info['login_required'], 1);
$course_info = vlc_get_course_info($site_info, $page_info['course_id'], 1);
/* get resource info */
$resource_query = <<< END_QUERY
  SELECT r.resource_type_id, r.title, IFNULL(s.display_order, 0) AS session_num
  FROM resources AS r LEFT JOIN sessions AS s USING (session_id)
  WHERE r.resource_id = {$page_info['resource_id']}
END_QUERY;
$result = mysql_query($resource_query, $site_info['db_conn']);
if (mysql_num_rows($result) == 1) $resource_info = mysql_fetch_array($result);
else trigger_error('INVALID RESOURCE ID: '.$page_info['resource_id']);
/* get all users in the course */
$users_query = <<< END_QUERY
  SELECT uc.user_id, uc.user_role_id, ui.send_email_notification
  FROM users_courses AS uc, user_info AS ui
  WHERE uc.user_id = ui.user_id
  AND uc.course_id = {$page_info['course_id']}
  AND uc.course_status_id IN (2, 3, 6, 7)
END_QUERY;
$result = mysql_query($users_query, $site_info['db_conn']);
$all_course_users = array();
while ($record = mysql_fetch_array($result)) $all_course_users[] = $record;
/* set default values for mail variables and return url */
$mail_to_array = array();
$mail_from = $user_info['user_id'];
$return_url = 'classes/resource.php?course='.$page_info['course_id'].'&session='.$page_info['session_id'].'&resource='.$page_info['resource_id'];
/*** SWITCH-CASE OPTIONS ***/
/* 4 => discussion board */
/* 46 => meeting place */
/* 26 => test, quiz, exercise, etc. */
/* 44 => course evaluation */
switch ($resource_info['resource_type_id'])
{
  /* 4 => discussion board */
  case 4:
  /* 46 => meeting place */
  case 46:
    if ($resource_info['resource_type_id'] == 4)
    {
      $mail_subject = $resource_info['title'];
      $discussion_url = 'session:'.$page_info['session_id'].',resource:'.$page_info['resource_id'];
    }
    else
    {
      $mail_subject = $lang['classes']['meeting-place']['mail-subject'];
      $discussion_url = 'meet';
      $return_url = 'classes/meet.php?course='.$page_info['course_id'];
    }
    $form_fields = $_POST;
    /* get message */
    if (isset($form_fields['message_rte']) and strlen(trim($form_fields['message_rte']))) $new_message = vlc_convert_html($form_fields['message_rte']);
    elseif (isset($form_fields['message']) and strlen(trim($form_fields['message']))) $new_message = str_replace("\n", "[br]\n", stripslashes(trim($form_fields['message'])));
    else vlc_exit_page('<li>'.$lang['classes']['discussion']['status']['message-required'].'</li>', 'error', $return_url);
    foreach ($all_course_users as $course_user)
    {
      if ($course_user['send_email_notification'] == true) $mail_to_array[] = "(NULL, {LAST_INSERT_ID}, {$course_user['user_id']}, 1)";
    }
    /* prepare message for database query */
    $message_slashed = addslashes($new_message);
    $insert_message_query = <<< END_QUERY
      INSERT INTO messages
      SET CREATED = NULL, discussion_board_id = {$page_info['resource_id']}, user_id = {$user_info['user_id']}, course_id = {$page_info['course_id']}, message = '$message_slashed'
END_QUERY;
    $result = mysql_query($insert_message_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "messages"');
    $mail_message = sprintf($lang['classes']['discussion']['email']['message-posted'], $discussion_url);
    $mail_message .= $new_message;
    $success_message = $lang['classes']['discussion']['status']['message-posted'];
    break;
  /* 26 => test, quiz, exercise, etc. */
  case 26:
  /* 44 => course evaluation */
  case 44:
    if ($resource_info['resource_type_id'] == 44) $mail_subject = $lang['classes']['evaluation']['mail-subject'];
    else $mail_subject = $resource_info['title'];
    /* get questions */
    $questions_query = <<< END_QUERY
      SELECT q.question_id, q.question_type_id, q.question
      FROM questions AS q
      WHERE q.test_id = {$page_info['resource_id']}
      ORDER BY q.display_order
END_QUERY;
    $result = mysql_query($questions_query, $site_info['db_conn']);
    $i = 0;
    while ($record = mysql_fetch_array($result))
    {
      $test_info['questions'][$record['question_id']] = $record;
      if ($resource_info['resource_type_id'] == 44) $test_info['questions'][$record['question_id']]['question'] = $lang['classes']['evaluation']['questions'][$i];
      $i++;
    }
    /* get answers */
    $answers_query = <<< END_QUERY
      SELECT a.answer_id, a.answer, a.is_correct
      FROM answers AS a, questions AS q
      WHERE a.question_id = q.question_id
      AND q.test_id = {$page_info['resource_id']}
END_QUERY;
    $result = mysql_query($answers_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result)) $test_info['answers'][$record['answer_id']] = $record;
    $form_fields = $_POST;
    $error_fields = array();
    $insert_answers_query = 'INSERT INTO responses (CREATED, course_id, user_id, question_id, answer_id, answer) VALUES ';
    foreach ($test_info['questions'] as $question)
    {
      $question_id_array[] = $question['question_id'];
      /*** SWITCH-CASE OPTIONS ***/
      /* 1 => essay (textarea) */
      /* 2 => short answer (input type=text) */
      /* 3 => true/false (radio button) */
      /* 4 => multiple choice, single selection (radio button) */
      /* 5 => multiple choice, multiple selection (checkbox) */
      switch ($question['question_type_id'])
      {
        /* 1 => essay (textarea) */
        case 1:
          if (isset($form_fields[$question['question_id']]) and strlen(trim($form_fields[$question['question_id']])) > 0)
          {
            $insert_values_array[] = '(NULL, '.$page_info['course_id'].', '.$user_info['user_id'].', '.$question['question_id'].', NULL, \''.addslashes($form_fields[$question['question_id']]).'\')';
            $question_answer_array[] = '[b]'.$lang['classes']['exercise']['misc']['question'].':[/b] '.$question['question'];
            $question_answer_array[] = '[b]'.$lang['classes']['exercise']['misc']['answer'].':[/b] '.$form_fields[$question['question_id']];
          }
          else $error_fields[] = $question['question_id'];
          break;
        /* 2 => short answer (input type=text) */
        case 2:
          if (isset($form_fields[$question['question_id']]) and strlen(trim($form_fields[$question['question_id']])) > 0)
          {
            $insert_values_array[] = '(NULL, '.$page_info['course_id'].', '.$user_info['user_id'].', '.$question['question_id'].', NULL, \''.addslashes($form_fields[$question['question_id']]).'\')';
            $question_answer_array[] = '[b]'.$lang['classes']['exercise']['misc']['question'].':[/b] '.$question['question'];
            $question_answer_array[] = '[b]'.$lang['classes']['exercise']['misc']['answer'].':[/b] '.$form_fields[$question['question_id']];
          }
          else $error_fields[] = $question['question_id'];
          break;
        /* 3 => true/false (radio button) */
        case 3:
        /* 4 => multiple choice, single selection (radio button) */
        case 4:
          if (isset($form_fields[$question['question_id']]) and is_numeric($form_fields[$question['question_id']]))
          {
            $insert_values_array[] = '(NULL, '.$page_info['course_id'].', '.$user_info['user_id'].', '.$question['question_id'].', '.$form_fields[$question['question_id']].', NULL)';
            $correct = $lang['classes']['exercise']['misc']['correct'][$test_info['answers'][$form_fields[$question['question_id']]]['is_correct']];
            $question_answer_array[] = '[b]'.$lang['classes']['exercise']['misc']['question'].':[/b] '.$question['question'];
            $question_answer_array[] = '[b]'.$lang['classes']['exercise']['misc']['answer'].': ['.$correct.'][/b] '.$test_info['answers'][$form_fields[$question['question_id']]]['answer'];
          }
          else $error_fields[] = $question['question_id'];
          break;
        /* 5 => multiple choice, multiple selection (checkbox) */
        case 5:
          if (isset($form_fields[$question['question_id']]) and is_array($form_fields[$question['question_id']]) and count($form_fields[$question['question_id']]) > 0)
          {
            $question_answer_array[] = '[b]'.$lang['classes']['exercise']['misc']['question'].':[/b] '.$question['question'];
            foreach ($form_fields[$question['question_id']] as $ans_id)
            {
              $insert_values_array[] = '(NULL, '.$page_info['course_id'].', '.$user_info['user_id'].', '.$question['question_id'].', '.$ans_id.', NULL)';
              $correct = $lang['classes']['exercise']['misc']['correct'][$test_info['answers'][$ans_id]['is_correct']];
              $question_answer_array[] = '[b]'.$lang['classes']['exercise']['misc']['answer'].': ['.$correct.'][/b] '.$test_info['answers'][$ans_id]['answer'];
            }
          }
          else $error_fields[] = $question['question_id'];
          break;
      }
    }
    if (count($error_fields) == 0)
    {
      $question_id_list = implode(', ', $question_id_array);
      $check_answers_query = <<< END_QUERY
        SELECT r.response_id
        FROM responses AS r
        WHERE r.course_id = {$page_info['course_id']}
        AND r.user_id = {$user_info['user_id']}
        AND r.question_id IN ($question_id_list)
END_QUERY;
      $result = mysql_query($check_answers_query, $site_info['db_conn']);
      if (mysql_num_rows($result) > 0) vlc_exit_page($lang['classes']['exercise']['status']['cannot-resubmit'], 'error', $return_url);
      /* facilitators (4) and secondary facilitators (8) should receive test results */
      $user_role_array = array(4, 8);
      foreach ($all_course_users as $course_user)
      {
        if (in_array($course_user['user_role_id'], $user_role_array)) $mail_to_array[] = "(NULL, {LAST_INSERT_ID}, {$course_user['user_id']}, 1)";
        /* course evaluations appear to come from the facilitator to protect anonymity */
        if ($course_user['user_role_id'] == 4 and $resource_info['resource_type_id'] == 44) $mail_from = $course_user['user_id'];
      }
      $mail_message = $lang['classes']['exercise']['email']['responses'];
      $mail_message .= implode("\n\n", $question_answer_array);
      $insert_answers_query .= implode(', ', $insert_values_array);
      $result = mysql_query($insert_answers_query, $site_info['db_conn']);
      if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "responses"');
      else $success_message = $lang['classes']['exercise']['status']['responses-submitted'];
    }
    else
    {
      $_SESSION['form_fields'] = $form_fields;
      vlc_exit_page('<li>'.$lang['classes']['exercise']['status']['answers-incomplete'].'</li>', 'error', $return_url);
    }
    break;
  /* none of the above */
  default:
    trigger_error('INVALID RESOURCE TYPE ID: '.$resource_info['resource_type_id']);
}
/* if mail recipients have been defined, send mail message via vlc-mail */
if (count($mail_to_array) > 0)
{
  if ($resource_info['session_num'] != 0) $mail_subject = sprintf($lang['classes']['common']['misc']['session-mail-subject'], $resource_info['session_num']).': '.$mail_subject;
  /* replace mail subject */
  $mail_message = str_replace('{MAIL_SUBJECT}', $mail_subject, $mail_message);
  /* for course evaluations, send copy of responses to webmaster */
  if ($resource_info['resource_type_id'] == 44)
  {
    if ($lang['common']['misc']['current-language-id'] === 2){
        $eval_to = $site_info['webmaster_email'] . ', ' . $site_info['spanish_curriculum_email'];
    }else{
        $eval_to = $site_info['webmaster_email'] . ', ' . $site_info['curriculum_email'];
    }
    $eval_from = 'From: "'.$user_info['full_name'].'" <'.$user_info['email'].'>';
    $eval_subject = 'VLCFF @ UD: Course Evaluation';
    $eval_message = $course_info['title']."\n\n".preg_replace("/\[\/?b\]/", "", $mail_message);
    mail($eval_to, $eval_subject, $eval_message, $eval_from);
  }
  /* prepare mail subject and message to be inserted into the database */
  $mail_subject = addslashes($mail_subject);
  $mail_message = addslashes($mail_message);
  $mail_message = preg_replace("/\n/", "[br]\n", $mail_message);
  /* insert mail message into database */
  $insert_mail_query = <<< END_QUERY
    INSERT INTO mail (CREATED, course_id, from_user_id, subject, message)
    VALUES (NULL, {$page_info['course_id']}, $mail_from, '$mail_subject', '$mail_message')
END_QUERY;
  $result = mysql_query($insert_mail_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "mail"');
  /* get last insert id */
  $last_insert_id = mysql_insert_id();
  /* build mail recipients query and insert into database */
  $insert_mail_users_query = 'INSERT INTO mail_users (CREATED, mail_id, to_user_id, mail_status_id) VALUES ';
  $insert_mail_users_query .= implode(', ', $mail_to_array);
  $insert_mail_users_query = str_replace('{LAST_INSERT_ID}', $last_insert_id, $insert_mail_users_query);
  $result = mysql_query($insert_mail_users_query, $site_info['db_conn']);
  if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "mail_users"');
}
/* go back to resource page with success message */
vlc_exit_page($success_message, 'success', $return_url);
?>

