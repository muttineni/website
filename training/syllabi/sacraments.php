<?php
  header("Content-type:application/msword");
  $fp = fopen("sacraments.doc","r");
  fpassthru($fp);
  fclose($fp);
 ?>