<?php
  header("Content-type:application/msword");
  $fp = fopen("liturgy.doc","r");
  fpassthru($fp);
  fclose($fp);
 ?>