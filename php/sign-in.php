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
	echo "0";
	if (isset($_POST['submit']) && check_entered_data() && db_check())
	{
		login();
	}
	else if (isset($_GET['code']))// || isset($token['access_token']))
	{
		$user_info = get_info_user_vk();

		if (!$user_info)
		{
			echo "1";
			// TODO: PRINT ERROR
			return;
		}

		if (!db_chek_for_duplicate($user_info))
		{
			if (!registration_vk($user_info))
			{
				echo "2";
				// TODO: PRINT ERROR
				return;
			}
		}

		login_vk(db_chek_for_duplicate($user_info));
	}
}

/**
 * создаёт сессию пользователю
 *
 * @return void
 * @author Mip
 **/
function login_vk($user_id)
{
	session_name('lowlogin');
	session_start();
	$_SESSION['user_id'] = $user_id;

	header("Location: .");
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

/**
 * функция, которая получает доступ к API вконтакте и возвращает данные пользователя
 *
 * @return array
 * @author Mip
 **/
function get_info_user_vk()
{
	global $data;
	global $vk_link;

	if (isset($_GET['code']))
	{
		var_dump($vk_link['access']);
		$token = json_decode(file_get_contents($vk_link['access']), true);

		if (isset($token['access_token']))
		{
			$url_api = 'https://api.vk.com/method/users.get';
			$params_api = array(
				'uids'           => $token['user_id'],
				'fields'         => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big',
				'acess_token'    => $token['access_token']
			);
			$user_info = json_decode(file_get_contents($url_api    . '?' . urldecode(http_build_query($params_api))), true);

			if (isset($user_info['response'][0]['uid']))
			{
				$user_info = $user_info['response'][0];
				return $user_info;
			}
			else
			{
				return array();
			}
		}
	}
}

/**
 * функция, которая возвращает id пользователя в случае, если он уже есть в бд
 *
 * @return int
 * @author Mip
 **/
function db_chek_for_duplicate($user_info)
{
	global $data;

	try
	{
		$uid = $user_info['uid'];

		$db_user_check = database::$DBH->prepare(
			"SELECT `id`
			 FROM `users`
			 WHERE `uid` = :uid");
		$db_user_check->bindValue(':uid', $uid);
		$db_user_check->execute();

		return ($db_user_check->rowCount()) ? $db_user_check->fetch() : 0;
	}
	catch(PDOException $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
		return 0;
	}

}

/**
 * Регистрирует пользователя, который заходит из вк и возвращает его id
 *
 * @return int
 * @author Mip
 **/
function registration_vk($user_info)
{
	global $data;

	try
	{
		$db_add_users = database::$DBH->prepare(
			"INSERT INTO `users` (`first_name`, `last_name`, `uid`)
			 VALUES (:first_name, :last_name, :uid)");
		$db_add_users->bindValue(':first_name', $user_info['first_name']);
		$db_add_users->bindValue(':last_name', $user_info['last_name']);
		$db_add_users->bindValue(':uid', $user_info['uid']);
		$db_add_users->execute();

		$data['success'] = "Регистрация прошла успешно, пожалуйста, кликните на иконку VK ещё раз, чтобы войти";

		return true;
	}
	catch(PDOException $e)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $e->getMessage();

		return false;
	}
}

sign_in();
echo "<pre>";
require_once('/html/header.html');
require_once('/html/sign-in.html');
require_once('/html/footer.html');

?>