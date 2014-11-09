<?php

/**
 * работа с отправкой сообщения на почту
 *
 * @package rating
 * @author Mip
 **/
class email
{
	/**
	 * хранит ссылку, которая будет отправляться в сообщении пользователю
	 *
	 * @var string
	 **/
	private $base_url = 'sdc/rating/activation.php';

	/**
	 * хранит гет параметры, которые будут приходить от пользователя
	 *
	 * @var string
	 **/
	private $get_parametrs;

	/**
	 * хранит хэш емейла пользователя, для дальнейших действий
	 *
	 * @var string
	 **/
	private $hash;

	/**
	 * хранит почту пользователя, на которую будет отправленно сообщение
	 *
	 * @var string
	 **/
	private $to;

	/**
	 * Тема сообщения/заголовок
	 *
	 * @var string
	 **/
	private $subject;

	/**
	 * Текст сообщения
	 *
	 * @var string
	 **/
	private $message;

	/**
	 * Информация о сообщении/от кого отправлено + настройки
	 *
	 * @var string
	 **/
	private $headers;

	/**
	 * конструктор класса email
	 *
	 * @return void
	 * @author Mip
	 **/
	function __construct($email)
	{
		$this->hash = md5($email);
		$this->get_parametrs = '?code=' . $this->hash;
		$this->to = $email;
		$this->subject = "Подтверждение электронной почты";
		$this->message = 'Здравствуйте! <br/> <br/> Мы должны убедиться в том, что вы человек. Пожалуйста, подтвердите адрес вашей электронной почты, и можете начать использовать ваш аккаунт на сайте. <br/> <br/> <a href = "' . $this->base_url . $this->get_parametrs .'">Подтвердить регестрацию</a>';
		$this->headers = 'From: pavel@sindycate.com' . "\r\n" .
							 'Reply-To: pavel@sindycate.com' . "\r\n" .
							 'Content-type: text/html; charset=iso-8859-1' . "\r\n".
							 'X-Mailer: PHP/' . phpversion();
		$this->message = str_replace("\n.", "\n..", $this->message);
	}

	/**
	 * функция, которая отправляет сообщение на почту пользователю
	 *
	 * @return bool
	 * @author Mip
	 **/
	public function send_mail()
	{
		return mail($this->to, $this->subject, $this->message, $this->headers);
	}

} // END class email

?>