<?php
  header("Content-type:application/msword");
  $fp = fopen("catholic_beliefs.doc","r");
  fpassthru($fp);
  fclose($fp);
 ?>