<?php


namespace royal\base;

/**
 * Class Object
 * @package royal\base
 *
 * Tha base class for working with an objects.
 */
abstract class Object
{
    /**
     * Getting property by name (using method get{$property}()).
     *
     * If there is getter-method defined -- return property by name.
     * If there is undefined getter-method and defined setter-method -- throw exception with message
     *      "property is white-only" (but it is case is rare).
     * If there is no setter or getter method -- message with "Getting unknown property".
     *
     * It's desirable to declare a variable into class docs.
     *
     * @param $name
     *
     * @return mixed
     * @throws \Exception
     */
    public function __get($name)
    {
        $method = "get" . $name;
        if (method_exists($this, $method)) {
            return $this->{$method}();
        } elseif (method_exists($this, "set" . $name)) {
            throw new \Exception("Property " . __NAMESPACE__ . "::{$name} can not be reading");
        } else {
            throw new \Exception("Getting unknown property: " . __NAMESPACE__ . "::{$name}");
        }
    }

    /**
     * Setting property's value (using method get{$property}($value)).
     *
     * If there is setter-method defined -- define property's value by property name.
     * If there is undefined setter-method and defined getter-method -- throw exception with message
     *      "property is read-only".
     * If there is no setter or getter method -- message with "Getting unknown property".
     *
     * It's desirable to declare a variable into class docs.
     *
     * @param $name
     * @param $value
     *
     * @return mixed
     * @throws \Exception
     */
    public function __set($name, $value)
    {
        $method = "set" . $name;
        if (method_exists($this, $method)) {
            return $this->{$method}($value);
        } elseif (method_exists($this, "get" . $name)) {
            throw new \Exception("Property " . __NAMESPACE__ . "::{$name} is read-only property");
        } else {
            throw new \Exception("Getting unknown property: " . __NAMESPACE__ . "::{$name}");
        }
    }
}