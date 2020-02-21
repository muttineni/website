<?php

if (!isset($_SESSION)) {
	session_start();
	$_SESSION = array(); //initialize session
}

$page_info['section'] = 'giftcert';
$page_info['page'] = 'giftcert_order';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;

$login_required = 0;
$user_info = vlc_get_user_info($login_required, 0);
?>

<div class="container">
    <h1><?php echo $lang['giftcert']['heading']['vlcff_giftcert_order_form'] ?></h1>
    <div data-ng-app="" data-ng-init="quantity=1;price=50">
        <div class="float-right">
            <img src="../images/gift_cert_01.jpg" width="150" class="non_responsive d-none d-sm-block" alt="gift certificate-image" />
        </div>
        <form action="<?php echo htmlspecialchars('payment_action.php')?>" method="post">
            <div class="form-section form-qty">
                <h2><?php print $lang['giftcert']['heading']['order_giftcerts']; ?></h2>
                <?php print $lang['giftcert']['text']['enter_qty']; ?>
                <div class="form-group form-inline">
                    <input type="number" name="certificate_qty" id="certificate_qty" ng-model="quantity" 
                        min="1" max="100" ng-pattern="/^[0-9]+(\.[0-9]{0})?$/"
                        class="form-control mr-3" />
                    <label for="certificate_qty"><?php echo $lang['giftcert']['label']['number_of_courses']; ?></label>
                </div>
                <div>
        	        <?php echo $lang['giftcert']['label']['total_cost'] ?>: <b> US$ {{quantity * price}} </b>
                </div>
            </div>
            <div class="form-section form-contact mt-3">
                <h2><?php echo $lang['giftcert']['heading']['buyer_contact_info']; ?></h2>
                <div>
                    <span class="required-example">*</span> <?php echo $lang['giftcert']['text']['form_req'] ?>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="form-row">
                            <div class="col-sm-6 form-group">
                                <label for="first_name" class="giftcert required"><?php echo $lang['giftcert']['label']['buyer_first_name'];?></label>
                                <input type="text" required="true" name="first_name" id="first_name" size="40" value="<?php echo isset($_SESSION    ['giftcert']['first_name'])?$_SESSION['giftcert']['first_name']:$user_info['first_name']; ?>" class="form-control" />
                            </div>
                            <div class="col-sm-6 form-group">
                                <label for="last_name" class="giftcert required"><?php echo $lang['giftcert']['label']['buyer_last_name'];?></label>
                                <input type="text" required="true" name="last_name" id="last_name" size="40"  value="<?php echo isset($_SESSION['giftcert']['last_name'])?$_SESSION['giftcert']['last_name']:$user_info['last_name']; ?>" class="form-control" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email" class="giftcert required"><?php echo $lang['giftcert']['label']['buyer_email'];?></label>
                            <input type="text" required="true" name="email" id="email" size="40"  value="<?php echo isset($_SESSION['giftcert']['email'])?$_SESSION['giftcert']['email']:$user_info['email'];?>" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label for="phone" class="giftcert required"><?php echo $lang['giftcert']['label']['buyer_phone']?></label>
                           <input type="text" required="true" name="phone" id="phone" size="40"  value="<?php echo isset($_SESSION['giftcert']['phone'])?$_SESSION['giftcert']['phone']:$user_info['phone']; ?>" class="form-control" />
                        </div>
                        <div class="form-row">
                            <input type="submit" name="confirm" class="btn btn-vlc m-3" value="<?php echo $lang['giftcert']['button']['confirm_order_info']?>">
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
