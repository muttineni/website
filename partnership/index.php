<?php
$page_info['section'] = 'partnership';
$page_info['page'] = 'index';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
$answer_list = '';
$question_list = '';
$answer_footer = '<div class="answer__footer"><a href="#q">'.$lang['partnership']['index']['misc']['return-link'].'</a></div>';
for ($i = 0; $i < count($lang['partnership']['index']['content']['questions']); $i++)
{
  $question_list .= '<li class="list-group-item"><a href="#q'.$i.'">'.$lang['partnership']['index']['content']['questions'][$i].'</a></li>';
  $answer_list .= '<span id="q'.$i.'" class="anchor"></span>';
  $answer_list .= '<section id="q'.$i.'" class="answer">'; #begin section
  $answer_list .= '<h3>'.$lang['partnership']['index']['content']['questions'][$i].'</h3>'; #add headers
  $answer_list .= '<div class="answer__body">'.$lang['partnership']['index']['content']['answers'][$i].$answer_footer.'</div>'; #add answer body
  $answer_list .= '</section>'; #close section
}
?>
<!-- begin page content -->
<div id="q" class="container">
    <span class="anchor"></span>
    <h1><?php print $lang['partnership']['index']['heading']['questions'] ?></h1>
    <div class="return-link">
      <i class="fa fa-arrow-left"></i> <?php print vlc_internal_link($lang['partnership']['index']['misc']['partner-list-link'], 'partnership/partners.php') ?>
    </div>
    <div class="card list">
      <ul class="list-group list-group-flush">
        <?php print $question_list ?>
      </ul>
    </div>
    <?php print $answer_list ?>
</div>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

