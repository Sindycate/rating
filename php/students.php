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
	 * Подготовка данных
	 *
	 * @return void
	 * @author Mip, SergeShaw
	 **/

	static public function data_prepare()
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
}

?>