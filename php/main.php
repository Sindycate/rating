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
		/*$db_get_students = database::$DBH->prepare(
			"SELECT *
			 FROM `students`
			 ORDER BY `category` desc");
		$db_get_students->execute();

		if ($db_get_students->rowCount())
		{
			return $db_get_students->fetchAll();
		}*/

		if (isset($_GET['page_num']))
		{
			$data['page']['current'] = $_GET['page_num'];
		}
		else {
			$data['page']['current'] = 1;
		}

		$articles_num = 4;
		// $start_pos = ($_GET['page_num'] * 5) ? $_GET['page_num'] * 5 : 0;
		$start_pos = ($data['page']['current'] - 1) * $articles_num;

		$query  = database::$DBH->prepare("SELECT * FROM students LIMIT $start_pos , $articles_num");
		$query2 = database::$DBH->prepare("SELECT * FROM students");
		//execute the query.0

		$query2->execute();
		$query->execute();

		$data['rows']['count'] = $query2->rowCount();

		if ($query->rowCount())
		{
			return $query->fetchAll();
		}

		$result = $query->fetchAll();


	}
	catch (PDOExeption $ee)
	{
		$data['error']['PDO'] = "Ошибка базы данных: " . $e->getMessage();
		return false;
	}
}



main();

require_once('/html/header.html');
require_once('/html/main.html');
require_once('/html/footer.html');

?>