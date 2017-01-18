<?php

namespace royal\base\console;


use royal\base\Interact;
use royal\type\Matrix;

/**
 * Class Command
 *
 * // TODO: describe the class
 *
 * @package royal\base\console
 *
 * @property array $execute      The results of the outputs.
 * @see \royal\base\console\Command::getExecute()
 *
 *
 * @method Command cls()                       "cls" command
 * @method Command dir()                       "dir" command
 * @method Command ipconfig()                  ...
 * @method Command ifconfig([$params, ...])    ...
 * @method Command cd([$params, ...])          etc...
 * @method Command php([$params, ...])         ...
 * @method Command mysql([$params, ...])       ...
 * @method Command mysqldump([$params, ...])   ...
 *
 * @author Fadi Ahmad
 */
class Command extends Interact
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
