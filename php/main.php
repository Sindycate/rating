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

	if ($data['user'])
	{
		$data['user']['vote'] = students::check_limit_vote($data['user']['id']);

		if ($data['user']['vote'] &&
			 isset($_POST['vote']) &&
			 $_POST[$data['user']['id']] &&
			 students::student_check($data['user']['id'], $_POST[$data['user']['id']]))
		{
			students::vote($data['user']['id'], $_POST[$data['user']['id']]);
			// с связи с возможным изменением данных
			// ещё раз проверяем может ли он голосовать
			$data['user']['vote'] = students::check_limit_vote($data['user']['id']);

			$data['success'] = "Ваш голос отдан";
		}
		else if (isset($_POST['subscribe']) &&
					$_POST[$data['user']['id']] &&
					students::check_subscribe($data['user']['id'], $_POST[$data['user']['id']]))
		{
			students::subscribe($data['user']['id'], $_POST[$data['user']['id']]);

			$data['success'] = "Вы подписались";
		}
		else if (isset($_POST['unsubscribe']) &&
					$_POST[$data['user']['id']] &&
					students::check_unsubscribe($data['user']['id'], $_POST[$data['user']['id']]))
		{
			students::unsubscribe($data['user']['id'], $_POST[$data['user']['id']]);

			$data['success'] = "Вы отписались";
		}
	}

	if ($data['place'] == 'all' ||
		!$data['user'] ||
		!students::get_subscribe_check($data['user']['id']))
	{
		students::students_prepare(); //подготовка данных()

		$data['place'] = 'all';
		$data['pages_num'] = students::$pages_num; // суммарное количество страниц
		$data['current_page'] = students::$current_page;  // текущая страница из подготовленных данных
		$data['students'] = students::get_students(); // выборка одной страницы студентов

		if ($data['user']['id'])
		{
			$data['subscribe'] = students::get_subscribe_id($data['user']['id']);
		}
	}
	else
	{
		students::subscribe_prepare($data['user']['id']); //подготовка данных()
		//возможно здесь есть ошибка ['type'] => ['place']
		$data['place'] = 'subscribe';
		$data['pages_num'] = students::$pages_num; // суммарное количество страниц
		$data['current_page'] = students::$current_page;  // текущая страница из подготовленных данных
		$data['students'] = students::get_students(); // выборка одной страницы студентов
		$data['students'] = students::get_subscribe($data['user']['id']);
	}
}

main();

// in_array("9", haystack)

require_once('./html/header.html');
require_once('./html/main.html');
require_once('./html/footer.html');

?>