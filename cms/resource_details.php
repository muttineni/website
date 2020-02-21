<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'resource-details';
$page_info['login_required'] = 1;
$page_info['course_subject_id'] = vlc_get_url_variable($site_info, 'subject', false);
$page_info['session_id'] = vlc_get_url_variable($site_info, 'session', false);
$page_info['resource_id'] = vlc_get_url_variable($site_info, 'resource', false);
$page_info['resource_type_id'] = vlc_get_url_variable($site_info, 'type', false);
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* initialize questions/answers variable */
$questions_answers = '';
/* get form fields from session variables (if form was submitted and validation errors occurred) */
if (strlen($status_message) > 0 and isset($_SESSION['form_fields']))
{
  $form_fields = $_SESSION['form_fields'];
  $_SESSION['form_fields'] = null;
  /* get action */
  $action = $form_fields['action'];
  /* get resource type description */
  $resource_type_query = <<< END_QUERY
    SELECT rt.description
    FROM resource_types AS rt
    WHERE rt.resource_type_id = {$page_info['resource_type_id']}
    LIMIT 1
END_QUERY;
  $result = mysql_query($resource_type_query, $site_info['db_conn']);
  $record = mysql_fetch_array($result);
  $resource_type = $record['description'];
}
/* get resource type (if adding a new resource) */
elseif (isset($page_info['resource_type_id']))
{
  /* action = 3 (add resource) */
  $action = 3;
  $resource_type_query = <<< END_QUERY
    SELECT rt.description
    FROM resource_types AS rt
    WHERE rt.resource_type_id = {$page_info['resource_type_id']}
    LIMIT 1
END_QUERY;
  $result = mysql_query($resource_type_query, $site_info['db_conn']);
  $record = mysql_fetch_array($result);
  $resource_type = $record['description'];
  $form_fields = array('language_id' => -1, 'display_order' => -1, 'title' => '', 'content' => '', 'url' => '', 'active_start_year' => 2004, 'active_start_month' => 1, 'active_start_day' => 1, 'active_end_year' => 2010, 'active_end_month' => 1, 'active_end_day' => 1, 'resource_format_id' => -1, 'author' => '', 'source' => '', 'notes' => '', 'isbn' => '', 'quickform' => '');
}
/* get resource details (if editing an existing resource) */
elseif (isset($page_info['resource_id']))
{
  /* action = 4 (edit resource) */
  $action = 4;
  $resource_details_query = <<< END_QUERY
    SELECT r.course_subject_id, r.session_id,
      r.resource_type_id, rt.description AS resource_type,
      IFNULL(r.display_order, 'NULL') AS display_order, IFNULL(r.language_id, 'NULL') AS language_id,
      IFNULL(r.title, '') AS title, IFNULL(r.content, '') AS content, IFNULL(r.url, '') AS url,
      YEAR(r.active_start) AS active_start_year, MONTH(r.active_start) AS active_start_month, DAYOFMONTH(r.active_start) AS active_start_day,
      YEAR(r.active_end) AS active_end_year, MONTH(r.active_end) AS active_end_month, DAYOFMONTH(r.active_end) AS active_end_day
    FROM resources AS r, resource_types AS rt
    WHERE r.resource_type_id = rt.resource_type_id
    AND r.resource_id = {$page_info['resource_id']}
    LIMIT 1
END_QUERY;
  $result = mysql_query($resource_details_query, $site_info['db_conn']);
  $form_fields = mysql_fetch_array($result);
  if (isset($form_fields['course_subject_id'])) $page_info['course_subject_id'] = $form_fields['course_subject_id'];
  else $page_info['course_subject_id'] = null;
  if (isset($form_fields['session_id'])) $page_info['session_id'] = $form_fields['session_id'];
  else $page_info['session_id'] = null;
  $form_fields['quickform'] = '';
  $page_info['resource_type_id'] = $form_fields['resource_type_id'];
  $resource_type = $form_fields['resource_type'];
  /* if the resource is a reading, get reading details from database */
  if (in_array($page_info['resource_type_id'], array(2, 22, 23, 24, 25, 35, 36, 54, 55, 57)))
  {
    $reading_details_query = <<< END_QUERY
      SELECT IFNULL(d.resource_format_id, -1) AS resource_format_id, IFNULL(d.author, '') AS author, IFNULL(d.source, '') AS source, IFNULL(d.notes, '') AS notes, IFNULL(d.isbn, '') AS isbn
      FROM resource_details AS d
      WHERE d.resource_id = {$page_info['resource_id']}
      LIMIT 1
END_QUERY;
    $result = mysql_query($reading_details_query, $site_info['db_conn']);
    $record = mysql_fetch_array($result);
    $form_fields = array_merge($form_fields, $record);
  }
  else
  {
    $form_fields = array_merge($form_fields, array('resource_format_id' => -1, 'author' => '', 'source' => '', 'notes' => '', 'isbn' => ''));
  }
  /* get questions and answers for exercises */
  if ($page_info['resource_type_id'] == 26)
  {
    $resource_type .= ' [<a href="#questions-answers">Scroll Down to Questions and Answers</a>]';
    /* get list of questions */
    $questions_query = <<< END_QUERY
      SELECT q.question_id, q.question_type_id, q.question, q.display_order
      FROM questions AS q
      WHERE q.test_id = {$page_info['resource_id']}
      ORDER BY q.display_order
END_QUERY;
    $result = mysql_query($questions_query, $site_info['db_conn']);
    /* build array for display order select box */
    for ($i = 1; $i <= 25; $i++) $display_order_array[$i] = $i;
    /* build test info array if there are questions */
    if (mysql_num_rows($result) > 0)
    {
      while ($record = mysql_fetch_array($result, MYSQL_ASSOC))
      {
        $test_info['questions'][$record['question_id']] = $record;
        $test_info['questions'][$record['question_id']]['answers'] = array();
      }
      /* get answer choices for multiple choice questions */
      $answers_query = <<< END_QUERY
        SELECT q.question_id, a.answer_id, a.answer, a.is_correct, a.display_order
        FROM answers AS a, questions AS q
        WHERE a.question_id = q.question_id
        AND q.test_id = {$page_info['resource_id']}
        ORDER BY q.question_id, a.display_order
END_QUERY;
      $result = mysql_query($answers_query, $site_info['db_conn']);
      while ($record = mysql_fetch_array($result, MYSQL_ASSOC)) $test_info['questions'][$record['question_id']]['answers'][$record['answer_id']] = $record;
      /* begin building list of questions and answers */
      $questions_answers .= '<tr><td colspan="2" align="center"><a name="questions-answers"></a><b>Exercise Questions and Answers:</b></td></tr>';
      foreach ($test_info['questions'] as $question_id => $question)
      {
        // $question['question'] = htmlspecialchars($question['question']);
        $questions_answers .= '<tr><td colspan="2">';
        $questions_answers .= vlc_select_box($display_order_array, 'array', 'questions['.$question_id.'][display_order]', $question['display_order'], true, 'form-field');
        $questions_answers .= '<input type="hidden" name="questions['.$question_id.'][previous_display_order]" value="'.$question['display_order'].'">';
        $questions_answers .= '&nbsp;<textarea name="questions['.$question_id.'][question]" style="vertical-align: top;" cols="60" rows="3" wrap="off" class="form-field">'.$question['question'].'</textarea>&nbsp;';
        $questions_answers .= '<input type="hidden" name="questions['.$question_id.'][previous_question]" value="'.$question['question'].'">';
        $questions_answers .= vlc_select_box('question_types', 'table', 'question_type_id', $question['question_type_id'], true, 'form-field', 'questions['.$question_id.'][question_type_id]');
        $questions_answers .= '<input type="hidden" name="questions['.$question_id.'][previous_question_type_id]" value="'.$question['question_type_id'].'">';
        $questions_answers .= '<input type="checkbox" name="questions['.$question_id.'][remove]" value="1" id="q'.$question_id.'">&nbsp;<label for="q'.$question_id.'">Remove</label>&nbsp;';
        $questions_answers .= '<ul>';
        /* list answers if there are answers for the current question */
        foreach ($question['answers'] as $answer_id => $answer)
        {
          // $answer['answer'] = htmlspecialchars($answer['answer']);
          $questions_answers .= '<li>';
          $questions_answers .= vlc_select_box($display_order_array, 'array', 'questions['.$question_id.'][answers]['.$answer_id.'][display_order]', $answer['display_order'], true, 'form-field');
          $questions_answers .= '<input type="hidden" name="questions['.$question_id.'][answers]['.$answer_id.'][previous_display_order]" value="'.$answer['display_order'].'">';
          $questions_answers .= '&nbsp;';
          $questions_answers .= vlc_select_box(array(0 => 'Incorrect', 1 => 'Correct'), 'array', 'questions['.$question_id.'][answers]['.$answer_id.'][is_correct]', $answer['is_correct'], true, 'form-field');
          $questions_answers .= '<input type="hidden" name="questions['.$question_id.'][answers]['.$answer_id.'][previous_is_correct]" value="'.$answer['is_correct'].'">';
          $questions_answers .= '&nbsp;<input type="text" name="questions['.$question_id.'][answers]['.$answer_id.'][answer]" value="'.$answer['answer'].'" size="60" class="form-field">';
          $questions_answers .= '<input type="hidden" name="questions['.$question_id.'][answers]['.$answer_id.'][previous_answer]" value="'.$answer['answer'].'">';
          $questions_answers .= '<input type="checkbox" name="questions['.$question_id.'][answers]['.$answer_id.'][remove]" value="1" id="a'.$answer_id.'">&nbsp;<label for="a'.$answer_id.'">Remove</label>&nbsp;';
          $questions_answers .= '</li>';
        }
        /* create empty field for adding additional answers */
        if (isset($answer['display_order'])) $next_display_order = $answer['display_order'] + 1;
        else $next_display_order = 1;
        $questions_answers .= '<li>Add an Answer:&nbsp;';
        $questions_answers .= vlc_select_box($display_order_array, 'array', 'questions['.$question_id.'][answers][a][display_order]', $next_display_order, true, 'form-field');
        $questions_answers .= '&nbsp;';
        $questions_answers .= vlc_select_box(array(0 => 'Incorrect', 1 => 'Correct'), 'array', 'questions['.$question_id.'][answers][a][is_correct]', -1, true, 'form-field');
        $questions_answers .= '&nbsp;<input type="text" name="questions['.$question_id.'][answers][a][answer]" value="" size="60" class="form-field">';
        $questions_answers .= '</li></ul>';
        $questions_answers .= '</td></tr>';
      }
    }
    /* create empty field for adding additional questions */
    if (isset($question['display_order'])) $next_display_order = $question['display_order'] + 1;
    else $next_display_order = 1;
    $questions_answers .= '<tr><td colspan="2">Add a Question:&nbsp;';
    $questions_answers .= vlc_select_box($display_order_array, 'array', 'questions[a][display_order]', $next_display_order, true, 'form-field');
    $questions_answers .= '&nbsp;<textarea name="questions[a][question]" style="vertical-align: top;" cols="60" rows="3" wrap="off" class="form-field"></textarea>&nbsp;';
    $questions_answers .= vlc_select_box('question_types', 'table', 'question_type_id', '', true, 'form-field', 'questions[a][question_type_id]');
    $questions_answers .= '<ul>';
    $j = 'a';
    for ($i = 1; $i < 5; $i++)
    {
      $questions_answers .= '<li>Add an Answer:&nbsp;';
      $questions_answers .= vlc_select_box($display_order_array, 'array', 'questions[a][answers]['.$j.'][display_order]', $i, true, 'form-field');
      $questions_answers .= '&nbsp;';
      $questions_answers .= vlc_select_box(array(0 => 'Incorrect', 1 => 'Correct'), 'array', 'questions[a][answers]['.$j.'][is_correct]', -1, true, 'form-field');
      $questions_answers .= '&nbsp;<input type="text" name="questions[a][answers]['.$j.'][answer]" value="" size="60" class="form-field">';
      $questions_answers .= '</li>';
      $j++;
    }
    $questions_answers .= '</ul>';
    $questions_answers .= '</td></tr>';
    $questions_answers .= '<tr><td colspan="2" align="center"><input type="submit" value="Save Resource"></td></tr>';
  }
}
/* exit page if neither resource id nor resource type id were provided */
else trigger_error('Either Resource ID or Resource Type ID Must be Provided.');
foreach ($form_fields as $key => $value)
{
  if ($key != 'content' and is_string($value)) $form_fields[$key] = $value;
}
/* get event details */
if (isset($page_info['resource_id']))
{
  $entity_id = $page_info['resource_id'];
  $event_type_array = array(
    RESOURCES_CREATE,
    RESOURCES_UPDATE
  );
  $event_history = vlc_get_event_history($event_type_array, $entity_id);
}
/* get course details (if applicable) */
if (isset($page_info['course_subject_id']))
{
  $course_details_query = <<< END_QUERY
    SELECT cs.description AS course_subject
    FROM course_subjects AS cs
    WHERE cs.course_subject_id = {$page_info['course_subject_id']}
    LIMIT 1
END_QUERY;
  $result = mysql_query($course_details_query, $site_info['db_conn']);
  $record = mysql_fetch_array($result);
  $course_subject = $record['course_subject'];
}
else $course_subject = 'N/A';
/* get session details (if applicable) */
if (isset($page_info['session_id']))
{
  $session_details_query = <<< END_QUERY
    SELECT s.description, s.display_order
    FROM sessions AS s
    WHERE s.course_subject_id = {$page_info['course_subject_id']}
    AND s.session_id = {$page_info['session_id']}
    LIMIT 1
END_QUERY;
  $result = mysql_query($session_details_query, $site_info['db_conn']);
  $record = mysql_fetch_array($result);
  $session_description = 'Session '.$record['display_order'].' ('.$record['description'].')';
}
else $session_description = 'N/A';
/*
** set required and disabled options for each resource type (all form fields)
** for text fields:
**    if the field is required, the array element's value will be: ' class="form-field-required" required="true" message="... is required."'
**    if the field is optional, the array element's value will be: ''
**    if the field is disabled, the array element's value will be: ' class="form-field-disabled" disabled'
** for select boxes:
**    select boxes are either required or disabled (not optional)
**    if the field is required, the array element's value will be: ''
**    if the field is disabled, the array element's value will be: 'null:n/a'
*/
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
  'language_id' =>        array('NULL' => 'N/A'),
  'display_order' =>      '',
  'title' =>              ' class="form-field-disabled" disabled',
  'content' =>            ' class="form-field-disabled" disabled',
  'url' =>                ' class="form-field-disabled" disabled',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field-disabled" disabled',
  'source' =>             ' class="form-field-disabled" disabled',
  'notes' =>              ' class="form-field-disabled" disabled',
  'isbn' =>               ' class="form-field-disabled" disabled',
  'quickform' =>          ' class="form-field-disabled" disabled'
);
/* course related websites (37), meeting place (46), key 1 (47) */
$field_options_array[37] = $field_options_array[46] = $field_options_array[47] = array
(
  'language_id' =>        array('NULL' => 'N/A'),
  'display_order' =>      array('NULL' => 'N/A'),
  'title' =>              ' class="form-field-disabled" disabled',
  'content' =>            ' class="form-field-disabled" disabled',
  'url' =>                ' class="form-field-disabled" disabled',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field-disabled" disabled',
  'source' =>             ' class="form-field-disabled" disabled',
  'notes' =>              ' class="form-field-disabled" disabled',
  'isbn' =>               ' class="form-field-disabled" disabled',
  'quickform' =>          ' class="form-field-disabled" disabled'
);
/* course introduction (11), session introduction (19) */
$field_options_array[11] = $field_options_array[19] = array
(
  'language_id' =>        array('NULL' => 'N/A'),
  'display_order' =>      array('NULL' => 'N/A'),
  'title' =>              ' class="form-field-disabled" disabled',
  'content' =>            ' class="form-field-required" required="true" message="Content is required."',
  'url' =>                ' class="form-field-disabled" disabled',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field-disabled" disabled',
  'source' =>             ' class="form-field-disabled" disabled',
  'notes' =>              ' class="form-field-disabled" disabled',
  'isbn' =>               ' class="form-field-disabled" disabled',
  'quickform' =>          ' class="form-field"'
);
/* vlcff announcement (1), new course announcement (56) */
$field_options_array[1] = $field_options_array[56] = array
(
  'language_id' =>        '',
  'display_order' =>      array('NULL' => 'N/A'),
  'title' =>              ' class="form-field-disabled" disabled',
  'content' =>            ' class="form-field-required" required="true" message="Content is required."',
  'url' =>                ' class="form-field-disabled" disabled',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field-disabled" disabled',
  'source' =>             ' class="form-field-disabled" disabled',
  'notes' =>              ' class="form-field-disabled" disabled',
  'isbn' =>               ' class="form-field-disabled" disabled',
  'quickform' =>          ' class="form-field-disabled" disabled'
);
/* vlcff announcement with audio (59) */
$field_options_array[59] = array
(
  'language_id' =>        '',
  'display_order' =>      array('NULL' => 'N/A'),
  'title' =>              ' class="form-field-disabled" disabled',
  'content' =>            ' class="form-field-required" required="true" message="Content is required."',
  'url' =>                ' class="form-field-required" required="true" message="URL is required."',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field-disabled" disabled',
  'source' =>             ' class="form-field-disabled" disabled',
  'notes' =>              ' class="form-field-disabled" disabled',
  'isbn' =>               ' class="form-field-disabled" disabled',
  'quickform' =>          ' class="form-field-disabled" disabled'
);
/* course objective (13), session objective (21) */
$field_options_array[13] = $field_options_array[21] = array
(
  'language_id' =>        array('NULL' => 'N/A'),
  'display_order' =>      '',
  'title' =>              ' class="form-field-disabled" disabled',
  'content' =>            ' class="form-field-disabled" disabled',
  'url' =>                ' class="form-field-disabled" disabled',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field-disabled" disabled',
  'source' =>             ' class="form-field-disabled" disabled',
  'notes' =>              ' class="form-field-disabled" disabled',
  'isbn' =>               ' class="form-field-disabled" disabled',
  'quickform' =>          ' class="form-field-required" required="true" message="QuickForm is required."'
);
/* discussion board (4) */
$field_options_array[4] = array
(
  'language_id' =>        array('NULL' => 'N/A'),
  'display_order' =>      '',
  'title' =>              ' class="form-field-required" required="true" message="Title is required."',
  'content' =>            ' class="form-field-required" required="true" message="Content is required."',
  'url' =>                ' class="form-field-disabled" disabled',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field-disabled" disabled',
  'source' =>             ' class="form-field-disabled" disabled',
  'notes' =>              ' class="form-field-disabled" disabled',
  'isbn' =>               ' class="form-field-disabled" disabled',
  'quickform' =>          ' class="form-field-disabled" disabled'
);
/* exercise (26) */
$field_options_array[26] = array
(
  'language_id' =>        array('NULL' => 'N/A'),
  'display_order' =>      '',
  'title' =>              ' class="form-field-required" required="true" message="Title is required."',
  'content' =>            ' class="form-field"',
  'url' =>                ' class="form-field-disabled" disabled',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field-disabled" disabled',
  'source' =>             ' class="form-field-disabled" disabled',
  'notes' =>              ' class="form-field-disabled" disabled',
  'isbn' =>               ' class="form-field-disabled" disabled',
  'quickform' =>          ' class="form-field"'
);
/* glossary (6) */
$field_options_array[6] = array
(
  'language_id' =>        array('NULL' => 'N/A'),
  'display_order' =>      '',
  'title' =>              ' class="form-field-disabled" disabled',
  'content' =>            ' class="form-field-disabled" disabled',
  'url' =>                ' class="form-field-disabled" disabled',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field-disabled" disabled',
  'source' =>             ' class="form-field-disabled" disabled',
  'notes' =>              ' class="form-field-disabled" disabled',
  'isbn' =>               ' class="form-field-disabled" disabled',
  'quickform' =>          ' class="form-field-required" required="true" message="QuickForm is required."'
);
/* session structure (17) */
$field_options_array[17] = array
(
  'language_id' =>        array('NULL' => 'N/A'),
  'display_order' =>      '',
  'title' =>              ' class="form-field-disabled" disabled',
  'content' =>            ' class="form-field-required" required="true" message="Content is required."',
  'url' =>                ' class="form-field-disabled" disabled',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field-disabled" disabled',
  'source' =>             ' class="form-field-disabled" disabled',
  'notes' =>              ' class="form-field-disabled" disabled',
  'isbn' =>               ' class="form-field-disabled" disabled',
  'quickform' =>          ' class="form-field-disabled" disabled'
);
/* optional reading/hardcopy (25), required reading/hardcopy (23) */
$field_options_array[25] = $field_options_array[23] = array
(
  'language_id' =>        array('NULL' => 'N/A'),
  'display_order' =>      '',
  'title' =>              ' class="form-field-required" required="true" message="Title is required."',
  'content' =>            ' class="form-field"',
  'url' =>                ' class="form-field-disabled" disabled',
  'resource_format_id' => '',
  'author' =>             ' class="form-field"',
  'source' =>             ' class="form-field"',
  'notes' =>              ' class="form-field"',
  'isbn' =>               ' class="form-field"',
  'quickform' =>          ' class="form-field-disabled" disabled'
);
/* optional reading/online - external website (36), required reading/online - external website (35) */
$field_options_array[36] = $field_options_array[35] = array
(
  'language_id' =>        array('NULL' => 'N/A'),
  'display_order' =>      '',
  'title' =>              ' class="form-field-required" required="true" message="Title is required."',
  'content' =>            ' class="form-field-disabled" disabled',
  'url' =>                ' class="form-field-required" required="true" message="URL is required."',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field"',
  'source' =>             ' class="form-field"',
  'notes' =>              ' class="form-field"',
  'isbn' =>               ' class="form-field"',
  'quickform' =>          ' class="form-field-disabled" disabled'
);
/* optional reading/online - vlcff (24), required reading/online - vlcff (22) */
$field_options_array[24] = $field_options_array[22] = array
(
  'language_id' =>        array('NULL' => 'N/A'),
  'display_order' =>      '',
  'title' =>              ' class="form-field-required" required="true" message="Title is required."',
  'content' =>            ' class="form-field-required" required="true" message="Content is required."',
  'url' =>                ' class="form-field-disabled" disabled',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field"',
  'source' =>             ' class="form-field"',
  'notes' =>              ' class="form-field"',
  'isbn' =>               ' class="form-field"',
  'quickform' =>          ' class="form-field-disabled" disabled'
);
/* vlcff news article (2) */
$field_options_array[2] = array
(
  'language_id' =>        '',
  'display_order' =>      array('NULL' => 'N/A'),
  'title' =>              ' class="form-field-required" required="true" message="Title is required."',
  'content' =>            ' class="form-field-required" required="true" message="Content is required."',
  'url' =>                ' class="form-field"',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field"',
  'source' =>             ' class="form-field"',
  'notes' =>              ' class="form-field"',
  'isbn' =>               ' class="form-field"',
  'quickform' =>          ' class="form-field-disabled" disabled'
);
/* course summary (53) */
$field_options_array[53] = array
(
  'language_id' =>        array('NULL' => 'N/A'),
  'display_order' =>      array('NULL' => 'N/A'),
  'title' =>              ' class="form-field-disabled" disabled',
  'content' =>            ' class="form-field-required" required="true" message="Content is required."',
  'url' =>                ' class="form-field-disabled" disabled',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field-disabled" disabled',
  'source' =>             ' class="form-field-disabled" disabled',
  'notes' =>              ' class="form-field-disabled" disabled',
  'isbn' =>               ' class="form-field-disabled" disabled',
  'quickform' =>          ' class="form-field-disabled" disabled'
);
/* newsletter (57) */
$field_options_array[57] = array
(
  'language_id' =>        '',
  'display_order' =>      array('NULL' => 'N/A'),
  'title' =>              ' class="form-field-required" required="true" message="Title is required."',
  'content' =>            ' class="form-field-required" required="true" message="Content is required."',
  'url' =>                ' class="form-field-disabled" disabled',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field"',
  'source' =>             ' class="form-field-disabled" disabled',
  'notes' =>              ' class="form-field-disabled" disabled',
  'isbn' =>               ' class="form-field-disabled" disabled',
  'quickform' =>          ' class="form-field-disabled" disabled'
);
/* video (58) */
$field_options_array[58] = array
(
  'language_id' =>        array('NULL' => 'N/A'),
  'display_order' =>      '',
  'title' =>              ' class="form-field-required" required="true" message="Title is required."',
  'content' =>            ' class="form-field"',
  'url' =>                ' class="form-field-required" required="true" message="URL is required."',
  'resource_format_id' => array('NULL' => 'N/A'),
  'author' =>             ' class="form-field-disabled" disabled',
  'source' =>             ' class="form-field-disabled" disabled',
  'notes' =>              ' class="form-field-disabled" disabled',
  'isbn' =>               ' class="form-field-disabled" disabled',
  'quickform' =>          ' class="form-field-disabled" disabled'
);
/* required course material (54), optional course material (55) */
$field_options_array[54] = $field_options_array[55] = array
(
  'language_id' =>        array('NULL' => 'N/A'),
  'display_order' =>      '',
  'title' =>              ' class="form-field-required" required="true" message="Title is required."',
  'content' =>            ' class="form-field"',
  'url' =>                ' class="form-field-disabled" disabled',
  'resource_format_id' => '',
  'author' =>             ' class="form-field"',
  'source' =>             ' class="form-field"',
  'notes' =>              ' class="form-field"',
  'isbn' =>               ' class="form-field"',
  'quickform' =>          ' class="form-field-disabled" disabled'
);
/* if editing the resource (action="edit"), revise field options array for resource types that use the quickform */
if ($action == 4)
{
  /* course introduction, session introduction */
  $field_options_array[1]['quickform'] = $field_options_array[11]['quickform'] = $field_options_array[19]['quickform'] = ' class="form-field-disabled" disabled';
  /* course objectives, session objectives */
  $field_options_array[13]['content'] = $field_options_array[21]['content'] = ' class="form-field-required" required="true" message="Content is required."';
  $field_options_array[13]['quickform'] = $field_options_array[21]['quickform'] = ' class="form-field-disabled" disabled';
  /* exercises */
  $field_options_array[26]['quickform'] = ' class="form-field-disabled" disabled';
  /* glossary */
  $field_options_array[6]['content'] = ' class="form-field-required" required="true" message="Content is required."';
  $field_options_array[6]['quickform'] = ' class="form-field-disabled" disabled';
}
/* select box for language */
if (is_array($field_options_array[$page_info['resource_type_id']]['language_id'])) $language_dropdown = vlc_select_box($field_options_array[$page_info['resource_type_id']]['language_id'], 'array', 'language_id', $form_fields['language_id'], true);
else $language_dropdown = vlc_select_box('languages', 'table', 'language_id', $form_fields['language_id'], true);
/* select box for resource format */
if (is_array($field_options_array[$page_info['resource_type_id']]['resource_format_id'])) $resource_format_dropdown = vlc_select_box($field_options_array[$page_info['resource_type_id']]['resource_format_id'], 'array', 'resource_format_id', $form_fields['resource_format_id'], true);
else $resource_format_dropdown = vlc_select_box('resource_formats', 'table', 'resource_format_id', $form_fields['resource_format_id'], true);
/* select box for display order (options are either "n/a" or the values 1-20) */
$display_order_array = array();
if (is_array($field_options_array[$page_info['resource_type_id']]['display_order'])) $display_order_array = $field_options_array[$page_info['resource_type_id']]['display_order'];
else
{
  for ($i = 1; $i <= 25; $i++) $display_order_array[$i] = $i;
}
$display_order_dropdown = vlc_select_box($display_order_array, 'array', 'display_order', $form_fields['display_order'], true);
/* month, day, year arrays */
for ($i = 1; $i <= 12; $i++) $months_array[$i] = date('F', mktime(0,0,0,$i,1,0));
for ($i = 1; $i <= 31; $i++) $days_array[$i] = $i;
for ($i = 2000; $i <= 2020; $i++) $years_array[$i] = $i;
/* select boxes for active start dates */
$active_start_month_dropdown = vlc_select_box($months_array, 'array', 'active_start_month', $form_fields['active_start_month'], true);
$active_start_day_dropdown = vlc_select_box($days_array, 'array', 'active_start_day', $form_fields['active_start_day'], true);
$active_start_year_dropdown = vlc_select_box($years_array, 'array', 'active_start_year', $form_fields['active_start_year'], true);
/* select boxes for active end dates */
$active_end_month_dropdown = vlc_select_box($months_array, 'array', 'active_end_month', $form_fields['active_end_month'], true);
$active_end_day_dropdown = vlc_select_box($days_array, 'array', 'active_end_day', $form_fields['active_end_day'], true);
$active_end_year_dropdown = vlc_select_box($years_array, 'array', 'active_end_year', $form_fields['active_end_year'], true);
/* build form action string */
$form_action = 'resource_action.php';
$form_action_array = array();
if (isset($page_info['course_subject_id'])) $form_action_array[] = 'subject='.$page_info['course_subject_id'];
if (isset($page_info['session_id'])) $form_action_array[] = 'session='.$page_info['session_id'];
if (isset($page_info['resource_id'])) $form_action_array[] = 'resource='.$page_info['resource_id'];
if (isset($page_info['resource_type_id'])) $form_action_array[] = 'type='.$page_info['resource_type_id'];
if (count($form_action_array)) $form_action .= '?'.join('&', $form_action_array);
/* link to return to course outline */
if (isset($page_info['course_subject_id'])) $return_link = '<p>'.vlc_internal_link('Return to Course Subject Details', 'cms/course_subject_details.php?subject='.$page_info['course_subject_id']).'</p>';
elseif (isset($_SERVER['HTTP_REFERER']) and $return_url = strstr($_SERVER['HTTP_REFERER'], 'cms/')) $return_link = '<p>'.vlc_internal_link('Return to Previous Page', $return_url).'</p>';
else $return_link = '';
/* rte fields */
$content_field = vlc_rte_field('content', $form_fields['content']);
/* build resource form */
$output = <<< END_FORM
<div align="center">
$return_link
<p><b>* Note: Required fields are purple - Optional fields are white - Disabled fields are grey</b></p>
<form action="$form_action" method="post" onsubmit="updateRTEs();">
<input type="hidden" name="action" value="$action">
<table border="1" cellpadding="5" cellspacing="0">
<tr>
  <td align="right"><b>Course&nbsp;Subject:</b></td>
  <td>$course_subject</td>
</tr>
<tr>
  <td align="right"><b>Session:</b></td>
  <td>$session_description</td>
</tr>
<tr>
  <td align="right"><b>Resource&nbsp;Type:</b></td>
  <td>$resource_type</td>
</tr>
<tr><td colspan="2" align="center"><input type="submit" value="Save Resource"></td></tr>
<tr>
  <td align="right"><b>Language:</b></td>
  <td>$language_dropdown</td>
</tr>
<tr>
  <td align="right"><b>Display&nbsp;Order:</b></td>
  <td>$display_order_dropdown</td>
</tr>
<tr>
  <td align="right"><b>Title:</b></td>
  <td><input type="text" name="title" value="{$form_fields['title']}" size="50" maxlength="255"{$field_options_array[$page_info['resource_type_id']]['title']}></td>
</tr>
<tr>
  <td colspan="2" align="center"><b>Content:</b></td>
</tr>
<tr>
  <td colspan="2">
$content_field
  </td>
</tr>
<tr>
  <td align="right"><b>URL:</b></td>
  <td><input type="text" name="url" value="{$form_fields['url']}" size="50" maxlength="255"{$field_options_array[$page_info['resource_type_id']]['url']}></td>
</tr>
<tr>
  <td align="right"><b>Start&nbsp;Date:</b></td>
  <td><nobr>$active_start_month_dropdown&nbsp;$active_start_day_dropdown&nbsp;$active_start_year_dropdown <img src="{$site_info['js_url']}calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == 'block') closeCalendar(); else displayCalendarSelectBox(document.forms[0].active_start_year,document.forms[0].active_start_month,document.forms[0].active_start_day,false,false,this);"></nobr></td>
</tr>
<tr>
  <td align="right"><b>End&nbsp;Date:</b></td>
  <td><nobr>$active_end_month_dropdown&nbsp;$active_end_day_dropdown&nbsp;$active_end_year_dropdown <img src="{$site_info['js_url']}calendar/calendar.gif" style="cursor: pointer; vertical-align: top;" onclick="if (calendarDiv && calendarDiv.style.display == 'block') closeCalendar(); else displayCalendarSelectBox(document.forms[0].active_end_year,document.forms[0].active_end_month,document.forms[0].active_end_day,false,false,this);"></nobr></td>
</tr>
<tr><td colspan="2" align="center"><input type="submit" value="Save Resource"></td></tr>
<tr>
  <td align="right"><b>Resource Format (For Hardcopy Materials Only):</b></td>
  <td>$resource_format_dropdown</td>
</tr>
<tr>
  <td align="right"><b>Author (For Readings and Course Materials Only):</b></td>
  <td><input type="text" name="author" value="{$form_fields['author']}" size="50" maxlength="255"{$field_options_array[$page_info['resource_type_id']]['author']}></td>
</tr>
<tr>
  <td align="right"><b>Source (For Readings and Course Materials Only):</b></td>
  <td><input type="text" name="source" value="{$form_fields['source']}" size="50" maxlength="255"{$field_options_array[$page_info['resource_type_id']]['source']}></td>
</tr>
<tr>
  <td align="right"><b>Notes (For Readings and Course Materials Only):</b></td>
  <td><input type="text" name="notes" value="{$form_fields['notes']}" size="50" maxlength="255"{$field_options_array[$page_info['resource_type_id']]['notes']}></td>
</tr>
<tr>
  <td align="right"><b>ISBN (For Readings and Course Materials Only):</b></td>
  <td><input type="text" name="isbn" value="{$form_fields['isbn']}" size="50" maxlength="255"{$field_options_array[$page_info['resource_type_id']]['isbn']}></td>
</tr>
<tr>
  <td align="right"><b>QuickForm Toolbar:</b></td>
  <td align="center">
    <input type="button" value="Strip White Space" onclick="replace_white_space(this.form.quickform, '', '', false, false);">
    <input type="button" value="Add Dashes" onclick="replace_white_space(this.form.quickform, '-', '', false, false);">
    <input type="button" value="Remove Newlines" onclick="remove_newlines(this.form.quickform);">
    <input type="button" value="Copy Text" onclick="select_all(this.form.quickform, 'copy');">
    <br>[<a href="help.php#quickform" target="_blank">Click here for help with the QuickForm Toolbar</a>]
  </td>
</tr>
<tr>
  <td colspan="2" align="center"><b>QuickForm:</b></td>
</tr>
<tr>
  <td colspan="2" align="center">
    <textarea name="quickform" wrap="off" cols="100" rows="20"{$field_options_array[$page_info['resource_type_id']]['quickform']}>{$form_fields['quickform']}</textarea>
  </td>
</tr>
<tr><td colspan="2" align="center"><input type="submit" value="Save Resource"></td></tr>
$questions_answers
</table>
</form>
</div>
END_FORM;
if (isset($page_info['resource_id'])) $output .= $event_history;
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
