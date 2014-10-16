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

	prepare_find_data();

	$data['students'] = get_students();
	$data['rows']['num'] = 4;
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
			 FROM students");
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

		$rows_num = 20;
		$start_pos = ($data['page']['current'] - 1) * $rows_num;

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
		$db_students->bindValue(':rows_num', $rows_num, PDO::PARAM_INT);
		$db_students->bindValue(':first_name', $data['find']['first_name']);
		$db_students->bindValue(':last_name', $data['find']['last_name']);
		$db_students->execute();

		if ($db_students->rowCount())
		{
			return $db_students->fetchAll();
		}

		$result = $db_students->fetchAll();
	}
	catch (PDOExeption $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $e->getMessage();
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

require_once('/html/header.html');
require_once('/html/main.html');
require_once('/html/footer.html');

?>