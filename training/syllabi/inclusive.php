<?php
  header("Content-type:application/msword");
  $fp = fopen("inclusive_ed.doc","r");
  fpassthru($fp);
  fclose($fp);
 ?>