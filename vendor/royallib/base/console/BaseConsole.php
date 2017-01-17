<?php

namespace royal\base\console;


use royal\base\Interact;
use royal\type\Matrix;

/**
 * Class BaseConsole
 *
 * // TODO: describe the class
 *
 * @package royal\base\console
 *
 * @property array $execute      The results of the outputs.
 * @see \royal\base\console\BaseConsole::getExecute()
 *
 *
 * @method BaseConsole cls()                       "cls" command
 * @method BaseConsole dir()                       "dir" command
 * @method BaseConsole ipconfig()                  ...
 * @method BaseConsole ifconfig([$params, ...])    ...
 * @method BaseConsole cd([$params, ...])          etc...
 * @method BaseConsole php([$params, ...])         ...
 * @method BaseConsole mysql([$params, ...])       ...
 * @method BaseConsole mysqldump([$params, ...])   ...
 *
 * @author Fadi Ahmad
 */
abstract class BaseConsole extends Interact
{
    protected $_output   = [];
    protected $_commands = [];

    public function command(string $command)
    {
        $this->_commands[] = $command;
        return $this;
    }

    public function getExecute()
    {
        $output = $this->executeAll()->_output;
        $this->_output = [];
        return $output;
    }

    /**
     * TODO: describe Console::__call() method
     *
     * @param string $name
     * @param array  $params
     *
     * @return static
     */
    public function __call($name, $params)
    {
        $params = func_get_args();
        array_shift($params);
        $params = (new Matrix($params))->multiImplode(" ");
        return $this->command("{$name} {$params}");
    }

    protected function executeAll()
    {
        foreach ($this->_commands as $command) {
            $this->_output[] = shell_exec($command);
        }
        return $this;
    }
}
