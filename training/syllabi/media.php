<?php
  header("Content-type:application/msword");
  $fp = fopen("media.doc","r");
  fpassthru($fp);
  fclose($fp);
 ?>