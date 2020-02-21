<?php
$page_info['section'] = 'certificates';
$page_info['page'] = 'es-ministerio-lev-2-app';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get catechesis certificate application status from session variable */
if (!isset($_SESSION['es_ministerio_cert_app_status']) or $_SESSION['es_ministerio_cert_app_status'] != 3) vlc_redirect('certificates/es_ministerio.php');
$user_info_query = <<< END_QUERY
  SELECT u.user_id, CONCAT(u.first_name, ' ', u.last_name) AS name, u.username, i.primary_email AS email_address, IFNULL(i.diocese_id, 0) AS diocese_id, IFNULL(p.description, '') AS diocese, IFNULL(p.is_partner, 0) AS is_partner
  FROM users AS u, user_info AS i
    LEFT JOIN partners AS p ON i.diocese_id = p.partner_id
  WHERE u.user_id = i.user_id
  AND u.user_id = {$user_info['user_id']}
END_QUERY;
$result = mysql_query($user_info_query, $site_info['db_conn']);
$user_details = mysql_fetch_array($result);
if (strlen($user_details['diocese']))
{
  if ($user_details['is_partner']) $user_details['diocese'] .= ' ('.$lang['certificates']['es-ministerio-lev-2-app']['content']['partner'].')';
  else $user_details['diocese'] .= ' ('.$lang['certificates']['es-ministerio-lev-2-app']['content']['non-partner'].')';
}
else
{
  $user_details['diocese'] = $lang['certificates']['es-ministerio-lev-2-app']['content']['no-diocese'];
}
$hidden_fields_string = '<input type="hidden" name="cert_prog_id" value="17">';
$hidden_fields_string .= '<input type="hidden" name="app_lang" value="2">';
$hidden_fields_string .= '<input type="hidden" name="certificate_program" value="'.$lang['certificates']['es-ministerio-lev-2-app']['content']['cert-prog'].'">';
foreach ($user_details as $name => $value)
{
  $hidden_fields_string .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
}
if (strlen($status_message))
{
  $form_fields = $_SESSION['form_fields'];
  $_SESSION['form_fields'] = null;
}
else
{
  $form_fields = array('parish_school_name' => '', 'parish_program' => '', 'parish_program_grade_level' => '', 'catholic_school' => '', 'catholic_school_grade_level' => '', 'other_ministry' => '', 'workshops_or_courses' => '', 'reason_to_participate' => '');
}
foreach ($form_fields as $key => $value)
{
  if (is_string($value)) $form_fields[$key] = htmlspecialchars($value);
}
print $header;
?>
<!-- begin page content -->
<div class="container">
<h1><?php print $lang['certificates']['es-ministerio-lev-2-app']['page-title'] ?></h1>
<div class="card"><form action="cert_app_action.php" method="post" onsubmit="return validate_form(this)">
  <div class="card-body">
    <div class="alert alert-info">
      <ul>
        <li><?php print $lang['certificates']['es-ministerio-lev-2-app']['content']['app-fee'] ?></li>
        <li><?php print $lang['certificates']['es-ministerio-lev-2-app']['content']['all-fields-required'] ?></li>
      </ul>
    </div>
    <?php echo $hidden_fields_string ?>
    <div class="form-group row">
      <label for="display_date" class="col-sm-3 col-form-label">
        <?php echo $lang['certificates']['es-ministerio-lev-2-app']['content']['date'] ?>
      </label>
      <div class="col-sm-9">
        <input type="text" name="display_date" readonly class="form-control-plaintext" value="<?php echo date('m/d/Y')?>" />
      </div>
    </div>
    <div class="form-group row">
      <label for="display_name" class="col-sm-3 col-form-label">
        <?php echo $lang['certificates']['es-ministerio-lev-2-app']['content']['name'] ?>
      </label>
      <div class="col-sm-9">
        <input type="text" name="display_name" readonly class="form-control-plaintext" value="<?php echo $user_details['name']?>" />
      </div>
    </div>        
    <div class="form-group row">
      <label for="display_username" class="col-sm-3 col-form-label">
        <?php echo $lang['certificates']['es-ministerio-lev-2-app']['content']['username'] ?>
      </label>
      <div class="col-sm-9">
        <input type="text" name="display_username" readonly class="form-control-plaintext" value="<?php echo $user_details['username']?>"   />
      </div>
    </div>        
    <div class="form-group row">
      <label for="display_email" class="col-sm-3 col-form-label">
        <?php echo $lang['certificates']['es-ministerio-lev-2-app']['content']['email-address'] ?>
      </label>
      <div class="col-sm-9">
        <input type="text" name="display_email" readonly class="form-control-plaintext" 
          value="<?php echo $user_details['email_address']?>" />
      </div>
    </div>        
    <div class="form-group row">
      <label for="display_diocese" class="col-sm-3 col-form-label">
        <?php echo $lang['certificates']['es-ministerio-lev-2-app']['content']['diocese'] ?>
      </label>
      <div class="col-sm-9">
        <input type="text" name="display_diocese" readonly class="form-control-plaintext" value="<?php echo $user_details['diocese']?>" />
      </div>
    </div>        
    <div class="form-group row">
      <label for="parish_school_name" class="col-sm-3 col-form-label">
        <?php echo $lang['certificates']['es-ministerio-lev-2-app']['content']['parish-school-name'] ?>
      </label>
      <div class="col-sm-9">
        <input type="text" name="parish_school_name" id="parish_school_name" class="form-control form-field" value="<?php echo $user_detail['parish_school_name']?>" />
      </div>
    </div>
  </div><!-- end card body -->
  <div class="card-body">
    <div class="alert alert-info">
      <?php echo $lang['certificates']['es-ministerio-lev-2-app']['content']['current-ministry'] ?>
    </div>
    <div class="form-group row">
      <label for="parish_program" class="col-sm-3 col-form-label">
        <?php echo $lang['certificates']['es-ministerio-lev-2-app']['content']['parish-program'] ?>
      </label>
      <div class="col-sm-9">
        <input type="text" name="parish_program" id="parish_program" class="form-control form-field" value="<?php echo $user_detail['parish_program']?>" />
      </div>
    </div>
    <div class="form-group row">
      <label for="parish_program_grade_level" class="col-sm-3 col-form-label">
        <i class="fa fa-arrow-right"></i> <?php echo $lang['certificates']['es-ministerio-lev-2-app']['content']['grade-level'] ?>
      </label>
      <div class="col-sm-9">
        <input type="text" name="parish_program_grade_level" id="parish_program_grade_level" class="form-control form-field" value="<?php echo $user_detail['parish_program_grade_level']?>" />
      </div>
    </div>
    <div class="form-group row">
      <label for="catholic_school" class="col-sm-3 col-form-label">
        <?php echo $lang['certificates']['es-ministerio-lev-2-app']['content']['catholic-school'] ?>
      </label>
      <div class="col-sm-9">
        <input type="text" name="catholic_school" id="catholic_school" class="form-control form-field" value="<?php echo $user_detail['catholic_school']?>" />
      </div>
    </div>
    <div class="form-group row">
      <label for="catholic_school_grade_level" class="col-sm-3 col-form-label">
        <i class="fa fa-arrow-right"></i> <?php echo $lang['certificates']['es-ministerio-lev-2-app']['content']['grade-level'] ?>
      </label>
      <div class="col-sm-9">
        <input type="text" name="catholic_school_grade_level" id="catholic_school_grade_level" class="form-control form-field" value="<?php echo $user_detail['catholic_school_grade_level']?>" />
      </div>
    </div>
  </div> <!-- end card body -->
  <div class="card-header card-footer">
    <h3>Extended Answer</h3>
  </div>
  <div class="card-body">
    <div class="form-group">
      <label for="reason_to_participate">
        <?php echo $lang['certificates']['es-ministerio-lev-2-app']['content']['reason-to-participate'] ?>
      </label>
      <textarea name="reason_to_participate" id="reason_to_participate" class="form-field form-control" cols="80" rows="10"><?php print $form_fields['reason_to_participate'] ?></textarea>
    </div>
    <div class="form-group">
      <label for="workshops_or_courses">
        <?php echo $lang['certificates']['es-ministerio-lev-2-app']['content']['workshops-courses'] ?>
      </label>
      <textarea name="workshops_or_courses" id="workshops_or_courses" class="form-field form-control" cols="80" rows="10"><?php print $form_fields['workshops_or_courses'] ?></textarea>
    </div>      
    <div class="form-group">
      <label for="other_ministry">
        <?php echo $lang['certificates']['es-ministerio-lev-2-app']['content']['other-ministry'] ?>
      </label>
      <textarea name="other_ministry" id="other_ministry" class="form-field form-control" cols="80" rows="10"><?php print $form_fields['other_ministry'] ?></textarea>
    </div>
    <input type="submit" class="submit-button btn btn-vlc" value="<?php print $lang['certificates']['es-ministerio-lev-2-app']['content']['submit'] ?>">
  </div>
</form></div> <!-- end form end card -->
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;