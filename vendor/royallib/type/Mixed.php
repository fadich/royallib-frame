<?php

namespace royal\type;

use royal\base\exceptions\IncorrectParamsException;

/**
 * Class Mixed
 * @package royal\type
 *
 * Helper for working with any types of variables (mixed).
 *
 * @property mixed $value       The value (of array).
 * @see Mixed::getValue()
 *
 * @author Fadi Ahmad
 */
class Mixed extends BaseType
{
    const FORMAT_ARRAY  = 1;
    const FORMAT_JSON   = 2;
    const FORMAT_OBJECT = 3;
    const FORMAT_STRING = 4;

    /** @var mixed $value */
    protected $_value;

    /**
     * Mixed constructor.
     *
     * Init the value property by any mixed value.
     *
     * @param mixed $value
     */
    public function __construct($value = null)
    {
        $this->_value = $value;
//        parent::__construct();
    }

    /**
     * Method works according to principle implode() (converts array to string), except for a few nuances.
     * At first, a string of the converted array also will contains an array keys, previous of elements,
     *      separated from them by some separator.
     * As well, there could be listed the keys of the array, that filtering on imploding.
     *
     * For example:
     * 
     * ```php
     *  // calling on:
     *      $this->_value = array ['el_1' => 1, 'el_2' => 2, 'el_3' => 3];
     * 
     *  // with arguments:
     *      implodeElements('&', "=", ['el_1', 'el_2']);
     * 
     *  // will make a string as: "el_1=1&el_2=2";
     * ```     
     *
     * @param string $glue       a symbol or string that will be placed between the imploded elements;
     * @param array  $keys       an array keys, that should be used (filtered),
     *                               in case of there is no specified keys, all keys will be used;
     * @param string $separator  a symbol or string that is the separator between a keys and an elements;
     * @param bool   $convert    a value ($this->_value) can be convented to array;
     *
     * @return $this
     * @throws \TypeError
     */
    public function implodeElements($glue = "&", string $separator = "=", array $keys = [], bool $convert = false)
    {
        if (!$convert && !is_array($this->_value)) {
            throw new \TypeError("Value should be type of array, but not " . gettype($this->_value));
        }
        try {
            $this->_value = (array)$this->_value;
        } catch (\Throwable $e) {
            throw new \TypeError("Value (with type " . gettype($this->_value) . ") cannot be converted to array ");
        }
        $string = ""; $i = 0;
        foreach ($this->_value as $key => $item) {
            if (!empty($keys)) {
                if (in_array($key, $keys)) {
                    $string .= ($i++ ? $glue : "") . "{$key}{$separator}{$item}";
                }
            } else {
                $string .= ($i++ ? $glue : "") . "{$key}{$separator}{$item}";
            }
        }
        $this->_value = $string;
        return $this;
    }

    /**
     * TODO: doc method explodeElements()
     *
     *
     * @param string $glue
     * @param string $separator
     * @param array  $keys
     * @param int    $format
     *
     * @return $this
     * @throws IncorrectParamsException
     */
    public function explodeElements(string $glue = "&", string $separator = "=", array $keys = [], int $format = self::FORMAT_ARRAY)
    {
        if ($format !== self::FORMAT_ARRAY && $format !== self::FORMAT_JSON && $format !== self::FORMAT_OBJECT) {
            throw new IncorrectParamsException("Incorrect result format");
        }
        if (!is_string($this->value)) {
            throw new IncorrectParamsException('Value (Mixed::$value) should be a string$ ' . gettype($this->value) . ' given');
        }
        $elems = explode($glue, $this->value);
        $res   = [];
        foreach ($elems as $elem) {
            $item = explode($separator, $elem);
            if (sizeof($item) > 2) {
                throw new IncorrectParamsException("Invalid value structure: given incorrect an element size");
            } elseif (sizeof($item) == 2) {
                if (empty($keys) || in_array($item[0], $keys)) {
                    $res[(string)$item[0]] = $item[1];
                }
            } else {
                $res[] = $item[0];
            }
        }
        $this->_value = $format === self::FORMAT_JSON ? json_encode($res) :
            ($format === self::FORMAT_OBJECT ? (object)$res : $res);
        return $this;
    }

    /**
     * Getting $value property.
     * @see Mixed::$value
     *
     * @return array
     */
    protected function getValue()
    {
        return $this->_value;
    }
}
