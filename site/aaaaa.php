<form action="trail.php" method = "post" enctype="multipart/form-data">
<input type="file" name ="image"/>
<input type="submit" value="Post"/>
</form>

<?php
if(isset($_FILES['image']['tmp_name']))
{
	
	
	$ch = curl_init();
	
	$cfile = new CURLfile($_FILES['image']['tmp_name'], $_FILES['image']['type'],$_FILES['image']['name']);
	$data = array("myimage"=>$cfile);
	
	curl_setopt($ch, CURLOPT_URL,"localhost/Ampps/www/public_html_test/site/aaaaa.php");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	
	$response = curl_exec($ch);
	
	if($response == ture)
	{
		echo "File Posted";
	}else
	{
		echo "Error:" . curl_error($ch);
	}
	
}

?>
