<?php
$page_info['section'] = 'about';
$page_info['page'] = 'index';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<div class="container about-content">
    <div class="row">
        <div class="col">
            <h1><?php print $lang['about']['index']['heading']['purpose'] ?></h1>
            <p><?php print $lang['about']['index']['content']['purpose'] ?></p>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 img-container">
            <img src="/images/site/about/img-zukowski-convocation_600w.jpg" class="mx-auto d-block"/>
        </div>
        <div class="col-lg-6">
            <h3><?php print $lang['about']['what']['content']['question'] ?></h3>
            <p><?php print $lang['about']['what']['content']['answer'] ?></p>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 img-container">
            <img src="/images/site/about/img-zukowski-satellite_dish-600w400h.jpg" class="mx-auto d-block" />
        </div>
        <div class="col-lg-6">
            <h3><?php print $lang['about']['history']['content']['question'] ?></h3>
            <p><?php print $lang['about']['history']['content']['answer'] ?></p>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 order-2 order-lg-1">
            <h3><?php print $lang['about']['who']['content']['question'] ?></h3>
            <p><?php print $lang['about']['who']['content']['answer'] ?></p>
        </div>
        <div class="col-lg-6 order-1 order-lg-2 img-container">
            <img src="/images/site/about/img-dayton_chapel_aerial_600w400h.jpg" class="mx-auto d-block" />
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 order-2 order-lg-1">
            <h3><?php print $lang['about']['ministry']['content']['question'] ?></h3>
            <p><?php print $lang['about']['ministry']['content']['answer'] ?></p>
        </div>
        <div class="col-lg-6 order-1 order-lg-2 img-container">
            <img src="/images/site/about/img-man-laptop-coffee.jpg" class="mx-auto d-block" />
        </div>
    </div>
</div>

<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

