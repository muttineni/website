<?php
$page_info['section'] = 'giftcert';
$page_info['page'] = 'giftcert-info';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<div class="container">
    <h1><?php print $lang['giftcert']['heading']['about_giftcerts']; ?></h1>
    <div class="jumbotron gc-jumbotron d-flex">
        <div>
            <img src="/images/site/header/gift_cert_400w.jpg" alt="The Virtual Learning Community for Faith Formation Logo" class = "gift-cert-400max">
        </div>
        <div class="gc-jumbotron__text align-self-end ml-auto">
            <p><?php print $lang['giftcert']['text']['byline'] ?></p>
        </div>
    </div>
    <div>
        <p>
            <?php print $lang['giftcert']['text']['about_giftcerts']; ?>
        </p>
    </div>
    <div class="text-center mb-5">
        <a href="giftcert_order.php" class="btn btn-vlc">
            <?php print $lang['giftcert']['button']['order']; ?>
        </a>
    </div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
