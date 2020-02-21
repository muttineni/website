<?php
$page_info['section'] = 'news';
$page_info['page'] = 'article';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<p class="center"><b>Security Update Affects VLCFF Users</b></p>
<p class="center">February 4, 2004</p>
<p>Due to an update to the Microsoft Internet Explorer web browser on computers with the Microsoft Windows operating system, some VLCFF users have experienced problems logging in to their courses from the VLCFF website.</p>
<p>Only users who use Internet Explorer on the Windows operating system will be affected by this update.  In order to give these users access to their VLCFF courses, we have provided a second link to each course listed in the "My Courses" area on the right side of our website.  After clicking on this new link, users will be required to enter their username and password a second time in order to gain access to their course.</p>
<p>We apologize for the inconvenience this issue causes.  Please be assured that we are concerned about privacy and security issues on our website and we are dedicated to addressing these issues as they arise.</p>
<p>We are in the process of redesigning our online classes and as part of this redesign we will be revising our login process.  Our new login process will eliminate the problems caused by this security update.  Look for our changes to be implemented during the summer of 2004.</p>
<p>More detailed information about this issue can be found on the <?php print vlc_external_link('Microsoft TechNet', 'http://www.microsoft.com/technet/security/bulletin/MS04-004.mspx') ?> website.</p>
<p>Feel free to <?php print vlc_internal_link('contact us', 'contact/') ?> if you have any additional concerns.</p>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

