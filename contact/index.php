<?php
$page_info['section'] = 'contact';
$page_info['page'] = 'index';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<div class="container">
  <h1><?php echo $lang['contact']['index']['page-title'] ?></h1>
  <div class="list-group">
    <div class="p-3"><?php print $lang['contact']['index']['content']['contact-intro'] ?></div>
    <div class="list-group-item">
      Sr. Angela Ann Zukowski, MHSH, D.Min.</br>
      <?php print $lang['contact']['index']['misc']['director-title'] ?>
    </div>
    <div class="list-group-item">
      <?php print vlc_mailto_link('Richard Drabik, M.A.', 'RDrabik1@udayton.edu', $lang['common']['misc']['vlcff']) ?><br>
      <?php print $lang['contact']['index']['misc']['asst-dir-title'] ?><br>
      (937) 229-3874<br>
      <strong><?php print $lang['contact']['index']['misc']['toll-free'] ?>:</strong> (888) 300-8436
    </div>
    <div class="list-group-item">
      <?php print vlc_mailto_link('Dorothy Mensah-Aggrey, M.A.', 'dmensahaggrey1@udayton.edu', $lang['common']['misc']['vlcff']) ?><br>
      <?php print $lang['contact']['index']['misc']['academic_consultant_01-title'] ?><br>
      (937) 229-4654
    </div>
    <div class="list-group-item">
      <?php print vlc_mailto_link('Liliana Montoya, M.A.', 'LMontoya1@udayton.edu', $lang['common']['misc']['vlcff']) ?><br>
      <?php print $lang['contact']['index']['misc']['spanish-webmaster-title'] ?><br>
      (937) 229-3462
    </div>
    <div class="list-group-item">
      <?php print vlc_mailto_link('Angela Hall', 'AHall1@udayton.edu', $lang['common']['misc']['vlcff']) ?><br>
      <?php print $lang['contact']['index']['misc']['payments-admin'] ?><br>
      (937) 229-4325
    </div>
    <div class="list-group-item">
      <?php print vlc_mailto_link('John LeComte', 'JLeComte1@udayton.edu', $lang['common']['misc']['vlcff']) ?><br>
      <?php print $lang['contact']['index']['misc']['social-media'] ?><br>
      (937) 229-3185
    </div>
    <div class="list-group-item">
      <?php print vlc_mailto_link('Laura Franklin', 'LFranklin1@udayton.edu', $lang['common']['misc']['vlcff']) ?><br>
      <?php print $lang['contact']['index']['misc']['certificate-program'] ?><br>
      (937) 229-3113
    </div>
    <div class="list-group-item">
      <?php print vlc_mailto_link('Robert Stewart', 'RStewart1@udayton.edu', $lang['common']['misc']['vlcff']) ?><br>
      <?php print $lang['contact']['index']['misc']['technology-title'] ?><br>
      (937) 229-3160
    </div>
    <div class="list-group-item">
      <?php print vlc_mailto_link('Margaret McCrate', 'MMcCrate1@udayton.edu', $lang['common']['misc']['vlcff']) ?><br>
      <?php print $lang['contact']['index']['misc']['assistant-title'] ?><br>
      (937) 229-4592
    </div>
    <div class="list-group-item">
      <strong><?php print $lang['contact']['index']['misc']['mailing-address'] ?></strong><br>
      <?php print $lang['common']['misc']['mailing-address'] ?><br>
    </div>
    <div class="list-group-item">
      <strong><?php print $lang['contact']['index']['misc']['fax'] ?>:</strong> (937) 229-3130
    </div>
    <div class="p-3"><?php print $lang['contact']['index']['misc']['contact-admin-link'] ?></div>
  </div>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>
