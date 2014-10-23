<?php

/**
 * Управляющая функция добавления нового человека
 *
 * @return void
 * @author Mip, SergeShaw
 **/
function profile()
{
	global $data;

	if ($data['place'] == 'subscribed')
	{
		if (isset($_POST['unsubscribe']) &&
			$_POST[$data['user']['id']] &&
			unsubscribe($data['user']['id'], $_POST[$data['user']['id']]))
		{
			$data['success'] = "Вы отписались";
		}

		$data['students'] = get_subscribe();

	}


	$data['profile-menu'] = array(
		'edit'=> array(
			'href' => '/rating/profile/edit',
			'title' => 'Изменить данные'),
		'subscribed'=> array(
			'href' => '/rating/profile/subscribed',
			'title' => 'Подписки'),
		'new'=> array(
			'href' => '/rating/profile/new',
			'title' => 'Добавить студента')
		);

	$data['profile-menu'][(!empty($data['place'])) ? $data['place'] : 'edit']['active'] = true;

	if (isset($_POST['submit']) && $data['user'] && check_entered_data() && check_for_duplicates())
	{
		add_person();
	}
}

/**
 * Получем список студентов за которыми следит пользователь
 *
 * @return array
 * @author SergeShaw
 **/
function get_subscribe()
{
	global $data;

	try
	{
		$db_subscribe = database::$DBH->prepare(
			"SELECT *
			 FROM `students`, `subscribe`
			 WHERE `subscribe`.`user_id`  = :user_id
			 AND `subscribe`.`student_id` = `students`.`id`");
		$db_subscribe->bindValue(':user_id', $data['user']['id']);
		$db_subscribe->execute();

		if ($db_subscribe->rowCount())
		{
			return $db_subscribe->fetchAll();
		}
	}
	catch (PDOExeption $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
		return false;
	}
}

/**
 * Отписываем пользвоателя от студента
 *
 * @return bool
 * @author SergeShaw
 **/
function unsubscribe($user_id, $student_id)
{
	global $data;

	try
	{
		$db_unsubscribe_check = database::$DBH->prepare(
			"SELECT *
			 FROM `subscribe`
			 WHERE `user_id`  = :user_id
			 AND `student_id` = :student_id");
		$db_unsubscribe_check->bindValue(":user_id", $user_id);
		$db_unsubscribe_check->bindValue(":student_id", $student_id);
		$db_unsubscribe_check->execute();
		if (!$db_unsubscribe_check->rowCount())
		{
			$data['warning']['subscribe'] = "Вы не подписаны на этого человека";
			return false;
		}

		$db_unsubscribe = database::$DBH->prepare(
			"DELETE FROM `subscribe`
			 WHERE `user_id`  = :user_id
			 AND `student_id` = :student_id");
		$db_unsubscribe->bindValue(":user_id", $user_id);
		$db_unsubscribe->bindValue(":student_id", $student_id);
		$db_unsubscribe->execute();

		return true;
	}
	catch (PDOException $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
	}

	return false;
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
			 WHERE (`first_name` = :firstName
			 AND    `last_name`  = :lastName
			 AND    `patronymic` = :patronymic)
			 OR     `owner`      = :owner");
		$db_reg_data_check->bindValue(':firstName', $_POST['firstName']);
		$db_reg_data_check->bindValue(':lastName', $_POST['lastName']);
		$db_reg_data_check->bindValue(':patronymic', $_POST['patronymic']);
		$db_reg_data_check->bindValue(':owner', $data['user']['id']);
		$db_reg_data_check->execute();

		if ($db_reg_data_check->rowCount())
		{
			$data['error']['db_check'] = 'Такой пользователь уже существует, проверьте списки. Либо ваш лимит израсходован.';
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
function add_person()
{
	global $data;

	try
	{
		$db_add_users = database::$DBH->prepare(
			"INSERT INTO `students` (`first_name`, `last_name`, `patronymic`, `owner`)
			 VALUES (:firstName, :lastName, :patronymic, :owner)");
		$db_add_users->bindValue(':firstName', $_POST['firstName']);
		$db_add_users->bindValue(':lastName', $_POST['lastName']);
		$db_add_users->bindValue(':patronymic', $_POST['patronymic']);
		$db_add_users->bindValue(':owner', $data['user']['id']);
		$db_add_users->execute();

		$data['success'] = "Добавление нового пользователя прошло успешно.";
	}
	catch(PDOException $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
	}
}

profile();

require_once('/html/header.html');
require_once('/html/footer.html');
require_once('/html/profile.html');

?>