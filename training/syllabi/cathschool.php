<?php
  header("Content-type:application/msword");
  $fp = fopen("cathschool.doc","r");
  fpassthru($fp);
  fclose($fp);
 ?>