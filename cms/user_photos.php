<?php

/* 
 * This script reads user photo files and inserts the photo filename into the user profile.
 * Photos are selected from the edited photos folder by date (yyyy-mm-dd).
 * Written by Robert Stewart 3-14-2019
 */

//check if form was submitted
if(isset($_POST['SubmitButton'])){ //check if form was submitted
  $input = $_POST['photo_date']; //get input text
  $message = "Success! You entered: ".$input;
}    
?>

   
<form action="" method="post">
<?php echo $message; ?>
  <input type="date" name="photo_date"/>
  <input type="submit" name="SubmitButton" value="Submit"/>
</form>    


