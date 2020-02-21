<?php
$page_info['section'] = 'news';
$page_info['page'] = 'article';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<p class="center"><b>Federation of Asian Bishops' Conferences Online Faith Formation Project in Asia Planned</b></p>
<p>The online adult faith formation program, Virtual Learning Community for Faith Formation (VLCFF), run by the University of Dayton in Ohio Institute for Pastoral Initiatives, will be open for Asian participants. This was resolved by a group of religious educators and communicators in Asia and University of Dayton who met at Assumption University in Bangkok May 20-21.</p>
<p>VLCFF supports &quot;the church's professional ministry of religious education and faith formation in cyberspace.&quot; It offers four- to six-weeks courses via the Internet on the sacraments, scripture, Jesus, Catholic beliefs and others. Students interact and learn like in a community through the <?php print vlc_external_link('University of Dayton\'s', 'http://www.udayton.edu/') ?> website.</p>
<p>An Asian VLCFF will use the university's Internet infrastructure but with courses tailored for Asian needs and situation. Facilitators from the region will devise such courses and interact with students in the Asian way.</p>
<p>The participants of the Bangkok meeting agreed to a three-year study period to identity partner agencies as well as countries and language areas for piloting the project. The FABC's vision of &quot;A New Way of Being Church in Asia&quot; through community building and faith formation in cyberspace also need to be sought.</p>
<p>FABC-OSC chairman, Bishop George Phimphisan of Udon Thani, was present at the meeting along with VLCFF director Sr. Angela Zukowski, MHSH, D.Min.. The FABC-Office of Laity, which pioneered an Asian Integrated Pastoral Approach (AsIPA) for small ecclesial communities in the region, was also represented. The meeting was organized by FABC-OSC, and the Assumption University and Saint John's University in Bangkok.</p>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

