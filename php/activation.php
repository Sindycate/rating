<?php

global $data;

if (!empty($_GET['code']))
{
	$code = mysql_real_escape_string($_GET['code']);
	$db_code_chek = database::$DBH->prepare(
		"SELECT `id`
		 FROM `users`
		 WHERE `activation` = '$code'");

	if ($db_code_chek->rowCount())
	{
		$db_activation_chek = database::$DBH->prepare(
			"SELECT `id`
			 FROM `users`
			 WHERE `activation` = '$code'
			 AND `active_status` = '0'");

		if ($db_activation_chek->rowCount() == 1)
		{
			database::$DBH->prepare(
				"UPDATE `users`
				 SET `active_status` = '1'
				 WHERE `activation` = '$code'");

			$data['success'] = "Ваш аккаунт активирован.";
		}
		else
		{
			$data['error']['db_check'] = 'Ваш аккаунт уже активирован нет необходимости делать это снова.';
		}
	}
	else
	{
		$data['error']['db_check'] = 'Неверынй код активации.';
	}
}

require_once('/html/header.html');
require_once('/html/footer.html');

?>