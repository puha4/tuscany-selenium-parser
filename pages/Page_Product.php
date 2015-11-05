<?php
require_once('lib/__init__.php');
include('simplehtmldom/simple_html_dom.php');

/**
 * Product page initialize
 * Created by Vitalii Puhach
*/
class Page_Product{

	private $colors;
	private $sizes;
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
		// Дожидаемся загрузки первого елемента(кнопка Add to cart)
		$wait = new WebDriverWait($this->web_driver,30);
		$wait->until(WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('MainContent_btn_addToCart')));

		$this->colors = $this->init_select();
		$this->sizes = $this->init_select_sizes();

	}

	/**
	 * Инициализация select colors что бы делать клик после обновления страницы с новым обьектом
	 * @return WebDriverSelect
	 */
	private function init_select() {
		return new WebDriverSelect($this->web_driver->findElement(WebDriverBy::id("MainContent_DDListColors")));
	}

	/**
	 * Инициализация select sizes что бы делать клик после обновления страницы с новым обьектом
	 * @return WebDriverSelect
	 */
	private function init_select_sizes() {
		return new WebDriverSelect($this->web_driver->findElement(WebDriverBy::id("MainContent_DDListSize")));
	}

	/**
	 * Возвращает цену продукта
	 * @return string
	 */
	public function get_price() {
		$str = $this->web_driver->getPageSource();
		$html = str_get_html($str);

		// цена со скидкой
		$price_discount = $html->find('#MainContent_lbl_Discount');
		if(!count($price_discount)) {
			$price = trim($html->find('#MainContent_lbl_listP', 0)->innertext);
		} else {
			$price = trim($price_discount[0]->plaintext);
		}

		$price = str_replace("$", "", $price);

		return $price;
	}

	/**
	 * Возвращает ссылку на изображение модели
	 * @return string
	 */
	public function get_main_img() {
		$str = $this->web_driver->getPageSource();
		$html = str_get_html($str);

		$main_img_url = trim($html->find('#MainContent_img_big', 0)->src);

		return $main_img_url;
	}

	/**
	 * Ожидаем завершения обновления страницы при ajax запросе
	 * @param  RemoteWebDriver $driver
	 * @param  string $framework
	 */
	function waitForAjax($driver, $framework='jquery') {
		// javascript framework
		switch($framework){
			case 'jquery':
				$code = "return jQuery.active;"; break;
			case 'prototype':
				$code = "return Ajax.activeRequestCount;"; break;
			case 'dojo':
				$code = "return dojo.io.XMLHTTPTransport.inFlight.length;"; break;
			default:
				throw new Exception('Not supported framework');
		}

		do {
			sleep(2);
		} while ($driver->executeScript($code));
	}

	/**
	 * Возвращаем все вариации с параметрами для модели
	 * @param  string $model_name
	 * @return array
	 */
	public function get_variations($model_name) {
		$options_text = array();
		$options_size_text = array();
		$variations = array();
		$options = $this->colors->getOptions();

		// сначала берем тексты всех option что бы потом нажимать по тексту
		foreach($options as $key => $option) {
			$options_text[] = $option->getText();
		}

		foreach($options_text as $key => $text) {
			$this->init_select()->selectByVisibleText($text);
			$this->waitForAjax($this->web_driver);

			$options_sizes = $this->init_select_sizes()->getOptions();
			// потом берем тексты всех option sizes что бы потом нажимать по тексту
			foreach($options_sizes as $key => $option_size) {
				$options_size_text = $option_size->getText();
				$this->init_select_sizes()->selectByVisibleText($options_size_text);
				$variations[] = $this->get_variation_param($model_name, $options_size_text);
			}

		}

		return $variations;
	}

	/**
	 * Возвращаем параметры для текущей вариации (текущего состояния страницы)
	 * @param  string $model_name
	 * @return array
	 */
	public function get_variation_param($model_name, $options_size_text) {
		// $variation = array();

		$str = $this->web_driver->getPageSource();
		$html = str_get_html($str);

		$img_url = trim($html->find('#MainContent_img_big', 0)->src);
		$color_code = trim($html->find('#MainContent_DDListColors option[selected]', 0)->value);
		$color_letter = trim($html->find('#MainContent_DDListColors option[selected]', 0)->innertext);

		if(count($html->find('#MainContent_lbl_frameColor'))) {
			$color_frame = $html->find('#MainContent_lbl_frameColor');
			$color_frame = $color_frame[0]->innertext;
		} else {
			$color_frame = $color_letter;
		}
		// $color_frame = trim($html->find('#MainContent_lbl_frameColor', 0)->innertext);
		// $size = trim($html->find('#MainContent_DDListSize option', 0)->innertext);
		$size = $options_size_text;
		$sizes = explode("-", $size);
		$name = $model_name.' '.$color_letter;
		$name = str_replace(' ', '_', trim($name));

		return array(
			'code' => $name,
			'color_letter' => $color_letter,
			'img_url' => $img_url,
			'color_code' => $color_code,
			'color_frame' => $color_frame,
			'size_1' => $sizes[0],
			'size_2' => $sizes[1],
			'size_3' => $sizes[2],
		);

		// return $variation;
	}

	/**
	 * Возврат на страницу категорий
	 * @return Page_Category
	 */
	public function navigate_back() {
		$this->web_driver->navigate()->back();
		return new Page_Category($this->web_driver);
	}
}
 ?>