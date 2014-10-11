<?php
$data = array();
main();
/**
 * Управляющая функция страницы
 *
 * @return void
 * @author
 **/
function main()
{
	if (isset($_POST['submit']))
	{
		global $data;

		if (!check_submitted_data())
		{
			$data['error']['data_check'] = true;
			return;
		}

		if (!check_for_duplicates())
		{
			$data['error']['db_check'] = true;
			return;
		}

		register();
	}
}

/**
 * Проверка переданных данных
 *
 * @return boolean
 * @author Mip
 **/
function check_submitted_data()
{
	return true;
}

/**
 * undocumented function
 *
 * @return void
 * @author
 **/
function check_for_duplicates()
{
	return true;
}

/**
 * Попытка зарегистрировать пользователя
 *
 * @return void
 * @author Mip
 **/
function register()
{
	try
	{
		/*$hash_password = md5($_POST);*/

		$db_add_users = database::$DBH->prepare(
			"INSERT INTO `users` (`login`, `hash`)
			 VALUES (:login, :hash)");
		$db_add_users->bindValue(':login', $_POST['login']);
		$db_add_users->bindValue(':hash', md5($_POST['password']));
		$db_add_users->execute();

		global $data;
		$data['success'] = true;
	}
	catch(PDOException $e)
	{
		echo $e;
	}
}

require_once '/html/header.html';
require_once '/html/registration.html';
require_once '/html/footer.html';

?>