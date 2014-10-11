<?php

/**
 * some text here
 *
 * @return array
 */
function test_fun()
{
	try
	{
		$db_users = database::$DBH->prepare(
			"SELECT *
			 FROM `users`");

		$db_users->execute();

		if (!$db_users->rowCount())
		{
			echo "netu polzavateley\n\n";
		}

		var_dump($db_users);

		return $db_users->fetchAll(PDO::FETCH_ASSOC);
	}
	catch(PDOException $e)
	{
		echo $e;
	}

	$pas = "123456";
	var_dump(md5($pas));
}

test_fun();

// require_once('/html/header.html');
// require_once('/html/main.html');
// require_once('/html/footer.html');

?>

