<?php

namespace royal\base\console;


abstract class Controller extends BaseConsole
{
    public $console;

    public function __construct()
    {
        $argv = $_SERVER['argv'];
        $this->console = new Console($argv);
    }
}
