<?php
$page_info['section'] = 'giftcert';
$page_info['page'] = 'giftcert_check';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->

<h2><?php echo $lang['giftcert']['heading']['giftcert_check']; ?></h2>
<br>
<div style="float:right; margin:30px 30px 0 0;">
<img src="../../images/gift_cert_01.jpg" width="150" />
</div>

<div style="border: 1px solid #bbb; padding:10px 0 0 30px;">

<?php echo $lang['giftcert']['text']['giftcert_check']; ?><br><br>

    <form action="payment_action.php" method="post" >
      <?php echo $lang['giftcert']['label']['giftcert_number']; ?><br><br>
      <input type="text" name="certificate_number" value="<?PHP if(isset($_POST['certificate_number'])) echo htmlspecialchars($_POST['certificate_number']); ?>">
      <br><br>
      <input type="submit" name="check_certificate_button" value="<?php echo $lang['giftcert']['button']['giftcert_check']; ?>">
    </form>
    <br><br>

<?php 
echo '<div style="font-size:14px;>"<br><b>' . $_SESSION['giftcert_check_response'] . '</b><br><br></div>'; 
$_SESSION['giftcert_check_response'] = null;
?>
</div>

<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>