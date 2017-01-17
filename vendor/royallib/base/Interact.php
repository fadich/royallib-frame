<?php

namespace royal\base;


/**
 * Class Interact
 * @package royal\base
 *
 *          TODO: describe \royal\base\Interact
 *
 * @property array $errors
 */
abstract class Interact extends Object
{
    protected $_errors = [];

    public function addError($name, $text)
    {
        if (!is_string($name) || !is_int($name)) {
            array_push($this->_errors, $text);
        } else {
            $this->_errors[$name] = $text;
        }
        return $this;
    }

    public function popError()
    {
        return array_pop($this->_errors);
    }

    public function hasErrors($error = null)
    {
        return $error ? isset($this->errors[$error]) : !empty($this->errors);
    }

    /**
     * @return array
     * @see Interact::$errors
     */
    protected function getErrors()
    {
        return $this->_errors;
    }
}
