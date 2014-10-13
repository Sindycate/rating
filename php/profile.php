<?php

/**
 * Управляющая функция добавления нового человека
 *
 * @return void
 * @author Mip
 **/
function add_person()
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
	if ((empty($_POST['firstName'])) || (empty($_POST['lastName'])) ||
		 (empty($_POST['patronymic'])))//  || (empty($_POST['password2'])))
	{
		$data['error']['data_check'] = 'Заполните все поля.';
		return false;
	}
	// Логин должен содержать от 4 до 20 символов латинского алфовита или цифр.
	// Также допускаются знаки подчёркивание и тире.
	if (!preg_match("/^[а-яА-Я]{1,20}$/u", $_POST['firstName']))
	{
		$data['error']['data_check'] = 'Введённое имя не корректно. Имя должно содержать только символы русского алфaвита.';
		return false;
	}
	else if (!preg_match("/^[-а-яА-Я]{1,20}$/u", $_POST['lastName']))
	{
		echo $_POST['lastName'];
		$data['error']['data_check'] = 'Введённая фамилия не корректна. Фамилия может содержать только символы русского алфaвита и знак тире для двойных фамилий.';
		return false;
	}
	else if (!preg_match("/^[а-яА-Я]{1,20}$/u", $_POST['patronymic']))
	{
		$data['error']['data_check'] = 'Введённое отчество не корректно. Отчество должно содержать только символы русского алфaвита.';
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
 * @author SergeShaw, Mip
 **/
function check_for_duplicates()
{
	global $data;

	try
	{
		$db_reg_data_check = database::$DBH->prepare(
			"SELECT *
			 FROM `students`
			 WHERE first_name = :firstName AND
			 last_name = :lastName AND
			 patronymic = :patronymic");
		$db_reg_data_check->bindValue(':firstName', $_POST['firstName']);
		$db_reg_data_check->bindValue(':lastName', $_POST['lastName']);
		$db_reg_data_check->bindValue(':patronymic', $_POST['patronymic']);
		$db_reg_data_check->execute();

		if ($db_reg_data_check->rowCount())
		{
			$data['error']['db_check'] = 'Такой пользователь уже существует, проверьте списки.';
			return false;
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
 * Попытка внести нового пользователя
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
			"INSERT INTO `students` (`first_name`, `last_name`, `patronymic`)
			 VALUES (:firstName, :lastName, :patronymic)");
		$db_add_users->bindValue(':firstName', $_POST['firstName']);
		$db_add_users->bindValue(':lastName', $_POST['lastName']);
		$db_add_users->bindValue(':patronymic', $_POST['patronymic']);
		$db_add_users->execute();

		$data['success'] = "Добавление нового пользователя прошло успешно.";
	}
	catch(PDOException $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
	}
}

add_person();

require_once('/html/header.html');
require_once('/html/footer.html');
require_once('/html/profile.html');

?>