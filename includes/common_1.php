<?php
/*******************************************************************************
** list of functions:
* vlc_convert_code
* vlc_convert_html
* vlc_create_hidden_fields
* vlc_db_connect
* vlc_embed_audio
* vlc_embed_video
* vlc_error_handler
* vlc_exit_page
* vlc_export_data
* vlc_external_link
* vlc_footer
* vlc_get_course_info
* vlc_get_event_history
* vlc_get_language
* vlc_get_message
* vlc_get_translation_table
* vlc_get_url_variable
* vlc_get_user_info
* vlc_header
* vlc_insert_events
* vlc_internal_link
* vlc_mailto_link
* vlc_redirect
* vlc_rte_field
* vlc_script_clock
* vlc_select_box
* vlc_show_debug
* vlc_utf8_chr
* vlc_utf8_mail
* vlc_webpay_log
*/
/*******************************************************************************
** convert vlc-code to html
*/
function vlc_convert_code($input, $course_id = '', $css_class = '', $is_rte = 0)
{
  global $site_info;
  /* remove leading and trailing whitespace and slashes added by php when form is submitted */
  $output = stripslashes(trim($input));
  
  
  /* rtl tags */
  $patterns[] = "/\[p-rtl\](.*?)\[\/p-rtl\]/is";
  $replacements[] = "<p dir=\"rtl\" $css_class_string>\\1</p>";
  /* rtl tags */
  $patterns[] = "/\[div-rtl\](.*?)\[\/div-rtl\]/is";
  $replacements[] = "<div dir=\"rtl\" $css_class_string>\\1</div>";
  /* ltr tags */
  $patterns[] = "/\[div-ltr\](.*?)\[\/div-ltr\]/is";
  $replacements[] = "<div dir=\"ltr\" $css_class_string>\\1</div>";  
  
    
  /* convert special html characters */
  $translation_table = vlc_get_translation_table('character');
  $output = strtr($output, $translation_table);
  /* br */
  $output = str_replace('[br]', '<br>', $output);
  /* hr */
  $output = str_replace('[hr]', '<hr width="50%">', $output);
  /* stylesheet class */
  if (strlen($css_class) > 0) $css_class_string = ' class="'.$css_class.'"';
  else $css_class_string = '';
  /* initialize pattern and replacement arrays */
  $patterns = $replacements = array();
  /* paragraph tags */
  $patterns[] = "/\[p\](.*?)\[\/p\]/is";
  $replacements[] = "<p$css_class_string>\\1</p>";
  /* bold text */
  $patterns[] = "/\[b\](.*?)\[\/b\]/is";
  $replacements[] = "<span style=\"font-weight: bold;\">\\1</span>";
  /* italic text */
  $patterns[] = "/\[i\](.*?)\[\/i\]/is";
  $replacements[] = "<span style=\"font-style: italic;\">\\1</span>";
  /* underlined text */
  $patterns[] = "/\[u\](.*?)\[\/u\]/is";
  $replacements[] = "<span style=\"text-decoration: underline;\">\\1</span>";
  /* text color */
  $patterns[] = "/\[color=([a-fA-F0-9]{6})\](.*?)\[\/color\]/is";
  $replacements[] = "<span style=\"color: #\\1;\">\\2</span>";
  /* background color */
  $patterns[] = "/\[bgcolor=([a-fA-F0-9]{6})\](.*?)\[\/bgcolor\]/is";
  $replacements[] = "<span style=\"background-color: #\\1;\">\\2</span>";
  /* blue text - DEPRECATED */
  $patterns[] = "/\[blue\](.*?)\[\/blue\]/is";
  $replacements[] = "<span style=\"color: blue;\">\\1</span>";
  /* red text - DEPRECATED */
  $patterns[] = "/\[red\](.*?)\[\/red\]/is";
  $replacements[] = "<span style=\"color: red;\">\\1</span>";
  /* font size */
  $patterns[] = "/\[size=([^\]]+)\](.*?)\[\/size\]/is";
  $replacements[] = "<span style=\"font-size: \\1;\">\\2</span>";
  /* large text - DEPRECATED */
  $patterns[] = "/\[large\](.*?)\[\/large\]/is";
  $replacements[] = "<span style=\"font-size: large;\">\\1</span>";
  /* small text - DEPRECATED */
  $patterns[] = "/\[small\](.*?)\[\/small\]/is";
  $replacements[] = "<span style=\"font-size: xx-small;\">\\1</span>";
  /* subscript/superscript */
  $patterns[] = "/\[(sub|super)\](.*?)\[\/\\1\]/is";
  $replacements[] = "<span style=\"font-size: xx-small; vertical-align: \\1;\">\\2</span>";
  /* font family */
  $patterns[] = "/\[font=([^\]]+)\](.*?)\[\/font\]/is";
  $replacements[] = "<span style=\"font-family: \\1;\">\\2</span>";
  /* text align */
  $patterns[] = "/\[align=([^\]]+)\](.*?)\[\/align\]/is";
  $replacements[] = "<div style=\"text-align: \\1;\">\\2</div>";
  /* centered text - DEPRECATED */
  $patterns[] = "/\[center\](.*?)\[\/center\]/is";
  $replacements[] = "<div style=\"text-align: center;\">\\1</div>";
  /* blockquote */
  $patterns[] = "/\[blockquote\](.*?)\[\/blockquote\]/is";
  $replacements[] = "<blockquote>\\1</blockquote>";
  /* heading */
  $patterns[] = "/\[h([1-6])\](.*?)\[\/h\\1\]/is";
  $replacements[] = "<h\\1$css_class_string>\\2</h\\1>";
  /* images */
  if (!$is_rte)
  {
    /* image (image files that are stored on external websites) */
    $patterns[] = "/\[img=(http:\/\/[^\[\]\s]+)\]/is";
    $replacements[] = "<img src=\"\\1\">";
    /* image (image files that are stored in "/classes/files/") */
    $patterns[] = "/\[img=([^\]]+)\]/is";
    $replacements[] = "<img src=\"{$site_info['files_url']}\\1\">";
  }
  /* url - "[url]http://www.udayton.edu/[/url]" */
  $patterns[] = "/\[url\](http:\/\/[^\[\]\s]+)\[\/url\]/ise";
  $replacements[] = "vlc_external_link('\\1', '\\1', '$css_class')";
  /* url with descriptive text - "[url=http://www.udayton.edu/]university of dayton[/url]" */
  $patterns[] = "/\[url=(http:\/\/[^\[\]\s]+)\](.*?)\[\/url\]/ise";
  $replacements[] = "vlc_external_link('\\2', '\\1', '$css_class')";
  /* link to a page in the course (facilitator, students, meeting place, vlc-mail) - "[url=meet]click here for meeting place[/url]" - this will create a link to "/classes/meet.php?course=789" */
  $patterns[] = "/\[url=(facilitator|students|meet|mail)\](.*?)\[\/url\]/ise";
  if ($is_rte) $replacements[] = "vlc_internal_link('\\2', '\\1', '', '', $is_rte)";
  else $replacements[] = "vlc_internal_link('\\2', 'classes/\\1.php?course=$course_id', '$css_class')";
  /* link to a course resource or a session homepage - "[url=resource:123]click here for course resource[/url]" - this will create a link to "/classes/resource.php?course=789&resource=123" - "[url=session:123]click here for session home[/url]" - this will create a link to "/classes/session.php?course=789&session=123" */
  $patterns[] = "/\[url=((resource|session):(\d+))\](.*?)\[\/url\]/ise";
  if ($is_rte) $replacements[] = "vlc_internal_link('\\4', '\\1', '', '', $is_rte)";
  else $replacements[] = "vlc_internal_link('\\4', 'classes/\\2.php?course=$course_id&\\2=\\3', '$css_class')";
  /* link to a session resource - "[url=session:123,resource:456]click here for session resource[/url]" - this will create a link to "/classes/resource.php?course=789&session=123&resource=456" */
  $patterns[] = "/\[url=(session:(\d+),resource:(\d+))\](.*?)\[\/url\]/ise";
  if ($is_rte) $replacements[] = "vlc_internal_link('\\4', '\\1', '', '', $is_rte)";
  else $replacements[] = "vlc_internal_link('\\4', 'classes/resource.php?course=$course_id&session=\\2&resource=\\3', '$css_class')";
  /* link to a page in the vlcff site - "[url=/courses/courses.php]click here for a list of courses[/url]" - this will create a link to "/courses/courses.php" */
  $patterns[] = "/\[url=\/([^\]]*)\](.*?)\[\/url\]/ise";
  $replacements[] = "vlc_internal_link('\\2', '\\1', '$css_class', '', $is_rte)";
  /* link to a file in the "/classes/files/" folder - "[url=test_page.html]click here for test page[/url]" - this will create a link to "/classes/files/test_page.html" */
  if (!$is_rte)
  {
    $patterns[] = "/\[url=([^\]]+)\](.*?)\[\/url\]/ise";
    $replacements[] = "vlc_external_link('\\2', '{$site_info['files_url']}\\1', '$css_class')";
  }
  /* e-mail address (will become a "mailto" link) */
  $patterns[] = "/\[email\]([^@<>\s\[\]\(\)]+@[^@<>\s\[\]\(\)]+\.[^@<>\s\[\]\(\)]+)\[\/email\]/ise";
  $replacements[] = "vlc_mailto_link('\\1', '\\1', '', '$css_class')";
  /* e-mail address (with text description) */
  $patterns[] = "/\[email=([^@<>\s\[\]\(\)]+@[^@<>\s\[\]\(\)]+\.[^@<>\s\[\]\(\)]+)\](.*?)\[\/email\]/ise";
  $replacements[] = "vlc_mailto_link('\\2', '\\1', '', '$css_class')";
  /* tables, lists, etc. */
  $patterns[] = "/\[(table|tr|td|ol|ul|li|dl|dt|dd)\](.*?)\[\/\\1\]/is";
  $replacements[] = "<\\1$css_class_string>\\2</\\1>";
  /* replace "patterns" with "replacements" (see http://www.php.net/manual/en/function.preg-replace.php for more information) */
  foreach ($patterns as $index => $pattern)
  {
    $replacement = $replacements[$index];
    while (preg_match($pattern, $output))
    {
      $output = stripslashes(preg_replace($pattern, $replacement, $output));
    }
  }
  /* return formatted text */
  return $output;
}
/*******************************************************************************
** convert html to vlc-code
*/
function vlc_convert_html($input)
{
  /* remove leading and trailing whitespace and slashes added by php when form is submitted */
  $output = stripslashes(trim($input));
  /* font size array (absolute and relative values) */
  $font_size_array = array(1 => 'xx-small', 2 => 'x-small', 3 => 'small', 4 => 'medium', 5 => 'large', 6 => 'x-large', 7 => 'xx-large', '-3' => 'xx-small', '-2' => 'x-small', '-1' => 'small', '+1' => 'medium', '+2' => 'large', '+3' => 'x-large', '+4' => 'xx-large');
  $font_size_list = implode('|', array_unique($font_size_array));
  /* initialize pattern and replacement arrays */
  $patterns = $replacements = array();
  /* remove "no-break" spaces */
  $output = str_replace(chr(160), " ", $output);
  /* remove excess internal whitespace */
  $output = preg_replace("/\s*\n\s*/is", "\n", $output);
  /* convert all html tags to lowercase */
  $output = preg_replace("/<(\/?)(\w+)/ise", "'<\\1'.strtolower('\\2')", $output);
  /* remove xml/php/etc tags */
  $patterns[] = "/<\?[^>]+>/is";
  $replacements[] = "";
  /* remove title tags */
  $patterns[] = "/<title>(.+?)<\/title>/is";
  $replacements[] = "";
  /* remove script tags */
  $patterns[] = "/<script[^>]*>(.+?)<\/script>/is";
  $replacements[] = "";
  /* remove style tags */
  $patterns[] = "/<style[^>]*>(.+?)<\/style>/is";
  $replacements[] = "";
  /* background color (html/hex) */
  $patterns[] = "/<(\w+)([^>]+)bgcolor=(['\"]?)#?([a-fA-F0-9]{6})\\3([^>]*)>(.+?)<\/\\1>/is";
  $replacements[] = "<\\1\\2\\5>[bgcolor=\\4]\\6[/bgcolor]</\\1>";
  /* background color (css/hex) */
  $patterns[] = "/<(\w+)([^>]+)(style=(['\"])[^\\4>]*)background-color:\s*#?([a-fA-F0-9]{6})([^\\4>]*\\4[^>]*)>(.+?)<\/\\1>/is";
  $replacements[] = "<\\1\\2\\3\\6>[bgcolor=\\5]\\7[/bgcolor]</\\1>";
  /* background color (css/rgb) */
  $patterns[] = "/<(\w+)([^>]+)(style=(['\"])[^\\4>]*)background-color:\s*rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)([^\\4>]*\\4[^>]*)>(.+?)<\/\\1>/ise";
  $replacements[] = "'<\\1\\2\\3\\8>[bgcolor='.sprintf('%02x%02x%02x', \\5, \\6, \\7).']\\9[/bgcolor]</\\1>'";
  /* text color (html/hex) */
  $patterns[] = "/<(\w+)([^>]+)color=(['\"]?)#?([a-fA-F0-9]{6})\\3([^>]*)>(.+?)<\/\\1>/is";
  $replacements[] = "<\\1\\2\\5>[color=\\4]\\6[/color]</\\1>";
  /* text color (css/hex) */
  $patterns[] = "/<(\w+)([^>]+)(style=(['\"])[^\\4>]*)(?<!-)color:\s*#?([a-fA-F0-9]{6})([^\\4>]*\\4[^>]*)>(.+?)<\/\\1>/is";
  $replacements[] = "<\\1\\2\\3\\6>[color=\\5]\\7[/color]</\\1>";
  /* text color (css/rgb) */
  $patterns[] = "/<(\w+)([^>]+)(style=(['\"])[^\\4>]*)(?<!-)color:\s*rgb\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*\)([^\\4>]*\\4[^>]*)>(.+?)<\/\\1>/ise";
  $replacements[] = "'<\\1\\2\\3\\8>[color='.sprintf('%02x%02x%02x', \\5, \\6, \\7).']\\9[/color]</\\1>'";
  /* font size (html) */
  $patterns[] = "/<(font)([^>]+)size=(['\"]?)([1-7]|-[1-3]|\+[1-4])\\3([^>]*)>(.+?)<\/\\1>/ise";
  $replacements[] = "'<\\1\\2\\5>[size='.\$font_size_array['\\4'].']\\6[/size]</\\1>'";
  /* font size (css) */
  $patterns[] = "/<(\w+)([^>]+)(style=(['\"])[^\\4>]*)font-size:\s*($font_size_list)([^\\4>]*\\4[^>]*)>(.+?)<\/\\1>/is";
  $replacements[] = "<\\1\\2\\3\\6>[size=\\5]\\7[/size]</\\1>";
  /* font family (html) */
  $patterns[] = "/<(font)([^>]+)face=(['\"])[^\\3>]*?(verdana|sans-serif|serif|monospace)[^\\3>]*\\3([^>]*)>(.+?)<\/\\1>/is";
  $replacements[] = "<\\1\\2\\5>[font=\\4]\\6[/font]</\\1>";
  /* font family (css) */
  $patterns[] = "/<(\w+)([^>]+)(style=(['\"])[^\\4>]*)font-family:[^\\4>;]*?(verdana|sans-serif|serif|monospace)[^\\4>;]*([^\\4>]*\\4[^>]*)>(.+?)<\/\\1>/is";
  $replacements[] = "<\\1\\2\\3\\6>[font=\\5]\\7[/font]</\\1>";
  /* bold text (css) */
  $patterns[] = "/<(\w+)([^>]+)(style=(['\"])[^\\4>]*)font-weight:\s*bold([^\\4>]*\\4[^>]*)>(.+?)<\/\\1>/is";
  $replacements[] = "<\\1\\2\\3\\5>[b]\\6[/b]</\\1>";
  /* italic text (css) */
  $patterns[] = "/<(\w+)([^>]+)(style=(['\"])[^\\4>]*)font-style:\s*italic([^\\4>]*\\4[^>]*)>(.+?)<\/\\1>/is";
  $replacements[] = "<\\1\\2\\3\\5>[i]\\6[/i]</\\1>";
  /* underlined text (css) */
  $patterns[] = "/<(\w+)([^>]+)(style=(['\"])[^\\4>]*)text-decoration:\s*underline([^\\4>]*\\4[^>]*)>(.+?)<\/\\1>/is";
  $replacements[] = "<\\1\\2\\3\\5>[u]\\6[/u]</\\1>";
  /* bold text (html) */
  $patterns[] = "/<(b|strong)>(.+?)<\/\\1>/is";
  $replacements[] = "[b]\\2[/b]";
  /* italic text (html) */
  $patterns[] = "/<(i|em)>(.+?)<\/\\1>/is";
  $replacements[] = "[i]\\2[/i]";
  /* underlined text (html) */
  $patterns[] = "/<u>(.+?)<\/u>/is";
  $replacements[] = "[u]\\1[/u]";
  /* subscript */
  $patterns[] = "/<sub>(.+?)<\/sub>/is";
  $replacements[] = "[sub]\\1[/sub]";
  /* superscript */
  $patterns[] = "/<sup>(.+?)<\/sup>/is";
  $replacements[] = "[super]\\1[/super]";
  /* paragraph */
  $patterns[] = "/<p[^>]*>(.+?)<\/p>/is";
  $replacements[] = "[p]\\1[/p]";
  /* br (line breaks) and hr (horizonal rule) */
  $patterns[] = "/<(br|hr)[^>]*\/?>/is";
  $replacements[] = "[\\1]";
  /* centered text */
  $patterns[] = "/<center>(.+?)<\/center>/is";
  $replacements[] = "[align=center]\\1[/align]";
  /* text align (html) */
  $patterns[] = "/<(\w+)([^>]+)align=(['\"]?)(\w+)\\3([^>]*)>(.+?)<\/\\1>/is";
  $replacements[] = "<\\1\\2\\5>[align=\\4]\\6[/align]</\\1>";
  /* text align (css) */
  $patterns[] = "/<(\w+)([^>]+)(style=(['\"])[^\\4>]*)text-align:\s*(\w+)([^\\4>]*\\4[^>]*)>(.+?)<\/\\1>/is";
  $replacements[] = "<\\1\\2\\3\\6>[align=\\5]\\7[/align]</\\1>";
  /* blockquote */
  $patterns[] = "/<blockquote[^>]*>(.+?)<\/blockquote>/is";
  $replacements[] = "[blockquote]\\1[/blockquote]";
  /* heading */
  $patterns[] = "/<(h[1-6])[^>]*>(.+?)<\/\\1>/is";
  $replacements[] = "[\\1]\\2[/\\1]";
  /* image (both local/internal and remote/external) */
  $patterns[] = "/<img[^>]+src=(['\"])([^>]+?)\\1[^>]*>/is";
  $replacements[] = "[img=\\2]";
  /* "mailto" links */
  $patterns[] = "/<a[^>]+href=(['\"])mailto:([^>]+?)\\1[^>]*>(.+?)<\/a>/is";
  $replacements[] = "[email=\\2]\\3[/email]";
  /* internal links (example: href="http://vlc/meet" links to the meeting place) */
  $patterns[] = "/<a[^>]+href=(['\"])http:\/\/vlc\/(facilitator|students|meet|mail)[^\\1>]*?\\1[^>]*>(.+?)<\/a>/is";
  $replacements[] = "[url=\\2]\\3[/url]";
  /* internal links (example: href="http://vlc/session:123,resource:123" links to a session resource) */
  $patterns[] = "/<a[^>]+href=(['\"])http:\/\/vlc\/(session:\d+,resource:\d+)[^\\1>]*?\\1[^>]*>(.+?)<\/a>/is";
  $replacements[] = "[url=\\2]\\3[/url]";
  /* internal links (example: href="http://vlc/session:123" links to a session) */
  $patterns[] = "/<a[^>]+href=(['\"])http:\/\/vlc\/((resource|session):\d+)[^\\1>]*?\\1[^>]*>(.+?)<\/a>/is";
  $replacements[] = "[url=\\2]\\4[/url]";
  /* internal links (example: href="http://vlc/courses/courses.php" links to the courses page on the main site) */
  $patterns[] = "/<a[^>]+href=(['\"])http:\/\/vlc(\/[^\\1>]*?)\\1[^>]*>(.+?)<\/a>/is";
  $replacements[] = "[url=\\2]\\3[/url]";
  /* all other links */
  $patterns[] = "/<a[^>]+href=(['\"])([^\\1>]+?)\\1[^>]*>(.+?)<\/a>/is";
  $replacements[] = "[url=\\2]\\3[/url]";
  /* tables, lists, etc. */
  $patterns[] = "/<(table|tr|td|ol|ul|li|dl|dt|dd)[^>]*>(.+?)<\/\\1>/is";
  $replacements[] = "[\\1]\\2[/\\1]";
  /* replace "patterns" with "replacements" (see http://www.php.net/manual/en/function.preg-replace.php for more information) */
  foreach ($patterns as $index => $pattern)
  {
    $replacement = $replacements[$index];
    while (preg_match($pattern, $output))
    {
      $output = stripslashes(preg_replace($pattern, $replacement, $output));
    }
  }
  /* strip remaining html tags */
  $output = strip_tags($output);
  /* convert special html characters */
  $translation_table = vlc_get_translation_table('entity');
  $output = strtr($output, $translation_table);
  /* return output */
  return $output;
}
/*******************************************************************************
** convert array to hidden form fields
*/
function vlc_create_hidden_fields($input_array, $parent_key = '')
{
  $output = '';
  foreach ($input_array as $key => $value)
  {
    if (strlen($parent_key)) $name = $parent_key.'['.$key.']';
    else $name = $key;
    if (is_array($value)) $output .= vlc_create_hidden_fields($value, $name);
    else
    {
      $output .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
    }
  }
  return $output;
}
/*******************************************************************************
** connect to the database using variables set in variables.php
*/
function vlc_db_connect()
{
  global $db_info;
  $db_conn = mysql_connect($db_info['server'], $db_info['username'], $db_info['password']);
  mysql_set_charset('utf8', $connection);
  mysql_select_db($db_info['database']);
  mysql_set_charset('latin1',$db_conn); //added by Bob to correct char set mis-match on live server
  return $db_conn;
}
/*******************************************************************************
** embed flash audio
*/
function vlc_embed_audio($audio_file, $width = 20, $height = 20, $config_vars = '')
{
  global $site_info;
  $audio_reg_code = 'NyUzQzVHa2VWViU1QiU4MCU2MEhnSCUzRTk4TE1LJTdCJTVDS1AlMkFPaXo5JTVD';
  $player_url = $site_info['audio_url'].'audio.swf';
  $flash_vars = 'theFile='.$audio_file.$config_vars.'&wimpyReg='.$audio_reg_code;
  $output = <<< END_HTML
<script type="text/javascript">
<!--s
var home_url = '{$site_info['home_url']}';
//-->
</script>
<script type="text/javascript" src="{$site_info['audio_url']}audio.js"></script>
<script type="text/javascript">
<!--//
writeWimpyButton('$audio_file', $width, $height, '$config_vars');
//-->
</script>
<noscript>
  <object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,0,0" width="$width" height="$height" align="middle">
    <param name="movie" value="$player_url" />
    <param name="loop" value="false" />
    <param name="menu" value="false" />
    <param name="quality" value="high" />
    <param name="wmode" value="transparent" />
    <param name="bgcolor" value="#ffffff" />
    <param name="allowScriptAccess" value="sameDomain" />
    <param name="flashvars" value="$flash_vars" />
    <embed src="$player_url" loop="false" menu="false" quality="high" wmode="transparent" bgcolor="#ffffff" width="$width" height="$height" align="middle" allowScriptAccess="sameDomain" flashvars="$flash_vars" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
  </object>
</noscript>
END_HTML;
  return $output;
}
/*******************************************************************************
** embed flash video
*/
function vlc_embed_video($video_file, $width = 320, $height = 240)
{
  global $site_info;
  $player_url = $site_info['video_url'].'video.swf';
  $output = <<< END_HTML
<div id="waspTarget_vlc_video"><a href="https://get.adobe.com/flashplayer/">Flash Player upgrade required</a></div>
<script type="text/javascript">
<!--
var home_url = '{$site_info['home_url']}';
//-->
</script>
<script type="text/javascript" src="{$site_info['video_url']}video.js"></script>
<script type="text/javascript">
// <![CDATA[
var waspConfigs_vlc_video = new Object();
waspConfigs_vlc_video.instanceID="_vlc_video";
waspConfigs_vlc_video.waspSwf="$player_url";
waspConfigs_vlc_video.pageColor="000000";
waspConfigs_vlc_video.r="N08lM0UlN0NMJTVDV3RUJTgwOFpjOUMlM0ElMkZFcCU1RFUlMkMlMkZRVEglM0IzJTVFJTJD";
waspConfigs_vlc_video.f="$video_file";
waspConfigs_vlc_video.me="0";
waspConfigs_vlc_video.ph="$height";
waspConfigs_vlc_video.pw="$width";
waspConfigs_vlc_video.waspSkin="sh_1|||B8B8B8|707070|FFFFFF^sf_1|1|||707070^sv_1|4|16|FFFFFF|000000^sp_1|||B8B8B8|707070|FFFFFF^sb_1||18|F8F8F8|F8F8F8|909090^sg_1|1|16^st_1||12||000000^sr_1|1|3";
writeWasp(waspConfigs_vlc_video);
// ]]>
</script>
END_HTML;
  return $output;
}
/*******************************************************************************
** error handling function
*/
function vlc_error_handler($error_code, $error_desc, $error_file, $error_line, $error_context)
{
  global $_SERVER, $_SESSION, $site_info, $lang;
  /* get current time */
  $error_time = date('Y-m-d H:i:s');
  /* define error types */
  $error_type_array = array
  (
    E_ERROR            => 'Error',
    E_WARNING          => 'Warning',
    E_PARSE            => 'Parsing Error',
    E_NOTICE           => 'Notice',
    E_CORE_ERROR       => 'Core Error',
    E_CORE_WARNING     => 'Core Warning',
    E_COMPILE_ERROR    => 'Compile Error',
    E_COMPILE_WARNING  => 'Compile Warning',
    E_USER_ERROR       => 'User Error',
    E_USER_WARNING     => 'User Warning',
    E_USER_NOTICE      => 'User Notice',
    E_STRICT           => 'Runtime Notice',
    E_DEPRECATED       => 'Deprecated Function'
  );
  /* get error type based on error code */
  $error_type = $error_type_array[$error_code];
  /* get error codes and descriptions */
  $error_message_array[] = 'Error Time: '.$error_time;
  $error_message_array[] = 'PHP Error Code: '.$error_code;
  $error_message_array[] = 'PHP Error Type: '.$error_type;
  $error_message_array[] = 'PHP Error Description: '.$error_desc;
  $error_message_array[] = 'PHP Error occurred on line '.$error_line.' in the following file: '.$error_file;
  if (mysql_errno() > 0) $error_message_array[] = 'MySQL Error Code: '.mysql_errno();
  if (strlen(mysql_error())) $error_message_array[] = 'MySQL Error Description: '.mysql_error();
  /* initialize error message to be displayed to user */
  $error_message = '<li>'.$lang['common']['errors']['error-occurred'].'</li>';
  /* add return link to error message (if available) */
  if (isset($_SERVER['HTTP_REFERER'])) $error_message .= '<li>'.sprintf($lang['common']['errors']['return-link'], $_SERVER['HTTP_REFERER']).'</li>';
  /* show additional error information for user id 559 */
  if (isset($_SESSION['user_info']['user_id']) and $_SESSION['user_info']['user_id'] == 559)
  {
    foreach ($error_message_array as $error_item)
    {
      $error_message .= '<li>'.$error_item.'</li>';
    }
  }
  /* get user id if the user is logged in */
  if (isset($_SESSION['user_info']['user_id'])) $user_id = $_SESSION['user_info']['user_id'];
  else $user_id = 'NULL';
  /* get ip address */
  $ip_address = $_SERVER['REMOTE_ADDR'];
  /* get php session id */
  $php_session_id = session_id();
  /* get environment variables from the "error context" variable */
  $environment_variables = print_r($error_context, true);
  $env_vars_slashes = addslashes($environment_variables);
  /* insert error details into database (if "log errors" is turned on) */
  if ($site_info['log_errors'] == true)
  {
    $insert_error_query = <<< END_QUERY
      INSERT INTO errors (CREATED, user_id, ip_address, php_session_id, error_code, error_type, error_description, error_file, error_line, error_variables)
      VALUES (NULL, $user_id, '$ip_address', '$php_session_id', $error_code, '$error_type', '$error_desc', '$error_file', $error_line, '$env_vars_slashes')
END_QUERY;
    $result = mysql_query($insert_error_query, $site_info['db_conn']);
  }
  /* create e-mail message to send to administrator (or to display on the screen if "log errors" is turned off) */
  $mail_message = "An error has occurred on the VLCFF website:\n";
  foreach ($error_message_array as $error_item)
  {
    $mail_message .= "\n  $error_item";
  }
  $mail_message .= "\n\nEnvironment Variables When Error Occurred:\n\n";
  $mail_message .= $environment_variables;
  $additional_headers = "MIME-Version: 1.0\nFrom: VLCFF Website Administrator <".$site_info['vlcff_email'].">";
  /* if additional errors are occurring on the error page, notify the administrator */
  if (basename($_SERVER['PHP_SELF']) == 'error.php')
  {
    $mail_message = "A recursive error has occurred in the error page (error.php).\n\n$mail_message";
    mail($site_info['webmaster_email'], 'VLCFF Website Error', $mail_message, $additional_headers);
    exit('<div class="error">'.$lang['common']['errors']['recursive-errors'].'</div>');
  }
  
  /********** COMMENTED OUT BY BOB DUE TO PHP 5.5 UPDATE ***************************
  // if "log errors" is turned on, send an e-mail message to the administrator and go to the error page
  if ($site_info['log_errors'] == true) mail($site_info['webmaster_email'].', '.$site_info['support_email'], 'VLCFF Website Error', $mail_message, $additional_headers);
  // otherwise, display error message on the screen and stop processing
  else
  {
    print '<pre>'.htmlspecialchars($mail_message).'</pre>';
    exit;
  }
  vlc_exit_page($error_message, 'error', 'misc/error.php?error=php');
 
 ************* END BOB COMMENT ************************************************/ 
  
}
/*******************************************************************************
** exit the page and display either error message or success message
*/

function vlc_exit_page($status_message, $message_type, $redirect_to)
{
  global $_SESSION, $lang, $site_info;
  if ($message_type == 'login-error') $status_message = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
  <i class="fa fa-exclamation-triangle"></i> '.sprintf($lang['common']['errors']['error-list-single'], $status_message).'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
  elseif ($message_type == 'access-error') $status_message = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
  <i class="fa fa-exclamation-triangle"></i> '.sprintf($lang['common']['errors']['access-error'], $status_message).'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
  elseif ($message_type == 'error') $status_message = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
  <i class="fa fa-exclamation-triangle"></i> '.sprintf($lang['common']['errors']['error-list-multiple'], $status_message).'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
  elseif ($message_type == 'success') $status_message = '<div class="alert alert-success alert-dismissible fade show" role="alert"><i class="fa fa-check-square-o"></i> '.$status_message.'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>';
  $_SESSION['status_message'] = $status_message;
  vlc_redirect($redirect_to);
}
/*******************************************************************************
** export data to csv or pdf
*/
function vlc_export_data($input_array, $filename, $format, $orientation = 'P', $font = 'Times', $style = '', $size = 10)
{
  global $site_info, $page_info, $lang;
  switch ($format)
  {
    case 1:
      $output_array = array();
      foreach ($input_array as $row) $output_array[] = '"'.join('","', $row).'"';
      $output = join("\n", $output_array);
      header('Content-Type: text/x-csv');
      header('Content-Disposition: attachment; filename="export-'.$filename.'-'.date('YmdHis').'.csv"');
      header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
      header('Pragma: public');
      print $output;
      exit;
      break;
    case 2:
      include_once($site_info['pdf_path'].'pdf.php');
      $output = new PDF($font, $style, $size);
      $output->Open();
      $output->SetDisplayMode('fullwidth', 'single');
      $output->SetTitle($lang['common']['misc']['vlcff'].' @ '.$lang['common']['misc']['ud'].' > '.$lang[$page_info['section']]['section-title'].' > '.$lang[$page_info['section']][$page_info['page']]['page-title']);
      $output->SetAuthor($lang['common']['misc']['ipi-long'].' ('.$lang['common']['misc']['ud-long'].')');
      $output->SetSubject($lang['common']['misc']['vlcff'].' Data PDF Export');
      $output->SetCreator('FPDF 1.53 (http://www.fpdf.org/)');
      $output->AddPage($orientation);
      $output->Heading($output->title);
      $max_width = $output->GetPageWidth();
      $num_fields = count($input_array[0]);
      $avg_width = $max_width / $num_fields;
      $column_width_array = array();
      for ($i = 0; $i < $num_fields; $i++) $column_width_array[] = $avg_width;
      $output->SetWidths($column_width_array);
      $output->SetHeaderRowData(array_shift($input_array));
      $output->HeaderRow();
      foreach ($input_array as $row) $output->Row($row);
      $output->Output('export-'.$filename.'-'.date('YmdHis').'.pdf', 'D');
      exit;
      break;
    default:
      trigger_error('Invalid Export Format.');
  }
}
/*******************************************************************************
** create link to external website (for web pages outside of the vlcff website)
*/
function vlc_external_link($text, $url, $css_class = '')
{
  if (strlen($text) > 60 and strpos($text, 'http://') == 0) $text = str_replace('/', '/ ', $text);
  if (strlen($css_class) > 0) $css_class = ' class="'.$css_class.'"';
  $external_link = "<a href=\"$url\" target=\"_blank\"$css_class>$text</a>";
  return $external_link;
}
/*******************************************************************************
** return page footer to script
*/
function vlc_footer($site_info, $page_info, $user_info, $lang)
{
  global $_GET, $_SERVER;
  /* cms section */
  if ($page_info['section'] == 'cms') $keyword = 'cms';
  /* classes section */
  elseif ($page_info['section'] == 'classes') $keyword = 'classes';
  /* most pages on the site */
  else $keyword = 'default';
  /* get print variable from url (if it exists) */
  if (isset($_GET['print'])) $print = $_GET['print'];
  else $print = 0;
  /* printable page */
  if ($print == true) $keyword .= '_print';
  /* define footer file */
  $footer_file = $site_info['includes_path'].'footer_'.$keyword.'.php';
  /* set footer variable to contents of footer file */
  $footer = include_once($footer_file);
  /* append debugging variables */
  if ($site_info['show_debug']) $footer .= vlc_show_debug();
  /* append closing tags */
  $footer .= '</body></html>';
  /* return footer to script */
  return $footer;
}
/*******************************************************************************
** retrieve course information from the database
*/
function vlc_get_course_info($site_info, $course_id, $is_action_page = 0)
{
  global $_SERVER, $lang;
  $course_info_query = <<< END_QUERY
    SELECT s.course_subject_id, l.code AS language_code, c.description AS title, UNIX_TIMESTAMP(y.cycle_start) AS start_date, c.is_sample
    FROM course_subjects AS s, languages AS l, courses AS c, cycles AS y
    WHERE s.language_id = l.language_id
    AND s.course_subject_id = c.course_subject_id
    AND c.cycle_id = y.cycle_id
    AND c.course_id = $course_id
END_QUERY;
  $result = mysql_query($course_info_query, $site_info['db_conn']);
  if (mysql_num_rows($result) == 1) $course_info = mysql_fetch_array($result);
  else trigger_error("INVALID COURSE ID: $course_id");
  if ($course_info['language_code'] != $lang['common']['misc']['current-language-code']) $lang = vlc_get_language($course_info['language_code']);
  /* do not allow users to execute "action" pages in "sample" courses */
  if ($is_action_page and $course_info['is_sample']) vlc_exit_page($lang['common']['errors']['sample-course'], 'access-error', substr($_SERVER['HTTP_REFERER'], strlen('http://'.$_SERVER['HTTP_HOST'].$site_info['home_url'])));
  $course_info['style_dir'] = str_pad($course_info['course_subject_id'], 3, '0', STR_PAD_LEFT);
  $course_info['logo'] = $course_info['style_dir'].'_logo.jpg';
  $resources_query = <<< END_QUERY
    SELECT r.resource_id, r.resource_type_id
    FROM resources AS r
    WHERE r.resource_type_id IN (6, 37, 46, 47)
    AND r.course_subject_id = {$course_info['course_subject_id']}
END_QUERY;
  $result = mysql_query($resources_query, $site_info['db_conn']);
  $course_info['resources'] = array();
  while ($record = mysql_fetch_array($result)) $course_info['resources'][$record['resource_type_id']] = $record;
  $session_info_query = <<< END_QUERY
    SELECT s.session_id, s.display_order, s.description AS session_title
    FROM courses AS c, sessions AS s
    WHERE c.course_subject_id = s.course_subject_id
    AND c.course_id = $course_id
    ORDER BY s.display_order
END_QUERY;
  $result = mysql_query($session_info_query, $site_info['db_conn']);
  $course_info['num_sessions'] = mysql_num_rows($result);
  $course_info['sessions'] = array();
  $i = 0;
  while ($record = mysql_fetch_array($result))
  {
    $j = $i * 7;
    $k = $j + 6;
    $course_info['sessions'][$record['session_id']] = $record;
    $course_info['sessions'][$record['session_id']]['start_date'] = strtotime("+$j days", $course_info['start_date']);
    $course_info['sessions'][$record['session_id']]['end_date'] = strtotime("+$k days", $course_info['start_date']);
    $i++;
  }
  return $course_info;
}
/*******************************************************************************
** get database event history
*/
function vlc_get_event_history($event_type_array, $entity_id)
{
  global $site_info;
  $event_type_list = join(', ', $event_type_array);
  $event_history_query = <<< END_QUERY
    SELECT v.description,
      CONCAT(u.first_name, ' ', u.last_name) AS CREATEDBY,
      DATE_FORMAT(e.CREATED, '%c/%e/%Y %l:%i:%s %p') AS CREATED
    FROM events AS e, event_types AS v, users AS u
    WHERE e.event_type_id = v.event_type_id
    AND e.CREATEDBY = u.user_id
    AND e.event_type_id IN ($event_type_list)
    AND e.entity_id = $entity_id
    ORDER BY e.event_id
END_QUERY;
  $result = mysql_query($event_history_query, $site_info['db_conn']);
  $event_history_array = array();
  while ($record = mysql_fetch_array($result)) $event_history_array[] = $record;
  $output = '';
  if (count($event_history_array))
  {
    $output .= '<h3>Database Record Update History</h3>';
    $output .= '<ul><li><a href="javascript:show_hide_content(\'event-history\')">[+/-] Show/Hide Database Record Update History</a></li></ul>';
    $output .= '<div id="event-history" style="display: none;">';
    $output .= '<table border="1" cellpadding="5" cellspacing="0" width="100%">';
    $output .= '<tr><th>Date</th><th>User</th><th>Event</th></tr>';
    foreach ($event_history_array as $event)
    {
      $output .= '<tr>';
      $output .= '<td>'.$event['CREATED'].'</td>';
      $output .= '<td>'.$event['CREATEDBY'].'</td>';
      $output .= '<td>'.$event['description'].'</td>';
      $output .= '</tr>';
    }
    $output .= '</table>';
    $output .= '</div>';
  }
  return $output;
}
/*******************************************************************************
** check for language preference and include appropriate language file
*/
function vlc_get_language($language_code = '')
{
  global $_GET, $_COOKIE, $site_info, $page_info, $lang;
  $set_lang_cookie = 1;
  if (strlen($language_code) > 0) $set_lang_cookie = 0;
  elseif (isset($_GET['lang'])) $language_code = $_GET['lang'];
  elseif (isset($_COOKIE['vlc_lang']))
  {
    $language_code = $_COOKIE['vlc_lang'];
    $set_lang_cookie = 0;
  }
  else $language_code = 'en';
  if (!in_array($language_code, array('en', 'es'))) vlc_redirect('misc/error.php?error=404');
  if ($set_lang_cookie == true) setcookie('vlc_lang', $language_code, time()+2592000, '/');
  /* include appropriate language file - language file defines $lang variable */
  $lang_file = $site_info['language_path'].$language_code.'.php';
  if (file_exists($lang_file)) $lang = include_once($lang_file);
  else $lang = array();
  return $lang;
}
/*******************************************************************************
** retrieve error or success message from session variable
*/
function vlc_get_message()
{
  global $_SESSION;
  if (isset($_SESSION['status_message']) and strlen($_SESSION['status_message']))
  {
    $status_message = $_SESSION['status_message'];
    $_SESSION['status_message'] = null;
  }
  else $status_message = '';
  return $status_message;
}
/*******************************************************************************
** get html entity to character translation table
*/
function vlc_get_translation_table($table = 'entity')
{
  $entity_array = array
  (
    34   => 'quot',     38   => 'amp',      39   => 'apos',     60   => 'lt',
    62   => 'gt',       128  => '',         129  => '',         130  => '',
    131  => '',         132  => '',         133  => '',         134  => '',
    135  => '',         136  => '',         137  => '',         138  => '',
    139  => '',         140  => '',         141  => '',         142  => '',
    143  => '',         144  => '',         145  => '',         146  => '',
    147  => '',         148  => '',         149  => '',         150  => '',
    151  => '',         152  => '',         153  => '',         154  => '',
    155  => '',         156  => '',         157  => '',         158  => '',
    159  => '',         160  => 'nbsp',     161  => 'iexcl',    162  => 'cent',
    163  => 'pound',    164  => 'curren',   165  => 'yen',      166  => 'brvbar',
    167  => 'sect',     168  => 'uml',      169  => 'copy',     170  => 'ordf',
    171  => 'laquo',    172  => 'not',      173  => 'shy',      174  => 'reg',
    175  => 'macr',     176  => 'deg',      177  => 'plusmn',   178  => 'sup2',
    179  => 'sup3',     180  => 'acute',    181  => 'micro',    182  => 'para',
    183  => 'middot',   184  => 'cedil',    185  => 'sup1',     186  => 'ordm',
    187  => 'raquo',    188  => 'frac14',   189  => 'frac12',   190  => 'frac34',
    191  => 'iquest',   192  => 'Agrave',   193  => 'Aacute',   194  => 'Acirc',
    195  => 'Atilde',   196  => 'Auml',     197  => 'Aring',    198  => 'AElig',
    199  => 'Ccedil',   200  => 'Egrave',   201  => 'Eacute',   202  => 'Ecirc',
    203  => 'Euml',     204  => 'Igrave',   205  => 'Iacute',   206  => 'Icirc',
    207  => 'Iuml',     208  => 'ETH',      209  => 'Ntilde',   210  => 'Ograve',
    211  => 'Oacute',   212  => 'Ocirc',    213  => 'Otilde',   214  => 'Ouml',
    215  => 'times',    216  => 'Oslash',   217  => 'Ugrave',   218  => 'Uacute',
    219  => 'Ucirc',    220  => 'Uuml',     221  => 'Yacute',   222  => 'THORN',
    223  => 'szlig',    224  => 'agrave',   225  => 'aacute',   226  => 'acirc',
    227  => 'atilde',   228  => 'auml',     229  => 'aring',    230  => 'aelig',
    231  => 'ccedil',   232  => 'egrave',   233  => 'eacute',   234  => 'ecirc',
    235  => 'euml',     236  => 'igrave',   237  => 'iacute',   238  => 'icirc',
    239  => 'iuml',     240  => 'eth',      241  => 'ntilde',   242  => 'ograve',
    243  => 'oacute',   244  => 'ocirc',    245  => 'otilde',   246  => 'ouml',
    247  => 'divide',   248  => 'oslash',   249  => 'ugrave',   250  => 'uacute',
    251  => 'ucirc',    252  => 'uuml',     253  => 'yacute',   254  => 'thorn',
    255  => 'yuml',     338  => 'OElig',    339  => 'oelig',    352  => 'Scaron',
    353  => 'scaron',   376  => 'Yuml',     402  => 'fnof',     710  => 'circ',
    732  => 'tilde',    913  => 'Alpha',    914  => 'Beta',     915  => 'Gamma',
    916  => 'Delta',    917  => 'Epsilon',  918  => 'Zeta',     919  => 'Eta',
    920  => 'Theta',    921  => 'Iota',     922  => 'Kappa',    923  => 'Lambda',
    924  => 'Mu',       925  => 'Nu',       926  => 'Xi',       927  => 'Omicron',
    928  => 'Pi',       929  => 'Rho',      931  => 'Sigma',    932  => 'Tau',
    933  => 'Upsilon',  934  => 'Phi',      935  => 'Chi',      936  => 'Psi',
    937  => 'Omega',    945  => 'alpha',    946  => 'beta',     947  => 'gamma',
    948  => 'delta',    949  => 'epsilon',  950  => 'zeta',     951  => 'eta',
    952  => 'theta',    953  => 'iota',     954  => 'kappa',    955  => 'lambda',
    956  => 'mu',       957  => 'nu',       958  => 'xi',       959  => 'omicron',
    960  => 'pi',       961  => 'rho',      962  => 'sigmaf',   963  => 'sigma',
    964  => 'tau',      965  => 'upsilon',  966  => 'phi',      967  => 'chi',
    968  => 'psi',      969  => 'omega',    977  => 'thetasym', 978  => 'upsih',
    982  => 'piv',      8194 => 'ensp',     8195 => 'emsp',     8201 => 'thinsp',
    8204 => 'zwnj',     8205 => 'zwj',      8206 => 'lrm',      8207 => 'rlm',
    8211 => 'ndash',    8212 => 'mdash',    8216 => 'lsquo',    8217 => 'rsquo',
    8218 => 'sbquo',    8220 => 'ldquo',    8221 => 'rdquo',    8222 => 'bdquo',
    8224 => 'dagger',   8225 => 'Dagger',   8226 => 'bull',     8230 => 'hellip',
    8240 => 'permil',   8242 => 'prime',    8243 => 'Prime',    8249 => 'lsaquo',
    8250 => 'rsaquo',   8254 => 'oline',    8260 => 'frasl',    8364 => 'euro',
    8465 => 'image',    8472 => 'weierp',   8476 => 'real',     8482 => 'trade',
    8501 => 'alefsym',  8592 => 'larr',     8593 => 'uarr',     8594 => 'rarr',
    8595 => 'darr',     8596 => 'harr',     8629 => 'crarr',    8656 => 'lArr',
    8657 => 'uArr',     8658 => 'rArr',     8659 => 'dArr',     8660 => 'hArr',
    8704 => 'forall',   8706 => 'part',     8707 => 'exist',    8709 => 'empty',
    8711 => 'nabla',    8712 => 'isin',     8713 => 'notin',    8715 => 'ni',
    8719 => 'prod',     8721 => 'sum',      8722 => 'minus',    8727 => 'lowast',
    8730 => 'radic',    8733 => 'prop',     8734 => 'infin',    8736 => 'ang',
    8743 => 'and',      8744 => 'or',       8745 => 'cap',      8746 => 'cup',
    8747 => 'int',      8756 => 'there4',   8764 => 'sim',      8773 => 'cong',
    8776 => 'asymp',    8800 => 'ne',       8801 => 'equiv',    8804 => 'le',
    8805 => 'ge',       8834 => 'sub',      8835 => 'sup',      8836 => 'nsub',
    8838 => 'sube',     8839 => 'supe',     8853 => 'oplus',    8855 => 'otimes',
    8869 => 'perp',     8901 => 'sdot',     8968 => 'lceil',    8969 => 'rceil',
    8970 => 'lfloor',   8971 => 'rfloor',   9001 => 'lang',     9002 => 'rang',
    9674 => 'loz',      9824 => 'spades',   9827 => 'clubs',    9829 => 'hearts',
    9830 => 'diams'
  );
  $translation_table = array();
  foreach ($entity_array as $code => $entity)
  {
    switch ($table)
    {
      case 'entity':
        if (strlen($entity))
        {
          $key = "&$entity;";
          $value = vlc_utf8_chr($code);
          $translation_table[$key] = $value;
        }
        $key = "&#$code;";
        $value = vlc_utf8_chr($code);
        $translation_table[$key] = $value;
        break;
      case 'character':
        $key = vlc_utf8_chr($code);
        $value = "&#$code;";
        $translation_table[$key] = $value;
        break;
    }
  }
  return $translation_table;
}
/*******************************************************************************
** retrieve variable from url - specifically an integer such as course id, session id, resource id, etc.
*/
function vlc_get_url_variable($site_info, $key, $required, $course_id = '')
{
  global $_GET, $lang;
  if (isset($_GET[$key]) and is_numeric($_GET[$key])) return $_GET[$key];
  elseif ($required == false) return null;
  else
  {
    $lang = vlc_get_language();
    switch ($key)
    {
      case 'course':
        $message = $lang['classes']['common']['errors']['invalid-course'];
        $url = '';
        break;
      case 'session':
        $message = $lang['classes']['common']['errors']['invalid-session'];
        $url = 'classes/?course='.$course_id;
        break;
      case 'resource':
        $message = $lang['classes']['common']['errors']['invalid-resource'];
        $url = 'classes/?course='.$course_id;
        break;
      case 'mail':
        $message = $lang['classes']['common']['errors']['invalid-mail-message'];
        $url = 'classes/mail.php?course='.$course_id;
        break;
      case 'recipient':
        $message = $lang['classes']['common']['errors']['invalid-recipient'];
        $url = 'classes/mail.php?course='.$course_id;
        break;
      case 'action':
        $message = $lang['classes']['common']['errors']['invalid-action'];
        $url = 'classes/mail.php?course='.$course_id;
        break;
      case 'folder':
        $message = $lang['classes']['common']['errors']['invalid-folder'];
        $url = 'classes/mail.php?course='.$course_id;
        break;
      case 'sort':
        $message = $lang['classes']['common']['errors']['invalid-sort'];
        $url = 'classes/mail.php?course='.$course_id;
        break;
      case 'dir':
        $message = $lang['classes']['common']['errors']['invalid-direction'];
        $url = 'classes/mail.php?course='.$course_id;
        break;
      case 'subject':
        $message = $lang['classes']['common']['errors']['invalid-course-subject'];
        $url = 'cms/';
        break;
      case 'type':
        $message = $lang['classes']['common']['errors']['invalid-resource-type'];
        $url = 'cms/';
        break;
      case 'cycle':
        $message = $lang['classes']['common']['errors']['invalid-cycle'];
        $url = 'cms/';
        break;
    }
    vlc_exit_page('<li>'.$message.'</li>', 'error', $url);
  }
}
/*******************************************************************************
** get user info
*/
function vlc_get_user_info($login_required, $is_action_page = 0)
{
  global $_SERVER, $_SESSION, $_COOKIE, $site_info, $page_info, $lang;
  if (isset($_SESSION['user_info']['logged_in']) and $_SESSION['user_info']['logged_in'] == true)
  {
    /* user is logged in */
    $user_info = $_SESSION['user_info'];
    /*
    ** do not execute "action" pages for "guest" users and users registered for a course as "guest"
    ** if the current page is an "action" page and one of the following is true:
    **    either the current user's user role is "guest" (7)
    **    or the current page is in the classes section and the current user is registered for the course as "guest" (7)
    */
    if ($is_action_page)
    {
      if (isset($_SERVER['HTTP_REFERER'])) $return_url = substr($_SERVER['HTTP_REFERER'], strlen('http://'.$_SERVER['HTTP_HOST'].$site_info['home_url']));
      else $return_url = '';
      if (in_array(7, $user_info['user_roles'])) vlc_exit_page($lang['common']['errors']['guest-account'], 'access-error', $return_url);
      if ($page_info['section'] == 'classes' and in_array($page_info['course_id'], array_keys($user_info['courses'])) and $user_info['courses'][$page_info['course_id']] == 7) vlc_exit_page($lang['common']['errors']['guest-registration'], 'access-error', $return_url);
    }
  }
  else
  {
    /* user is not logged in */
    $user_info['logged_in'] = 0;
    if (isset($_COOKIE['vlc_username'])) $user_info['username'] = $_COOKIE['vlc_username'];
    else $user_info['username'] = '';
    /* if the current page requires that the user be logged in, send them to the login page */
    if ($login_required == true)
    {
      /* get current url and forward after successful login */
      $_SESSION['continue_url'] = substr($_SERVER['REQUEST_URI'], strlen($site_info['home_url']));
      $error_message = '<li>'.$lang['common']['errors']['not-logged-in'].'</li><li>'.$lang['common']['errors']['session-expired'].'</li>';
      vlc_exit_page($error_message, 'login-error', 'profile/');
    }
  }
  return $user_info;
}
/*******************************************************************************
** set user_info, lang, status message, and header and return all to script
*/
function vlc_header($site_info, $page_info)
{
  global $_GET, $_SERVER, $user_info, $lang;
  /* get language variable */
  $lang = vlc_get_language();
  /* get user info */
  $user_info = vlc_get_user_info($page_info['login_required']);
  /* get status message */
  $status_message = vlc_get_message();
  /* cms section (content management system) */
  if ($page_info['section'] == 'cms')
  {
    $keyword = 'cms';
    /* if the user is not an "admin" or a "course technician", do not allow access */
    if (!in_array(1, $user_info['user_roles']) and !in_array(9, $user_info['user_roles'])) vlc_exit_page($lang['common']['errors']['cms-access'], 'access-error', 'profile/');
  }
  /* classes section */
  elseif ($page_info['section'] == 'classes')
  {
    $keyword = 'classes';
    $course_info = vlc_get_course_info($site_info, $page_info['course_id']);
    /*
    ** exit page if:
    **  (1) the user is not registered for the course they are trying to access and
    **  (2) the user is not an administrator (admins can go into any course) and
    **  (3) the course is not a sample course (any registered user can access a sample course)
    */
    if (in_array($page_info['course_id'], array_keys($user_info['courses'])) == false
      and in_array(1, $user_info['user_roles']) == false
      and $course_info['is_sample'] == false)
    {
      vlc_exit_page($lang['classes']['common']['errors']['not-registered'], 'access-error', 'profile/');
    }
  }
  /* most pages on the site */
  else $keyword = 'default';
  /* send http headers */
  header('Content-Type: text/html; charset=utf-8');
  header('Content-Language: '.$lang['common']['misc']['current-language-code']);
  /* get print variable from url (if it exists) */
  if (isset($_GET['print'])) $print = $_GET['print'];
  else $print = 0;
  /* printable page */
  if ($print == true) $keyword .= '_print';
  /* define header file */
  $header_file = $site_info['includes_path'].'header_'.$keyword.'.php';
  /* set header variable to contents of header file */
  $header = include_once($header_file);
  /* return value differs based on whether the page is in the classes section or a different section */
  if ($page_info['section'] == 'classes') return array($user_info, $course_info, $lang, $status_message, $header);
  else return array($user_info, $lang, $status_message, $header);
}
/*******************************************************************************
** insert database events
*/
function vlc_insert_events($events, $created_by = null)
{
  global $site_info, $user_info;
  if (!isset($created_by) and isset($user_info['user_id'])) $created_by = $user_info['user_id'];
  else $created_by = 'NULL';
  $insert_query_array = array();
  foreach ($events as $event) $insert_query_array[] = '(NULL, '.$event[0].', '.$event[1].', NULL, NULL, NULL, '.$created_by.')';
  if (count($insert_query_array))
  {
    $insert_events_query = 'INSERT INTO events VALUES '.join(', ', $insert_query_array);
    $result = mysql_query($insert_events_query, $site_info['db_conn']);
    if (mysql_affected_rows() < 1) trigger_error('INSERT FAILED: events');
  }
  return;
}
/*******************************************************************************
** create link to a page in the vlcff website
*/
function vlc_internal_link($text, $url = '', $css_class = '', $title = '', $is_rte = 0, $new_win = 0, $confirm = 0)
{
  global $site_info;
  if ($is_rte) $internal_link = '<a href="http://vlc/'.$url.'">'.$text.'</a>';
  else
  {
    if (strlen($css_class) > 0) $css_class = ' class="'.$css_class.'"';
    if (strlen($title)) $title = ' title="'.$title.'"';
    if ($new_win) $target = ' target="_blank"';
    else $target = '';
    if ($confirm) $js_confirm = ' onclick="return confirm(\'Delete?\');"';
    else $js_confirm = '';
    $internal_link = '<a href="'.$site_info['home_url'].$url.'"'.$css_class.$title.$target.$js_confirm.'>'.$text.'</a>';
  }
  return $internal_link;
}
/*******************************************************************************
** create "mailto" link (for messages to persons outside of the vlcff)
*/
function vlc_mailto_link($text, $email, $subject = '', $css_class = '')
{
  if (strlen($subject)) $subject = '?subject='.$subject;
  if (strlen($css_class) > 0) $css_class = ' class="'.$css_class.'"';
  $mail_link = '<a href="mailto:'.$email.$subject.'"'.$css_class.'>'.$text.'</a>';
  return $mail_link;
}
/*******************************************************************************
** redirect user to new page
*/
function vlc_redirect($location = '')
{
  global $_SERVER, $site_info;
  $redirect_to = 'http://'.$_SERVER['HTTP_HOST'].$site_info['home_url'].$location;
  header("Location: $redirect_to");
  exit;
}
/*******************************************************************************
** rich text editor field
*/
function vlc_rte_field($name, $value = '', $course_id = '', $width = 700, $height = 350)
{
  global $site_info, $lang, $_GET;
  /* show regular plain text field for printable pages */
  if (isset($_GET['print']) and $_GET['print'])
  {
    $output = '<textarea name="'.$name.'" rows="10" cols="60" class="text-area">'.$value.'</textarea>';
  }
  else
  {
    $value_rte = vlc_convert_code($value, $course_id, '', 1);
    $value_rte = preg_replace("/\s+/is", " ", $value_rte);
    $output = <<< END_HTML
<script type="text/javascript" src="{$site_info['rtef_url']}{$lang['common']['misc']['current-language-code']}.js"></script>
<script type="text/javascript" src="{$site_info['rtef_url']}xhtml.js"></script>
<script type="text/javascript" src="{$site_info['rtef_url']}richtext.js"></script>
<script type="text/javascript">
<!--//
initRTE('{$site_info['rtef_url']}images/', '{$site_info['rtef_url']}', '', true);
//-->
</script>
<script type="text/javascript">
<!--//
writeRichText('{$name}_rte', '$value_rte', '', $width, $height, true, false, false);
//-->
</script>
<noscript>
<textarea name="$name" rows="10" cols="60" class="text-area">$value</textarea>
</noscript>
END_HTML;
  }
  return $output;
}
/*******************************************************************************
** log script execution time
*/
function vlc_script_clock($close = 0, $label = '', $delimiter = ' : ')
{
  global $clock_array;
  if (strlen($label)) $label = $label.$delimiter;
  $time = $label.array_sum(split(' ', microtime()));
  if (!is_array($clock_array)) $clock_array = array($time);
  else $clock_array[] = $time;
  if ($close)
  {
    $filename = '/usr/users/vlc/tmp/_php_script_clock_'.date('YmdHis').'.log';
    $clock_log = join("\r\n", $clock_array);
    $handle = fopen($filename, 'w');
    if (fwrite($handle, $clock_log) === false) trigger_error('Unable to write to file "'.$filename.'".');
    fclose($handle);
  }
}
/*******************************************************************************
** dynamically create select box
*/
function vlc_select_box($source, $source_type, $field_name, $selected_value, $required, $css_class = '', $form_field_name = '', $element_id = '')
{
  global $site_info, $lang;
  if (strlen($css_class) > 0) $css_class = ' class="'.$css_class.'"';
  if (strlen($form_field_name) == 0) $form_field_name = $field_name;
  if (strlen($element_id) > 0) $element_id = ' id="'.$element_id.'"';
  $return_value = '<select name="'.$form_field_name.'"'.$css_class.$element_id.'>';
  if ($required == false) $return_value .= '<option value="NULL">'.$lang['common']['misc']['select-one'].'</option>';
  if ($source_type == 'table')
  {
    $sql_query = 'SELECT '.$field_name.' AS id, description FROM '.$source.' ORDER BY description';
    $result = mysql_query($sql_query, $site_info['db_conn']);
    $num_rows = mysql_num_rows($result);
    if ($num_rows)
    {
      while ($row = mysql_fetch_array($result))
      {
        $id = $row['id']; $description = $row['description'];
        if ((int)$id == (int)$selected_value) $return_value .= '<option value="'.$id.'" selected>'.$description.'</option>';
        else $return_value .= '<option value="'.$id.'">'.$description.'</option>';
      }
    }
    else $return_value .= '<option>select box error: no rows returned</option>';
  }
  elseif ($source_type == 'array')
  {
    if (count($source) > 0)
    {
      /* multiple (grouped) arrays */
      if (is_array(current($source)))
      {
        foreach ($source as $group)
        {
          $return_value .= '<optgroup label="'.$group['label'].'">';
          foreach ($group['options'] as $id => $description)
          {
            if ((int)$id == (int)$selected_value) $return_value .= '<option value="'.$id.'" selected>'.$description.'</option>';
            else $return_value .= '<option value="'.$id.'">'.$description.'</option>';
          }
          $return_value .= '</optgroup>';
        }
      }
      /* single array */
      else
      {
        foreach ($source as $id => $description)
        {
          if ((int)$id == (int)$selected_value) $return_value .= '<option value="'.$id.'" selected>'.$description.'</option>';
          else $return_value .= '<option value="'.$id.'">'.$description.'</option>';
        }
      }
    }
    else $return_value .= '<option>select box error: array empty</option>';
  }
  else $return_value .= '<option>select box error: invalid source type</option>';
  $return_value .= '</select>';
  return $return_value;
}
/*******************************************************************************
** show all variables for debugging purposes
*/
function vlc_show_debug()
{
  $global_vars = htmlspecialchars(print_r($GLOBALS, true));
  $debug_info = '<p style="text-align: center;"><a href="javascript:show_hide_content(\'debug-content\')">[+/-] show/hide debugging information</a></p>';
  $debug_info .= '<div class="debug" id="debug-content" style="display: none;">';
  $debug_info .= '<script type="text/javascript">if (window.print_form_elements) print_form_elements();</script>';
  $debug_info .= '<p><b>Environment Variables:</b></p>';
  $debug_info .= '<pre>'.$global_vars.'</pre>';
  $debug_info .= '</div>';
  return $debug_info;
}
/*******************************************************************************
** translate numeric character code to utf-8 character
**  - function taken from comments in php manual: http://www.php.net/manual/en/function.utf8-encode.php
**  - also see php manual for explanation of bitwise operators: http://www.php.net/manual/en/language.operators.bitwise.php
*/
function vlc_utf8_chr($code)
{
   if ($code < 128) return chr($code);
   if ($code < 2048) return chr(($code >> 6) + 192).chr(($code & 63) + 128);
   if ($code < 65536) return chr(($code >> 12) + 224).chr((($code >> 6) & 63) + 128).chr(($code & 63) + 128);
   if ($code < 2097152) return chr(($code >> 18) + 240).chr((($code >> 12) & 63) + 128).chr((($code >> 6) & 63) + 128).chr(($code & 63) + 128);
   return '?';
}

/*******************************************************************************
** formats email as utf8 character set
*/
function vlc_utf8_mail($to,$s,$body,$from_name="vlcff",$from_a = "vlcff@udayton.edu", $reply="vlcff@udayton.edu")
{
    $s= "=?utf-8?b?".base64_encode($s)."?=";
    $headers = "MIME-Version: 1.0\r\n";
    $headers.= "From: =?utf-8?b?".base64_encode($from_name)."?= <".$from_a.">\r\n";
    $headers.= "Content-Type: text/plain;charset=utf-8\r\n";
    $headers.= "Reply-To: $reply\r\n";  
    $headers.= "X-Mailer: PHP/" . phpversion();
    mail($to, $s, $body, $headers);
}


/*******************************************************************************
** log webpay report upload
*/
function vlc_webpay_log($inserts, $duplicates, $invalids)
{
  global $site_info;
  $webpay_log = '';
  /* inserts */
  $num_inserts = count($inserts);
  $webpay_log .= "\n\n*** $num_inserts RECORDS INSERTED ***\n\n";
  if ($num_inserts) $webpay_log .= join("\n", $inserts);
  /* duplicates */
  $num_duplicates = count($duplicates);
  $webpay_log .= "\n\n*** $num_duplicates DUPLICATE RECORDS ***\n\n";
  if ($num_duplicates) $webpay_log .= join("\n", $duplicates);
  /* invalids */
  $num_invalids = count($invalids);
  $webpay_log .= "\n\n*** $num_invalids INVALID RECORDS ***\n\n";
  if ($num_invalids) $webpay_log .= join("\n", $invalids);
  /* write to log file */
  $filename = $site_info['webpay_reports_path'].'_webpay_'.date('YmdHis').'.log';
  $handle = fopen($filename, 'w');
  if (fwrite($handle, $webpay_log) === false) trigger_error('Unable to write to file "'.$filename.'".');
  fclose($handle);
}
?>
