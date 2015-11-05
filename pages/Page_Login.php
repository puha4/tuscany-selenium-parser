<?php
require_once('lib/__init__.php');
/**
* Login page initialize
* Created by Vitalii Puhach
*/
class Page_Login {

	private $email;
	private $password;
	private $remember;
	private $submit;

	/**
	 * Обьект драйвера
	 * @var RemoteWebDriver
	 */
	private $web_driver;

	/**
	 * @param  RemoteWebDriver $web_driver
	 */
	function __construct(RemoteWebDriver $web_driver) {
		$this->web_driver = $web_driver;
		// Дожидаемся загрузки первого елемента
		$wait = new WebDriverWait($this->web_driver,30);
		$wait->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('MainContent_Email')));

		$this->email = $this->web_driver->findElement(WebDriverBy::id('MainContent_Email'));
		$this->password = $this->web_driver->findElement(WebDriverBy::id('MainContent_Password'));
		$this->remember = $this->web_driver->findElement(WebDriverBy::id('MainContent_RememberMe'));
		$this->submit = $this->web_driver->findElement(WebDriverBy::name('ctl00$MainContent$ctl05'));
	}

	/**
	 * Отправка формы авторизации
	 */
	public function submit_form() {
		$this->email->sendKeys("eyelandvision1@yahoo.com");
		$this->password->sendKeys("Gg1970gg");
		$this->remember->click();
		$this->submit->click();
	}
}

 ?>