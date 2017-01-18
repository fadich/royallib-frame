<?php


namespace royal\base;


use royal\base\console\Console;
use royal\base\exceptions\BadRequestException;
use royal\type\Str;

final class Application extends Object
{
    /** @var Request $request */
    public static $request;

    private static $_baseAppPath;

    private $_url;
    private $_controller;
    private $_action;

    private function __construct() {  }

    public static function run()
    {
        if (isset($_SESSION)) {
            self::runBrowser();
        } else {
            self::runConsole();
        }
    }

    public static function basePath()
    {
        return self::$_baseAppPath;
    }

    private static function runBrowser()
    {
        //        ini_set('display_errors', 1);
        self::$request = new Request();
        self::$_baseAppPath = __DIR__ . '/../../../';
        $app = new static();
        $app->_url = explode("?", $_SERVER['REQUEST_URI'])[0];
        $app->_controller = explode('/', $app->_url)[1] ?? '';
        $app->_action   = explode('/', $app->_url)[2] ?? '';
//        try {
        $app->call();
//        } catch (BadRequestException $exception) {
//            Application::$request->redirect('/', 404);
//        } catch (\Throwable $throwable) {
//            throw $throwable;
//        }
    }

    private static function runConsole()
    {
        $argv = $_SERVER['argv'];
        array_shift($argv);
        $controller = array_shift($argv);
        $con = new Console($argv);
        echo '<pre>'; var_dump($con); die;
    }

    private function call()
    {
        if ($this->_controller) {
            try {
                $controller = new $this->controllerClass;
            } catch (\Error $error) {
                throw new BadRequestException("Unknown controller \"{$this->_controller}\"");
            }
            if ($this->_action) {
                $scenario = "a{$this->_action}";
                if (!method_exists($controller, $scenario)) {
                    throw new BadRequestException("Unknown action \"{$this->_controller}/{$this->_action}\"");
                }
                return $controller->$scenario();
            }
            return $controller->aIndex();
        }
        $this->_controller = 'main';
        return (new $this->controllerClass)->aIndex();
    }

    protected function getControllerClass()
    {
        return '\\' . APP_NAME . '\\controllers\\' . (new Str($this->_controller))->toClassName() . "Controller";
    }
}
