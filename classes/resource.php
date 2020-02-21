<?php
$page_info['section'] = 'classes';
$page_info['sub_section'] = 'resource';
$page_info['login_required'] = 1;
$page_info['course_id'] = vlc_get_url_variable($site_info, 'course', true);
$page_info['session_id'] = vlc_get_url_variable($site_info, 'session', false, $page_info['course_id']);
$page_info['resource_id'] = vlc_get_url_variable($site_info, 'resource', true, $page_info['course_id']);
list($user_info, $course_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get resource info */
$resource_query = <<< END_QUERY
  SELECT r.resource_type_id, IFNULL(r.title, '') AS title, IFNULL(r.content, '') AS content, IFNULL(r.url, '') AS url
  FROM resources AS r, courses AS c
  WHERE r.course_subject_id = c.course_subject_id
  AND c.course_id = {$page_info['course_id']}
  AND r.resource_id = {$page_info['resource_id']}
  LIMIT 1
END_QUERY;
$result = mysql_query($resource_query, $site_info['db_conn']);
if (mysql_num_rows($result) == 1) $resource_info = mysql_fetch_array($result);
else trigger_error('INVALID RESOURCE ID: '.$page_info['resource_id']);
$resource_info['resource_type'] = $lang['database']['resource-types'][$resource_info['resource_type_id']];
if (in_array($resource_info['resource_type_id'], array(22, 23, 24, 25, 35, 36)) == true)
{
  $reading_details_query = <<< END_QUERY
    SELECT IFNULL(r.author, '') AS author, IFNULL(r.source, '') AS source, IFNULL(r.notes, '') AS notes
    FROM resource_details AS r
    WHERE r.resource_id = {$page_info['resource_id']}
END_QUERY;
  $result = mysql_query($reading_details_query, $site_info['db_conn']);
  $resource_info = array_merge($resource_info, mysql_fetch_array($result));
}
/*** SWITCH-CASE OPTIONS ***/
/* 4 => discussion board */
/* 6 => glossary */
/* 17 => session structure */
/* 15 => course requirements */
/* 16 => course methodology */
/* 18 => theological reflection */
/* 37 => course related websites */
/* 41 => clues for success */
/* 23 => required offline reading (hardcopy) */
/* 25 => recommended offline reading (hardcopy) */
/* 35 => required online reading (external) */
/* 36 => recommended online reading (external) */
/* 22 => required online reading (internal) */
/* 24 => recommended online reading (internal) */
/* 26 => test, quiz, exercise, etc. */
/* 44 => course evaluation */
/* 38 => course readings overview */
/* 39 => course outline */
/* 40 => weekly study chart */
/* 58 => video */
switch ($resource_info['resource_type_id'])
{
  /* 4 => discussion board */
  case 4:
    $resource_output = '<table><tr><td align="right" valign="top" width="25%"><b>'.$lang['classes']['discussion']['misc']['topic'].':</b></td><td width="75%">'.vlc_convert_code($resource_info['content'], $page_info['course_id'], 'table-background').'</td></tr><tr><th>'.$lang['classes']['discussion']['misc']['author'].'</th><th>'.$lang['classes']['discussion']['misc']['message'].'</th></tr>';
    $messages_query = <<< END_QUERY
      SELECT m.message, DATE_FORMAT(m.CREATED, '%c-%e-%Y %l:%i %p') AS create_date, u.user_id, CONCAT(IF(LENGTH(TRIM(IFNULL(u.nickname, ''))) > 0, u.nickname, u.first_name), ' ', u.last_name) AS name
      FROM messages AS m LEFT JOIN users_courses AS uc ON m.course_id = uc.course_id AND m.user_id = uc.user_id, users AS u, users_roles AS ur
      WHERE m.user_id = u.user_id
        AND u.user_id = ur.user_id
        AND (uc.course_status_id IN (2, 3, 6, 7) OR ur.user_role_id = 1)
        AND m.discussion_board_id = {$page_info['resource_id']}
        AND m.course_id = {$page_info['course_id']}
      GROUP BY m.message_id
      ORDER BY m.CREATED
END_QUERY;
    $result = mysql_query($messages_query, $site_info['db_conn']);
    $discussion_output = '';
    $i = 0;
    while ($record = mysql_fetch_array($result))
    {
      if ($i % 2 == 0) $css_class = 'table-background';
      else $css_class = 'alternate-row';
      $record['message'] = vlc_convert_code($record['message'], $page_info['course_id'], $css_class);
      $mail_link = vlc_internal_link($record['name'], 'classes/mail_form.php?course='.$page_info['course_id'].'&recipient='.$record['user_id'].'&action=2', $css_class);
      $discussion_output .= '<tr><td valign="top" class="'.$css_class.'">'.$mail_link.'<br><br>['.$record['create_date'].']</td>';
      $discussion_output .= '<td valign="top" class="'.$css_class.'">'.$record['message'].'</td></tr>';
      $i++;
    }
    if (strlen($discussion_output) > 0) $resource_output .= $discussion_output;
    else $resource_output .= '<tr><td>&nbsp;</td><td>'.$lang['classes']['discussion']['misc']['no-messages'].'</td></tr>';
    $form_action = 'resource_action.php?course='.$page_info['course_id'].'&session='.$page_info['session_id'].'&resource='.$page_info['resource_id'];
    $message_field = vlc_rte_field('message', '', $page_info['course_id']);
    $resource_output .= <<< END_TEXT
<tr>
  <td align="right" valign="top"><b>{$lang['classes']['discussion']['misc']['directions-label']}:</b></td>
  <td>{$lang['classes']['discussion']['misc']['directions']}</td>
</tr>
<tr>
  <td align="right" valign="top"><b>{$lang['classes']['discussion']['misc']['add-message']}:</b></td>
  <td>
<form method="post" action="$form_action" onsubmit="updateRTEs(); this.submitbtn.disabled = true;">
$message_field
<br>{$lang['classes']['discussion']['misc']['review-message']}<br><br>
<input type="submit" name="submitbtn" value="{$lang['classes']['discussion']['form-fields']['add-message-button']}" class="submit-button">
</form>
  </td>
</tr>
</table>
END_TEXT;
    break;

  /* 6 => glossary */
  case 6:
  /* 17 => session structure */
  case 17:
    $resource_output = vlc_convert_code($resource_info['content'], $page_info['course_id']);
    break;

  /* 37 => course related websites */
  case 37:
    $resource_output = $lang['classes']['links']['content'];
    break;

  /* 47 => key 1 */
  case 47:
  /* 48 => key 2 */
  case 48:
  /* 49 => key 3 */
  case 49:
  /* 50 => key 4 */
  case 50:
  /* 51 => key 5 */
  case 51:
  /* 52 => key 6 */
  case 52:
    $resource_output = $lang['classes']['keys'][$resource_info['resource_type_id']];
    break;

  /* 15 => course requirements */
  case 15:
  /* 16 => course methodology */
  case 16:
  /* 18 => theological reflection */
  case 18:
  /* 41 => clues for success */
  case 41:
    /* create an array that matches resource type id to "template" resource type id */
    $resource_template_array = array(15 => 32, 16 => 28, 18 => 29, 37 => 43, 41 => 42);
    $resource_template_id = $resource_template_array[$resource_info['resource_type_id']];
    /* get "template" content from database */
    $resource_content_query = <<< END_QUERY
      SELECT r.content
      FROM resources AS r
      WHERE r.resource_type_id = $resource_template_id
      AND r.language_id = {$lang['common']['misc']['current-language-id']}
      AND r.course_subject_id IS NULL
      AND r.session_id IS NULL
      LIMIT 1
END_QUERY;
    $result = mysql_query($resource_content_query, $site_info['db_conn']);
    $resource_content = mysql_result($result, 0);
    $resource_output = vlc_convert_code($resource_content, $page_info['course_id']);
    break;

  /* 23 => required offline reading (hardcopy) */
  case 23:
  /* 25 => recommended offline reading (hardcopy) */
  case 25:
    $author_source_notes = '';
    if (strlen($resource_info['author']) > 0) $author_source_notes .= '<li><b>'.$lang['classes']['reading']['misc']['author'].':</b> '.$resource_info['author'].'</li>';
    if (strlen($resource_info['source']) > 0) $author_source_notes .= '<li><b>'.$lang['classes']['reading']['misc']['source'].':</b> '.$resource_info['source'].'</li>';
    if (strlen($resource_info['notes']) > 0) $author_source_notes .= '<li><b>'.$lang['classes']['reading']['misc']['notes'].':</b> '.$resource_info['notes'].'</li>';
    if (strlen($author_source_notes) > 0) $author_source_notes = '<ul>'.$author_source_notes.'</ul>';
    $resource_output = '<p>'.$resource_info['resource_type'].': <b>'.$resource_info['title'].'</b></p>'.$author_source_notes;
    if (strlen($resource_info['content']) > 0) $resource_output .= vlc_convert_code($resource_info['content'], $page_info['course_id']);
    break;

  /* 35 => required online reading (external) */
  case 35:
  /* 36 => recommended online reading (external) */
  case 36:
    $author_source_notes = '';
    if (strlen($resource_info['author']) > 0) $author_source_notes .= '<li><b>'.$lang['classes']['reading']['misc']['author'].':</b> '.$resource_info['author'].'</li>';
    if (strlen($resource_info['source']) > 0) $author_source_notes .= '<li><b>'.$lang['classes']['reading']['misc']['source'].':</b> '.$resource_info['source'].'</li>';
    if (strlen($resource_info['notes']) > 0) $author_source_notes .= '<li><b>'.$lang['classes']['reading']['misc']['notes'].':</b> '.$resource_info['notes'].'</li>';
    $resource_output = '<p>'.$resource_info['resource_type'].': <b>'.$resource_info['title'].'</b></p><ul><li><b>'.$lang['classes']['reading']['misc']['url'].':</b> '.vlc_external_link($resource_info['url'], $resource_info['url']).'</li>'.$author_source_notes.'</ul>';
    break;

  /* 22 => required online reading (internal) */
  case 22:
  /* 24 => recommended online reading (internal) */
  case 24:
    $author_source_notes = '';
    if (strlen($resource_info['author']) > 0) $author_source_notes .= '<p><b>'.$lang['classes']['reading']['misc']['author'].':</b> '.$resource_info['author'].'</p>';
    if (strlen($resource_info['source']) > 0) $author_source_notes .= '<p><b>'.$lang['classes']['reading']['misc']['source'].':</b> '.$resource_info['source'].'</p>';
    if (strlen($resource_info['notes']) > 0) $author_source_notes .= '<p><b>'.$lang['classes']['reading']['misc']['notes'].':</b> '.$resource_info['notes'].'</p>';
    $content_array = explode('|', $resource_info['content']);
    $num_pages = count($content_array);
    if (isset($_GET['print']) and is_numeric($_GET['print'])) $printer_friendly = $_GET['print'];
    else $printer_friendly = 0;
    if ($num_pages > 1 and $printer_friendly == false)
    {
      if (isset($_GET['page']) and is_numeric($_GET['page'])) $page_num = $_GET['page'];
      else $page_num = 0;
      $content = vlc_convert_code($content_array[$page_num], $page_info['course_id']);
      $content .= '<div class="next-prev">&nbsp;&laquo;&nbsp;';
      $prev_page = $page_num - 1;
      if (isset($content_array[$prev_page])) $content .= vlc_internal_link($lang['classes']['reading']['misc']['previous-page'], 'classes/resource.php?course='.$page_info['course_id'].'&session='.$page_info['session_id'].'&resource='.$page_info['resource_id'].'&page='.$prev_page, 'next-prev').'&nbsp;';
      for ($i = 0; $i < $num_pages; $i++)
      {
        $this_page = $i + 1;
        if ($i == $page_num) $content .= $this_page.'&nbsp;';
        else $content .= vlc_internal_link($this_page, 'classes/resource.php?course='.$page_info['course_id'].'&session='.$page_info['session_id'].'&resource='.$page_info['resource_id'].'&page='.$i, 'next-prev').'&nbsp;';
      }
      $next_page = $page_num + 1;
      if (isset($content_array[$next_page])) $content .= vlc_internal_link($lang['classes']['reading']['misc']['next-page'], 'classes/resource.php?course='.$page_info['course_id'].'&session='.$page_info['session_id'].'&resource='.$page_info['resource_id'].'&page='.$next_page, 'next-prev').'&nbsp;';
      $content .= '&nbsp;&raquo;&nbsp;</div>';
    }
    else $content = vlc_convert_code(str_replace('|', '', $resource_info['content']), $page_info['course_id']);
    $resource_output = $author_source_notes.$content;
    break;

  /* 26 => test, quiz, exercise, etc. */
  case 26:
  /* 44 => course evaluation */
  case 44:
    $resource_output = '';
    if ($resource_info['resource_type_id'] == 44) $resource_info['content'] = $lang['classes']['evaluation']['intro'];
    if (strlen($resource_info['content']) > 0) $resource_output .= vlc_convert_code($resource_info['content'], $page_info['course_id']);
    /* get list of questions */
    $questions_query = <<< END_QUERY
      SELECT q.question_id, q.question_type_id, q.question
      FROM questions AS q
      WHERE q.test_id = {$page_info['resource_id']}
      ORDER BY q.display_order
END_QUERY;
    $result = mysql_query($questions_query, $site_info['db_conn']);
    if (mysql_num_rows($result) > 0)
    {
      $num_essay_questions = 0;
      $i = 0;
      while ($record = mysql_fetch_array($result))
      {
        $test_info['questions'][$record['question_id']] = $record;
        $question_id_array[] = $record['question_id'];
        if ($record['question_type_id'] == 1) $num_essay_questions++;
        if ($resource_info['resource_type_id'] == 44) $test_info['questions'][$record['question_id']]['question'] = $lang['classes']['evaluation']['questions'][$i];
        $i++;
      }
      if ($resource_info['resource_type_id'] == 26 and $num_essay_questions > 0 and $page_info['resource_id'] != 16205) $resource_output .= '<p><b>'.$lang['classes']['exercise']['misc']['essay-requirements'].'</b></p>';
      /* get answer choices for multiple choice questions */
      $answers_query = <<< END_QUERY
        SELECT q.question_id, a.answer_id, a.answer, a.is_correct
        FROM answers AS a, questions AS q
        WHERE a.question_id = q.question_id
        AND q.test_id = {$page_info['resource_id']}
        ORDER BY q.question_id, a.display_order
END_QUERY;
      $result = mysql_query($answers_query, $site_info['db_conn']);
      while ($record = mysql_fetch_array($result)) $test_info['questions'][$record['question_id']]['answers'][$record['answer_id']] = $record;
      /* get user's previously submitted answers */
      $question_id_list = implode(', ', $question_id_array);
      $user_answers_query = <<< END_QUERY
        SELECT q.question_id, q.question_type_id, r.answer_id, r.answer
        FROM responses AS r, questions AS q
        WHERE r.question_id = q.question_id
        AND r.course_id = {$page_info['course_id']}
        AND r.user_id = {$user_info['user_id']}
        AND q.question_id IN ($question_id_list)
END_QUERY;
      $result = mysql_query($user_answers_query, $site_info['db_conn']);
      $num_rows = mysql_num_rows($result);
      /* if the user has already submitted answers for this test, they cannot edit or modify their responses */
      if ($num_rows > 0)
      {
        /* disable all form fields */
        $disabled = ' disabled';
        while ($record = mysql_fetch_array($result))
        {
          if ($record['question_type_id'] == 5) $user_answers[$record['question_id']][] = $record['answer_id'];
          else $user_answers[$record['question_id']] = $record;
        }
        $resource_output .= '<p><b>'.$lang['classes']['exercise']['misc']['correct-in-bold'].'</b></p>';
      }
      else $disabled = '';
      /* get form fields from session variable if form has been submitted and errors occurred on action page */
      if (strlen($status_message) > 0 and isset($_SESSION['form_fields']))
      {
        $form_fields = $_SESSION['form_fields'];
        $_SESSION['form_fields'] = null;
      }
      /* dynamically build test */
      $form_action = 'resource_action.php?course='.$page_info['course_id'].'&session='.$page_info['session_id'].'&resource='.$page_info['resource_id'];
      $resource_output .= '<form method="post" action="'.$form_action.'"><ol>';
      foreach ($test_info['questions'] as $question)
      {
        $resource_output .= '<li>'.vlc_convert_code($question['question'], $page_info['course_id']);
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
            if (isset($user_answers[$question['question_id']]['answer']))
            {
              $ans = str_replace("\n", '<br>', $user_answers[$question['question_id']]['answer']);
              $resource_output .= '<br><br><div style="background: #eee; color: #000; border: solid 1px #000; padding: 5px; margin-left: 10px; margin-right: 10px;">'.$ans.'</div><br><br>';
            }
            else
            {
              if (isset($form_fields[$question['question_id']])) $ans = $form_fields[$question['question_id']];
              else $ans = '';
              $resource_output .= '<p><textarea name="'.$question['question_id'].'" rows="6" cols="60"'.$disabled.'>'.$ans.'</textarea></p>';
            }
            break;

          /* 2 => short answer (input type=text) */
          case 2:
            if (isset($user_answers[$question['question_id']]['answer'])) $ans = $user_answers[$question['question_id']]['answer'];
            elseif (isset($form_fields[$question['question_id']])) $ans = $form_fields[$question['question_id']];
            else $ans = '';
            $resource_output .= '<p><input type="text" name="'.$question['question_id'].'" size="30" value="'.$ans.'"'.$disabled.'></p>';
            break;

          /* 3 => true/false (radio button) */
          case 3:
          /* 4 => multiple choice, single selection (radio button) */
          case 4:
            if (isset($user_answers[$question['question_id']]['answer_id']))
            {
              $ans_id = $user_answers[$question['question_id']]['answer_id'];
              $show_correct = 1;
            }
            elseif (isset($form_fields[$question['question_id']]))
            {
              $ans_id = $form_fields[$question['question_id']];
              $show_correct = 0;
            }
            else
            {
              $ans_id = 0;
              $show_correct = 0;
            }
            $resource_output .= '<ol type="a">';
            foreach ($question['answers'] as $answer)
            {
              $answer_text = vlc_convert_code($answer['answer'], $page_info['course_id']);
              if ($ans_id == $answer['answer_id']) $checked = ' checked';
              else $checked = '';
              $correct = '';
              if ($show_correct == true)
              {
                if ($answer['is_correct'] == true)
                {
                  $answer_text = '<b>'.$answer_text.'</b>';
                  if (strlen($checked)) $correct = '&nbsp;<img src="correct.gif">';
                  else $correct = '&nbsp;<img src="arrow.png">';
                }
                elseif (strlen($checked))
                {
                  $answer_text = '<span style="text-decoration: line-through;">'.$answer_text.'</span>';
                  $correct = '&nbsp;<img src="incorrect.gif">';
                }
              }
              $resource_output .= '<li><input type="radio" name="'.$answer['question_id'].'" value="'.$answer['answer_id'].'" id="'.$answer['answer_id'].'"'.$checked.$disabled.'>'.$correct.'&nbsp;<label for="'.$answer['answer_id'].'">'.$answer_text.'</label></li>';
            }
            $resource_output .= '</ol>';
            break;

          /* 5 => multiple choice, multiple selection (checkbox) */
          case 5:
            if (isset($user_answers[$question['question_id']]))
            {
              $ans_id_array = $user_answers[$question['question_id']];
              $show_correct = 1;
            }
            elseif (isset($form_fields[$question['question_id']]) and is_array($form_fields[$question['question_id']]))
            {
              $ans_id_array = $form_fields[$question['question_id']];
              $show_correct = 0;
            }
            else
            {
              $ans_id_array = array();
              $show_correct = 0;
            }
            $resource_output .= '<ol type="a">';
            foreach ($question['answers'] as $answer)
            {
              $answer_text = vlc_convert_code($answer['answer'], $page_info['course_id']);
              if (in_array($answer['answer_id'], $ans_id_array)) $checked = ' checked';
              else $checked = '';
              $correct = '';
              if ($show_correct == true)
              {
                if ($answer['is_correct'] == true)
                {
                  $answer_text = '<b>'.$answer_text.'</b>';
                  if (strlen($checked)) $correct = '&nbsp;<img src="correct.gif">';
                  else $correct = '&nbsp;<img src="arrow.png">';
                }
                elseif (strlen($checked))
                {
                  $answer_text = '<span style="text-decoration: line-through;">'.$answer_text.'</span>';
                  $correct = '&nbsp;<img src="incorrect.gif">';
                }
              }
              $resource_output .= '<li><input type="checkbox" name="'.$answer['question_id'].'[]" value="'.$answer['answer_id'].'" id="'.$answer['answer_id'].'"'.$checked.$disabled.'>'.$correct.'&nbsp;<label for="'.$answer['answer_id'].'">'.$answer_text.'</label></li>';
            }
            $resource_output .= '</ol>';
            break;
        }
        $resource_output .= '</li>';
      }
      $resource_output .= '</ol><input type="submit" name="submit" value="'.$lang['classes']['exercise']['form-fields']['submit-button'].'" class="submit-button"'.$disabled.'></form>';
    }
    break;

  /* 38 => course readings overview */
  case 38:
    $course_readings_query = <<< END_QUERY
      SELECT r.resource_id, r.resource_type_id, IFNULL(r.session_id, '') AS session_id, IFNULL(r.title, '') AS resource_title, IFNULL(rd.author, '') AS author, IFNULL(rd.source, '') AS source, IFNULL(rd.notes, '') AS notes
      FROM resources AS r LEFT JOIN resource_details AS rd ON r.resource_id = rd.resource_id
      WHERE r.resource_type_id IN (22, 23, 24, 25, 35, 36)
      AND r.course_subject_id = {$course_info['course_subject_id']}
      ORDER BY r.session_id, r.display_order
END_QUERY;
    $result = mysql_query($course_readings_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result)) $session_readings[$record['session_id']][] = $record;
    $resource_output = '';
    if (strlen($resource_info['content']) > 0) $resource_output .= vlc_convert_code($resource_info['content'], $page_info['course_id']);
    foreach ($course_info['sessions'] as $session)
    {
      $session_id = $session['session_id'];
      if (isset($session_readings[$session_id]))
      {
        $session_title = sprintf($lang['classes']['common']['misc']['session'], $session['display_order']);
        $resource_output .= '<h3>'.$session_title.'</h3>';
        $resource_output .= '<ul>';
        for ($i = 0; $i < count($session_readings[$session_id]); $i++)
        {
          $resource_id = $session_readings[$session_id][$i]['resource_id'];
          $resource_type_id = $session_readings[$session_id][$i]['resource_type_id'];
          $resource_type = $lang['database']['resource-types'][$resource_type_id];
          $resource_title = $session_readings[$session_id][$i]['resource_title'];
          $author = $session_readings[$session_id][$i]['author'];
          $source = $session_readings[$session_id][$i]['source'];
          $notes = $session_readings[$session_id][$i]['notes'];
          $reading_details = '';
          if (strlen($author)) $reading_details .= '<li><b>'.$lang['classes']['reading']['misc']['author'].':</b> '.$author.'</li>';
          if (strlen($source)) $reading_details .= '<li><b>'.$lang['classes']['reading']['misc']['source'].':</b> '.$source.'</li>';
          if (strlen($notes)) $reading_details .= '<li><b>'.$lang['classes']['reading']['misc']['notes'].':</b> '.$notes.'</li>';
          if (in_array($resource_type_id, array(22, 24, 35, 36)))
          {
            $resource_url = 'classes/resource.php?course='.$page_info['course_id'].'&session='.$session_id.'&resource='.$resource_id;
            $resource_title = vlc_internal_link($resource_title, $resource_url);
          }
          $resource_output .= '<li>'.$resource_type.': '.$resource_title;
          if (strlen($reading_details)) $resource_output .= '<ul>'.$reading_details.'</ul>';
          $resource_output .= '</li>';
        }
        $resource_output .= '</ul>';
      }
    }
    break;

  /* 39 => course outline */
  case 39:
    $course_resources_query = <<< END_QUERY
      SELECT r.resource_id, r.resource_type_id, IFNULL(r.session_id, '') AS session_id, IFNULL(r.title, '') AS resource_title
      FROM resources AS r
      WHERE r.resource_type_id IN (4, 6, 15, 16, 17, 18, 22, 23, 24, 25, 26, 35, 36, 37, 38, 39, 40, 41, 44)
      AND r.course_subject_id = {$course_info['course_subject_id']}
      ORDER BY r.session_id, r.display_order
END_QUERY;
    $result = mysql_query($course_resources_query, $site_info['db_conn']);
    while ($record = mysql_fetch_array($result))
    {
      if (strlen($record['session_id']) > 0) $course_resources[$record['session_id']][] = $record;
      else $course_resources[0][] = $record;
    }
    $resource_output = '<ul>';
    foreach ($course_resources[0] as $resource)
    {
      if ($resource['resource_type_id'] == 4) $resource_output .= '<li>'.$lang['database']['resource-types'][$resource['resource_type_id']].': '.vlc_internal_link($resource['resource_title'], 'classes/resource.php?course='.$page_info['course_id'].'&resource='.$resource['resource_id']).'</li>';
      else $resource_output .= '<li>'.vlc_internal_link($lang['database']['resource-types'][$resource['resource_type_id']], 'classes/resource.php?course='.$page_info['course_id'].'&resource='.$resource['resource_id']).'</li>';
    }
    foreach ($course_info['sessions'] as $session)
    {
      $session_id = $session['session_id'];
      $session_num = $session['display_order'];
      $session_title = $session['session_title'];
      $session_start = date('M j', $session['start_date']);
      $session_end = date('M j', $session['end_date']);
      $session_url = 'classes/session.php?course='.$page_info['course_id'].'&session='.$session_id;
      $resource_output .= '<li><b>'.sprintf($lang['classes']['common']['misc']['session'], $session_num).' ('.$session_start.' - '.$session_end.'):</b> '.vlc_internal_link($session_title, $session_url);
      if (isset($course_resources[$session_id]))
      {
        $resource_output .= '<ol>';
        for ($i = 0; $i < count($course_resources[$session_id]); $i++)
        {
          $resource_id = $course_resources[$session_id][$i]['resource_id'];
          $resource_type_id = $course_resources[$session_id][$i]['resource_type_id'];
          $resource_type = $lang['database']['resource-types'][$resource_type_id];
          $resource_title = $course_resources[$session_id][$i]['resource_title'];
          $resource_url = 'classes/resource.php?course='.$page_info['course_id'].'&session='.$session_id.'&resource='.$resource_id;
          if (in_array($resource_type_id, array(40, 44))) $resource_output .= '<li>'.vlc_internal_link($resource_type, $resource_url).'</li>';
          else $resource_output .= '<li>'.$resource_type.': '.vlc_internal_link($resource_title, $resource_url).'</li>';
        }
        $resource_output .= '</ol>';
      }
      $resource_output .= '</li><br>';
    }
    $resource_output .= '</ul>';
    break;

  /* 40 => weekly study chart */
  case 40:
    $session_resources_query = <<< END_QUERY
      SELECT r.resource_id, r.resource_type_id, r.session_id, IFNULL(r.title, '') AS resource_title, IFNULL(rd.author, '') AS author, IFNULL(rd.source, '') AS source, IFNULL(rd.notes, '') AS notes
      FROM resources AS r LEFT JOIN resource_details AS rd ON r.resource_id = rd.resource_id
      WHERE r.session_id = {$page_info['session_id']}
      AND r.resource_type_id IN (4, 22, 23, 24, 25, 26, 35, 36, 44, 58)
      AND r.course_subject_id = {$course_info['course_subject_id']}
      ORDER BY r.session_id, r.display_order
END_QUERY;
    $result = mysql_query($session_resources_query, $site_info['db_conn']);
    $resource_output = '<p>'.$lang['classes']['study-chart']['misc']['intro'].'</p>';
    while ($record = mysql_fetch_array($result))
    {
      $resource_type = $lang['database']['resource-types'][$record['resource_type_id']];
      if ($record['resource_type_id'] == 4)
      {
        $resource_output .= '<p>&nbsp;____&nbsp;<b>'.$resource_type.'</b>: '.$record['resource_title'].' ('.$lang['classes']['study-chart']['misc']['first-entry'].')</p>';
        $resource_output .= '<p>&nbsp;____&nbsp;<b>'.$resource_type.'</b>: '.$record['resource_title'].' ('.$lang['classes']['study-chart']['misc']['second-entry'].')</p>';
        $resource_output .= '<p>&nbsp;____&nbsp;<b>'.$resource_type.'</b>: '.$record['resource_title'].' ('.$lang['classes']['study-chart']['misc']['third-entry'].')</p>';
      }
      elseif (in_array($record['resource_type_id'], array(22, 23, 24, 25, 35, 36)))
      {
        $author_source_notes = '';
        if (strlen($record['author']) > 0) $author_source_notes .= '<br><span style="margin-left: 100px;">- '.$lang['classes']['reading']['misc']['author'].': '.$record['author'].'</span>';
        if (strlen($record['source']) > 0) $author_source_notes .= '<br><span style="margin-left: 100px;">- '.$lang['classes']['reading']['misc']['source'].': '.$record['source'].'</span>';
        if (strlen($record['notes']) > 0) $author_source_notes .= '<br><span style="margin-left: 100px;">- '.$lang['classes']['reading']['misc']['notes'].': '.$record['notes'].'</span>';
        $resource_output .= '<p>&nbsp;____&nbsp;<b>'.$resource_type.'</b>: '.$record['resource_title'].$author_source_notes.'</p>';
      }
      elseif ($record['resource_type_id'] == 44) $resource_output .= '<p>&nbsp;____&nbsp;<b>'.$resource_type.'</b></p>';
      else $resource_output .= '<p>&nbsp;____&nbsp;<b>'.$resource_type.'</b>: '.$record['resource_title'].'</p>';
    }
    break;

  /* 58 => video */
  case 58:
    $video_url = $site_info['files_url'].$resource_info['url'];
    $resource_output = '<p>'.$resource_info['resource_type'].': <b>'.$resource_info['title'].'</b></p>';
    $resource_output .= vlc_convert_code($resource_info['content'], $page_info['course_id']);
    $resource_output .= '<div align="center">'.vlc_embed_video($video_url).'</div>';
    break;

  /* none of the above */
  default:
    trigger_error('INVALID RESOURCE TYPE ID: '.$resource_info['resource_type_id']);
}
print $header;
?>
<!-- begin page content -->
<?php print $resource_output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

