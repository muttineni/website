<?php
$page_info['section'] = 'courses';
$page_info['page'] = 'ceu-guidelines';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
$answer_list = '';
$question_list = '';
$answer_footer = '<div class="answer__footer"><a href="#q">'.$lang['courses']['ceu-guidelines']['misc']['question-return-link'].'</a></div>';
for ($i = 0; $i < count($lang['courses']['ceu-guidelines']['content']['questions']); $i++)
{
  $question_list .= '<li class="list-group-item"><a href="#q'.$i.'">'.$lang['courses']['ceu-guidelines']['content']['questions'][$i].'</a></li>';
  $answer_list .= '<span id="q'.$i.'" class="anchor"></span>';
  $answer_list .= '<section class="answer">'; #begin section
  $answer_list .= '<h3>'.$lang['courses']['ceu-guidelines']['content']['questions'][$i].'</h3>';
  $answer_list .= '<div class="answer__body">'.$lang['courses']['ceu-guidelines']['content']['answers'][$i].$answer_footer.'<div>';
  $answer_list .= '</section>'; #close section
}
?>
<!-- begin page content -->
<div id="q" class="container">
  <span class="anchor"></span>
  <h1><?php print $lang['courses']['ceu-guidelines']['heading']['ceu-guidelines'] ?></h1>
  <div class="return-link">
      <i class="fa fa-arrow-left"></i> <?php print vlc_internal_link($lang['courses']['shared']['return-link'], 'courses/') ?>
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

