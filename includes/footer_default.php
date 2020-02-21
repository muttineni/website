<?php
/* General Links */
$general_contact_link = vlc_internal_link($lang['common']['navigation']['contact'], 'contact/');
$general_director_link = vlc_internal_link($lang['common']['navigation']['footer']['general']['director'], 'newsletter/');
$general_gift_certificate_link = vlc_internal_link($lang['common']['navigation']['footer']['general']['gift-cert'], 'gift_certificates/giftcert_info.php');
$general_gift_certificate_check_link = vlc_internal_link($lang['common']['navigation']['footer']['general']['gift-cert-value'], 'gift_certificates/check/');
$general_ipi_link = vlc_external_link($lang['common']['misc']['ipi-long'], 'https://www.udayton.edu/artssciences/ctr/ipi/index.php');

/* Students Links */
$students_become_link = vlc_internal_link($lang['common']['navigation']['footer']['student']['become'], 'profile/become_student.php');
$students_register_link = vlc_internal_link($lang['common']['navigation']['footer']['student']['register'], 'register/');
$students_fees_link = vlc_internal_link($lang['navbar']['navigation']['footer']['student']['fees'], 'courses/payment_policy.php');
$students_scholarship_link = vlc_internal_link($lang['home']['index']['misc']['scholarship-link'], 'news/article.php?article=16334');
$ud_employee_link = vlc_internal_link($lang['home']['index']['misc']['ud-employee-link'], 'ud_employee/');

/* Courses Links */
$courses_catalog_link = vlc_internal_link($lang['navbar']['navigation']['footer']['course']['catalog'], 'courses/courses.php?group_by_track=1');
$courses_certificate_link = vlc_internal_link($lang['navbar']['navigation']['footer']['course']['cert'], 'certificates/');
$courses_calendar_link = vlc_internal_link(date("Y").' '.$lang['navbar']['navigation']['footer']['course']['calendar'], 'calendar/');
$courses_sample_link = vlc_internal_link($lang['navbar']['navigation']['footer']['course']['sample'], 'courses/');

/* Bottom Links */
$copyright = sprintf($lang['common']['misc']['copyright'], '2000 - ' . date('Y'), vlc_internal_link($lang['common']['misc']['vlcff-long'])). ' & '.vlc_external_link($lang['common']['misc']['ud-long'], 'https://www.udayton.edu/');



$footer = <<< END_FOOTER
        </div> <!-- end page-content -->
      </div> <!-- end content -->
    </div> <!-- end main_content -->
  </main> <!-- end main -->
  <footer class="footer">
    <div class="footer-top container">
    <div class="row">
      <div class="col-md-4">
        <h3>{$lang['common']['navigation']['general']}</h3>
        <ul class="list-unstyled footer-nav">
          <li>$general_contact_link</li>
          <li>$general_director_link</li>
          <li>$general_gift_certificate_link</li>
          <li>$general_gift_certificate_check_link</li>
          <li>$general_ipi_link</li>
          <li class="footer-social-row">
            <!-- facebook link -->
            <a class="social-link social-link--facebook" href="http://www.facebook.com/#!/groups/46287548562" target="_blank"   aria-label="Go to the VLCFF's Facebook"><span class="fa-stack fa-lg" alt="facebook logo" aria-hidden="true"><i class="fa  fa-circle fa-stack-2x"></i><i class="fa fa-facebook fa-stack-1x"></i></span></a>

            <!-- twitter link -->
            <a class="social-link social-link--twitter" href="http://twitter.com/VLCFF" target="_blank" aria-label="Go to the VLCFF's   Twitter"><span class="fa-stack fa-lg" alt="twitter logo" aria-hidden="true"><i class="fa fa-circle fa-stack-2x"></i><i   class="fa fa-twitter fa-stack-1x"></i></span></a>
          </li>
        </ul>
      </div>
      <div class="col-md-4">
        <h3>{$lang['common']['navigation']['students']}</h3>
        <ul class="list-unstyled footer-nav">
          <li>$students_become_link</li>
          <li>$students_register_link</li>
          <li>$students_fees_link</li>
          <li>$students_scholarship_link</li>
          <li>$ud_employee_link</li>
        </ul>
      </div>
      <div class="col-md-4 col-6">
        <h3>{$lang['common']['navigation']['courses']}</h3>
        <ul class="list-unstyled footer-nav">
          <li>$courses_catalog_link</li>
          <li>$courses_calendar_link</li>
          <li>$courses_certificate_link</li>
          <li>$courses_sample_link</li>
        </ul>
      </div>
    </div>
    </div><!--End footer-top-->
    <div class="footer-bottom py-3 container-fluid">
      <div class="row">
        <div class="col-sm-6">$copyright</div>
        <div class="col-sm-6 text-right">
          <img src="/images/site/logos-icons/logo_UD_chapel_text_40h.jpg" class="non_responsive" alt={$lang['common']['misc']['ud-logo']} />
        </div>
      </div>
    </div><!--End footer bottom-->
    <!-- jQuery Slim -->
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>

    <!-- Bootstrap 4.0.0 Beta 2 & Popper.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>

    <!-- Angular -->
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.4.5/angular.min.js"></script>
    
    <script type="text/javascript" src="{$site_info['js_url']}{$lang['common']['misc']['current-language-code']}.js"></script>
    <script type="text/javascript" src="{$site_info['js_url']}global.js"></script>

    <script type="text/javascript">
      var _gaq = _gaq || [];
      _gaq.push(['_setAccount', 'UA-26370497-1']);
      _gaq.push(['_setDomainName', 'vlcff.udayton.edu']);
      _gaq.push(['_setAllowLinker', true]);
      _gaq.push(['_trackPageview']);

      (function() {
        var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
      })();
    </script>
  </footer>
END_FOOTER;

return $footer;
?>