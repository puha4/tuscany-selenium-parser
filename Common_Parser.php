<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('max_execution_time', '1000');

require_once('lib/__init__.php');
require_once('pages/Page_Home.php');
require_once('pages/Page_Login.php');
require_once('pages/Page_Category.php');
require_once('pages/Page_Product.php');
/**
* Created by Vitalii Puhach
*/
class Common_Parser {

    private $brand;
    private $type;
    private $brands = array(
        1 => "Porsche",
        2 => "Tuscany",
        3 => "Bocci",
        4 => "Mount",
        5 => "Select",
        6 => "Sports-Goggles",
    );
    private $products;
    protected $web_driver;
    private $links = array(
        // Porsche
        // http://localhost/Common_Parser.php?brand_id=1&type=sun
        // http://localhost/Common_Parser.php?brand_id=1&type=nosun
        1 => array(
            "nosun" => array(
                1 => "/porsche/all_porsche_nosun.aspx",
                2 => "/porsche/all_porsche_rtools.aspx",
            ),
            "sun" => array(
                1 => "/porsche/all_porsche_sun.aspx",
                2 => "/porsche/all_porsche_sun.aspx?cat=Polarized",
            ),
        ),
        // Tuscany
        // http://localhost/Common_Parser.php?brand_id=2&type=sun
        // http://localhost/Common_Parser.php?brand_id=2&type=nosun
        2 => array(
            "nosun" => array(
                1 => "/tusc_eyewear/all_tusc_nosun?W=0",
            ),
            "sun" => array(
                2 => "/tusc_eyewear/all_tusc_sun?W=0",
            ),
        ),
        // Bocci
        // http://localhost/Common_Parser.php?brand_id=3&type=nosun
        3 => array(
            "nosun" => array(
                1 => "/bocci/all_bocci?W=0",
            )
        ),
        // Mount
        // http://localhost/Common_Parser.php?brand_id=4&type=nosun
        4 => array(
            "nosun" => array(
                1 => "/mount/all_mount_nosun",
                2 => "/mount/all_newStainless",
                2 => "/mount/all_monel",
                4 => "/mount/all_signature",
                5 => "/mount/all_studio",
            )
        ),
        // Select
        // http://localhost/Common_Parser.php?brand_id=5&type=nosun
        5 => array(
            "nosun" => array(
                1 => "/select/all_select",
            )
        ),
        // Sports-Goggles
        // http://localhost/Common_Parser.php?brand_id=6&type=sun
        6 => array(
            "sun" => array(
                1 => "/tusc_eyewear/goggles"
            )
        ),
    );

    const URL_BASE = 'http://www.tuscanyeyewear.com/';
    // const URL_PARSER = 'http://95.211.168.207/glassestest/rhino_v2/parse/sync-tuscany.php';
    const URL_PARSER = 'http://108.59.12.8/sync-tuscany.php';

    function __construct($brand, $type) {
        $this->brand = $brand;
        $this->type = $type;
        $this->set_up();
    }

    /**
     * Создаем connect
     */
    private function set_up() {
        $host = 'http://localhost:4444/wd/hub'; // this is the default
        $capabilities = DesiredCapabilities::firefox();
        $this->web_driver = RemoteWebDriver::create($host, $capabilities, 5000);
        // $this->web_driver->manage()->timeouts()->pageLoadTimeout(10);
        $this->web_driver->get(self::URL_BASE);
    }

    /**
     * Точка входа
     */
    public function parse() {
        $brand_links = $this->links[$this->brand][$this->type];
        if(!$brand_links) {
            die('Failed link.');
        }
        echo "Parse method:<br>";
        echo "--Do login operations:<br>";
        $this->do_login();
        echo "--Do category operations:<br>";
        $this->parse_categories($brand_links);
        echo "--Send to parser<br>";
        $this->send_to_parser();
    }

    /**
     * Проверка и выполнение авторизации
     */
    private function do_login() {
        $page_home = new Page_Home($this->web_driver);

        if(!$page_home->check_login()) {
            echo "<br>Already logged in<br>";
            return;
        }

        $page_login = $page_home->click_login();
        $page_login->submit_form();
    }

    // private function do_relogin($current_link) {
    // 	$page_home = new Page_Home($this->web_driver);

    // 	if(!$page_home->check_login()) {
    // 		$page_home = $page_home->click_logout();

    // 		// $page_home = new Page_Home($this->web_driver);
    // 		$page_login = $page_home->click_login();
    // 		$page_login->submit_form();

    // 		$this->web_driver->get(self::URL_BASE. $current_link);

    // 		return new Page_Category($this->web_driver);
    // 	}
    // }

    /**
     * Парсинг товаров на страницах категорий
     * @param  [type] $brand_links [description]
     */
    private function parse_categories($brand_links) {
        $i = 0;
        $this->products = array();

        foreach($brand_links as $key_type => $link) {
                $product_names = array();
                $this->web_driver->get(self::URL_BASE . $link);
                $page_category = new Page_Category($this->web_driver);
                $category_products = $page_category->get_products();

                foreach($category_products as $key_prod => $product) {
                    $product_names[] = $product->getText();
                }
                // if($key_type !== "sun") {
                // 	$product_names = array_splice($product_names, -2);
                // }

                foreach($product_names as $key_name => $name) {
                    // if(!in_array($name, array("P 8597", "P 8613"))) {
                    // continue;
                    // }
                    // if ($i % 1 == 0 && $i > 0) {
                    // 	echo '<pre>';
                    // 	echo count($products);
                    // 	print_r($products);
                    // 	echo '</pre>';
                    // 	$page_category= $this->do_relogin($link);
                    // }
                    $page_product = $page_category->click_product_by_link_text($name);
                    $name_code = str_replace(' ', '_', trim($name));
                    $item_title = str_replace(' ', '', trim($name));

                    $this->products[$name_code]['brand'] = $this->brands[$this->brand];
                    $this->products[$name_code]['item_title'] = $item_title;
                    $this->products[$name_code]['item_name'] = $name;
                    $this->products[$name_code]['type'] = $this->type;
                    $this->products[$name_code]['price'] = $page_product->get_price();
                    $this->products[$name_code]['main_img'] = $page_product->get_main_img();
                    $this->products[$name_code]['variations'] = $page_product->get_variations($name);

                    $page_category = $page_product->navigate_back();
                    // if($i){
                    // 	echo '<pre>';
                    // 	print_r($this->products);
                    // 	echo '</pre>';
                    // 	die('??');
                    // 	return;
                    // }
                    $i++;
            }
        }
    }

    /**
     * Отправляем массив продуктов
     */
    private function send_to_parser() {
        if(empty($this->products)) {
            die('No one products to send');
        }
        $products = serialize($this->products);

        $response_data = $this->sendHttpData(self::URL_PARSER, $products);

        echo($response_data);
    }

    /**
     * Для отправки данных
     * @param  string $url
     * @param  array $data
     * @return response
     */
    private function sendHttpData($url, $data) {
        $ch = curl_init($url);

        // curl_setopt($this->getHttp()->getCurl(), CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2049.0 Safari/537.36");
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "products={$data}");

        $result = curl_exec($ch);
        $info = curl_getinfo($ch);

        curl_close($ch);

        if($info['http_code'] == 0) {
            echo "\nhttp code = 0 !!!\n";
        }

        return $result;
    }

}

$brands = array(
    1 => "Porsche",
    2 => "Tuscany",
    3 => "Bocci",
    4 => "Mount",
    5 => "Select",
    6 => "Sports-Goggles",
);

$types = array(
    "sun" => "sun",
    "nosun" => "nosun"
);

if(!isset($_GET['brand_id']) || empty($_GET['brand_id'])) {
    if (!isset($_GET['type']) || empty($_GET['type'])) {
        echo "Please input a type key:(example - php Common_Parser.php?brand_id=1&type=sun)\n";
    }
    echo "Please input a brand key:(example - php Common_Parser.php?brand_id=1)\n";
    print_r($brands);
    die("--end of script--\n");
}

echo "Selected brand is {$brands[$_GET['brand_id']]} and type - {$types[$_GET['type']]}<br>";
$brand_id = $_GET['brand_id'];
$type = $_GET['type'];
$parser = new Common_Parser($brand_id, $type);
$parser->parse();



 ?>