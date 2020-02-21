<?php
  header("Content-type:application/msword");
  $fp = fopen("usinginter.doc","r");
  fpassthru($fp);
  fclose($fp);
 ?>