<?php
require_once('lib/__init__.php');
/**
* Category page initialize
* Created by Vitalii Puhach
*/
class Page_Category
{
	private $products = array();
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
		// Дожидаемся загрузки первого елемента(в данном случае картинка)
		$wait = new WebDriverWait($this->web_driver,30);
		$wait->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('MainContent_img_lg')));

		$this->products = $this->web_driver->findElements(WebDriverBy::cssSelector("a.pname_list"));
	}

	/**
	 * Все продукты на странице категории
	 */
	public function get_products() {
		return $this->products;
	}

	/**
	 * Поиск продукта по названию и переход на страницу
	 * @param  String $link_text
	 * @return Page_Product
	 */
	public function click_product_by_link_text($link_text) {
		$wait = new WebDriverWait($this->web_driver,30);
		$wait->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::linkText($link_text)));
		$this->web_driver->findElement(WebDriverBy::linkText($link_text))->click();

		return new Page_Product($this->web_driver);
	}

	/**
	 * переход на страницу продукта
	 * @param  RemoteWebElement $product
	 * @return Page_Product
	 */
	public function click_product(RemoteWebElement $product) {
		$product->click();

		return new Page_Product($this->web_driver);
	}
}


 ?>