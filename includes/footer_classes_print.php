<?php
$printed_date = date('j|n|Y|g|i|s|A|T');
$printed_date_array = explode('|', $printed_date);
$printed_date_array[1] = $lang['common']['months']['abbr'][$printed_date_array[1]];
$printed_date_format = sprintf($lang['common']['misc']['print-date'], $lang['common']['misc']['short-date-format'], $lang['common']['misc']['long-time-format']);
array_unshift($printed_date_array, $printed_date_format);
$printed_date = call_user_func_array('sprintf', $printed_date_array);
$copyright = sprintf($lang['common']['misc']['copyright'], '2000 - ' . date('Y'), $lang['common']['misc']['vlcff-long']);
$footer = <<< END_FOOTER
<!-- begin footer -->
<hr width="75%">
<div style="text-align: center; font-size: x-small;">
  $printed_date<br>
  $copyright
</div>
END_FOOTER;
return $footer;
?>

