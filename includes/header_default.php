<?php
/* get specific css file */
$custom_css = $page_info['section'].'.css';

/* switch language link */
$current_url = substr($_SERVER['PHP_SELF'], strlen($site_info['home_url']));
$current_url = str_replace('index.php', '', $current_url);
$current_url .= '?';

foreach ($_GET as $key => $value) {
    if ($key != 'lang') {
        $current_url .= $key.'='. $value .'&';
    }
}

// Sanitize URL
$current_url = htmlspecialchars($current_url);

$switch_language_url = $current_url.'lang='.$lang['common']['misc']['switch-language-code'];
$switch_language_link = vlc_internal_link('<i class="fa fa-comments"></i>'.'<span class="hidden-sm-down user-text"> '.$lang['common']['misc']['switch-language-link'].'</span>', $switch_language_url, 'nav-link');

/* login links */
if ($user_info['logged_in'] == false)
{
  $login_link = '<li class="nav-item"><a class="nav-link" href="#" data-toggle="modal" data-target="#login-modal" aria-haspopup="true" aria-expanded="false" aria-label="login"> <i class="fa fa-user"></i> <span class="hidden-xs-down user-text">'.$lang['common']['sidebar']['form-fields']['log-in-button'].'</span></a></li>';
}else{
    $login_link =  '<li class="nav-item">'.vlc_internal_link('<i class="fa fa-user"></i>'.'<span class="hidden-xs-down user-text"> '.sprintf($lang['common']['misc']['logged-in'], $user_info['username']).'</span>', 'profile/', 'nav-link').'</li>'.'<li class="nav-item">'.vlc_internal_link('<i class="fa fa-sign-out"></i>'.'<span class="hidden-xs-down user-text">'.$lang['common']['misc']['log-out-link'].'</span>', 'profile/logout_action.php', 'nav-link').'</li>';
}

$forgot_password = vlc_internal_link($lang['common']['sidebar']['misc']['forgot-password-link'], 'profile/forgot_password.php');

/* courses dropdown links */
$courses_info_link = vlc_internal_link($lang['common']['navigation']['course-dropdown']['info'], 'courses/', 'dropdown-item');
$courses_fees_link = vlc_internal_link($lang['common']['navigation']['course-dropdown']['fees'], 'courses/payment_policy.php', 'dropdown-item');
$courses_catalog_link = vlc_internal_link($lang['common']['navigation']['course-dropdown']['catalog'], 'courses/courses.php?group_by_track=1', 'dropdown-item');
$courses_facilitators_link = vlc_internal_link($lang['common']['navigation']['course-dropdown']['facilitators'], 'courses/facilitators.php', 'dropdown-item');
$courses_guidelines_link = vlc_internal_link($lang['common']['navigation']['course-dropdown']['guidelines'], 'courses/guidelines.php', 'dropdown-item');
$courses_rubric_link = vlc_internal_link($lang['common']['navigation']['course-dropdown']['rubric'], 'courses/scoring.php', 'dropdown-item');
$courses_credit_link = vlc_internal_link($lang['common']['navigation']['course-dropdown']['credit'], 'courses/undergraduate_credit.php', 'dropdown-item');
$courses_ceu_link = vlc_internal_link($lang['common']['navigation']['course-dropdown']['ceu'], 'courses/ceu.php', 'dropdown-item');
$courses_cert_programs_link = vlc_internal_link($lang['common']['navigation']['course-dropdown']['cert_programs'], 'certificates/', 'dropdown-item');

/* nav links */
$about_link = vlc_internal_link($lang['common']['navigation']['about'], 'about/', 'nav-link');
$students_link = vlc_internal_link($lang['common']['navigation']['students'], 'profile/become_student.php', 'nav-link');
$partnership_link = vlc_internal_link($lang['common']['navigation']['partnership'], 'partnership/', 'nav-link');
$calendar_link = vlc_internal_link($lang['common']['navigation']['calendar'], 'calendar/', 'nav-link');
$news_link = vlc_internal_link($lang['common']['navigation']['news'], 'news/', 'nav-link');

/* about description */
$about_desc = $lang['common']['navigation']['description'];

if ($page_info['page'] == 'partner-map')
{
  $body_attributes = ' onload="load_map();" onunload="GUnload()"';
  $gmap_script = '<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=ABQIAAAAIP_2asOPqgXiFkL_c1bYSxRv-rRNb9OFlA9BR2bCzaU9lbK5dBQExc55_vTwl3-kMptw1W61h7f7Nw&amp;hl='.$lang['common']['misc']['current-language-code'].'" type="text/javascript"></script>';
}
else
{
  $body_attributes = '';
  $gmap_script = '';
}
$header = <<< END_HEADER
<!--
  Virtual Learning Community for Faith Formation (VLCFF)
  Institute for Pastoral Initiatives (IPI)
  University of Dayton
  vlcff@udayton.edu
  https://vlcff.udayton.edu/
-->
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang['common']['misc']['current-language-code']}" lang="{$lang['common']['misc']['current-language-code']}">
<head>
<title>{$lang['common']['misc']['vlcff']} @ {$lang['common']['misc']['ud']} &gt; {$lang[$page_info['section']][$page_info['page']]['page-title']}</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">

<!-- <link rel="stylesheet" type="text/css" href="{$site_info['css_url']}style.css"> -->
<link rel="stylesheet" type="text/css" href="{$site_info['css_url']}font-awesome-4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" type="text/css" href="{$site_info['css_url']}normalize.css">
<link rel="stylesheet" type="text/css" href="{$site_info['css_url']}main.css">
<link rel="stylesheet" type="text/css" href="{$site_info['css_url']}{$custom_css}">
<link rel="icon" href="{$site_info['images_url']}favicon.ico" type="image/x-icon">
<link rel="shortcut icon" href="{$site_info['images_url']}favicon.ico" type="image/x-icon">
<script type="text/javascript">
<!--
  var home_url = '{$site_info['home_url']}';
//-->
</script>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<meta name="Keywords" content="{$lang['common']['misc']['meta-keywords']}" />
<meta name="Description" content="{$lang['common']['misc']['meta-description']}" />
<meta name="Author" content="{$lang['common']['misc']['meta-author']}" />

$gmap_script
   </head>
<body $body_attributes>

<nav class="navbar navbar-expand-xl fixed-top user-nav justify-content-end m-0 p-0">
    <ul class="nav">
      <li class="nav-item">
        $switch_language_link
      </li>
      $login_link
    </ul>
</nav>
<nav class="navbar navbar-expand-lg navbar-light fixed-top main-nav">
<a class="navbar-brand mr-auto" href="{$site_info['home_url']}">
  <img src="/images/site/logos-icons/logo-vlcff.png" width="220" class="non_responsive" alt="The Virtual Learning Community for Faith Formation Logo">
</a>
<button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#main-nav" aria-controls="main-nav"
    aria-expanded="false" aria-label="Toggle navigation">
  <span class="navbar-toggler-icon"></span>
</button>
<div class="collapse navbar-collapse d-lg-flex flex-column" id="main-nav">
  <div class="ml-auto">
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          $about_link
        </li>
        <li class="nav-item">
          $students_link
        </li>
        <li class="nav-item">
          $partnership_link
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="main-nav__courses-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{$lang['common']['navigation']['courses']}</a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="main-nav__courses-dropdown">
                $courses_info_link
                $courses_fees_link
                $courses_catalog_link
                $courses_facilitators_link
                $courses_guidelines_link
                $courses_rubric_link
                $courses_credit_link
                $courses_ceu_link
                $courses_cert_programs_link  
            </div>
        </li>
        <li class="nav-item">
          $calendar_link
        </li>
        <li class="nav-item">
          $news_link
        </li>
    </ul>
    </div>
  </div>
</nav>

<main class="container-fluid mx-0 px-0">
  <div class="modal fade" id="login-modal" tabindex="-1" role="dialog" aria-labelledby="login-modal-title" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="login-modal-title">{$lang['common']['sidebar']['form-fields']['log-in-button']}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form action="{$site_info['home_url']}profile/login_action.php" method="post" onsubmit="return validate_form(this);"    style="display:  inline;">
          <div class="form-group">
              <label for="username">{$lang['common']['sidebar']['form-fields']['username']}</label>
              <input type="text" id="username" size="8" maxlength="6" class="form-control" name="username" value="{$user_info['username']}" required oninvalid="this.setUsernameValidity('{$lang['common']['sidebar']['status']['username-required']}')"      oninput="setUsernameValidity('')" accesskey="u" tabindex="1">
          </div>
          <div class="form-group">
              <label for="password">{$lang['common']['sidebar']['form-fields']['password']}</label>
              <input type="password" id="password" size="8" maxlength="6" class="form-control" name="password" required       oninvalid="this.setPasswordValidity('{$lang['common']['sidebar']['status']['password-required']}')"   oninput="setPasswordValidity('')" accesskey="p" tabindex="2" aria-describedby="forgot-password">
              <small id="forgot-password" class="form-text">$forgot_password</small>
          </div>
            <button type="submit" class="submit-button btn btn-vlc align-self-end" name="submit" tabindex="3">
              {$lang['common']['sidebar']['form-fields']['log-in-button']}
            </button>
          </form>
        </div>
        <div class="modal-footer d-block">
         {$lang['common']['sidebar']['misc']['create-profile-link']}
        </div>
      </div>
    </div>
  </div>
  <div class="main_content mb-0">
    <div class="content"> 
    $status_message
    <div id="page-content">
      <!-- begin page content -->
END_HEADER;

if ($page_info['section'] == 'home')
{
  $newsletter_link = vlc_internal_link($lang['home']['index']['misc']['directors-newsletter-link'], 'newsletter/');
  $cert_prog_link = vlc_internal_link($lang['home']['index']['misc']['cert-prog-link'], 'certificates/');
  $new_courses_link = vlc_internal_link($lang['home']['index']['misc']['new-courses-link'], 'courses/new.php');
  $multimedia_link = vlc_internal_link($lang['home']['index']['misc']['multimedia-link'], 'av/');
  $scholarship_link = vlc_internal_link($lang['home']['index']['misc']['scholarship-link'], 'news/article.php?article=16334');
  $giftcert_link = vlc_internal_link($lang['home']['index']['misc']['giftcert-link'], 'gift_certificates/giftcert_info.php');}
return $header;