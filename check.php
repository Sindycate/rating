<form action=""method="post">
	<input type="hidden" name="53" value="99">
	<input type="hidden" name="hello" value="oleg">
	<input type="submit" name="submit" value="submit">
</form>
<pre>
<?php

var_dump($_POST);
$data['user']['id'] = 53;
echo $_POST[53];
echo $_POST[$data['user']['id']];
?>
</pre>