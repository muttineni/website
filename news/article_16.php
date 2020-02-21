<?php
$page_info['section'] = 'news';
$page_info['page'] = 'article';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<p class="center"><b>Announcing:<br>A Social Justice Distance Learning Certificate Program</b></p>
<p>The Roundtable Association of Diocesan Social Action Directors has developed a Social Justice Certificate Program with the University of Dayton Virtual Learning Community for Faith Formation.</p>
<p>The following is a brief overview of the program. For further Information, contact Jeff Korgen at <?php print vlc_mailto_link('jkorgen@nplc.org', 'jkorgen@nplc.org') ?> or (212) 431-7825.</p>
<p><?php print vlc_external_link('http://www.nplc.org/roundtable.htm', 'http://www.nplc.org/roundtable.htm') ?></p>
<p>To earn the certificate, a student completes:</p>
<ol>
  <li>Five 5-Week Courses at University of Dayton VLC</li>
  <li>An Internship with a Diocesan Social Action Office, Parish, or Community Organization.</li>
  <li>The Social Action Summer Institute (Track I) (optional)</li>
</ol>
<p><b>1.  Five 5-Week Courses:</b></p>
<p>Check the Calendar link for when each of the following courses are offered. Please note that each course is only offered once per year.</p>
<ul>
  <li>Adv Catholic Social Teaching</li>
  <li>Scripture and Justice</li>
  <li>History of Catholic Social Action</li>
  <li>The Parish and Social Action</li>
  <li>Poverty in America</li>
</ul>
<p>(other issue-based electives in the future)</p>
<p><b>2.  Internship:</b></p>
<p>Four hours work weekly for four months with either:</p>
<ul>
  <li>
    A diocesan social action office working on a specific project.
    <ul>
      <li>Operation Rice Bowl</li>
      <li>(Arch)Diocesan Legislative Network</li>
      <li>Social Justice Education</li>
      <li>Other</li>
    </ul>
  </li>
  <li>A CCHD-funded community organization.</li>
  <li>Developing a (new or existing) parish social concerns committee to increase its social action activities.</li>
</ul>
<p>Diocesan social action directors or others identified in the local area would serve as mentors.</p>
<p><b>3. Social Action Summer Institute (optional)</b></p>
<p>The 2007 Social Action Summer Institute will be held at Marquette University in Milwaukee, WI, July 15-20. Registration materials will be available in March.</p>
<p><?php print vlc_external_link('http://www.nplc.org/roundtable/events.htm', 'http://www.nplc.org/roundtable/events.htm') ?></p>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

