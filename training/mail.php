<?php



 $message = sprintf("VLCFF Fac Competencies \n");
 $message .= "

Name:           $name\n

";

 $count = count($info_request);



  for ($i=0;$i<$count;$i++) {

  $message .=  sprintf("%s\n", $info_request[$i]) ;

  }



 $message .= "\n";


if(mail($to, $subject, $message, "From: $name <$email>"))

	{

		echo("<h1>Feedback Form</h1>\n");

		echo("<br>");

		echo("Thank you $name for filling out this form.");

		echo("Your form results have been successfully sent.");

                echo("<br>");







  }

  

   else

	{

		echo("E-mail failed, results not sent.");

	}

?>