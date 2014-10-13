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

	$data['students'] = get_all_students();
}

/**
 * Выборка всех студентов из рейтинга
 *
 * @return array
 * @author SergeShaw
 **/
function get_all_students()
{
	try
	{
		$db_get_students = database::$DBH->prepare(
			"SELECT *
			 FROM `students`
			 ORDER BY `category` desc");
		$db_get_students->execute();

		if ($db_get_students->rowCount())
		{
			return $db_get_students->fetchAll();
		}
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