<?php
$page_info['section'] = 'profile';
$page_info['page'] = 'become-student';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;

/* Become Student Links */
$student_create_link = vlc_internal_link($lang['profile']['become-student']['content']['create-profile'], 'profile/agreement.php');
$student_fees_link = vlc_internal_link($lang['profile']['become-student']['content']['fees'], 'courses/payment_policy.php');
$student_calendar_link = vlc_internal_link($lang['profile']['become-student']['content']['calendar'], 'calendar/') ;
$student_sample_link = vlc_internal_link($lang['profile']['become-student']['content']['sample'], 'courses/');
?>
<!-- begin page content -->
<div class="container mb-3">
    <section id="welcome">
        <h1><?php print $lang['profile']['become-student']['heading']['welcome'] ?></h1>
        <div class="welcome-body">
            <?php print $lang['profile']['become-student']['welcome']['shown'] ?>
            <div id="welcome-text" class="collapse">
                <?php print $lang['profile']['become-student']['welcome']['toggle'] ?>
            </div>
            <a href="#welcome-text" data-toggle="collapse" class="toggler" id="welcome-text-toggle" aria-expanded="false" aria-controls="welcome-text"><i class="fa fa-chevron-down" aria-hidden="true"></i> <?php print $lang['profile']['become-student']['welcome']['toggle-text'] ?></a>   
            <div>
                <?php print $lang['profile']['become-student']['welcome']['end'] ?>
            </div>         
        </div>
    </section>
    <section id="nav" class="mt-5">
        <div class="card list">
            <div class="card-header">
                <h3><?php print $lang['profile']['become-student']['heading']['become-student'] ?></h3>
            </div>
            <ul class="list-group list-group-flush">
                <li class="list-group-item"><?php print $student_create_link ?></li>
                <li class="list-group-item"><?php print $student_fees_link ?></li>
                <li class="list-group-item"><?php print $student_calendar_link ?></li>
                <li class="list-group-item"><?php print $student_sample_link ?></li>
            </ul>
    </section>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
