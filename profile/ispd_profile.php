<?php
$uri = $_SERVER['REQUEST_URI'];
if (substr($uri, -1)== '/') {
header('Location: ' . rtrim($uri,"/"));
}
$page_info['section'] = 'profile';
$page_info['page'] = 'profile';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
if ($user_info['logged_in'] == true)
{
  $action = 'edit';
  $title = $lang['profile']['profile']['heading']['edit-profile'];
  /* if there is no status message or the status message is a success message, then get profile information from the database */
  if (strlen($status_message) == 0 or strpos($status_message, 'class="success"') or strpos($status_message, 'guest') or strpos($status_message, 'visitante'))
  {
    $profile_query = <<< END_QUERY
      SELECT IFNULL(u.prefix, '') AS prefix, IFNULL(u.first_name, '') AS first_name, IFNULL(u.middle_name, '') AS middle_name, IFNULL(u.last_name, '') AS last_name,
        IFNULL(u.suffix, '') AS suffix, IFNULL(u.nickname, '') AS nickname, IFNULL(i.address_1, '') AS address_1, IFNULL(i.address_2, '') AS address_2,
        IFNULL(i.city, '') AS city, IFNULL(i.state_id, -1) AS state_id, IFNULL(i.zip, '') AS zip, IFNULL(i.country_id, '') AS country_id, IFNULL(i.is_us_citizen, -1) AS is_us_citizen,
        IFNULL(i.diocese_id, -1) AS diocese_id, 
        IFNULL(i.parish, '') AS parish, 
		  IFNULL(i.primary_phone, '') AS primary_phone,
        IFNULL(i.secondary_phone, '') AS secondary_phone, IFNULL(i.primary_email, '') AS primary_email,
        IFNULL(i.primary_email, '') AS verify_primary_email, IFNULL(i.secondary_email, '') AS secondary_email,
        IFNULL(i.secondary_email, '') AS verify_secondary_email, IFNULL(i.gender_type_id, -1) AS gender_type_id,
        IFNULL(YEAR(i.birth_date), -1) AS birth_year, IFNULL(MONTH(i.birth_date), -1) AS birth_month, IFNULL(DAYOFMONTH(i.birth_date), -1) AS birth_day,
        IFNULL(i.marital_status_id, -1) AS marital_status_id, IFNULL(i.religion_id, -1) AS religion_id,
        IFNULL(i.race_type_id, -1) AS race_type_id, IFNULL(i.occupation_id, -1) AS occupation_id,
        IFNULL(i.biography, '') AS biography, IFNULL(i.fax, '') AS fax, IFNULL(i.url, '') AS url, IFNULL(i.title, '') AS title
      FROM users AS u, user_info AS i
      WHERE u.user_id = i.user_id
      AND u.user_id = {$user_info['user_id']}
END_QUERY;
    $result = mysql_query($profile_query, $site_info['db_conn']);
    $form_fields = mysql_fetch_array($result);
  }
  /* if there is a status message and the status message is not a success message, then get profile information from the form fields session variable */
  else
  {
    $form_fields = $_SESSION['form_fields'];
    $_SESSION['form_fields'] = null;
  }
}
else
{
  $action = 'create';
  $title = $lang['profile']['profile']['heading']['ispd-create-profile'];
  if (strlen($status_message) == 0)
  {
    $form_fields = array('username' => '', 'password' => '', 'prefix' => '', 'first_name' => '', 'middle_name' => '', 'last_name' => '', 'suffix' => '', 'nickname' => '', 'address_1' => '', 'address_2' => '', 'city' => '', 'state_id' => -1, 'zip' => '', 'country_id' => 222, 'is_us_citizen' => -1, 'diocese_id' => -1, 'parish' => '',  'primary_phone' => '', 'secondary_phone' => '', 'primary_email' => '', 'verify_primary_email' => '', 'secondary_email' => '', 'verify_secondary_email' => '', 'mm_subscribe' => 1, 'mm_text_only' => 0, 'gender_type_id' => -1, 'birth_month' => '', 'birth_day' => '', 'birth_year' => '', 'marital_status_id' => -1, 'religion_id' => -1, 'race_type_id' => -1, 'occupation_id' => -1, 'biography' => '', 'fax' => '', 'url' => '', 'title' => '');
  }
  else
  {
    $form_fields = $_SESSION['form_fields'];
    $_SESSION['form_fields'] = null;
  }
}
foreach ($form_fields as $key => $value)
{
  // if (is_string($value)) $form_fields[$key] = htmlspecialchars($value); commented out by bob 10/5/2015 to fix hidden data issue
}
/* build array for state drop down */
$state_array = array();
$state_query = <<< END_QUERY
  SELECT state_id, code, description
  FROM states
  ORDER BY description
END_QUERY;
$result = mysql_query($state_query, $site_info['db_conn']);
while ($record = mysql_fetch_array($result)) $state_array[$record['state_id']] = $record['description'].' ('.$record['code'].')';
/* build array for diocese drop down */
$diocese_array = array();
$diocese_query = <<< END_QUERY
  SELECT p.partner_id, p.is_partner, IFNULL(s.code, c.description) AS state_country
  FROM partners AS p LEFT JOIN states AS s USING (state_id), countries AS c
  WHERE p.is_diocese = 1
  AND (p.country_id = 222 OR p.is_partner = 1)
  AND p.country_id = c.country_id
END_QUERY;
$result = mysql_query($diocese_query, $site_info['db_conn']);
$diocese_array[1]['label'] = $lang['profile']['common']['misc']['partner'];
$diocese_array[0]['label'] = $lang['profile']['common']['misc']['non-partner'];
while ($record = mysql_fetch_array($result))
{
  if ($record['is_partner']) 
  	$diocese_array[1]['options'][$record['partner_id']] = $lang['database']['partners'][$record['partner_id']].' ('.$record['state_country'].')';
  else
  	$diocese_array[0]['options'][$record['partner_id']] = $lang['database']['partners'][$record['partner_id']].' ('.$record['state_country'].')';
}
asort($diocese_array[1]['options']);
asort($diocese_array[0]['options']);
/* sort drop down options */
asort($lang['database']['countries']);
asort($lang['database']['race-types']);
asort($lang['database']['occupations']);
asort($lang['database']['marital-status']);
asort($lang['database']['religions']);

?>
<div class="container">
<h1><?php print $title ?></h1>
<div class="col-md-8 col-sm-10 mb-3 mx-auto">
<form action="ispd_profile_action.php" method="post" onsubmit="return validate_form(this)">
  <input type="hidden" name="action" value="<?php print $action ?>">
  <div class="required text-right">
    <span class="required-example">*</span>&nbsp;<?php print $lang['profile']['profile']['misc']['required-field'] ?>
  </div>
  
<!-- begin form content within card -->
<div class="card">
<?php
/* when creating a new profile, users must enter username and password */
if ($action == 'create')
{
?>
  <div class="card-header">
    <h3><?php print $lang['profile']['profile']['misc']['username-password-heading'] ?></h3>
  </div>
  <div class="card-body"> <!-- begin userinfo section -->
    <div class="alert alert-info" role="alert">
      <?php print $lang['profile']['profile']['misc']['username-password-guidelines']?>
    </div>
    <div class="form-group">
      <label for="username" class="required"><?php print $lang['profile']['profile']['form-fields']['username'] ?></label>
      <input id="username" name="username" value="<?php print $form_fields['username'] ?>"
        type="text" maxlength="6" required="true" message="<?php print $lang['profile']['profile']['status']['username-required'] ?>"
        class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['username'] ?>" />
    </div>
    <div class="form-row">
      <div class="form-group col-sm-6">
        <label for="password" class="required"><?php print $lang['profile']['profile']['form-fields']['password'] ?></label>
        <input id="password" name="password" value=""
          type="password" maxlength="password" required="true" message="<?php print $lang['profile']['profile']['status']['password-required'] ?>"
          class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['password'] ?>" />
      </div>
      <div class="form-group col-sm-6">
        <label for="verify_password" class="required"><?php print $lang['profile']['profile']['form-fields']['verify-password'] ?></label>
        <input id="verify_password" name="verify_password" value=""
          type="password" maxlength="password" required="true" message="<?php print $lang['profile']['profile']['status']['verify-password'] ?>"
          class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['verify-password'] ?>" />
        <input type="hidden" name="link_fields" value="password:verify_password" message="<?php print $lang['profile']['profile']['status']['password-must-match'] ?>">
      </div>
    </div>
  </div> <!-- end userinfo section -->
<?php
} else { /* username cannot be edited, so display username in static text */
?>
  <div class="card-header"> 
    <h3><?php print $lang['profile']['profile']['misc']['username-heading']?></h3>
  </div>
  <div class="card-body"> <!-- begin userinfo section -->
    <div class="form-group row">
      <label for="username" class="col-4 col-form-label"><?php print $lang['profile']['profile']['form-fields']['username'] ?></label>
      <div class="col-8">
        <input id="username" name="username" value="<?php print $user_info['username']?>"
        readonly class="form-control-plaintext" />
      </div>
    </div>
  </div> <!-- end userinfo section -->
<?php
}
?>
  <div class="card-header card-footer"> 
    <h3><?php print $lang['profile']['profile']['misc']['name-personal-heading'] ?></h3>
  </div>
  <div class="card-body"> <!-- begin personal info section -->
    <div class="alert alert-info" role="alert"> 
      <?php print $lang['profile']['profile']['misc']['name-instructions'] ?>
    </div>
    <div class="form-row">
      <div class="form-group col">
        <label for="prefix"><?php print $lang['profile']['profile']['form-fields']['prefix'] ?></label>
        <input id="prefix" name="prefix" value="<?php print $form_fields['prefix']?>"
          type="text" maxlength="10" 
          class="form-field form-control" placeholder="<?php print $lang['profile']['profile']['form-fields']['prefix']?>" />
      </div>
      <div class="form-group col-sm-9">
        <label for="first_name" class="required"><?php print $lang['profile']['profile']['form-fields']['first-name']?></label>
        <input id="first_name" name="first_name" value="<?php print $form_fields['first_name'] ?>"
          type="text" required="true" message="<?php print $lang['profile']['profile']['status']['first-name-required']?>"
          class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['first-name']?>" />
      </div>
    </div>
    <div class="form-group">
      <label for="middle_name"><?php print $lang['profile']['profile']['form-fields']['middle-name'] ?></label>
      <input id="middle_name" name="middle_name" value="<?php print $form_fields['middle_name'] ?>"
        type="text"
        class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['middle-name']?>" />
    </div>
    <div class="form-row">
      <div class="form-group col-sm-9">
        <label for="last_name" class="required"><?php print $lang['profile']['profile']['form-fields']['last-name']?></label>
        <input id="last_name" name="last_name" value="<?php print $form_fields['last_name'] ?>"
          type="text" required="true" message="<?php print $lang['profile']['profile']['status']['last-name-required']?>"
          class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['last-name']?>" />
      </div>
      <div class="form-group col">
        <label for="suffix"><?php print $lang['profile']['profile']['form-fields']['suffix'] ?></label>
        <input id="suffix" name="suffix" value="<?php print $form_fields['suffix']?>"
          type="text" maxlength="10" 
          class="form-field form-control" placeholder="<?php print $lang['profile']['profile']['form-fields']['suffix']?>" />
      </div>
    </div>
    <div class="form-row">
      <div class="form-group col">
        <label for="nickname"><?php print $lang['profile']['profile']['form-fields']['nickname'] ?></label>
        <input id="nickname" name="nickname" value="<?php print $form_fields['nickname']?>"
          type="text" 
          class="form-field form-control" placeholder="<?php print $lang['profile']['profile']['form-fields']['nickname']?>" />
      </div>
      <div class="form-group col">
        <label for="title"><?php print $lang['profile']['profile']['form-fields']['title'] ?></label>
        <input id="title" name="title" value="<?php print $form_fields['title']?>"
          type="text" 
          class="form-field form-control" placeholder="<?php print $lang['profile']['profile']['form-fields']['title']?>" />
      </div>
    </div>
    <div class="form-group">
      <label class="required"><?php print $lang['profile']['profile']['form-fields']['birthdate'] ?></label>
      <div class="form-row">
        <div class="col-sm-6 form-group">
          <label for="birth_month" class="sr-only"><?php print $lang['profile']['profile']['form-fields']['birth-month'] ?></label>
          <?php print vlc_select_box($lang['common']['months']['full'], 'array', 'birth_month', $form_fields['birth_month'], true, 'custom-select form-control form-field')?>
        </div>
        <div class="col-sm-2 form-group">
          <label for="birth_day" class="sr-only"><?php print $lang['profile']['profile']['form-fields']['birth-day'] ?></label>
          <?php for ($i = 1; $i <= 31; $i++) $days[$i] = $i;
           print vlc_select_box($days, 'array', 'birth_day', $form_fields['birth_day'], true, 'custom-select form-control form-field') ?>
        </div>
        <div class="col-sm-4 form-group">
          <label for="birth_year" class="sr-only"><?php print $lang['profile']['profile']['form-fields']['birth-year'] ?></label>
          <?php for ($i = 1900; $i <= 2005; $i++) $years[$i] = $i;
           print vlc_select_box($years, 'array', 'birth_year', $form_fields['birth_year'], true, 'custom-select form-control form-field') ?>
        </div>
      </div>
    </div>
  </div> <!-- end personal info -->
  <div class="card-header card-footer"> 
    <h3><?php print $lang['profile']['profile']['misc']['address-phone-heading'] ?></h3>
  </div>
  <div class="card-body"> <!-- begin address/phone section -->
    <div class="form-group">
      <label for="address_1" class="required"><?php print $lang['profile']['profile']['form-fields']['address-1'] ?></label>
      <input id="address_1" name="address_1" value="<?php print $form_fields['address_1'] ?>"
        type="text" required="true" message="<?php print $lang['profile']['profile']['status']['address-required'] ?>"
        class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['address-1'] ?>" />
    </div>
    <div class="form-group">
      <label for="address_2"><?php print $lang['profile']['profile']['form-fields']['address-2'] ?></label>
      <input id="address_2" name="address_2" value="<?php print $form_fields['address_2'] ?>" 
        type="text" 
        class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['address-2'] ?>" />
    </div>
    <div class="form-row">
      <div class="col-sm-5 form-group">
        <label for="city" class="required"><?php print $lang['profile']['profile']['form-fields']['city'] ?></label>
        <input id="city" name="city" value="<?php print $form_fields['city'] ?>"
          type="text" required="true" message="<?php print $lang['profile']['profile']['status']['city-required'] ?>"
          class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['city'] ?>" /> 
      </div>
      <div class="col-sm-2 form-group">
        <label for="state_id"><?php print $lang['profile']['profile']['form-fields']['state'] ?></label>
        <?php print vlc_select_box($state_array, 'array', 'state_id', $form_fields['state_id'], false, 'custom-select form-control form-field') ?>
      </div>
      <div class="col-sm-5 form-group">
        <label for="zip" class="required"><?php print $lang['profile']['profile']['form-fields']['zip'] ?></label>
        <input id="zip" name="zip" value="<?php print $form_fields['zip'] ?>"
          type="text" maxlength="20" required="true" message="<?php print $lang['profile']['profile']['status']['zip-required'] ?>"
          class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['zip'] ?>" /> 
      </div>
    </div>
    <div class="form-group">
      <label for="country_id" class="required"><?php print $lang['profile']['profile']['form-fields']['country'] ?></label>
      <?php print vlc_select_box($lang['database']['countries'], 'array', 'country_id', $form_fields['country_id'], true, 'custom-select form-control form-field') ?>
    </div>
    <div class="form-row">
      <div class="col-sm-6 form-group">
        <label for="primary_phone" class="required"><?php print $lang['profile']['profile']['form-fields']['primary-phone'] ?></label>
        <input id="primary_phone" name="primary_phone" value="<?php print $form_fields['primary_phone'] ?>"
          type="text" maxlength="20" required="true" message="<?php print $lang['profile']['profile']['status']['phone-required'] ?>"
          class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['primary-phone'] ?>" />
      </div>
      <div class="col-sm-6 form-group">
        <label for="secondary_phone"><?php print $lang['profile']['profile']['form-fields']['secondary-phone'] ?></label>
        <input id="secondary_phone" name="secondary_phone" value="<?php print $form_fields['secondary_phone'] ?>"
          type="text" maxlength="20" 
          class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['secondary-phone'] ?>" />
      </div>
    </div>
  </div><!-- end address/phone section -->
  <div class="card-header card-footer">
    <h3><?php print $lang['profile']['profile']['misc']['email-address-heading'] ?></h3>
  </div>
  <div class="card-body"> <!-- begin email section -->
    <div class="form-row">
      <div class="col-sm-6 form-group">
        <label for="primary_email" class="required"><?php print $lang['profile']['profile']['form-fields']['primary-email'] ?></label>
        <input id="primary_email" name="primary_email" value="<?php print $form_fields['primary_email'] ?>"
          type="text" required="true" message="<?php print $lang['profile']['profile']['status']['email-required'] ?>"
          class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['primary-email'] ?>" />
      </div>
      <div class="col-sm-6 form-group">
        <label for="verify_primary_email" class="required"><?php print $lang['profile']['profile']['form-fields']['verify-primary-email'] ?></label>
        <input id="verify_primary_email" name="verify_primary_email" value="<?php print $form_fields['verify_primary_email'] ?>"
          type="text" required="true" message="<?php print $lang['profile']['profile']['status']['verify-email'] ?>"
          class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['verify-primary-email'] ?>" />
        <input type="hidden" name="link_fields" value="primary_email:verify_primary_email" message="<?php print $lang['profile']['profile']['status']['primary-email-must-match'] ?>"/>
      </div>
    </div>
    <div class="form-row">        
      <div class="col-sm-6 form-group">
        <label for="secondary_email"><?php print $lang['profile']['profile']['form-fields']['secondary-email'] ?></label>
        <input id="secondary_email" name="secondary_email" value="<?php print $form_fields['secondary_email'] ?>"
          type="text"
          class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['secondary-email'] ?>" />
      </div>
      <div class="col-sm-6 form-group">
        <label for="verify_secondary_email"><?php print $lang['profile']['profile']['form-fields']['verify-secondary-email'] ?></label>
        <input id="verify_secondary_email" name="verify_secondary_email" value="<?php print $form_fields['verify_secondary_email'] ?>"
          type="text"
          class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['verify-secondary-email'] ?>" />
        <input type="hidden" name="link_fields" value="secondary_email:verify_secondary_email" message="<?php print $lang['profile']['profile']['status']['secondary-email-must-match'] ?>"/>
      </div>
    </div>
  </div><!-- end email section -->
<?php
/* only show magnet mail newsletter fields when creating a new profile */
if ($action == 'create')
{
?>
  <div class="card-header card-footer">
    <h3><?php print $lang['profile']['profile']['misc']['email-newsletter-heading'] ?></h3>
  </div>
  <div class="card-body"> <!-- begin newsletter section -->
    <div class="form-group">
      <label class="custom-control custom-checkbox">
        <input id="mm_subscribe" name="mm_subscribe" 
          type="checkbox" <?php print ($form_fields['mm_subscribe'] ? 'checked="checked"' : '') ?> 
          class="custom-control-input" />
        <span class="custom-control-indicator"></span>
        <span class="custom-control-description"><?php print $lang['profile']['profile']['form-fields']['email-subscribe'] ?></span>
      </label>
    </div>
    <div class="form-group row">
      <label class="col">
        <?php print $lang['profile']['profile']['form-fields']['email-format'] ?>:
      </label>
      <label class="col custom-control custom-radio">
        <input id="text_only_radio_0" name="mm_text_only" 
          type="radio" <?php print ($form_fields['mm_text_only'] ? '' : 'checked="checked"') ?>
          class="custom-control-input" />
        <span class="custom-control-indicator"></span>
        <span class="custom-control-description"><?php print $lang['profile']['profile']['form-fields']['email-format-html'] ?></span>
      </label>
      <label class="col custom-control custom-radio">
        <input id="text_only_radio_1" name="mm_text_only" 
          type="radio" <?php print ($form_fields['mm_text_only'] ? 'checked="checked"' : '') ?>
          class="custom-control-input" />
        <span class="custom-control-indicator"></span>
        <span class="custom-control-description"><?php print $lang['profile']['profile']['form-fields']['email-format-text'] ?></span>
      </label>
    </div>
  </div> <!-- end newsletter section -->
<?php
}
?>
  <div class="card-header card-footer">
    <h3><?php print $lang['profile']['profile']['misc']['diocese-partner-heading'] ?></h3>
  </div>
  <div class="card-body"> <!-- begin diocese section -->
    <div class="form-group">
      <label for="diocese_id"><?php print $lang['profile']['profile']['form-fields']['diocese'] ?></label>
      <?php print vlc_select_box($diocese_array, 'array', 'diocese_id', $form_fields['diocese_id'], false, 'custom-select form-control form-field') ?>
    </div>
    <div class="form-group">
      <label for="parish"><?php print $lang['profile']['profile']['form-fields']['parish'] ?></label>
      <input id="parish" name="parish" value="<?php print $form_fields['parish'] ?>"
        type="text"
        class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['parish'] ?>" />
    </div>
  </div> <!-- end diocese section -->
  <div class="card-header card-footer">
    <h3><?php print $lang['profile']['profile']['misc']['miscellaneous-heading'] ?></h3>
  </div>
  <div class="card-body"> <!-- begin misc section -->
    <div class="form-group">
      <label for="is_us_citizen"><?php print $lang['profile']['profile']['form-fields']['citizen'] ?></label>
      <?php print vlc_select_box($lang['profile']['profile']['misc']['citizen-options'], 'array', 'is_us_citizen', $form_fields['is_us_citizen'], false, 'custom-select form-control form-field') ?>
    </div>
    <div class="form-row">
      <div class="col-sm-4 form-group">
        <label for="gender_type_id"><?php print $lang['profile']['profile']['form-fields']['gender'] ?></label>
        <?php print vlc_select_box($lang['database']['gender-types'], 'array', 'gender_type_id', $form_fields['gender_type_id'], false, 'custom-select form-control form-field') ?>
      </div>
      <div class="col-sm-8 form-group">
        <label for="race_type_id"><?php print $lang['profile']['profile']['form-fields']['race'] ?></label>
        <?php print vlc_select_box($lang['database']['race-types'], 'array', 'race_type_id', $form_fields['race_type_id'], false, 'custom-select form-control form-field') ?>
      </div>
    </div>
    <div class="form-group">
      <label for="occupation_id"><?php print $lang['profile']['profile']['form-fields']['occupation'] ?></label>
      <?php print vlc_select_box($lang['database']['occupations'], 'array', 'occupation_id', $form_fields['occupation_id'], false, 'custom-select form-control form-field') ?>
    </div>
    <div class="form-row">
      <div class="col-sm-6 form-group">
        <label for="marital_status_id"><?php print $lang['profile']['profile']['form-fields']['marital-status'] ?></label>
        <?php print vlc_select_box($lang['database']['marital-status'], 'array', 'marital_status_id', $form_fields['marital_status_id'], false, 'custom-select form-control form-field') ?>
      </div>
      <div class="col-sm-6 form-group">
        <label for="religion_id"><?php print $lang['profile']['profile']['form-fields']['religious-affiliation'] ?></label>
        <?php print vlc_select_box($lang['database']['religions'], 'array', 'religion_id', $form_fields['religion_id'], false, 'custom-select form-control form-field')?>
      </div>
    </div>
    <div class="form-group">
      <label for="url"><?php print $lang['profile']['profile']['form-fields']['home-page'] ?></label>
      <input id="url" name="url" value="<?php print $form_fields['url']?>"
        type="text"
        class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['home-page']?>"/>
    </div>
    <div class="form-group">
      <label for="biography"><?php print $lang['profile']['profile']['form-fields']['bio'] ?></label>
      <textarea id="biography" name="biography" rows="10" 
        class="form-control form-field" placeholder="<?php print $lang['profile']['profile']['form-fields']['bio'] ?>"><?php print $form_fields['biography'] ?></textarea>
    </div>
  </div> <!-- end misc section -->
  <div class="card-body">
    <button type="submit" class="btn btn-vlc submit-button" name="submit"><?php print $lang['profile']['profile']['form-fields']['submit-profile-button'] ?></button>
  </div>
</form>
</div> <!-- end card -->
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
