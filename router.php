<?php

Error_Reporting(E_ALL & ~E_NOTICE);

require_once('./php/config.php');

/**
 * Массив хранящий все флаги и данные - ошибки/резульаты/успехи
 *
 * @var array
 **/
$data = array();

/**
 * Строка, которая будет отправлятся приложению ВК для авторизации
 *
 * @var string
 **/
$vk_link = prepare_vk_link();

/**
 * Маршрутизация запросов
 *
 * @return void
 * @author SergeShaw
 **/
function router()
{
	global $data;
	global $vk_link;

	$data['datetime'] = Date("Y-m-d H:i:s");

	if (sign_in_check())
	{
		$data['user']['id'] = $_SESSION['user_id'];
	}

	$patterns = '/(\w+)?*/';
	$_SERVER['REDIRECT_URL'] = preg_replace($patterns, "${1}", $string);
	$requestURI    = explode('/', $_SERVER['REDIRECT_URL']); //['REQUEST_URI']);
	$requestURI[0] = (count($requestURI)-1);

	if ($requestURI[2] == 'registration')
	{
		require_once('./php/email.php');
		require_once('./php/registration.php');
	}
	else if ($requestURI[2] == 'sign-in')
	{
		require_once('./php/sign-in.php');
	}
	else if ($requestURI[2] == 'sign-in-vk')
	{
		require_once('./php/sign-in-vk.php');
	}
	else if ($requestURI[2] == 'logout')
	{
		require_once('./php/logout.php');
	}
	else if ($requestURI[2] == 'activation')
	{
		require_once('./php/activation.php');
	}
	else if ($requestURI[2] == 'profile' && $data['user'])
	{
		$data['place'] = $requestURI[3];
		require_once('./php/email.php');
		require_once('./php/students.php');
		require_once('./php/profile.php');
	}
	else
	{
		$data['place'] = $requestURI[2];
		require_once('./php/students.php');
		require_once('./php/main.php');
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

/**
 * возвращает ассоциативный массив,
 * который нужен для отправки в вк приложение для авторизации пользователя
 *
 * @return array
 * @author Mip
 **/
function prepare_vk_link()
{
	$client_id = '4638991'; // ID приложения
	$client_secret = 'skPO6RDJWe54Yf12pIuN'; // Защищённый ключ
	$redirect_uri = 'http://rating.1gb.ru/sign-in'; // Адрес сайта

	$url_auth = 'http://oauth.vk.com/authorize';
	$url_access = 'http://oauth.vk.com/access_token';
	$url_api = 'https://api.vk.com/method/users.get';


	$params_auth = array(
		'client_id'      => $client_id,
		'redirect_uri'   => $redirect_uri,
		'response_type'  => 'code');

	$params_access = array(
		'client_id'      => $client_id,
		'redirect_uri'   => $redirect_uri,
		'code'           => $_GET['code'],
		'client_secret'  => $client_secret);

	$params_api = array(
		'uids'           => $token['user_id'],
		'fields'         => 'uid,first_name,last_name,screen_name,sex,bdate,photo_big',
		'acess_token'    => $token['access_token']);


	return array(
		'auth'    => $url_auth   . '?' . urldecode(http_build_query($params_auth)),
		'access'  => $url_access . '?' . urldecode(http_build_query($params_access)),
		'api'     => $url_api    . '?' . urldecode(http_build_query($params_api))
	);

}

router();

?>