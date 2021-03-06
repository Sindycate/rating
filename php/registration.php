<?php

/**
 * Управляющая функция страницы
 *
 * @return void
 * @author Mip, SergeShaw
 **/
function registration()
{
	if (isset($_POST['submit']) && check_entered_data() && check_for_duplicates())
	{
		register();
	}
}

/**
 * Проверка введённых данных
 * Validate entered data
 *
 * @return bool
 * @author Mip, SergeShaw
 **/
function check_entered_data()
{
	global $data;

	// Задел на будущее
	// Проверка дополнительных данных
	if ((empty($_POST['login'])) || (empty($_POST['email'])) ||
		 (empty($_POST['password'])))//  || (empty($_POST['password2'])))
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

	else if (!preg_match("/^[\-\.a-zA-Z0-9]+@[a-z\-]+\.[a-zA-Z]+\.?[a-zA-Z]*$/", $_POST['email']))
	{
		$data['error']['data_check'] = 'Введённый адрес электронной почты не корректен.';
		return false;
	}

	// Логин должен содержать от 4 до 20 символов латинского алфовита или цифр.
	// Также допускаются знаки подчёркивание и тире.
	else if (!preg_match("/^[\-\_a-zA-Z0-9]{4,20}$/", $_POST['login']))
	{
		$data['error']['data_check'] = 'Введённый логин не корректен. Логин должен содержать от 4 до 20 символов латинского алфaвита или цифр. Также допускаются знаки подчёркивание и тире.';
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
function check_for_duplicates()
{
	global $data;

	try
	{
		$db_reg_login_check = database::$DBH->prepare(
			"SELECT *
			 FROM `users`
			 WHERE login = :login");
		$db_reg_login_check->bindValue(':login', $_POST['login']);
		$db_reg_login_check->execute();

		if ($db_reg_login_check->rowCount())
		{
			$data['error']['db_check'] = 'Введёный логин уже используется.';
			return false;
		}
		else
		{
			//Проверка на совпадение с уже используемым емейлом.
			$db_reg_email_check = database::$DBH->prepare(
				"SELECT *
				 FROM `users`
				 WHERE email = :email");
			$db_reg_email_check->bindValue(':email', $_POST['email']);
			$db_reg_email_check->execute();

			if ($db_reg_email_check->rowCount())
			{
				$data['error']['db_check'] = 'Введённый адрес электронной почты уже используется.';
				return false;
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
			"INSERT INTO `users` (`login`, `hash`, `email`, `activation`)
			 VALUES (:login, :hash, :email, :activation)");
		$db_add_users->bindValue(':login', $_POST['login']);
		$db_add_users->bindValue(':hash', md5($_POST['password']));
		$db_add_users->bindValue(':email', $_POST['email']);
		$db_add_users->bindValue(':activation', md5($_POST['email']));
		$db_add_users->execute();

		$data['success'] = "Регистрация прошла успешно";

		$verification_email = new email($_POST['email']);
		if($verification_email->send_mail())
		{
			$data['success'] .= ", пожалуйста, зайдите на почу и подтвердите ваш email.";
		}
		else
		{
			$data['warning']['send_mail'] = "Cобщение не было отправлено, попробуйте сделать это позже. В профиле, во вкладке 'управление' нажмите на кнопку 'Отправить сообщение'.";
		}

	}
	catch(PDOException $e)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $e->getMessage();
	}
}

registration();

require_once('/html/header.html');
require_once('/html/registration.html');
require_once('/html/footer.html');

/*require_once('../html/header.html');
require_once('../html/registration.html');
require_once('../html/footer.html');*/

?>