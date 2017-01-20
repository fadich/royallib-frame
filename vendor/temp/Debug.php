<?php

namespace app\components\HelpClasses\debug;
use app\components\HelpClasses\type\Mixed;
use yii\base\Model;

/**
 * Class Debug
 * @package app\components\HelpClasses\debug
 *
 * Testing and debug application.
 * @property mixed  $log
 * @see \app\components\HelpClasses\debug\Debug::setLog()
 * @property string $microdate
 */
class Debug extends \yii\debug\models\search\Debug
{
    const FORMAT_SECONDS      = 1;
    const FORMAT_MILLISECONDS = 1000;
    const FORMAT_MICROSECONDS = 1000000;
    const FORMAT_BYTES        = 1;
    const FORMAT_KBYTES       = 1024;
    const FORMAT_MBYTES       = 1048576;
    const FORMAT_GBYTES       = 1073741824;

    /** @var array $_timer  Save the time */
    protected $_timer = [];

    /** @var array $_log    The debug's logs */
    protected $_log = [];

    /** @var array $_memory Memory usage */
    protected $_memory = [];

    public function __destruct()
    {
        $this->log = $this->logDelays($this->_timer);
        $this->saveLogs();
    }

    /**
     * Init debug us global or get it (global can be initialized in session).
     *
     * @return static
     */
    public static function getGlobal()
    {
        if (!isset($GLOBALS['debug']) || !($GLOBALS['debug'] instanceof Debug)) {
            $GLOBALS['debug'] = new static;
        }
        return $GLOBALS['debug'];
    }

    /**
     * Start timer.
     *
     * @param bool|int|string $index  The timer index. Default is new index of array.
     *
     * @return $this
     */
    public function startTimer($index = false)
    {
        if ($index === false) {
            $this->_timer[] = microtime(true);
        } else {
            $this->_timer[$index] = microtime(true);
        }
        return $this->setLog('timer "' . key(array_slice($this->_timer, -1, 1, true)) . '" started (' . $this->microdate . ')', 'timer');
    }

    /**
     * Getting the time difference by timer index (default is 1-st timer).
     * Returns the delay's value (default seconds).
     *
     * @param bool $index       The timer index.
     * @param int  $precision   The time accuracy.
     * @param int  $factor      Result format (seconds, milliseconds, microseconds)
     *
     * @return float
     */
    public function delay($index = false, int $precision = 10, $factor = self::FORMAT_SECONDS) : float
    {
        if (empty($this->_timer)) {
            throw new \InvalidArgumentException("There is no timers started");
        }
        $index = $index === false ? 0 : $index;
        if (!isset($this->_timer[$index])) {
            throw new \InvalidArgumentException("Undefined timer {$index}");
        }
        return $factor * round(microtime(true) - $this->_timer[$index], $precision);
    }

    /**
     * Save info about variable (as var_dump()).
     *
     * For example,
     * ```php
     *  $this->dump('string')
     *  // save info about variable: 'string(6)"string"'
     *  $this->dump(12.15)
     *  // save 'float(12.15)'
     * ```
     *
     * @param mixed $var
     *
     * @return $this
     * @throws \TypeError
     */
    public function dump($var)
    {
        if (is_array($var)) {
            $this->_log['dump'][] = 'array(' . sizeof($var) . ') [' . (new Mixed($var))->implodeElements(", ", [], '=>')->value . ']';
        } elseif (is_int($var) || is_bool($var) || is_float($var)) {
            $this->_log['dump'][] = gettype($var) . "(" . json_encode($var) . ")";
        } elseif (is_string($var)) {
            $this->_log['dump'][] = 'string(' . strlen($var) . ') ' . json_encode($var);
        } else {

        }
        return $this;
    }

    /**
     * Log setter.
     * Save something into $this->_log.
     * The logs will be auto-saved (on __destruct()).
     *
     * Example,
     * ```php
     *  $this->log = '123';
     *  // $this->_log = [0 => '123'];
     *  $this->setLog('123', 'example');
     *  // $this->_log = [0 => '123', 'example' => '123'];
     * ```
     *
     * @param mixed $value The logging variable.
     * @param bool  $key   Log key name.
     *
     * @return $this
     */
    public function setLog($value, $key = false)
    {
        if ($key === false) {
            $this->_log[] = $value;
        } else {
            $this->_log[$key][] = $value;
        }
        return $this;
    }

    /**
     * Log getter of $this->_log.
     *
     * Example,
     * ```php
     *  $this->log;
     *  // returns [0 => '123', 'example' => '123']
     *  $this->getLog('example');
     *  // (string)"123"
     * ```
     *
     * @param bool $key   Log key name, in case of $key === false, returns (array) $this->_log (all logs).
     *
     * @return array|mixed
     */
    public function getLog($key = false)
    {
        return $key !== false ? $this->_log[$key] : $this->_log;
    }

    /**
     * Debug info about object (on var_dump())
     *
     * @return mixed
     */
    public function __debugInfo()
    {
        return $this->log;
    }

    /**
     * Getting microtime() as date format.
     *
     * For example,
     * ```php
     * // Getting microdate with default format
     *  var_dump($this->microdate);
     * // Result: string(24) "15.12.2016 13:12:56:0823"
     * ```
     *
     * @param string $format The date format.
     *
     * @return string
     */
    public function getMicrodate(string $format = 'd.m.Y H:m:s:')
    {
        return date($format, time()) . substr(time () - microtime(true), 3, 4);
    }

    /**
     * Save the logs in log-file (that default is "project/base/path/h-log/{d-m-Y_H-m-s-mcs}.log")
     *
     * @param bool $key The logs key (default is all logs).
     *
     * @return Debug
     */
    public function saveLogs($key = false)
    {
        return $this->log($key === false ? $this->log : $this->log[$key]);
    }

    public function loadModel(Model $model, $data, $scenario = 'default')
    {
        $model->scenario = $scenario;
        foreach ($data as $key => $value) {
            if ($model->hasProperty($key)) {
                $model->{$key} = $value;
            }
        }
        return $model;
    }

    public function fixMemoryUsage()
    {
        $this->_memory[] = (float)memory_get_usage() - $this->sumMemory();
        $this->log = "Used memory diff: " . number_format(end($this->_memory), 0, ".", " ") .
            " bytes. Total usage: " . number_format($this->sumMemory(), 0, ".", " ") . " bytes";
        return $this;
    }

    public function usedMemory($format = self::FORMAT_BYTES)
    {
        return (float)memory_get_usage() / $format;
    }

    /* **************/

    /**
     * Writing logs to file.
     *
     * @param mixed $data Saving data
     *
     * @return $this
     */
    protected function log($data)
    {
        if (!is_dir(self::logDir())) {
            mkdir(self::logDir(), 0777, true);
        }
        file_put_contents(self::logDir() . '/' . $this->getMicrodate('d-m-Y_H-m-s-') . '.txt', $this->foreachData($data));
        return $this;
    }

    /**
     * Convert logs array to string.
     *
     * @param mixed  $data
     * @param string $separator
     *
     * @return string
     */
    protected function foreachData($data, string $separator = "\n")
    {
        $log = '';
        if (is_array($data)) {
            foreach ($data as $item) {
                $log .= $this->foreachData($item, $separator);
            }
        } else {
            $log .= $data . $separator;
        }
        return $log;
    }

    /**
     * Base log dir.
     *
     * @return string
     */
    protected static function logDir()
    {
        return \Yii::$app->basePath . '/h-log';
    }

    /**
     * Converting to string the info about timers.
     *
     * @param mixed $data
     *
     * @return string
     */
    protected function logDelays($data)
    {
        $res = "Timers (delay) = [\n";
        foreach ($data as $key => $item) {
            $res .= "\t\"{$key}\" => " . $this->delay($key). " sec,\n";
        }
        return "{$res}]";
    }

    protected function sumMemory($format = self::FORMAT_BYTES)
    {
        return array_sum($this->_memory) / $format;
    }
}
