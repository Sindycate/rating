<?php

/**
 * Управляющая функция страницы
 *
 * @return void
 * @author SergeShaw
 **/
function main()
{
	global $data;

	$data['rows']['num'] = 20;

	if ($data['user'])
	{
		$data['user']['vote'] = check_for_vote($data['user']['id']);

		if ($data['user']['vote'] &&
			 isset($_POST['vote']) &&
			 $_POST[$data['user']['id']] &&
			 student_check($data['user']['id'], $_POST[$data['user']['id']]) &&
			 vote($data['user']['id'], $_POST[$data['user']['id']]))
		{
			// с связи с возможным изменением данных
			// ещё раз проверяем может ли он голосовать
			$data['user']['vote'] = check_for_vote($data['user']['id']);

			$data['success'] = "Ваш голос отдан";
		}
		else if (isset($_POST['subscribe']) &&
					$_POST[$data['user']['id']] &&
					subscribe($data['user']['id'], $_POST[$data['user']['id']]))
		{
			$data['success'] = "Вы подписались";
		}
		else if (isset($_POST['unsubscribe']) &&
					$_POST[$data['user']['id']] &&
					unsubscribe($data['user']['id'], $_POST[$data['user']['id']]))
		{
			$data['success'] = "Вы отписались";
		}
	}

	prepare_find_data();
	if ($data['place'] == 'all' || !$data['user'] || !get_subscribe_check())
	{
		$data['type'] = 'all';
		$data['students'] = get_students();

		if ($data['user']['id'])
		{
			$data['subscribe'] = get_subscribe_id($data['user']['id']);
		}
	}
	else
	{
		$data['type'] = 'subscribe';
		$data['students'] = get_subscribe();
	}
}

/**
 * Возвращаем массив c id фоловеров
 *
 * @return array
 * @author SergeShaw
 **/
function get_subscribe_id($user_id)
{
	try
	{
		$db_subscribe_id = database::$DBH->prepare(
			"SELECT student_id
			 FROM `subscribe`
			 WHERE `user_id` = :user_id");
		$db_subscribe_id->bindValue(":user_id", $user_id);
		$db_subscribe_id->execute();

		if ($db_subscribe_id->rowCount())
		{
			$subscribe_id = $db_subscribe_id->fetchAll();
			$formated_subscribe_id;

			for ($ii = 0; $ii < count($subscribe_id); ++$ii)
			{
				$formated_subscribe_id[$ii] = $subscribe_id[$ii][0];
			}
			return $formated_subscribe_id;
		}

		return array('0');
	}
	catch (PDOExeption $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
		return false;
	}
}

/**
 * Есть ли у пользователя хоть один студен за которым он следит
 *
 * @return bool
 * @author SergeShaw
 **/
function get_subscribe_check()
{
	global $data;

	if (!isset($data['user']['id']))
	{
		return false;
	}

	try
	{
		$db_subscribe_num = database::$DBH->prepare(
			"SELECT count(*)
			 FROM `subscribe`
			 WHERE user_id = :user_id");
		$db_subscribe_num->bindValue(":user_id", $data['user']['id']);
		$db_subscribe_num->execute();

		$subscribe_num = $db_subscribe_num->fetch();
		$data['rows']['count'] = $subscribe_num[0];

		if (!$subscribe_num[0])
		{
			return false;
		}

		if (isset($_GET['page_num']))
		{
			$data['page']['current'] = $_GET['page_num'];
		}
		else {
			$data['page']['current'] = 1;
		}
	}
	catch (PDOExeption $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
		return false;
	}

	return true;
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
		$start_pos = ($data['page']['current'] - 1) * $data['rows']['num'];

		$db_subscribe = database::$DBH->prepare(
			"SELECT *
			 FROM `students`, `subscribe`
			 WHERE `subscribe`.`user_id`  = :user_id
			 AND `subscribe`.`student_id` = `students`.`id`
			 LIMIT :start_pos, :rows_num");
		$db_subscribe->bindValue(':start_pos', $start_pos, PDO::PARAM_INT);
		$db_subscribe->bindValue(':rows_num', $data['rows']['num'], PDO::PARAM_INT);
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
			echo $user_id;
			echo $student_id;
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
 * Подписываем пользователя на студента
 *
 * @return bool
 * @author SergeShaw
 **/
function subscribe($user_id, $student_id)
{
	global $data;

	try
	{
		$db_subscribe_check = database::$DBH->prepare(
			"SELECT *
			 FROM `subscribe`
			 WHERE `user_id`  = :user_id
			 AND `student_id` = :student_id");
		$db_subscribe_check->bindValue(":user_id", $user_id);
		$db_subscribe_check->bindValue(":student_id", $student_id);
		$db_subscribe_check->execute();
		if ($db_subscribe_check->rowCount())
		{
			$data['warning']['subscribe'] = "Вы уже подписаны на этого человека";
			return false;
		}

		$db_subscribe = database::$DBH->prepare(
			"INSERT INTO `subscribe` (`user_id`, `student_id`)
			 VALUES (:user_id, :student_id)");
		$db_subscribe->bindValue(":user_id", $user_id);
		$db_subscribe->bindValue(":student_id", $student_id);
		$db_subscribe->execute();

		return true;
	}
	catch (PDOException $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
	}

	return false;
}

/**
 * Защитываем отданный голос
 *
 * @return bool
 * @author SergeShaw
 **/
function vote($user_id, $student_id)
{
	try
	{
		// Страховка
		database::$DBH->beginTransaction();

		// Инкрментрируем поинты у студента
		$db_inc_point = database::$DBH->prepare(
			"UPDATE `students` SET
			 `points`   = points + 1
			 WHERE `id` = :id");
		$db_inc_point->bindValue(":id", $student_id);
		$db_inc_point->execute();

		// добавляем связь
		$db_new_vote = database::$DBH->prepare(
			"INSERT INTO `vote` (`user_id`, `student_id`, `date`)
			 VALUES (:user_id, :student_id, CURDATE())");
		$db_new_vote->bindValue(":user_id", $user_id);
		$db_new_vote->bindValue(":student_id", $student_id);
		$db_new_vote->execute();

		// успех
		database::$DBH->commit();

		return true;
	}
	catch (PDOException $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
	}

	return false;
}

/**
 * Проверка на существование студнета
 *
 * @return bool
 * @author SergeSahw
 **/
function student_check($user_id, $student_id)
{
	global $data;

	try
	{
		$db_student_check = database::$DBH->prepare(
			"SELECT *
			 FROM `students`
			 WHERE id = :id");
		$db_student_check->bindValue(":id", $student_id);
		$db_student_check->execute();

		if (!$db_student_check->rowCount())
		{
			return false;
		}

		$db_student_user_check = database::$DBH->prepare(
			"SELECT *
			 FROM `vote`
			 WHERE user_id  = :user_id
			 AND student_id = :student_id
			 AND date       = CURDATE()");
		$db_student_user_check->bindValue(":user_id", $user_id);
		$db_student_user_check->bindValue(":student_id", $student_id);
		$db_student_user_check->execute();

		if ($db_student_user_check->rowCount())
		{
			$data['error']['vote'] = "Сегодня вы уже голосовали за этого человека";
			return false;
		}
	}
	catch (PDOException $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
		return false;
	}

	return true;
}

/**
 * Выборка всех студентов из рейтинга
 *
 * @return array
 * @author SergeShaw
 **/
function get_students()
{
	global $data;

	try
	{
		$db_students_num = database::$DBH->prepare(
			"SELECT count(*)
			 FROM `students`");
		$db_students_num->execute();

		$students_num = $db_students_num->fetch();
		$data['rows']['count'] = $students_num[0];

		$order_list = array("points", "last_name","faculty", "USE");

		if (isset($_GET['page_num']))
		{
			$data['page']['current'] = $_GET['page_num'];
		}
		else {
			$data['page']['current'] = 1;
		}

		$start_pos = ($data['page']['current'] - 1) * $data['rows']['num'];

		$order = $order_list[($order_list[$_GET['order']]) ? $_GET['order'] : 0];

		$db_students = database::$DBH->prepare(
			"SELECT *
			 FROM `students`
			 WHERE `first_name` REGEXP :first_name
			 AND `last_name` REGEXP :last_name
			 ORDER BY `$order` DESC
			 LIMIT :start_pos, :rows_num");
		// $db_students->bindValue(':order', $data['order']);1
		$db_students->bindValue(':start_pos', $start_pos, PDO::PARAM_INT);
		$db_students->bindValue(':rows_num', $data['rows']['num'], PDO::PARAM_INT);
		$db_students->bindValue(':first_name', $data['find']['first_name']);
		$db_students->bindValue(':last_name', $data['find']['last_name']);
		$db_students->execute();

		if ($db_students->rowCount())
		{
			return $db_students->fetchAll();
		}
	}
	catch (PDOExeption $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
		return false;
	}
}

/**
 * Проверка на возможность голосовать
 *
 * @return bool
 * @author SergeShaw
 **/
function check_for_vote($id)
{
	try
	{
		$db_vote_check = database::$DBH->prepare(
			"SELECT count(*)
			 FROM  `vote`
			 WHERE `user_id` = :user_id
			 AND   `date`    = CURDATE()");
		$db_vote_check->bindValue(":user_id", $id);
		$db_vote_check->execute();

		$vote_check = $db_vote_check->fetch();

		// TODO: ЗАМЕНИТЬ КОНСТАНТУ
		return ($vote_check[0] < 3) ? true : false;
	}
	catch (PDOException $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
		return false;
	}
}

/**
 * Сохраняет данные о поиске студентов
 *
 * @return void
 * @author Mip
 **/
function prepare_find_data()
{
	global $data;

	$data['find']['last_name'] = (!empty($_GET['last_name']) ? $_GET['last_name'] : '.*');
	$data['find']['first_name'] = (!empty($_GET['first_name']) ? $_GET['first_name'] : '.*');
}



main();

// in_array("9", haystack)

require_once('/html/header.html');
require_once('/html/main.html');
require_once('/html/footer.html');

?>