<?php
$page_info['section'] = 'register';
$page_info['page'] = 'diocese';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get form field values from session variable */
if (isset($_SESSION['form_fields'])) $form_fields = $_SESSION['form_fields'];
else vlc_redirect('register/');
/* set default partner id value if it is not set */
if (!isset($form_fields['partner_id'])) $form_fields['partner_id'] = -1;
/* build array for diocese drop down */
$diocese_array = array();
$diocese_query = <<< END_QUERY
  SELECT p.partner_id, IFNULL(s.code, c.description) AS state_country
  FROM partners AS p LEFT JOIN states AS s USING (state_id), countries AS c
  WHERE p.is_diocese = 1
  AND p.is_partner = 1
  AND p.country_id = c.country_id
END_QUERY;
$result = mysql_query($diocese_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $diocese_array[$record['partner_id']] = $lang['database']['partners'][$record['partner_id']].' ('.$record['state_country'].')';
asort($diocese_array);
/* build hidden fields */
$hidden_fields = '<input type="hidden" name="action_id" value="'.$form_fields['action_id'].'">';
$hidden_fields .= '<input type="hidden" name="course_id" value="'.$form_fields['course_id'].'">';
$hidden_fields .= '<input type="hidden" name="registration_type_id" value="'.$form_fields['registration_type_id'].'">';
$hidden_fields .= '<input type="hidden" name="discount_type_id" value="3">';
$hidden_fields .= '<input type="hidden" name="previous_discount_type_id" value="1">';

print $header;
?>
<!-- begin page content -->
<div class="container">
  <h1><?php echo $lang['register']['diocese']['heading']['diocese'] ?></h1>
  <div class="card">
    <div class="card-body">
      <div class="alert alert-info">
        <?php echo sprintf($lang['register']['diocese']['content']['intro'], $lang['register']['common']['misc']['previous-message'], $lang['register']['common']['misc']['cancel-message']) ?>
      </div>
      <form method="post" action="register_action.php">
        <?php echo $hidden_fields ?>
        <div class="form-group">
          <label for="partner_id">Select Diocese</label>
          <?php echo vlc_select_box($diocese_array, 'array', 'partner_id', $form_fields['partner_id'], false, 'form-field form-control custom-select') ?>
        </div>
        <input type="submit" name="previous" value="<?php echo $lang['register']['common']['form-fields']['previous-button'] ?>" class="submit-button btn btn-default" />
        <input type="submit" name="cancel" value="<?php echo $lang['register']['common']['form-fields']['cancel-button'] ?>" class="submit-button btn btn-danger" />
        <input type="submit" name="next" value="<?php echo $lang['register']['common']['form-fields']['next-button']?>" class="submit-button btn btn-vlc" />
      </form>
    </div>
  </div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
