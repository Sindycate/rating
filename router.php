<?php

Error_Reporting(E_ALL & ~E_NOTICE);

require_once('/php/config.php');

/**
 * Массив хранящий все флаги и данные - ошибки/резульаты/успехи
 *
 * @var array
 **/
$data = array();

/**
 * Маршрутизация запросов
 *
 * @return void
 * @author SergeShaw
 **/
function router()
{
	global $data;

	$data['datetime'] = Date("Y-m-d H:i:s");

	if (sign_in_check())
	{
		$data['user']['id'] = $_SESSION['user_id'];
	}

	$requestURI    = explode('/', $_SERVER['REDIRECT_URL']); //['REQUEST_URI']);
	$requestURI[0] = (count($requestURI)-1);

	if ($requestURI[2] == 'registration')
	{
		require_once('/php/registration.php');
	}
	else if ($requestURI[2] == 'sign-in')
	{
		require_once('/php/sign-in.php');
	}
	else if ($requestURI[2] == 'logout')
	{
		require_once('/php/logout.php');
	}
	else if ($requestURI[2] == 'profile' && $data['user'])
	{
		require_once('/php/profile.php');
	}
	else
	{
		require_once('/php/main.php');
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

			$db_hash = database::$DBH->prepare(
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

router();

?>