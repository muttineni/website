<?php
  header("Content-type:application/msword");
  $fp = fopen("church_history.doc","r");
  fpassthru($fp);
  fclose($fp);
 ?>