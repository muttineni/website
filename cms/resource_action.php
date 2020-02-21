<?php
$page_info['section'] = 'cms';
$page_info['login_required'] = 1;
$page_info['course_subject_id'] = vlc_get_url_variable($site_info, 'subject', false);
$page_info['session_id'] = vlc_get_url_variable($site_info, 'session', false);
$page_info['resource_id'] = vlc_get_url_variable($site_info, 'resource', false);
$page_info['resource_type_id'] = vlc_get_url_variable($site_info, 'type', false);
$user_info = vlc_get_user_info($page_info['login_required']);
$lang = vlc_get_language();
/* get form fields from posted variables */
if (isset($_POST) and count($_POST)) $form_fields = $_POST;
elseif (isset($_GET) and count($_GET)) $form_fields = $_GET;
/* get resource action */
if (isset($form_fields['action']) and is_numeric($form_fields['action'])) $page_info['action_id'] = $form_fields['action'];
else trigger_error('Invalid Resource Action.');
if (isset($page_info['course_subject_id'])) $return_url = 'cms/course_subject_details.php?subject='.$page_info['course_subject_id'];
elseif (isset($_SERVER['HTTP_REFERER']) and strpos($_SERVER['HTTP_REFERER'], 'cms/', 1)) $return_url = strstr($_SERVER['HTTP_REFERER'], 'cms/');
else $return_url = 'cms/';
$db_events_array = array();
/* set form field options when creating or editing a resource */
if (in_array($page_info['action_id'], array(3, 4)))
{
  /* set required and disabled options for each resource type (all form fields) */
  $field_options_array = array();
  /*
  ** course requirements (15), course methodology (16), theological reflection (18),
  ** course readings overview (38), course outline (39), weekly study chart (40),
  ** clues for success (41), course evaluation (44), key 2 (48),
  ** key 3 (49), key 4 (41), key 5 (51),
  ** key 6 (52)
  */
  $field_options_array[15] = $field_options_array[16] = $field_options_array[18] =
    $field_options_array[38] = $field_options_array[39] = $field_options_array[40] =
    $field_options_array[41] = $field_options_array[44] = $field_options_array[48] =
    $field_options_array[49] = $field_options_array[50] = $field_options_array[51] =
    $field_options_array[52] = array
  (
    'required' => array('display_order', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('title', 'content', 'url', 'author', 'source', 'notes', 'isbn', 'quickform')
  );
  /* course related websites (37), meeting place (46), key 1 (47) */
  $field_options_array[37] = $field_options_array[46] = $field_options_array[47] = array
  (
    'required' => array('active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('title', 'content', 'url', 'author', 'source', 'notes', 'isbn', 'quickform')
  );
  /* course introduction (11), session introduction (19) */
  $field_options_array[11] = $field_options_array[19] = array
  (
    'required' => array('content', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('title', 'url', 'author', 'source', 'notes', 'isbn')
  );
  /* vlcff announcement (1), new course announcement (56) */
  $field_options_array[1] = $field_options_array[56] = array
  (
    'required' => array('language_id', 'content', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('title', 'url', 'author', 'source', 'notes', 'isbn', 'quickform')
  );
  /* vlcff announcement with audio (59) */
  $field_options_array[59] = array
  (
    'required' => array('language_id', 'content', 'url', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('title', 'author', 'source', 'notes', 'isbn', 'quickform')
  );
  /* course objective (13), session objective (21) */
  $field_options_array[13] = $field_options_array[21] = array
  (
    'required' => array('display_order', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year', 'quickform'),
    'disabled' => array('title', 'content', 'url', 'author', 'source', 'notes', 'isbn')
  );
  /* discussion board (4) */
  $field_options_array[4] = array
  (
    'required' => array('display_order', 'title', 'content', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('url', 'author', 'source', 'notes', 'isbn', 'quickform')
  );
  /* exercise (26) */
  $field_options_array[26] = array
  (
    'required' => array('display_order', 'title', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('url', 'author', 'source', 'notes', 'isbn')
  );
  /* glossary (6) */
  $field_options_array[6] = array
  (
    'required' => array('display_order', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year', 'quickform'),
    'disabled' => array('title', 'content', 'url', 'author', 'source', 'notes', 'isbn')
  );
  /* session structure (17) */
  $field_options_array[17] = array
  (
    'required' => array('display_order', 'content', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('title', 'url', 'author', 'source', 'notes', 'isbn', 'quickform')
  );
  /* optional reading/hardcopy (25), required reading/hardcopy (23) */
  $field_options_array[25] = $field_options_array[23] = array
  (
    'required' => array('display_order', 'title', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('url', 'quickform')
  );
  /* optional reading/online - external website (36), required reading/online - external website (35) */
  $field_options_array[36] = $field_options_array[35] = array
  (
    'required' => array('display_order', 'title', 'url', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('content', 'quickform')
  );
  /* optional reading/online - vlcff (24), required reading/online - vlcff (22) */
  $field_options_array[24] = $field_options_array[22] = array
  (
    'required' => array('display_order', 'title', 'content', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('url', 'quickform')
  );
  /* vlcff news article (2) */
  $field_options_array[2] = array
  (
    'required' => array('language_id', 'title', 'content', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('quickform')
  );
  /* course summary (53) */
  $field_options_array[53] = array
  (
    'required' => array('content', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('title', 'url', 'author', 'source', 'notes', 'isbn', 'quickform')
  );
  /* newsletter (57) */
  $field_options_array[57] = array
  (
    'required' => array('language_id', 'title', 'content', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('url', 'source', 'notes', 'isbn', 'quickform')
  );
  /* video (58) */
  $field_options_array[58] = array
  (
    'required' => array('display_order', 'title', 'url', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('author', 'source', 'notes', 'isbn', 'quickform')
  );
  /* required course material (54), optional course material (55) */
  $field_options_array[54] = $field_options_array[55] = array
  (
    'required' => array('display_order', 'title', 'active_start_month', 'active_start_day', 'active_start_year', 'active_end_month', 'active_end_day', 'active_end_year'),
    'disabled' => array('url', 'quickform')
  );
  /* if editing the resource (action="edit"), revise field options array for resource types that use the quickform */
  if ($page_info['action_id'] == 4)
  {
    /* vlcff announcement, course introduction, session introduction */
    $field_options_array[1]['disabled'][] = $field_options_array[11]['disabled'][] = $field_options_array[19]['disabled'][] = 'quickform';
    /* course objectives, session objectives */
    array_splice($field_options_array[13]['required'], array_search('quickform', $field_options_array[13]['required']), 1);
    array_splice($field_options_array[13]['disabled'], array_search('content', $field_options_array[13]['disabled']), 1);
    array_splice($field_options_array[21]['required'], array_search('quickform', $field_options_array[21]['required']), 1);
    array_splice($field_options_array[21]['disabled'], array_search('content', $field_options_array[21]['disabled']), 1);
    $field_options_array[13]['required'][] = $field_options_array[21]['required'][] = 'content';
    $field_options_array[13]['disabled'][] = $field_options_array[21]['disabled'][] = 'quickform';
    /* exercises */
    $field_options_array[26]['disabled'][] = 'quickform';
    /* glossary */
    array_splice($field_options_array[6]['required'], array_search('quickform', $field_options_array[6]['required']), 1);
    array_splice($field_options_array[6]['disabled'], array_search('content', $field_options_array[6]['disabled']), 1);
    $field_options_array[6]['required'][] = 'content';
    $field_options_array[6]['disabled'][] = 'quickform';
  }
  /* trim white space from form fields */
  foreach ($form_fields as $key => $value)
  {
    if (!is_array($form_fields[$key])) $form_fields[$key] = trim($value);
  }
  /* check to see if rich text field was submitted */
  if (isset($form_fields['content_rte']) and strlen($form_fields['content_rte'])) $form_fields['content'] = vlc_convert_html($form_fields['content_rte']);
  elseif (isset($form_fields['content']) and strlen($form_fields['content'])) $form_fields['content'] = str_replace("\n", "[br]\n", stripslashes(trim($form_fields['content'])));
  else $form_fields['content'] = '';
  /* initialize error message variable */
  $error_message = '';
  /* check to see if required fields were filled in */
  foreach ($field_options_array[$page_info['resource_type_id']]['required'] as $field)
  {
    if (!isset($form_fields[$field]) or strlen($form_fields[$field]) == 0)
    {
      $field_name = ucwords(str_replace('_', ' ', $field));
      $error_message .= '<li>'.$field_name.' is required.</li>';
    }
  }
  /* set disabled fields to empty string */
  foreach ($field_options_array[$page_info['resource_type_id']]['disabled'] as $field)
  {
    $form_fields[$field] = '';
  }
  /* if errors have occurred, go back to form */
  if (strlen($error_message) > 0)
  {
    $_SESSION['form_fields'] = $form_fields;
    /* create return url */
    $error_url = 'cms/resource_details.php';
    $error_url_array = array();
    if (isset($page_info['course_subject_id'])) $error_url_array[] = 'subject='.$page_info['course_subject_id'];
    if (isset($page_info['session_id'])) $error_url_array[] = 'session='.$page_info['session_id'];
    if (isset($page_info['resource_id'])) $error_url_array[] = 'resource='.$page_info['resource_id'];
    if (isset($page_info['resource_type_id'])) $error_url_array[] = 'type='.$page_info['resource_type_id'];
    if (count($error_url_array)) $error_url .= '?'.join('&', $error_url_array);
    vlc_exit_page($error_message, 'error', $error_url);
  }
}
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = addslashes($value);
}
/* perform action based on action id */
switch ($page_info['action_id'])
{
  /* 1 => add a new session */
  case 1:
    if (isset($form_fields['session_description']) and strlen(trim($form_fields['session_description'])) > 0) $session_description = trim($form_fields['session_description']);
    else vlc_exit_page('<li>Session description is required.</li>', 'error', $return_url);
    if (isset($form_fields['next_session_num']) and is_numeric($form_fields['next_session_num'])) $next_session_num = $form_fields['next_session_num'];
    else trigger_error('Next Session Number is Required.');
    $insert_session_query = <<< END_QUERY
      INSERT INTO sessions
      SET CREATED = NULL, CREATEDBY = {$user_info['user_id']}, course_subject_id = {$page_info['course_subject_id']}, description = '$session_description', display_order = $next_session_num
END_QUERY;
    $result = mysql_query($insert_session_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "sessions"');
    $success_message = 'Session successfully added.';
    $db_events_array[] = array(COURSE_SUBJECTS_ADD_SESSION, $page_info['course_subject_id']);
    $db_events_array[] = array(SESSIONS_CREATE, mysql_insert_id());
    break;
  /* 5 => update session */
  case 5:
    if (isset($form_fields['session_description']) and strlen(trim($form_fields['session_description'])) > 0) $session_description = trim($form_fields['session_description']);
    else vlc_exit_page('<li>Session description is required.</li>', 'error', $return_url);
    if (isset($form_fields['display_order']) and is_numeric($form_fields['display_order'])) $display_order = $form_fields['display_order'];
    else trigger_error('Display Order is Required.');
    $update_session_query = <<< END_QUERY
      UPDATE sessions
      SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']}, description = '$session_description', display_order = $display_order
      WHERE session_id = {$form_fields['session_id']}
END_QUERY;
    $result = mysql_query($update_session_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "sessions"');
    $success_message = 'Session successfully updated.';
    $db_events_array[] = array(COURSE_SUBJECTS_UPDATE_SESSION, $page_info['course_subject_id']);
    $db_events_array[] = array(SESSIONS_UPDATE, $form_fields['session_id']);
    break;
  /* 2 => reorder resources */
  case 2:
    foreach ($form_fields as $resource_id => $display_order)
    {
      if (is_numeric($resource_id))
      {
        $update_display_order_query = 'UPDATE resources SET UPDATED = NULL, UPDATEDBY = '.$user_info['user_id'].', display_order = '.$display_order.' WHERE resource_id = '.$resource_id;
        $result = mysql_query($update_display_order_query, $site_info['db_conn']);
        if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "resources"');
        $db_events_array[] = array(COURSE_SUBJECTS_UPDATE_RESOURCE, $page_info['course_subject_id']);
        $db_events_array[] = array(RESOURCES_UPDATE, $resource_id);
      }
    }
    $success_message = 'Display order successfully updated.';
    break;
  /* 3 => add resource */
  case 3:
    /* course subject id is null if this is a vlcff resource */
    if (isset($page_info['course_subject_id'])) $course_subject_id = $page_info['course_subject_id'];
    else $course_subject_id = 'NULL';
    /* session id is null if this is a course resource */
    if (isset($page_info['session_id'])) $session_id = $page_info['session_id'];
    else $session_id = 'NULL';
    /* create dates from parts */
    $active_start = $form_fields['active_start_year'].'-'.$form_fields['active_start_month'].'-'.$form_fields['active_start_day'];
    $active_end = $form_fields['active_end_year'].'-'.$form_fields['active_end_month'].'-'.$form_fields['active_end_day'];
    /* if the resource is not a course objective or session objective (these will be in the quickform field) */
    if (!in_array($page_info['resource_type_id'], array(13, 21)))
    {
      $insert_resource_query = <<< END_QUERY
        INSERT INTO resources
        SET CREATED = NULL, CREATEDBY = {$user_info['user_id']},
          course_subject_id = $course_subject_id,
          session_id = $session_id,
          resource_type_id = {$page_info['resource_type_id']},
          language_id = {$form_fields['language_id']},
          display_order = {$form_fields['display_order']},
          title = NULLIF('{$form_fields['title']}', ''),
          content = NULLIF('{$form_fields['content']}', ''),
          url = NULLIF('{$form_fields['url']}', ''),
          active_start = '$active_start',
          active_end = '$active_end'
END_QUERY;
      $result = mysql_query($insert_resource_query, $site_info['db_conn']);
      if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "resources"');
      $resource_insert_id = mysql_insert_id();
      /* create return url */
      $return_url = 'cms/resource_details.php';
      $return_url_array = array();
      if (isset($page_info['course_subject_id'])) $return_url_array[] = 'subject='.$page_info['course_subject_id'];
      if (isset($page_info['session_id'])) $return_url_array[] = 'session='.$page_info['session_id'];
      $return_url_array[] = 'resource='.$resource_insert_id;
      if (count($return_url_array)) $return_url .= '?'.join('&', $return_url_array);
      if (isset($page_info['course_subject_id'])) $db_events_array[] = array(COURSE_SUBJECTS_ADD_RESOURCE, $page_info['course_subject_id']);
      $db_events_array[] = array(RESOURCES_CREATE, $resource_insert_id);
    }
    /* if the resource is a reading, insert reading details */
    if (in_array($page_info['resource_type_id'], array(2, 22, 23, 24, 25, 35, 36, 54, 55, 57)))
    {
      $insert_reading_details_query = <<< END_QUERY
        INSERT INTO resource_details
        SET CREATED = NULL, CREATEDBY = {$user_info['user_id']},
          resource_id = $resource_insert_id,
          resource_format_id = {$form_fields['resource_format_id']},
          author = NULLIF('{$form_fields['author']}', ''),
          source = NULLIF('{$form_fields['source']}', ''),
          notes = NULLIF('{$form_fields['notes']}', ''),
          isbn = NULLIF('{$form_fields['isbn']}', '')
END_QUERY;
      $result = mysql_query($insert_reading_details_query, $site_info['db_conn']);
      if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "resource_details"');
    }
    /* if the resource is a course evaluation, insert blank placeholder questions */
    if ($page_info['resource_type_id'] == 44)
    {
      $insert_eval_questions_query = 'INSERT INTO questions (CREATED, CREATEDBY, test_id, question_type_id, question, display_order) VALUES ';
      for ($i = 1; $i <= 10; $i++)
      {
        $eval_questions_array[] = "(NULL, {$user_info['user_id']}, $resource_insert_id, 1, '(see language file for questions)', $i)";
      }
      $insert_eval_questions_query .= join(', ', $eval_questions_array);
      $result = mysql_query($insert_eval_questions_query, $site_info['db_conn']);
      if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "questions"');
    }
    if (strlen($form_fields['quickform']) > 0)
    {
      /* define quick form query array for later use */
      $quick_form_query_array = array();
      /* remove slashes from form submission */
      $input = stripslashes($form_fields['quickform']);
      /* convert html to vlc-code */
      $output = vlc_convert_html($input);
      /* add slashes for database insert */
      $form_fields['quickform'] = addslashes($output);
      /* each line becomes an element in the array */
      $quick_form_contents_array = preg_split("/\n/", $form_fields['quickform']);
      /* manipulate quickform contents based on resource type */
      switch ($page_info['resource_type_id'])
      {
        /* 11 => course introduction; quick form contains course objectives */
        case 11:
        /* 13 => course objective; quick form contains additional course objectives */
        case 13:
          $display_order = 1;
          foreach ($quick_form_contents_array as $resource_line)
          {
            if (strlen(trim($resource_line)))
            {
              $quick_form_query = "INSERT INTO resources (CREATED, CREATEDBY, course_subject_id, resource_type_id, display_order, content, active_start, active_end) VALUES (NULL, {$user_info['user_id']}, {$page_info['course_subject_id']}, 13, $display_order, '$resource_line', '$active_start', '$active_end')";
              $result = mysql_query($quick_form_query, $site_info['db_conn']);
              if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "resources"');
              else
              {
                $db_events_array[] = array(RESOURCES_CREATE, mysql_insert_id());
                $db_events_array[] = array(COURSE_SUBJECTS_ADD_RESOURCE, $page_info['course_subject_id']);
              }
              $display_order++;
            }
          }
          break;
        /* 19 => session introduction; quick form contains session objectives */
        case 19:
        /* 21 => session objective; quick form contains additional session objectives */
        case 21:
          $display_order = 1;
          foreach ($quick_form_contents_array as $resource_line)
          {
            if (strlen(trim($resource_line)))
            {
              $quick_form_query = "INSERT INTO resources (CREATED, CREATEDBY, course_subject_id, session_id, resource_type_id, display_order, content, active_start, active_end) VALUES (NULL, {$user_info['user_id']}, {$page_info['course_subject_id']}, {$page_info['session_id']}, 21, $display_order, '$resource_line', '$active_start', '$active_end')";
              $result = mysql_query($quick_form_query, $site_info['db_conn']);
              if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "resources"');
              else
              {
                $db_events_array[] = array(RESOURCES_CREATE, mysql_insert_id());
                $db_events_array[] = array(COURSE_SUBJECTS_ADD_RESOURCE, $page_info['course_subject_id']);
              }
              $display_order++;
            }
          }
          break;
        /* 6 => glossary; quick form contains glossary terms and definitions */
        case 6:
          $glossary_entry_array = array('[dl]');
          for ($i = 0; $i < count($quick_form_contents_array); $i++)
          {
            $term = $quick_form_contents_array[$i];
            $i++;
            if (isset($quick_form_contents_array[$i])) $definition = $quick_form_contents_array[$i];
            else $definition = '';
            $glossary_entry_array[] = '[dt][b]'.$term.'[/b][/dt][dd]'.$definition.'[/dd]';
          }
          $glossary_entry_array[] = '[/dl]';
          $glossary_entry_list = join("\n", $glossary_entry_array);
          $quick_form_query = "UPDATE resources SET UPDATED = NULL, UPDATEDBY = NULL, content = CONCAT(IFNULL(content, ''), '$glossary_entry_list') WHERE resource_id = $resource_insert_id LIMIT 1";
          $result = mysql_query($quick_form_query, $site_info['db_conn']);
          if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "resources"');
          break;
        /* 26 => exercises/tests/quizzes/etc.; quick form contains questions and (optionally) answers */
        case 26:
          $question_answer_array = array();
          $ques_display_order = 0;
          foreach ($quick_form_contents_array as $resource_line)
          {
            /* if the line begins with a plus or minus sign, then it is an answer */
            if (preg_match("/^[-\+].+$/", $resource_line) == true)
            {
              $question_answer_array[$ques_display_order]['answers'][$ans_display_order]['answer'] = substr($resource_line, 1);
              /* if the line begins with a plus sign, then it is a correct answer choice */
              if (preg_match("/^\+.+$/", $resource_line) == true) $question_answer_array[$ques_display_order]['answers'][$ans_display_order]['is_correct'] = 1;
              /* otherwise it is an incorrect answer choice */
              else $question_answer_array[$ques_display_order]['answers'][$ans_display_order]['is_correct'] = 0;
              $question_answer_array[$ques_display_order]['question_type_id'] = 4;
              $ans_display_order++;
            }
            /* otherwise, it is a question */
            else
            {
              $ques_display_order++;
              $question_answer_array[$ques_display_order]['question'] = $resource_line;
              $question_answer_array[$ques_display_order]['question_type_id'] = 1;
              $question_answer_array[$ques_display_order]['answers'] = array();
              $ans_display_order = 1;
            }
          }
          foreach ($question_answer_array as $ques_display_order => $question_details)
          {
            $insert_question_query = "INSERT INTO questions (CREATED, CREATEDBY, test_id, question_type_id, question, display_order) VALUES (NULL, {$user_info['user_id']}, $resource_insert_id, {$question_details['question_type_id']}, '{$question_details['question']}', $ques_display_order)";
            $result = mysql_query($insert_question_query, $site_info['db_conn']);
            if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "questions"');
            $last_question_id = mysql_insert_id();
            foreach ($question_details['answers'] as $ans_display_order => $answer_details) $quick_form_query_array[] = "(NULL, {$user_info['user_id']}, $last_question_id, '{$answer_details['answer']}', {$answer_details['is_correct']}, $ans_display_order)";
          }
          if (count($quick_form_query_array) > 0)
          {
            $quick_form_query = 'INSERT INTO answers (CREATED, CREATEDBY, question_id, answer, is_correct, display_order) VALUES ';
            $quick_form_query .= join(', ', $quick_form_query_array);
            $result = mysql_query($quick_form_query, $site_info['db_conn']);
            if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "answers"');
          }
          break;
      }
    }
    $success_message = 'Resource successfully added.';
    break;
  /* 4 => edit resource */
  case 4:
    /* create dates from parts */
    $active_start = $form_fields['active_start_year'].'-'.$form_fields['active_start_month'].'-'.$form_fields['active_start_day'];
    $active_end = $form_fields['active_end_year'].'-'.$form_fields['active_end_month'].'-'.$form_fields['active_end_day'];
    $update_resource_query = <<< END_QUERY
      UPDATE resources
      SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']},
        language_id = {$form_fields['language_id']},
        display_order = {$form_fields['display_order']},
        title = NULLIF('{$form_fields['title']}', ''),
        content = NULLIF('{$form_fields['content']}', ''),
        url = NULLIF('{$form_fields['url']}', ''),
        active_start = '$active_start',
        active_end = '$active_end'
      WHERE resource_id = {$page_info['resource_id']}
      LIMIT 1
END_QUERY;
    $result = mysql_query($update_resource_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "resources"');
    /* if the resource is a reading, update reading details */
    if (in_array($page_info['resource_type_id'], array(2, 22, 23, 24, 25, 35, 36, 54, 55, 57)))
    {
      $update_reading_details_query = <<< END_QUERY
        UPDATE resource_details
        SET UPDATED = NULL, UPDATEDBY = {$user_info['user_id']},
          resource_format_id = {$form_fields['resource_format_id']},
          author = NULLIF('{$form_fields['author']}', ''),
          source = NULLIF('{$form_fields['source']}', ''),
          notes = NULLIF('{$form_fields['notes']}', ''),
          isbn = NULLIF('{$form_fields['isbn']}', '')
        WHERE resource_id = {$page_info['resource_id']}
        LIMIT 1
END_QUERY;
      $result = mysql_query($update_reading_details_query, $site_info['db_conn']);
      if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "resource_details"');
    }
    /* edit questions and answers */
    if (isset($form_fields['questions']) and is_array($form_fields['questions']) and count($form_fields['questions']))
    {
      $question_id_array = $answer_id_array = $keep_question_delete_answers = $insert_question_array = $insert_answer_array = array();
      foreach ($form_fields['questions'] as $question_id => $question)
      {
        /* remove the question */
        if (isset($question['remove']) and $question['remove'] == 1)
        {
          $question_id_array[] = $question_id;
        }
        /* update the question */
        elseif (is_numeric($question_id))
        {
          if (in_array($question['question_type_id'], array(1, 2))) $keep_question_delete_answers[] = $question_id;
          if ($question['display_order'] != $question['previous_display_order'] or $question['question'] != $question['previous_question'] or $question['question_type_id'] != $question['previous_question_type_id'])
          {
            $question['question'] = addslashes($question['question']);
            $update_question_query = <<< END_QUERY
              UPDATE questions
              SET display_order = {$question['display_order']},
                question = '{$question['question']}',
                question_type_id = {$question['question_type_id']}
              WHERE question_id = $question_id
END_QUERY;
            $result = mysql_query($update_question_query, $site_info['db_conn']);
            if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "questions"');
          }
        }
        /* add new question */
        else
        {
          if (strlen($question['question']))
          {
            $question['question'] = addslashes($question['question']);
            $insert_question_query = 'INSERT INTO questions (CREATED, CREATEDBY, test_id, question_type_id, question, display_order) VALUES (NULL, '.$user_info['user_id'].', '.$page_info['resource_id'].', '.$question['question_type_id'].', \''.$question['question'].'\', '.$question['display_order'].')';
            $result = mysql_query($insert_question_query, $site_info['db_conn']);
            if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "questions"');
            else $question_id = mysql_insert_id();
          }
        }
        if (isset($question['answers']) and is_array($question['answers']) and count($question['answers']))
        {
          foreach ($question['answers'] as $answer_id => $answer)
          {
            /* remove the answer */
            if (isset($answer['remove']) and $answer['remove'] == 1)
            {
              $answer_id_array[] = $answer_id;
            }
            /* update the answer */
            elseif (is_numeric($answer_id))
            {
              if ($answer['display_order'] != $answer['previous_display_order'] or $answer['answer'] != $answer['previous_answer'] or $answer['is_correct'] != $answer['previous_is_correct'])
              {
                $answer['answer'] = addslashes($answer['answer']);
                $update_answer_query = <<< END_QUERY
                  UPDATE answers
                  SET display_order = {$answer['display_order']},
                    answer = '{$answer['answer']}',
                    is_correct = {$answer['is_correct']}
                  WHERE answer_id = $answer_id
END_QUERY;
                $result = mysql_query($update_answer_query, $site_info['db_conn']);
                if (mysql_affected_rows() < 1) trigger_error('UPDATE FAILED: "answers"');
              }
            }
            /* add new answer */
            else
            {
              if (strlen($answer['answer']) and !in_array($question['question_type_id'], array(1, 2)))
              {
                $answer['answer'] = addslashes($answer['answer']);
                $insert_answer_array[] = '(NULL, '.$user_info['user_id'].', '.$question_id.', \''.$answer['answer'].'\', '.$answer['is_correct'].', '.$answer['display_order'].')';
              }
            }
          }
        }
      }
      if (count($question_id_array) or count($keep_question_delete_answers))
      {
        if (count($question_id_array))
        {
          $question_id_list = join(', ', $question_id_array);
          /* delete questions */
          $num_questions = count($question_id_array);
          $delete_questions_query = <<< END_QUERY
            DELETE FROM questions
            WHERE question_id IN ($question_id_list)
            LIMIT $num_questions
END_QUERY;
          $result = mysql_query($delete_questions_query, $site_info['db_conn']);
        }
        /* get answer id's */
        $question_id_array = array_merge($question_id_array, $keep_question_delete_answers);
        $question_id_list = join(', ', $question_id_array);
        $answer_id_query = <<< END_QUERY
          SELECT answer_id
          FROM answers
          WHERE question_id IN ($question_id_list)
END_QUERY;
        $result = mysql_query($answer_id_query, $site_info['db_conn']);
        while ($record = mysql_fetch_array($result)) $answer_id_array[] = $record['answer_id'];
        $answer_id_array = array_unique($answer_id_array);
      }
      if (count($answer_id_array))
      {
        $answer_id_list = join(', ', $answer_id_array);
        /* delete answers */
        $num_answers = count($answer_id_array);
        $delete_answers_query = <<< END_QUERY
          DELETE FROM answers
          WHERE answer_id IN ($answer_id_list)
          LIMIT $num_answers
END_QUERY;
        $result = mysql_query($delete_answers_query, $site_info['db_conn']);
      }
      if (count($insert_answer_array) > 0)
      {
        $insert_answer_query = 'INSERT INTO answers (CREATED, CREATEDBY, question_id, answer, is_correct, display_order) VALUES ';
        $insert_answer_query .= join(', ', $insert_answer_array);
        $result = mysql_query($insert_answer_query, $site_info['db_conn']);
        if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: "answers"');
      }
    }
    /* create return url */
    $return_url = 'cms/resource_details.php';
    $return_url_array = array();
    if (isset($page_info['course_subject_id'])) $return_url_array[] = 'subject='.$page_info['course_subject_id'];
    if (isset($page_info['session_id'])) $return_url_array[] = 'session='.$page_info['session_id'];
    $return_url_array[] = 'resource='.$page_info['resource_id'];
    if (count($return_url_array)) $return_url .= '?'.join('&', $return_url_array);
    $success_message = 'Resource successfully updated.';
    if (isset($page_info['course_subject_id'])) $db_events_array[] = array(COURSE_SUBJECTS_UPDATE_RESOURCE, $page_info['course_subject_id']);
    $db_events_array[] = array(RESOURCES_UPDATE, $page_info['resource_id']);
    break;
  /* 6 => delete resource */
  case 6:
    $delete_resource_query = <<< END_QUERY
      DELETE FROM resources
      WHERE resource_id = {$page_info['resource_id']}
      LIMIT 1
END_QUERY;
    $result = mysql_query($delete_resource_query, $site_info['db_conn']);
    /* if the resource is a reading, delete reading details */
    if (in_array($page_info['resource_type_id'], array(2, 22, 23, 24, 25, 35, 36, 54, 55, 57)))
    {
      $delete_reading_details_query = <<< END_QUERY
        DELETE FROM resource_details
        WHERE resource_id = {$page_info['resource_id']}
        LIMIT 1
END_QUERY;
      $result = mysql_query($delete_reading_details_query, $site_info['db_conn']);
    }
    /* if the resource is an exercise, delete questions and answers */
    if (in_array($page_info['resource_type_id'], array(26, 44)))
    {
      /* get question id's */
      $question_id_query = <<< END_QUERY
        SELECT question_id
        FROM questions
        WHERE test_id = {$page_info['resource_id']}
END_QUERY;
      $result = mysql_query($question_id_query, $site_info['db_conn']);
      $question_id_array = array();
      while ($record = mysql_fetch_array($result)) $question_id_array[] = $record['question_id'];
      $question_id_list = join(', ', $question_id_array);
      /* delete answers */
      $delete_answers_query = <<< END_QUERY
        DELETE FROM answers
        WHERE question_id IN ($question_id_list)
END_QUERY;
      $result = mysql_query($delete_answers_query, $site_info['db_conn']);
      /* delete questions */
      $delete_questions_query = <<< END_QUERY
        DELETE FROM questions
        WHERE test_id = {$page_info['resource_id']}
END_QUERY;
      $result = mysql_query($delete_questions_query, $site_info['db_conn']);
    }
    $success_message = 'Resource successfully deleted.';
    if (isset($page_info['course_subject_id'])) $db_events_array[] = array(COURSE_SUBJECTS_REMOVE_RESOURCE, $page_info['course_subject_id']);
    $db_events_array[] = array(RESOURCES_DELETE, $page_info['resource_id']);
    break;
}
vlc_insert_events($db_events_array);
vlc_exit_page($success_message, 'success', $return_url);
?>
