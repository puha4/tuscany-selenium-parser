<?php
require_once('lib/__init__.php');
/**
 * Home page initialize
 * Created by Vitalii Puhach
 */
class Page_Home {

	private $login;

	/**
	 * Обьект драйвера
	 * @var RemoteWebDriver
	 */
	private $web_driver;

	/**
	 * @param  RemoteWebDriver $web_driver
	 */
	public function __construct(RemoteWebDriver $web_driver) {
		$this->web_driver = $web_driver;

		$this->login = $this->web_driver->findElement(WebDriverBy::id('HeadLoginView_HeadLoginStatus'));
	}

	/**
	 * Текст кнопки Login
	 * @return string
	 */
	public function get_login_text() {
		return strtolower(trim($this->login->getText()));
	}

	/**
	 * Провериям не залогинены ли
	 * @return bool
	 */
	public function check_login() {
		$login_text = $this->get_login_text();
		return ($login_text == "login") ? true : false;
	}

	public function click_login() {
		$this->login->click();
		return new Page_Login($this->web_driver);
	}

	public function click_logout() {
		$this->login->click();
		return new Page_Home($this->web_driver);
	}

}



?>