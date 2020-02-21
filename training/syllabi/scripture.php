<?php
  header("Content-type:application/msword");
  $fp = fopen("scripture.doc","r");
  fpassthru($fp);
  fclose($fp);
 ?>