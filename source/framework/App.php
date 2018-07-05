<?php
namespace framework;

class App {

    private static $engine;

    private function __construct() {}
    private function __destruct() {}
    private function __clone() {}

    public static function __callStatic($name, $params) {
        $app = App::app();
        return \framework\core\Dispatcher::invokeMethod(array($app, $name), $params);
    }

    public static function app() {
        static $initialized = false;

        if (!$initialized) {
            require_once __DIR__ . '/autoload.php';

            self::$engine = new \framework\Engine();

            $initialized = true;
        }

		//载入路由配置
		
		if( $initialized )  require_once CONFIG_PATH.'/router.php';
		
        return self::$engine;
    }
}
