<?php
/**
 * Created by PhpStorm.
 * User: lexam85
 * Date: 22.05.15
 * Time: 15:14
 */
require_once('lib/__init__.php');
class WebMy {
    private $driver;
    function __construct()
    {
        $host = 'http://localhost:4444/wd/hub'; // this is the default
        $capabilities = DesiredCapabilities::firefox();
        $this->driver = RemoteWebDriver::create($host,$capabilities);
    }
    public function getWebPage($url)
    {
        $this->driver->get($url);
        $result = $this->driver->getPageSource();
        return $result;
    }

    public function close(){
        $this->driver->close();
    }
}