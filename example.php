<?php
// An example of using php-webdriver.

require_once('lib/__init__.php');

// start Firefox with 5 second timeout
$host = 'http://localhost:4444/wd/hub'; // this is the default
$capabilities = DesiredCapabilities::firefox();
$driver = RemoteWebDriver::create($host, $capabilities, 5000);

// navigate to 'http://docs.seleniumhq.org/'
$driver->get('http://www.tuscanyeyewear.com/');
echo "\n Class - " .get_class($driver). "\n";
// var_dump($driver->getPageSource());

// // adding cookie
// $driver->manage()->deleteAllCookies();
// $driver->manage()->addCookie(array(
//   'name' => 'cookie_name',
//   'value' => 'cookie_value',
// ));
// $cookies = $driver->manage()->getCookies();
// print_r($cookies);

// // click the link 'About'
$link = $driver->findElement(
  WebDriverBy::id('HeadLoginView_HeadLoginStatus')
);
$login_text = strtolower(trim($link->getText()));
if ($login_text =="login") {
	$link->click();
}

// // print the title of the current page
// echo "The title is " . $driver->getPageSource() . "'\n";

// // print the title of the current page
// echo "The current URI is " . $driver->getCurrentURL() . "'\n";

// // Search 'php' in the search box
// $input = $driver->findElement(
//   WebDriverBy::id('q')
// );
// $input->sendKeys('php')->submit();

// // wait at most 10 seconds until at least one result is shown
// $driver->wait(10)->until(
//   WebDriverExpectedCondition::presenceOfAllElementsLocatedBy(
//     WebDriverBy::className('gsc-result')
//   )
// );

// close the Firefox
// $driver->quit();
