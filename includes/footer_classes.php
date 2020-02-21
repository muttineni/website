<?php
$copyright = sprintf($lang['common']['misc']['copyright'], '2000 - ' . date('Y'), vlc_internal_link($lang['common']['misc']['vlcff-long']));
$footer = <<< END_FOOTER
<!-- begin footer -->
  <br><img src="{$site_info['images_url']}spacer.gif" width="100%" height="1" border="0">
  <hr width="75%"><p style="text-align: center;">$copyright</p>
  </td>
</tr>
</table>
END_FOOTER;
return $footer;
?>

