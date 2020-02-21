<?php
  if (!isset($action) or !strlen($action) or !file_exists($action))
  {
    $redirect_url = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/index.php';
    header("Location: $redirect_url");
    exit;
  }
?>
<html>
<head>
<title>Cycle Preparation Generation</title>
<script language="JavaScript" type="text/javascript">
<!--
function select_and_copy(form)
{
  form.cycle_prep_results.focus();
  form.cycle_prep_results.select();
  if(document.execCommand) document.execCommand('copy');
  else alert('Unable to copy to clipboard.');
  return true;
}
//-->
</script>
</head>
<body>
<h1>Cycle Preparation Generation Results</h1>
<form name="cycle_prep_results_form">
<input type="button" value="&lt;&lt; Back" onclick="history.go(-1)">
&nbsp;
<input type="button" value="Select Text and Copy to Clipboard" onclick="select_and_copy(this.form)">
<p>&nbsp;</p>
<textarea name="cycle_prep_results" rows="20" cols="100" wrap="off">
<?php
include $action;
?>
</textarea>
</form>
</body>
</html>
