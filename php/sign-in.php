<?php

/**
 * Массив хранящий информацию о пользавателе
 *
 * @var array
 **/
$user = array();

/**
 * Управляющая функция страницы
 *
 * @return void
 * @author SergeShaw
 **/
function sign_in()
{
	if (isset($_POST['submit']) && check_entered_data() && db_check())
	{
		login();
	}
}

/**
 * Remember user
 *
 * @return void
 * @author SergeShaw
 **/
function login()
{
	global $user;

	session_name('lowlogin');
	session_start();
	$_SESSION['user_id'] = $user['id'];

	if (isset($_POST['remember']) && !$_POST['remember'])
	{
		$user['hash_cook'] = md5($_POST['password']);
		$user['hash'] = md5($_SERVER['HTTP_USER_AGENT'] . $user['hash_cook']);
		setcookie('highlogin', $user['hash_cook'], time() + (60 * 60 * 24 * 100), '/'); // 100 дней

		try
		{
			$db_session_get = database_main::$DBH->prepare(
				"SELECT *
				 FROM  `session`
				 WHERE `id` = :id");
			$db_session_get->bindValue(':id', $user['id']);
			$db_session_get->execute();

			if (!$db_session_get->rowCount())
			{
				$db_session_insert = database_main::$DBH->prepare(
					"INSERT INTO `session` (`id`, `hash`)
					 VALUES                (:id,  :hash)");
				$db_session_insert->bindValue(':id', $user['id']);
				$db_session_insert->bindValue(':hash', $user['hash']);
				$db_session_insert->execute();
			}
			else if ($db_session_get->rowCount() == 1)
			{
				$db_session_update = database_main::$DBH->prepare(
					"UPDATE `session`
					 SET `hash` = :hash
					 WHERE `id` = :id");
				$db_session_update->bindValue(':id', $user['id']);
				$db_session_update->bindValue(':hash', $user['hash']);
				$db_session_update->execute();
			}

			header("Location: .");
		}
		catch (PDOException $ee)
		{
			$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
		}
	}
	else
	{
		header("Location: .");
	}
}

/**
 * Проверка введённых данных
 * Validate entered data
 *
 * @return bool
 * @author SergeShaw
 **/
function check_entered_data()
{
	global $data;

	// Задел на будущее
	// Проверка дополнительных данных
	if (empty($_POST['login']) || empty($_POST['password']))
	{
		$data['error']['data_check'] = 'Заполните все поля.';
		return false;
	}
	else
	{
		return true;
	}
}


/**
 * Сравнение введённых данных с данными имеющимися в базе данных
 * Validate entered data with db data
 *
 * @return bool
 * @author SergeShaw
 **/
function db_check()
{
	global $data;
	global $user;

	try
	{
		$db_login_check = database::$DBH->prepare(
			"SELECT *
			 FROM `users`
			 WHERE login = :login");
		$db_login_check->bindValue(':login', $_POST['login']);
		$db_login_check->execute();

		if (!$db_login_check->rowCount())
		{
			$data['error']['db_check'] = 'Неправильный логин.';
			return false;
		}
		else
		{
			$db_password_check = database::$DBH->prepare(
				"SELECT *
				 FROM `users`
				 WHERE hash = :hash
				 AND  login = :login");
			$db_password_check->bindValue(':hash', md5($_POST['password']));
			$db_password_check->bindValue(':login', $_POST['login']);
			$db_password_check->execute();

			if ($db_password_check->rowCount() != 1)
			{
				$data['error']['db_check'] = 'Неправильный пароль.';
				return false;
			}
			else
			{
				$user = $db_password_check->fetch();
			}
		}

		return true;
	}
	catch(PDOException $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
		return false;
	}
}

sign_in();

require_once('/html/header.html');
// require_once('/html/sign-in.html');
require_once('/html/footer.html');

?>