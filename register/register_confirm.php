<?php
$page_info['section'] = 'register';
$page_info['page'] = 'confirm';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
if (isset($_SESSION['course_details']) and isset($_SESSION['form_fields']))
{
  /* get course details from session variable */
  $course_details = $_SESSION['course_details'];
  /* get form field values from session variable */
  $form_fields = $_SESSION['form_fields'];
}
else vlc_redirect('register/');
/* build page content */
$page_content = '';
$confirmation_message = '';
/* create confirmation message based on action (register = 1, waiting list = 2) */
if ($form_fields['action_id'] == 1) $confirmation_message .= sprintf($lang['register']['confirm']['content']['register-message'], $course_details['description'], $course_details['code'], $lang['register']['common']['misc']['previous-message'], $lang['register']['common']['misc']['cancel-message']);
if ($form_fields['action_id'] == 2) $confirmation_message .= sprintf($lang['register']['confirm']['content']['wait-message'], $course_details['description'], $course_details['code'], $lang['register']['common']['misc']['previous-message'], $lang['register']['common']['misc']['cancel-message']);


$table_summary = '<table class="table table-striped w-50 mx-auto"><tbody>';
$table_summary .= '<tr><th scope="row">'.$lang['register']['common']['misc']['course-label'].'</th><td>'.$course_details['description'].' ('.$course_details['code'].')</td></tr>';
$table_summary .= '<tr><th scope="row">'.$lang['register']['common']['misc']['start-date-label'].'</th><td>'.$course_details['cycle_start'].'</td></tr>';
$table_summary .= '<tr><th scope="row">'.$lang['register']['common']['misc']['course-details-label'].'</th><td>'.$lang['database']['course-levels'][$course_details['course_level_id']].' / '.$lang['database']['course-types'][$course_details['course_type_id']].' / '.$course_details['ceu'].' '.$lang['register']['index']['misc']['ceu'].'</td></tr>';
$table_summary .= '<tr><th scope="row">'.$lang['register']['common']['misc']['facilitator-label'].'</th><td>'.$course_details['first_name'].' '.$course_details['last_name'].'</td></tr>';
$table_summary .= '<tr><th scope="row">'.$lang['register']['common']['misc']['discount-type-label'].'</th><td>'.$course_details['discount_type_description'].'</td></tr>';
$table_summary .= '<tr><th scope="row">'.$lang['register']['common']['misc']['discount-label'].'</th><td>'.$course_details['discount_description'].'</td></tr>';
$table_summary.= '<tr><th scope="row">'.$lang['register']['common']['misc']['course-cost-label'].'</th><td>'.$course_details['student_cost'].'</td></tr>';
$table_summary .= '</tbody></table>';

if ($form_fields['scoring_required'])
{
  $disabled = ' disabled';
  $hidden_field = '<input type="hidden" name="is_scored" value="1">';
}
else $disabled = $hidden_field = '';

$hidden_fields = $hidden_field;
$hidden_fields .= '<input type="hidden" name="action_id" value="'.$form_fields['action_id'].'">';
$hidden_fields .= '<input type="hidden" name="course_id" value="'.$form_fields['course_id'].'">';
$hidden_fields .= '<input type="hidden" name="registration_type_id" value="'.$form_fields['registration_type_id'].'">';
$hidden_fields .= '<input type="hidden" name="discount_type_id" value="'.$form_fields['discount_type_id'].'">';
$hidden_fields .= '<input type="hidden" name="previous_discount_type_id" value="'.$form_fields['previous_discount_type_id'].'">';
if (isset($form_fields['hidden_field'])) $hidden_fields .= $form_fields['hidden_field'];

print $header;
?>
<!-- begin page content -->
<div class="container">
  <h1><?php echo $lang['register']['confirm']['heading']['confirm'] ?></h1>
  <div class="card">
    <div class="card-body">
      <div class="alert alert-info">
        <?php echo $confirmation_message ?>
      </div>
      <?php echo $table_summary ?>
      <form method="post" action="register_action.php">
        <?php echo $hidden_fields ?>
        <div class="card card-body w-75 mx-auto">
          <?php echo $lang['register']['confirm']['content']['scoring-message'] ?>
          <div class="form-group">
            <label class="custom-control custom-radio">
              <input id="scoring-yes" name="is_scored" type="radio" value="1" checked<?php echo $disabled?> class="custom-control-input" />
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description"><?php echo $lang['register']['confirm']['form-fields']['yes'] ?></span>
            </label>
            <label class="custom-control custom-radio">
              <input id="scoring-no" name="is_scored" type="radio" value="0"<?php echo $disabled?>
                class="custom-control-input" />
              <span class="custom-control-indicator"></span>
              <span class="custom-control-description"><?php echo $lang['register']['confirm']['form-fields']['no'] ?></span>
            </label>
          </div>
        </div>
        <input type="submit" name="previous" value="<?php echo $lang['register']['common']['form-fields']['previous-button']?>" class="submit-button btn btn-default" />
        <input type="submit" name="cancel" value="<?php echo $lang['register']['common']['form-fields']['cancel-button']?>" class="submit-button btn btn-danger" />
        <input type="submit" name="confirm" value="<?php echo $lang['register']['confirm']['form-fields']['confirm-button']?>" class="submit-button btn btn-vlc" />
      </form>
    </div>
  </div>
</div>
<?php print $page_content ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
