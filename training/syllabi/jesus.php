<?php
  header("Content-type:application/msword");
  $fp = fopen("jesus.doc","r");
  fpassthru($fp);
  fclose($fp);
 ?>