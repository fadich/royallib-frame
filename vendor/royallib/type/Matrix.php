<?php

namespace royal\type;


/**
 * Class Matrix
 * @package royal\type
 *
 * @author Fadi Ahmad
 */
class Matrix extends Mixed
{
    protected $_temp;

    /**
     * Using implode() for an array and for every it's element, if there is array etc...
     *
     * For example (it will be easier to understand),
     *
     * ```php
     *   $array  = [15, 6, 9, 100 => 78, "a" => 78, 666 => "qwerty"];
     *   $result = (new Mixed($array))->multiImplode(" | ")->value;
     *   var_export($result);
     *   // Result will be a string such as: "15 | 6 | 9 | 78 | 78 | qwerty"
     *   // In this case method works like the standard implode();
     *
     *   // But the method's beauty is as follows (in case of the array composed of arrays):
     *   $array  = [
     *                 15.15,
     *                 [
     *                     15 => [
     *                         784,
     *                         "qq" => 6,
     *                         9 => "qwe",
     *                         7 => 7.8,
     *                     ],
     *                 ],
     *                 123,
     *             ];
     *   $result = (new Mixed($array))->multiImplode(" | ")->value;
     *   var_export($result);
     *   // Result will be a string such as: "15.15 | 784 | 6 | qwe | 7.8 | 123"
     * ```
     *
     * @param string $glue      A symbol or a string, that will be placed between the imploded elements;
     *
     * @return string
     */
    public function multiImplode($glue = "")
    {
        foreach($this->value as $val) {
            $_array[] = is_array($val) ? (new static($val))->multiImplode($glue) : $val;
        }
        $this->_temp = implode($glue, $_array ?? []);
        return $this->_temp;
    }

    /**
     * TODO: describe \royal\type\Matrix::map()
     *
     * @param $from
     * @param $to
     *
     * @return $this
     * @throws \Exception
     */
    public function map($from, $to)
    {
        $fromCol = array_column($this->value, $from);
        $toCol   = array_column($this->value, $to);
        $size    = sizeof($this->value);
        $sizeFr  = sizeof($fromCol);
        $sizeTo  = sizeof($toCol);
        $error   = "";
        if ($size !== $sizeFr) {
            $error .= " - cannot getting column {$from} \n";
        }
        if ($size !== $sizeTo) {
            $error .= " - cannot getting column {$to} \n";
        }
        if ($error) {
            throw new \Exception("Error:\n" . $error . "\nCheck an array columns names");
        }
        for ($i = 0; $i < $size; $i++) {
            $res[$fromCol[$i]] = $toCol[$i];
        }
        $this->_value = $res ?? [];
        return $this;
    }
}
