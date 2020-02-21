<?php
$page_info['section'] = 'giftcert';
$page_info['page'] = 'giftcert-use';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;

// put this variable in a session to make available on subsequent pages
$_SESSION['EXT_TRANS_ID'] = $_POST['EXT_TRANS_ID'];

?>
<!-- begin page content -->

<div class="container">
  <h1><?php echo $lang['giftcert']['heading']['giftcert_use'] ?></h1>
  <div class="card">
    <div class="card-body">
      <div class="alert alert-info">
        <?php echo $lang['giftcert']['text']['giftcert_use']; ?>
      </div>
      <div class="float-right d-none d-sm-block">
        <img src="../images/gift_cert_01.jpg" width="150" class="non_responsive" alt="gift certificate" />
      </div>
      <form action="payment_action.php" method="post" class="my-2 py-3">
        <div class="form-group my-2 form-inline">
          <label for="certificate_number"><?php echo $lang['giftcert']['label']['giftcert_number'] ?></label>
          <input type="text" name="certificate_number" id="certificate_number" value="<?PHP if(isset($_POST['certificate_number'])) echo htmlspecialchars($_POST['certificate_number']); ?>" class="form-control mx-2" />
        </div>
        <div class="d-block">
          <input type="submit" name="back_to_payment_options" value="<?php echo $lang['giftcert']['button']['back']; ?>" class="btn btn-default d-md-inline d-block my-2" /> 
          <input type="submit" name="cancel" value="<?php echo $lang['giftcert']['button']['cancel']; ?>" class="btn btn-danger d-md-inline d-block my-2" />
          <input type="submit" name="pay_with_giftcert" value="<?php echo $lang['giftcert']['button']['pay_with_giftcert']; ?>" class="btn btn-vlc d-md-inline d-block my-2" />  
        </div>
      </form>
      <div class="check-result">
        <?php 
          echo $_SESSION['giftcert_check_response']; 
          // reset cert check box
          $_SESSION['giftcert_check_response'] = null;
        ?>
      </div>
    </div>
  </div>
</div>

<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>