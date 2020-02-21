<?php
  header("Content-type:application/msword");
  $fp = fopen("mary_tt.doc","r");
  fpassthru($fp);
  fclose($fp);
 ?>