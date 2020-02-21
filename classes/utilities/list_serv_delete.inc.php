<?php
  $input = $_POST['list_serv_review'];
  // 08-27-2003 - do not need to keep sister on all lists
  // 02-24-2004 - do not need to keep sraaz on any lists - she is now owner of all lists
  // 02-25-2004 - someone has to be on the lists or the review command returns an error, so erskinpv@notes.udayton.edu was added to all vlcff lists
  $save_email = 'erskinpv@notes.udayton.edu'; // angela.zukowski@notes.udayton.edu, angelaannz@aol.com
  $input_array = explode('>', $input); // for testing: print_r($input_array);
  $num_elements = count($input_array);
  if ($num_elements > 1)
  {
    for ($i = 0; $i < $num_elements; $i++)
    {
      $haystack = $input_array[$i];
      $needle = 'review ';
      $needle_pos = strpos($haystack, $needle);
      if ($needle_pos === false) continue; // needle not found; for older versions of php, use "if (!is_integer($needle_pos)); print $needle_pos;
      $list_name_pos = $needle_pos + strlen($needle); // start of list name; print $list_name_pos;
      $new_line_pos = strpos($haystack, chr(10), $list_name_pos) - 1; // end of list name; print $new_line_pos;
      $list_name_len = $new_line_pos - $list_name_pos;
      $list_name = substr($haystack, $list_name_pos, $list_name_len); // print $list_name."\n";
      /* get e-mail addresses */
      $email_array = explode(chr(10), $haystack); // print_r($email_array);
      for ($j = 0; $j < count($email_array); $j++)
      {
        $email = $email_array[$j];
        $email_pos = strpos($email, ' - '); // print $email_pos;
        if ($email_pos !== false)
        {
          $email = substr($email, 0, $email_pos);
          if (!stristr($save_email, $email)) print 'delete '.$list_name.' '.$email."\n";
        }
      }
    }
    print 'quit';
  }
  else
  {
    print 'incorrect format';
  }
?>

