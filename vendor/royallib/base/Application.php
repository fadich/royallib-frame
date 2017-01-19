<?php


namespace royal\base;


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
    private $_from;

    private function __construct() {  }

    public static function run()
    {
        self::$_baseAppPath = __DIR__ . '/../../../';
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
        $app = new static();
        $app->_from       = '\controllers\\';
        $app->_url        = explode("?", $_SERVER['REQUEST_URI'])[0];
        $app->_controller = explode('/', $app->_url)[1] ?? '';
        $app->_action     = explode('/', $app->_url)[2] ?? '';
        $app->call();
    }

    private static function runConsole()
    {
        $app  = new static();
        $argv = $_SERVER['argv'];
        array_shift($argv);
        $toCall = explode('/', array_shift($argv));
        $app->_from = '\console\controllers\\';
        $app->_controller = $toCall[0] ?? '';
        if (!$app->_controller) {
            self::startScreen();
            exit();
        }
        $app->_action = $toCall[1] ?? '';
        $app->call();
    }

    private static function startScreen()
    {
        $res = 'Hello!';
        print_r($res);
    }

    private function call()
    {
        if ($this->_controller) {
            try {
                $controller = new $this->controllerClass;
            } catch (\Error $error) {
                throw new BadRequestException("Unknown controller \"{$this->controllerClass}\"");
            }
            if ($this->_action) {
                $this->_action = ucfirst($this->_action); // For pretty view
                $action = "a{$this->_action}";
                if (!method_exists($controller, $action)) {
                    throw new BadRequestException("Unknown method (action) \"{$this->controllerClass}::{$action}()\"");
                }
                return $controller->$action();
            }
            return $controller->aIndex();
        }
        $this->_controller = 'main';
        return (new $this->controllerClass)->aIndex();
    }

    protected function getControllerClass()
    {
        return '\\' . APP_NAME . $this->_from . (new Str($this->_controller))->toClassName() . "Controller";
    }
}
