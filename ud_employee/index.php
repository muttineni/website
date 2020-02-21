<?php
$page_info['section'] = 'ud-employee';
$page_info['page'] = 'index';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<div class="container about-content">
    <div class="row">
        <div class="col">
            <h1><?php print $lang['ud-employee']['index']['heading']['page'] ?></h1>
            <p><?php print $lang['ud-employee']['index']['content']['welcome'] ?></p>
        </div>
    </div>
    <div class="row">
        
        <div class="col-lg-10 img-container">
            <div class="embed-responsive embed-responsive-16by9">
                <iframe width="560" height="315" src="https://www.youtube.com/embed/9YZ5yZ6SZ7E?rel=0" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-10">
            <h3><?php print $lang['ud-employee']['getting-started']['heading'] ?></h3>
            <p><?php print $lang['ud-employee']['getting-started']['content'] ?></p>
        </div>
    </div>
</div>

<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

