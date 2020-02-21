<?php
$page_info['section'] = 'cms';
$page_info['page'] = 'index';
$page_info['login_required'] = 1;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
$output = '';

$output .= '<h3>Courses</h3>';
$output .= '<ul>';
$output .= '<li>';
$output .= vlc_internal_link('Courses', 'cms/courses.php').' <sup>[ '.vlc_internal_link('Create New', 'cms/course_details.php').' ]</sup>';
$output .= '<ul><li>Find courses, create courses, edit course details (cycle, course subject, course dates, etc.), view/export course evaluations, view/export course rosters, add users, update course status, update facilitator stipend amount, link to related records (cycles, course subjects, users, etc.)</li></ul>';
$output .= '</li>';
$output .= '<li>';
$output .= vlc_internal_link('Course Registrations', 'cms/users_courses.php');
$output .= '<ul><li>Find course registration records, edit course registration details (course status, certificate date, etc.), link to related records (users, courses, orders, etc.)</li></ul>';
$output .= '</li>';
$output .= '<li>';
$output .= vlc_internal_link('Course Subjects', 'cms/course_subjects.php').' <sup>[ '.vlc_internal_link('Create New', 'cms/course_subject_details.php').' ]</sup>';
$output .= '<ul><li>Find course subjects, create course subjects, edit course subject details (description, course type, course level, etc.), create/edit course content, link to specific course offerings</li></ul>';
$output .= '</li>';
$output .= '<li>';
$output .= vlc_internal_link('Cycles', 'cms/cycles.php').' <sup>[ '.vlc_internal_link('Create New', 'cms/cycle_details.php').' ]</sup>';
$output .= '<ul><li>Find cycles, create cycles, edit cycle details (code, cycle dates, registration dates, etc.), add courses, view/export course evaluations, view/export course rosters, link to courses</li></ul>';
$output .= '</li>';
$output .= '</ul>';

$output .= '<h3>Certificate Programs</h3>';
$output .= '<ul>';
$output .= '<li>';
$output .= vlc_internal_link('Certificate Programs', 'cms/cert_progs.php').' <sup>[ '.vlc_internal_link('Create New', 'cms/cert_prog_details.php').' ]</sup>';
$output .= '<ul><li>Find certificate programs, create certificate programs, edit certificate program details, add courses, add users</li></ul>';
$output .= '</li>';
$output .= '<li>';
$output .= vlc_internal_link('Certificate Program Registrations', 'cms/certs_users.php');
$output .= '<ul><li>Find certificate program registration records, edit certificate program registration details, link to related records</li></ul>';
$output .= '</li>';
$output .= '</ul>';

$output .= '<h3>Users</h3>';
$output .= '<ul>';
$output .= '<li>';
$output .= vlc_internal_link('Users', 'cms/users.php').' <sup>[ '.vlc_internal_link('Create New', 'cms/user_details.php').' ]</sup>';
$output .= '<ul><li>Find users, create users, edit user details (login information, contact information, etc.), add courses, link to related records (courses, course registrations, orders, etc.)</li></ul>';
$output .= '</li>';
$output .= '</ul>';

$output .= '<h3>Orders</h3>';
$output .= '<ul>';
$output .= '<li>';
$output .= vlc_internal_link('Student Registration Orders', 'cms/student_orders.php');
$output .= '<ul><li>Find student registration orders, edit student registration order details (course status, order status, discount type, etc.), enter transactions, link to related records (courses, users, orders, transactions, etc.)</li></ul>';
$output .= '</li>';
$output .= '<li>';
$output .= vlc_internal_link('Orders', 'cms/orders.php');
$output .= '<ul><li>Find orders, edit order details (order status, discount type, etc.), enter transactions, link to related records (users, transactions, etc.)</li></ul>';
$output .= '</li>';
$output .= '<li>';
$output .= vlc_internal_link('Transactions', 'cms/transactions.php');
$output .= '<ul><li>Find transactions, edit transaction details (transaction status, payment method, etc.), add multiple orders to a single transaction, link to related records (users, orders, etc.), upload transaction reports from bursar</li></ul>';
$output .= '</li>';
$output .= '<li>';
$output .= vlc_internal_link('Payment Codes', 'cms/payment_codes.php').' <sup>[ '.vlc_internal_link('Create New', 'cms/payment_code_details.php').' ]</sup>';
$output .= '<ul><li>Find payment codes, create payment codes, edit payment code details (code, partner information, discount information, etc.)</li></ul>';
$output .= '</li>';
$output .= '</ul>';

$output .= '<h3>Partners</h3>';
$output .= '<ul>';
$output .= '<li>';
$output .= vlc_internal_link('Partners', 'cms/partners.php').' <sup>[ '.vlc_internal_link('Create New', 'cms/partner_details.php').' ]</sup>';
$output .= '<ul><li>Find partners, create partners, edit partner details (partner status, discount information, contact information, etc.)</li></ul>';
$output .= '</li>';
$output .= '<li>';
$output .= vlc_internal_link('Partner Reports', 'cms/partner_reports.php');
$output .= '<ul><li>Generate a list of participants for a given partner in either HTML, CSV, or PDF format</li></ul>';
$output .= '</li>';
$output .= '</ul>';

$output .= '<h3>Miscellaneous</h3>';
$output .= '<ul>';
$output .= '<li>';
$output .= vlc_internal_link('Website Resources', 'cms/resources.php');
$output .= '<ul><li>Manage announcements, articles, etc.</li></ul>';
$output .= '</li>';
$output .= '<li>';
$output .= vlc_internal_link('Course Rosters', 'cms/roster.php');
$output .= '<ul><li>Generate course roster for a given cycle or course in either HTML, CSV, or PDF format</li></ul>';
$output .= '</li>';
$output .= '<li>';
$output .= vlc_internal_link('Course Evaluations', 'cms/evaluations.php');
$output .= '<ul><li>Compile course evaluations for a given cycle or course in either HTML, CSV, or PDF format</li></ul>';
$output .= '</li>';
$output .= '<li>';
$output .= vlc_internal_link('Certificate Data', 'cms/certificate.php');
$output .= '<ul><li>Generate certificate data for a given cycle or course in either HTML, CSV, or PDF format</li></ul>';
$output .= '</li>';
$output .= '<li>';
$output .= vlc_internal_link('Facilitator Checklist Data', 'cms/checklist.php');
$output .= '<ul><li>Generate facilitator checklist data for a given cycle or course in either HTML, CSV, or PDF format</li></ul>';
$output .= '</li>';
$output .= '<li>';
$output .= vlc_internal_link('MailChimp', 'cms/magnetmail.php');
$output .= '<ul><li>Generate e-mail lists for a given cycle or course in either HTML, CSV, or PDF format</li></ul>';
$output .= '</li>';
$output .= '<li>';
$output .= vlc_internal_link('Sympa Lists', 'cms/sympa.php');
$output .= '<ul><li>Generate Sympa commands</li></ul>';
$output .= '</li>';
$output .= '<li>';
$output .= vlc_internal_link('SQL', 'cms/sql.php');
$output .= '<ul><li>Run SQL queries and export results</li></ul>';
$output .= '</li>';
$output .= '</ul>';
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
