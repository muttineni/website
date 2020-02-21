<?php
$page_info['section'] = 'news';
$page_info['page'] = 'article';
$page_info['login_required'] = 0;
list($user_info, $lang, $status_message, $header) = vlc_header($site_info, $page_info);
print $header;
?>
<!-- begin page content -->
<p class="center"><b>UNIVERSITY OF DAYTON TO EXPAND POPULAR ONLINE CLASSES, OFFER RELIGIOUS COURSES OVER THE INTERNET TO HISPANICS</b></p>
<p>DAYTON, Ohio – In an effort to minister to a soaring Hispanic population, the University of Dayton is exploring offering Spanish faith formation classes over the Internet, With a pilot program tentatively slated for early 2005.</p>
<p>In partnership with 21 dioceses in 16 states, the University of Dayton’s Institute for pastoral Initiatives already offers more than two dozen online classes through a program called &quot;Virtual Learning Community for Faith Formation.&quot; Last year, more than 1,000 students took 26 classes in such topics as Catholic beliefs, church history, Jesus, sacraments, scripture, social justice and media literacy. Approximately 20 more courses are expected to be developed within the next two years.</p>
<p>&quot;The Catholic church needs to find many ways to minister to the dramatically growing Hispanic population,&quot; said Sister Angela Ann Zukowski, M.H.S.H., director of the Institute for Pastoral Initiatives and a proponent of expanding the traditional classroom cyberspace. &quot;The Internet is a terrific tool for reaching new audiences and engaging a community of learners who want to study their faith. These are not correspondence courses because the students interact not just with the professor, but also with each other. Hispanic ministry leaders from our partner dioceses strongly support expanding our successful online program and offering courses in Spanish.&quot;</p>
<p>The need is great: The Hispanic population in the United States increased 58 percent between 1990 and 2000, according to the U.S. census. Hispanics now account for 45 percent of all Catholics under the age of 30 in the U.S., according to Instituto Fe Y Vida, which issued a recent report, &quot;The Status of Hispanic Youth and Young Adult Ministry in the United States.&quot;</p>
<p>The &quot;Virtual Learning Community for Faith Formation&quot; initiative began as a pilot program between the University of Dayton and the archdiocese of Cincinnati in 1997. It was expanded in 2000 to include a number or rural dioceses. Today, students are enrolled from more than 100 dioceses in seven countries. In collaboration with the Federation for Asian Bishops’ Office for Social Communications in Manila and St. John’s University in Bangkok, Zukowski is even exploring developing classes for Catholics in Asian countries.</p>
<p>Participants earn continuing education credit from the University if Dayton. Diocesan catechists in training receive credit toward certification. Classes, which take between four to six weeks to complete, cost $40 for students from a partner diocese and $75 for students from other dioceses. The next cycle of courses starts Feb. 29.</p>
<p>&quot;Virtual Learning Community for Faith Formation&quot; is supported by the University of Dayton and the Marianist Province of the United States. For more information, see <?php print vlc_internal_link('http://www.udayton.edu/~vlc/') ?>.</p>
<p>For media interviews, contact Sister Angela Ann Zukowski, MHSH, D.Min., at (937) 229-3126 or via e-mail at <?php print vlc_mailto_link('Angela.Zukowski@notes.udayton.edu', 'Angela.Zukowski@notes.udayton.edu', 'VLCFF') ?>.</p>
<p class="center">
  Released By:<br>
  Office of Public Relations<br>
  300 College Park<br>
  Dayton, OH 45469-1679<br>
  (937) 229-3241<br>
  Fax: (937) 229-3063<br>
  <?php print vlc_external_link('http://www.udayton.edu/', 'http://www.udayton.edu/') ?><br>
  February 17, 2004
</p>
<!-- end page content -->
<?php
$footer = vlc_footer($site_info, $page_info, $user_info, $lang);
print $footer;
?>

