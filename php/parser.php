<pre>
<?php

require_once('./config.php');

$handle = @fopen("../data/mag-asp.txt", "r");

if ($handle)
{
	while (($buffer = fgets($handle)) !== false)
	{
		$mas = explode("	", $buffer);

		if (count($mas) == 11)
		{
			try
			{
				// $db_add = database::$DBH->prepare(
				// 	"INSERT INTO `students` (`faculty`, `last_name`, `first_name`, `patronymic`, `USE`, `category`, `level`, `study_mode`)
				// 	 VALUES                 (:faculty,  :last_name,  :first_name,  :patronymic,  :USE,  :category,  :level,  :study_mode)");
				// $db_add->bindValue(':faculty', $mas[0]);
				// $db_add->bindValue(':last_name', $mas[4]);
				// $db_add->bindValue(':first_name', $mas[5]);
				// $db_add->bindValue(':patronymic', $mas[6]);
				// $db_add->bindValue(':USE', $mas[7]);
				// $db_add->bindValue(':category', $mas[8]);
				// $db_add->bindValue(':level', $mas[9]);
				// $db_add->bindValue(':study_mode', $mas[10]);
				// $db_add->execute();
				// echo $mas[0] . ' - ';
				// echo $mas[4] . ' - ';
				// echo $mas[5] . ' - ';
				// echo $mas[6] . ' - ';
				// echo $mas[7] . ' - ';
				// echo $mas[8] . ' - ';
				// echo $mas[9] . ' - ';
				// echo $mas[10] . ' - ';
			}
			catch (PDOExeption $ee)
			{
				echo $ee->getMessage();
			}
		} else {
			echo "error";
		}
	}
	if (!feof($handle))
	{
		echo "Error: unexpected fgets() fail \n";
	}

	fclose($handle);
}
