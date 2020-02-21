<?php
$page_info['section'] = 'home';
$page_info['page'] = 'index';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
/* get announcements */
$announcement_query = <<< END_QUERY
  SELECT content, url
  FROM resources
  WHERE resource_type_id IN (1, 59)
  AND CURDATE() >= active_start
  AND CURDATE() <= active_end
  AND language_id = {$lang['common']['misc']['current-language-id']}
  ORDER BY active_start DESC, active_end ASC, CREATED DESC
END_QUERY;
$result = mysql_query($announcement_query, $site_info['db_conn']);
$announcement_list = '';

while ($record = mysql_fetch_array($result))
{
  $audio_button = '';
  if (isset($record['url']))
  {
    $audio_file = $site_info['audio_url'].'files/'.$record['url'];
    $audio_button = vlc_embed_audio($audio_file, 35, 35, '&playingColor=006669&grinderColor=636363&rollOverColor=C9C984');
  }
  $announcement_list .= vlc_convert_code($record['content']).' '. $audio_button;
}

$announcements = strlen($announcement_list) 
? '<section class="row home-announcements">
    <div class="col home-announcements">
        <h2>'.$lang['home']['index']['heading']['announcements'].'</h2>
        <ul>'.$announcement_list.'</ul>
    </div>
  </section>' 
: '';
if ($lang['common']['misc']['current-language-id'] == '1') {
    
$output = <<< END_OUTPUT

<div class="container-fluid">
      
<div id="Indicators" class="carousel slide" data-ride="carousel" data-interval="8000">
    <ol class="carousel-indicators">
        <li data-target="#Indicators" data-slide-to="0" class="active"></li>
        <li data-target="#Indicators" data-slide-to="1"></li>
        <li data-target="#Indicators" data-slide-to="2"></li>
        <li data-target="#Indicators" data-slide-to="3"></li>
    </ol>

      <!-- Wrapper for slides -->
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img class="d-block w-100" src="/images/site/home/en_slide_1.jpg" alt="The VLCFF is...">
        </div>

        <div class="carousel-item">
        <a href="https://vlcff.udayton.edu/register/">
          <img class="d-block w-100" src="/images/site/home/en_slide_2.jpg" alt="Registration Open">
        </a>
        </div>

        <div class="carousel-item">
        <a href="http://www.sinodoamazonico.va/content/sinodoamazonico/en.html" target="_blank">
          <img class="d-block w-100" src="/images/site/home/en_slide_3.jpg" alt="Amazon Synod">
        </a>
        </div>	
            
        <div class="carousel-item">
        <a href="http://w2.vatican.va/content/francesco/en/messages/missions/documents/papa-francesco_20190609_giornata-missionaria2019.html" target="_blank">
          <img class="d-block w-100" src="/images/site/home/en_slide_4.jpg" alt="Mission Month">
        </a>
        </div>
	
      </div>

      <!-- Left and right controls -->
        <a class="carousel-control-prev" href="#Indicators" role="button" data-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#Indicators" role="button" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="sr-only">Next</span>
        </a>
    </div>        
        
$announcements
        
<section class="row home-welcome-en align-self-start">
    <div class="col-lg-6 col-12 section-content">
            <h2>{$lang['home']['index']['heading']['intro']}</h2>
            {$lang['home']['index']['content']['intro']}
    </div>
    <div class="welcome-image col-lg-6">
           
    </div>
            
</section>
<section class="row home-mission-en justify-content-end">

    <div class="col-lg-6 col-12 ">
        <div class="embed-responsive blog-image">
            <a href="https://udayton.edu/blogs/ipi/2019/19-09-12-malawistudent.php" target="_blank" ></a>
        </div>
        <a href="https://udayton.edu/blogs/ipi/2019/19-09-12-malawistudent.php" target="_blank" >
            <h3>IPI Blog: <b>The VLCFF in Malawi.</b></h3>
        </a>
    </div>
            
    <div class="col-lg-6 col-12 section-content">
        <h2>{$lang['home']['index']['heading']['mission']}</h2>
        {$lang['home']['index']['content']['mission']}
    </div>
        
</section>
<section class="row home-explore">
    <div class="col-12">
        <h2>{$lang['home']['index']['heading']['explore']}</h2>
    </div>
    <div class="col-md-4 explore-video-en">
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item" src="{$lang['home']['index']['video_01']['url']}" frameborder="0" allowfullscreen></iframe>
        </div>
        <h3>{$lang['home']['index']['video_01']['title']}</h3>
    </div>    
    <div class="col-md-4 explore-video-en">
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item" src="{$lang['home']['index']['overview']['url']}" frameborder="0" allowfullscreen></iframe>
        </div>
        <h3>{$lang['home']['index']['overview']['title']}</h3>
    </div>
    <div class="col-md-4 explore-video-en">
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item" src="{$lang['home']['index']['getting-started']['url']}" frameborder="0" allowfullscreen></iframe>
        </div>
        <h3>{$lang['home']['index']['getting-started']['title']}</h3>
    </div>
</section>  
 
<section class="row justify-content-center home-testimonial">
    <div class="col-md-8 col-sm-10 col-12">
        <h3 class="d-block text-left">{$lang['home']['index']['testimonial']['byline']}</h3>
    </div>
    <div class="col-md-8 col-sm-10 col-12 media">
        <img class="align-self-center mr-3 non_responsive rounded" width="125" height="175" src="{$lang['home']['index']['testimonial']['image']}" alt="testimonial image">
        <div class="media-body">
            <p>{$lang['home']['index']['testimonial']['text']}</p>
            <footer class="blockquote-footer">{$lang['home']['index']['testimonial']['author']}</footer>
        </div>
    </div>
</section>
            
            
<section class="row home-donate justify-content-start mb-0">
    <div class="col-12 col-lg-8 col-xl-7 section-content">
        <h2>{$lang['home']['index']['bosco-fund']['heading']}</h2>
        <p>{$lang['home']['index']['bosco-fund']['text']}</p>
        <a class="btn btn-vlc btn-donate" name="donate" target="_BLANK" href="mailto:ahall1@udayton.edu?subject=VLCFF scholarship fund inquiry">
        <i class="fa fa-gift"></i> <span class="btn-text">{$lang['home']['index']['bosco-fund']['button']}</span>
    </a>
    </div>
</section>
</div>
END_OUTPUT;
        
} else { //Spanish output
    
$output = <<< END_OUTPUT

<div class="container-fluid">
<div id="Indicators" class="carousel slide" data-ride="carousel" data-interval="8000">
    <ol class="carousel-indicators">
        <li data-target="#Indicators" data-slide-to="0" class="active"></li>
        <li data-target="#Indicators" data-slide-to="1"></li>
        <li data-target="#Indicators" data-slide-to="2"></li>
	<li data-target="#Indicators" data-slide-to="3"></li>
	<li data-target="#Indicators" data-slide-to="4"></li>
    </ol>

      <!-- Wrapper for slides -->
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img class="d-block w-100" src="/images/site/home/sp_slide_1.jpg" alt="The VLCFF is...">
        </div>

        <div class="carousel-item">
        <a href="https://www.youtube.com/watch?reload=9&v=n3EjEEClHAs&feature=youtu.be" target="_blank">
          <img class="d-block w-100" src="/images/site/home/sp_slide_2.jpg" alt="Ivan Diaz">
        </a>
        </div>

        <div class="carousel-item">
        <a href="https://vlcff.udayton.edu/register/?lang=es">
          <img class="d-block w-100" src="/images/site/home/sp_slide_3.jpg" alt="Registration Open">
        </a>
        </div>
        
        <div class="carousel-item">
        <a href="http://www.sinodoamazonico.va/content/sinodoamazonico/es.html" target="_blank">
          <img class="d-block w-100" src="/images/site/home/sp_slide_4.jpg" alt="Amazonian Synod">
        </a>
        </div>	        

        <div class="carousel-item">
        <a href="http://w2.vatican.va/content/francesco/es/messages/missions/documents/papa-francesco_20190609_giornata-missionaria2019.html" target="_blank">
          <img class="d-block w-100" src="/images/site/home/sp_slide_5.jpg" alt="Mission Month">
        </a>
        </div>
        
	
      </div>

      <!-- Left and right controls -->
        <a class="carousel-control-prev" href="#Indicators" role="button" data-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#Indicators" role="button" data-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="sr-only">Next</span>
        </a>
    </div> 
    
$announcements
<section class="row home-welcome-es align-self-start">
    <div class="col-lg-7 col-12 section-content">
        <h2>{$lang['home']['index']['heading']['intro']}</h2>
        {$lang['home']['index']['content']['intro']}
    </div>
</section>
<section class="row home-mission-es justify-content-end">
    <div class="col-lg-7 col-12 section-content">
        <h2>{$lang['home']['index']['heading']['mission']}</h2>
        {$lang['home']['index']['content']['mission']}
    </div>
</section>
<section class="row home-explore">
    <div class="col-12">
        <h2>{$lang['home']['index']['heading']['explore']}</h2>
    </div>
    <div class="col-md-6 explore-video-es">
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item" src="{$lang['home']['index']['promo']['url']}" frameborder="0" allowfullscreen></iframe>
        </div>
        <h3>{$lang['home']['index']['promo']['title']}</h3>
    </div>
    <div class="col-md-6 explore-video-es">
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item" src="{$lang['home']['index']['overview']['url']}" frameborder="0" allowfullscreen></iframe>
        </div>
        <h3>{$lang['home']['index']['overview']['title']}</h3>
    </div>
    <div class="col-md-6 explore-video-es">
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item" src="{$lang['home']['index']['getting-started']['url']}" frameborder="0" allowfullscreen></iframe>
        </div>
        <h3>{$lang['home']['index']['getting-started']['title']}</h3>
    </div>
    <div class="col-md-6 explore-video-es">
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item" src="{$lang['home']['index']['promo-02']['url']}" frameborder="0" allowfullscreen></iframe>
        </div>
        <h3>{$lang['home']['index']['promo-02']['title']}</h3>
    </div>       
</section>

   
<section class="row justify-content-center home-testimonial">
    <div class="col-12">
      <h2>{$lang['home']['index']['testimonial']['heading']}</h2>
    </div>
    <div class="col-md-8 col-sm-10 col-12">
        <h3 class="d-block text-left">{$lang['home']['index']['testimonial']['byline']}</h3>
    </div>
    <div class="col-md-8 col-sm-10 col-12 media">
        <img class="align-self-center mr-3 non_responsive rounded" width="125" height="175" src="{$lang['home']['index']['testimonial']['image']}" alt="testimonial image">
        <div class="media-body">
            <p>{$lang['home']['index']['testimonial']['text']}</p>
            <footer class="blockquote-footer">{$lang['home']['index']['testimonial']['author']}</footer>
        </div>
    </div>
</section>

<section class="row home-testimonial-2">
    <div class="col-md-6 explore-video-es">
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item" src="{$lang['home']['index']['testimonial']['video-url-01-es']}" frameborder="0" allowfullscreen></iframe>
        </div>
        <h3>{$lang['home']['index']['testimonial']['video-caption-01-es']}</h3>
    </div>
    <div class="col-md-6 explore-video-es">
        <div class="embed-responsive embed-responsive-16by9">
            <iframe class="embed-responsive-item" src="{$lang['home']['index']['testimonial']['video-url-02-es']}" frameborder="0" allowfullscreen></iframe>
        </div>
        <h3>{$lang['home']['index']['testimonial']['video-caption-02-es']}</h3>
    </div>     
</section>
            
          
            
            
            
            
<section class="row home-donate justify-content-start mb-0">
    <div class="col-12 col-lg-8 col-xl-7 section-content">
        <h2>{$lang['home']['index']['bosco-fund']['heading']}</h2>
        <p>{$lang['home']['index']['bosco-fund']['text']}</p>
        <a class="btn btn-vlc btn-donate" name="donate" target="_BLANK" href="mailto:ahall1@udayton.edu?subject=VLCFF scholarship fund inquiry">
        <i class="fa fa-gift"></i> <span class="btn-text">{$lang['home']['index']['bosco-fund']['button']}</span>
    </a>
    </div>
</section>
</div>
END_OUTPUT;
        
 }       
print $header;
?>
<!-- begin page content -->
<?php print $output ?>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;