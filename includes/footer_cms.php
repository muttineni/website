<?php
$cms_nav_links = '<b>Courses:</b>';
$cms_nav_links .= '&nbsp;'.vlc_internal_link('Courses', 'cms/courses.php');
$cms_nav_links .= ' / '.vlc_internal_link('Course Registrations', 'cms/users_courses.php');
$cms_nav_links .= ' / '.vlc_internal_link('Course Subjects', 'cms/course_subjects.php');
$cms_nav_links .= ' / '.vlc_internal_link('Cycles', 'cms/cycles.php');

$cms_nav_links .= '&nbsp;<b>Certificate Programs:</b>';
$cms_nav_links .= '&nbsp;'.vlc_internal_link('Certificate Programs', 'cms/cert_progs.php');
$cms_nav_links .= ' / '.vlc_internal_link('Certificate Program Registrations', 'cms/certs_users.php');

$cms_nav_links .= '<br><b>Users:</b>';
$cms_nav_links .= '&nbsp;'.vlc_internal_link('Users', 'cms/users.php');

$cms_nav_links .= '&nbsp;<b>Orders:</b>';
$cms_nav_links .= '&nbsp;'.vlc_internal_link('Student Registration Orders', 'cms/student_orders.php');
$cms_nav_links .= ' / '.vlc_internal_link('Orders', 'cms/orders.php');
$cms_nav_links .= ' / '.vlc_internal_link('Transactions', 'cms/transactions.php');
$cms_nav_links .= ' / '.vlc_internal_link('Payment Codes', 'cms/payment_codes.php');

$cms_nav_links .= '&nbsp;<b>Partners:</b>';
$cms_nav_links .= '&nbsp;'.vlc_internal_link('Partners', 'cms/partners.php');
$cms_nav_links .= ' / '.vlc_internal_link('Partner Reports', 'cms/partner_reports.php');

$cms_nav_links .= '<br><b>Miscellaneous:</b>';
$cms_nav_links .= '&nbsp;'.vlc_internal_link('Website Resources', 'cms/resources.php');
$cms_nav_links .= ' / '.vlc_internal_link('Course Rosters', 'cms/roster.php');
$cms_nav_links .= ' / '.vlc_internal_link('Course Evaluations', 'cms/evaluations.php');
$cms_nav_links .= ' / '.vlc_internal_link('Certificate Data', 'cms/certificate.php');
$cms_nav_links .= ' / '.vlc_internal_link('Facilitator Checklist Data', 'cms/checklist.php');
$cms_nav_links .= ' / '.vlc_internal_link('MailChimp', 'cms/magnetmail.php');
$cms_nav_links .= ' / '.vlc_internal_link('Sympa Lists', 'cms/sympa.php');
$cms_nav_links .= ' / '.vlc_internal_link('SQL', 'cms/sql.php');

$vlcff_home_link = vlc_internal_link('VLCFF Home');
$my_start_page_link = vlc_internal_link('My Start Page', 'profile/');
$cms_home_link = vlc_internal_link('CMS Home', 'cms/');
$logout_link = vlc_internal_link('Log Out', 'profile/logout_action.php');

$footer = <<< END_FOOTER
<!-- begin footer -->
<hr width="50%">
<p align="center">$cms_nav_links</p>
<hr width="50%">
<p align="center">$vlcff_home_link / $my_start_page_link / $cms_home_link / $logout_link</p>
END_FOOTER;
return $footer;
?>

