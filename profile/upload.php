<?php

/* 
 * Receives and stores an image file from a remote server.
 * R Stewart copyright 1/25/2020
 */


/*
if (isset($_FILES['myimage']['tmp_name'])){
    $path ="images/uploads/" . $_FILES['myimage']['tmp_name'];
    move_uploaded_file($_FILES['myimage']['tmp_name'], $path);
}
 * */

   
$encoded_file = $_POST['file'];
$decoded_file = base64_decode($encoded_file);
    
file_put_contents("images/uploads/test.jpg", $decoded_file);