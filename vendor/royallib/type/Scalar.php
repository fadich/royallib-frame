<?php

namespace royal\type;

/**
 * Class Scalar
 * @package royal\type
 *          
 * Helps working with a scalar-type variables.
 *
 * @property mixed $value
 *
 * @author Fadi Ahmad
 */
class Scalar extends BaseType
{
    /** @var mixed $value is scalar value */
    protected $_value;

    /**
     * Scalar constructor.
     *
     * @param bool|float|int|string $value
     * @throws \TypeError
     */
    public function __construct($value)
    {
        if (!is_string($value) && !is_bool($value) && !is_int($value) && !is_float($value)) {
            throw new \TypeError("Value should be scalar, " . gettype($value) . " given");
        }
        $this->_value = $value;
//        parent::__construct();
    }

    /**
     * Convert $this->_value to float (with replacement for string).
     * 
     * @return static
     */
    public function parseFloat()
    {
        if (is_string($this->value)) {
            $this->value = preg_replace('/\s|`|\'|,/', "", preg_replace('/,{1}/', '.', $this->value));
        }
        $this->value = (float)$this->value;
        return $this;
    }

    /**
     * Return the value.
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->value;
    }

    /**
     * Setter of the $_value property.
     * 
     * @param bool|float|int|string $value
     *
     * @throws \TypeError
     */
    public function setValue($value)
    {
        if (!is_string($value) && !is_bool($value) && !is_int($value) && !is_float($value)) {
            throw new \TypeError("Value should be scalar, " . gettype($value) . " given");
        }
        $this->_value = $value;
    }

    /**
     * The getter of the $_value property.
     * 
     * @return bool|float|int|string
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * Convert the value ($_value property) parsed to float to pretty number format.
     * For example, value is 150102.1110225 will be converted
     *      to a string of the form "150 102.11".
     *
     * @return static
     */
    public function prettyFormat()
    {
        $this->_value = number_format($this->parseFloat()->value, 2, '.', ' ');
        return $this;
    }

    /**
     * Converting amount of seconds to something like that: "XX h. XX m. XX s." (though in stupid russian language)
     *
     * @return static
     * @throws \Exception
     */
    public function convertTime() {
        if(is_int($this->_value) && $this->_value > 0) {
            $this->_value = $this->value > 60 ?
                $this->value > 3600 ?
                    floor($this->value/3600) . ' ч. ' . floor($this->value%3600/60) . ' мин. '
                : floor($this->value/60) . ' мин. ' . $this->value%60 . ' сек.'
            : $this->value . ' сек.';
            return $this;
        } else {
            throw new \Exception("Value should be a positive number of integer type");
        }
    }

    /**
     * Convert an object to variable in invoke.
     * For example, it can be used as:
     *
     * ```php
     *      $sum = (new Scalar('12.80'))->parseFloat();
     *      var_dump($sum());
     *      // result: float(12.8)
     * ```
     * 
     * @return bool|float|int|string
     */
    public function __invoke()
    {
        return $this->value;
    }
}
