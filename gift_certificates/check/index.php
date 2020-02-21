<?php
$page_info['section'] = 'giftcert';
$page_info['page'] = 'giftcert_check';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->

<div class="container">
  <h1><?php echo $lang['giftcert']['heading']['giftcert_check'] ?></h1>
  <div class="card">
    <div class="card-body">
      <div class="alert alert-info">
        <?php echo $lang['giftcert']['text']['giftcert_check']; ?>
      </div>
      <div class="float-right d-none d-sm-block">
        <img src="../../images/gift_cert_01.jpg" width="150" class="non_responsive" alt="gift certificate" />
      </div>
      <form action="../payment_action.php" method="post" class="form-inline my-2 py-3">
        <label for="certificate_number" class="mx-2"><?php echo $lang['giftcert']['label']['giftcert_number'] ?></label>
        <input type="text" name="certificate_number" id="certificate_number" value="<?PHP if(isset($_POST['certificate_number'])) echo  htmlspecialchars($_POST['certificate_number']); ?>" class="form-control mx-2" />
        <input type="submit" name="check_certificate_button" value="<?php echo $lang['giftcert']['button']['giftcert_check']; ?>"   class="btn btn-vlc mx-2 my-2" />
      </form>
      <div class="check-result">
        <?php 
          echo $_SESSION['giftcert_check_response'];
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