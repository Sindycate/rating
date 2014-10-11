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
 * @author Mip, SergeShaw
 **/
function main()
{
	if (isset($_POST['submit']))
	{
		// ошибки добавляются внутри вызываемых функций
		//  содержат описание ошибки.
		if (data_validation() || check_for_duplicates())
		{
			register();
		}
	}
}

/**
 * Проверка введённых данных
 * Validate entered data
 *
 * @return bool
 * @author Mip, SergeShaw
 **/
function data_validation()
{
	global $data;

	// Задел на будущее
	// Проверка дополнительных данных
	if ((!isset($_POST['login'])) ||//     || (!isset($_POST['sign_up']['email'])) ||
		 (!isset($_POST['password'])))//  || (!isset($_POST['sign_up']['password2'])))
	{
		$data['error']['data_check'] = 'Заполните все поля.';
		return false;
	}

	// Пароль должен содержать от 6 до 20 символов
	if (!preg_match("/^.{6,20}$/", $_POST['password']))
	{
		$data['error']['data_check'] = 'Пароль должен содержать от 6 до 20 символов';
		return false;
	}
	// Задел на будущее
	// Проверка на корректность емейла
	//
	// else if (!preg_match("/^[\-\.a-zA-Z0-9]+@[a-z\-]+\.[a-zA-Z]+\.?[a-zA-Z]*$/", $POST_['email']))
	// {
	// 	$data['error']['data_check'] = 'Введённый адрес электронной почты не корректен.';
	// 	return false;
	// }
	//
	// Логин должен содержать от 4 до 20 символов латинского алфовита или цифр.
	// Также допускаются знаки подчёркивание и тире.
	else if (!preg_match("/^[\-\_a-zA-Z0-9]{4,20}$/", $POST_['login']))
	{
		$data['error']['data_check'] = 'Введённый логин не корректен. Логин должен содержать от 4 до 20 символов латинского алфовита или цифр. Также допускаются знаки подчёркивание и тире.';
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
function db_validation()
{
	global $data;

	try
	{
		$db_reg_login_check = database_main::$DBH->prepare(
			"SELECT *
			 FROM `users`
			 WHERE login = :login");
		$db_reg_login_check->bindValue(':login', $POST_['login']);
		$db_reg_login_check->execute();

		if ($db_reg_login_check->rowCount())
		{
			$data['error']['db_check'] = 'Введёный логин уже используется.';
			return false;
		}
		else
		{
			// Задел на будущие.
			// Проверка на совпадение с уже используемым емейлом.
			//
			// $db_reg_email_check = database_main::$DBH->prepare(
			// 	"SELECT *
			// 	 FROM `users`
			// 	 WHERE email = :email");
			// $db_reg_email_check->bindValue(':email', $POST_['email']);
			// $db_reg_email_check->execute();

			// if ($db_reg_email_check->rowCount())
			// {
			// 	$data['error']['db_check'] = 'Введённый адрес электронной почты уже используется.';
			// 	return false;
			// }
		}

		return true;
	}
	catch(PDOException $e)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $e->getMessage();
		return false;
	}
}

/**
 * Попытка зарегистрировать пользователя
 *
 * @return void
 * @author Mip, SergeShaw
 **/
function register()
{
	global $data;

	try
	{
		$db_add_users = database::$DBH->prepare(
			"INSERT INTO `users` (`login`, `hash`)
			 VALUES (:login, :hash)");
		$db_add_users->bindValue(':login', $_POST['login']);
		$db_add_users->bindValue(':hash', md5($_POST['password']));
		$db_add_users->execute();

		$data['success'] = "Регистрация прошла успешно.";
	}
	catch(PDOException $e)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $e->getMessage();
	}
}

main();

require_once('/html/header.html');
require_once('/html/registration.html');
require_once('/html/footer.html');

?>