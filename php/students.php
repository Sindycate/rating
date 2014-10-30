<?php

/**
 * работа со студентами
 *
 * @package rating
 * @author Mip, SergeShaw
 **/
class students
{
	/**
	 * количество студентов на странице
	 *
	 * @var int
	 **/
	private static $students_on_page = 20;

	/**
	 * количество голосов в день
	 *
	 * @var int
	 **/
	private static $vote_on_day = 3;

	/**
	 * общее количество студентов
	 *
	 * @var int
	 **/
	private static $students_num;

	/**
	 * стартовая позиция
	 *
	 * @var int
	 **/
	private static $start_pos;

	/**
	 * параметр по которому нужно сортировать
	 *
	 * @var string
	 **/
	private static $order;

/**
 * имя студента
 *
 * @var string
 **/
private static $first_name;

/**
 * фамилия студента
 *
 * @var string
 **/
private static $last_name;

	/**
	 * Массив, который хранит параметры для сортировки при запросе к б\д
	 *
	 * @var array
	 **/
	private static $order_list = array("points", "last_name","faculty", "USE");

	/**
	 * количество страниц
	 *
	 * @var int
	 **/
	public static $pages_num;

	/**
	 * текущая страница
	 *
	 * @var int
	 **/
	public static $current_page;

	/**
	 * Подготовка данных (students)
	 *
	 * @return void
	 * @author Mip, SergeShaw
	 **/

	static public function students_prepare()
	{
		global $data;

		try
		{
			$db_students_num = database::$DBH->prepare(
				"SELECT count(*)
				 FROM `students`");

			$db_students_num->execute();

			$students_num = $db_students_num->fetch();
			self::$students_num = $students_num[0];
		}
		catch (PDOException $ee)
		{
			$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
			return false;
		}

		self::$current_page = (isset($_GET['page_num'])) ? $_GET['page_num'] : 1;

		self::$start_pos = (self::$current_page - 1) * self::$students_on_page;

		self::$order = self::$order_list[(self::$order_list[$_GET['order']]) ? $_GET['order'] : 0];

		self::$pages_num = (self::$students_num / self::$students_on_page + ((self::$students_num % self::$students_on_page) ? 1 : 0));

		self::$first_name = (!empty($_GET['first_name']) ? $_GET['first_name'] : '.*');
		self::$last_name = (!empty($_GET['last_name']) ? $_GET['last_name'] : '.*');
	}

/**
 * подготовка данных (subscribers)
 *
 * @return void
 * @author Mip
 **/
static public function subscribe_prepare($user_id)
{
	global $data;

	try
	{
		$db_subscribe_num = database::$DBH->prepare(
			"SELECT count(*)
			 FROM `subscribe`
			 WHERE `user_id` = :user_id");
		$db_subscribe_num->bindValue(':user_id',$user_id, PDO::PARAM_INT);
		$db_subscribe_num->execute();

		$students_num = $db_subscribe_num->fetch();
		self::$students_num = $students_num[0];
	}
	catch (PDOException $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
		return false;
	}

	self::$current_page = (isset($_GET['page_num'])) ? $_GET['page_num'] : 1;

	self::$start_pos = (self::$current_page - 1) * self::$students_on_page;

	self::$order = self::$order_list[(self::$order_list[$_GET['order']]) ? $_GET['order'] : 0];

	self::$pages_num = (self::$students_num / self::$students_on_page + ((self::$students_num % self::$students_on_page) ? 1 : 0));

	self::$first_name = (!empty($_GET['first_name']) ? $_GET['first_name'] : '.*');
	self::$last_name = (!empty($_GET['last_name']) ? $_GET['last_name'] : '.*');
}

	/**
	 * Выборка всех студентов из рейтинга
	 *
	 * @return array
	 * @author SergeShaw
	 **/
	static public function get_students()
	{
		global $data;

		$order = self::$order;

		try
		{
			$db_students = database::$DBH->prepare(
				"SELECT *
				 FROM `students`
				 WHERE `first_name` REGEXP :first_name
				 AND `last_name` REGEXP :last_name
				 ORDER BY `$order` DESC
				 LIMIT :start_pos, :rows_num");
			// $db_students->bindValue(':order', $data['order']);1
			$db_students->bindValue(':start_pos', self::$start_pos, PDO::PARAM_INT);
			$db_students->bindValue(':rows_num', self::$students_on_page, PDO::PARAM_INT);
			$db_students->bindValue(':first_name', self::$first_name);
			$db_students->bindValue(':last_name', self::$last_name);
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
	static public function check_limit_vote($id)
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

			return ($vote_check[0] < self::$vote_on_day) ? true : false;
		}
		catch (PDOException $ee)
		{
			$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
			return false;
		}
	}

	/**
	 * Проверка на существование студента и был ли отдан голос за него сегодня
	 *
	 * @return bool
	 * @author SergeSahw, Mip
	 **/
	static public function student_check($user_id, $student_id)
	{
		global $data;

		try
		{
			$db_student_check = database::$DBH->prepare(
				"SELECT *
				 FROM `students`
				 WHERE `id` = :student_id");
			$db_student_check->bindValue(":student_id", $student_id, PDO::PARAM_INT);
			$db_student_check->execute();

			if (!$db_student_check->rowCount())
			{
				$data['error']['vote'] = "Этого студента не существует";
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
	 * Защитываем отданный голос
	 *
	 * @return bool
	 * @author SergeShaw, Mip
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
 * Проверка на возможность подписки
 *
 * @return bool
 * @author Mip
 **/
static public function check_subscribe($user_id, $student_id)
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

		return true;
	}
	catch (PDOException $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
		return false;
	}
}

	/**
	 * Подписываем пользователя на студента
	 *
	 * @return bool
	 * @author SergeShaw, Mip
	 **/
	static public function subscribe($user_id, $student_id)
	{
		global $data;

		try
		{
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
			return false;
		}

		return false;
	}

/**
 * Проверка на возможность отписки
 *
 * @return bool
 * @author Mip
 **/
static public function check_unsubscribe($user_id, $student_id)
{
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

		return true;
	}
	catch (PDOException $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $ee->getMessage();
		return false;
	}
}

	/**
	 * Отписываем пользователя от студента
	 *
	 * @return bool
	 * @author SergeShaw, Mip
	 **/
	static public function unsubscribe($user_id, $student_id)
	{
		global $data;

		try
		{
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
			return false;
		}

		return false;
	}

	/**
	 * Есть ли у пользователя хоть один студен, за которым он следит
	 *
	 * @return bool
	 * @author SergeShaw
	 **/
	static public function get_subscribe_check($user_id)
	{
		global $data;

		try
		{
			$db_subscribe_num = database::$DBH->prepare(
				"SELECT count(*)
				 FROM `subscribe`
				 WHERE user_id = :user_id");
			$db_subscribe_num->bindValue(":user_id", $user_id);
			$db_subscribe_num->execute();

			$subscribe_num = $db_subscribe_num->fetch();

			if (!$subscribe_num[0])
			{
				return false;
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
	 * Возвращаем массив c id фоловеров
	 *
	 * @return array
	 * @author SergeShaw
	 **/
	static public function get_subscribe_id($user_id)
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
			return array('0');
			// return false;
		}
	}

/**
	 * Получем список студентов за которыми следит пользователь
	 *
	 * @return array
	 * @author SergeShaw, Mip
	 **/
	function get_subscribe($user_id)
	{
		global $data;

		try
		{
			$db_subscribe = database::$DBH->prepare(
				"SELECT *
				 FROM `students`, `subscribe`
				 WHERE `subscribe`.`user_id`  = :user_id
				 AND `subscribe`.`student_id` = `students`.`id`
				 AND `students`.`first_name` REGEXP :first_name
				 AND `students`.`last_name` REGEXP :last_name
				 LIMIT :start_pos, :rows_num");
			$db_subscribe->bindValue(':start_pos', self::$start_pos, PDO::PARAM_INT);
			$db_subscribe->bindValue(':rows_num', self::$students_on_page, PDO::PARAM_INT);
			$db_subscribe->bindValue(':first_name', self::$first_name);
			$db_subscribe->bindValue(':last_name', self::$last_name);
			$db_subscribe->bindValue(':user_id', $user_id);
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
}



?>