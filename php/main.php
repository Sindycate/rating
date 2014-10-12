<?php

/**
 * Массив хранящий все флаги - ошибки/резульаты/успехи
 *
 * @var array
 **/
$data = array();

/**
 * Управляющая функция страницы
 *
 * @return void
 * @author SergeShaw
 **/
function main()
{
	global $data;

	if (sign_in_check())
	{
		$data['user']['id'] = $_SESSION['user_id'];
	}
}

/**
 * Autorization check
 *
 * @return bool
 * @author SergeShaw
 **/
function sign_in_check()
{
	// на будущие проверка включённости куков
	//
	// if ($auto_cook_chek == true) // хуйня какая-то которая передавалась как предустановленный тру в функцию
	// {
	// 	$this->cookcheck();
	// }

	session_name('lowlogin');

	if (isset($_COOKIE[session_name()]))
	{
		session_start();
		return true;
	}
	else if (isset($_COOKIE['highlogin']))    // проверка на hight_login
	{
		try
		{
			$userHash = md5($_SERVER['HTTP_USER_AGENT'] . $_COOKIE['highlogin']);

			$db_hash = database_main::$DBH->prepare(
				"SELECT `id`
				 FROM `session`
				 WHERE `hash` = :userHash");
			$db_hash->bindValue(':userHash', $userHash);
			$db_hash->execute();

			if ($db_hash->rowCount() == 1)
			{
				session_start();

				$row = $db_hash->fetch();
				$_SESSION['user_id'] = $row['id'];

				return true;
			}
			else
			{
				setcookie('highlogin', "", time() - (60 * 60 * 24 * 100), '/rating'); // удаляем

				return false;
			}
		}
		catch(PDOException $ee)
		{
			$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
		}
	}

	return false;
}

main();

require_once('/html/header.html');
require_once('/html/main.html');
require_once('/html/footer.html');

?>