<form action="trail.php" method = "post" enctype="multipart/form-data">
<input type="file" name ="image"/>
<input type="submit" value="Post"/>
</form>

<?php
if(isset($_FILES['image']['tmp_name']))
{

	echo $_FILES['image']['tmp_name'];
}
?>