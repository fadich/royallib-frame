<?php

namespace royal\type;

/**
 * Class Str
 * @package royal\type
 *
 * Helps working with a string variables.
 *
 * @property string $value
 *
 * @author Fadi Ahmad
 */
class Str extends Scalar
{    
    public function __construct(string $string)
    {
        parent::__construct($string);
    }

    /**
     * Making some the value from camelCaseString to string, that uses delimiter between words.
     *
     * For example, "usingUnderscoreString" (using $replacement = '_$0') will be converted to "using_underscore_string".
     *
     * @param string $replacement
     *
     * @return Str
     */
    public function fromCamelCase(string $replacement = '_') : Str
    {
        $this->value = strtolower(preg_replace('/(?<!^)[A-Z]/', $replacement . '$0', $this->value));
        return $this;
    }

    /**
     * Making some the value to camelCaseString from string, that uses delimiter between words.
     *
     * For example, using $replacement = '_' will be converted from "using_underscore_string" to "usingUnderscoreString".
     *
     * @param string $replacement
     *
     * @return Str
     */
    public function toCamelCase(string $replacement = '_') : Str
    {
        $this->value = strtolower(substr($this->value = str_replace($replacement, '', ucwords(strtolower($this->value), $replacement)), 0, 1)) . substr($this->value, 1);
        return $this;
    }

    /**
     * Making some the value to ClassName from string, that uses delimiter between words.
     *
     * For example, using $replacement = '_' will be converted from "using_underscore_string" to "UsingUnderscoreString".
     *
     * @param string $replacement
     *
     * @return Str
     */
    public function toClassName(string $replacement = '_') : Str
    {
        $this->value = str_replace($replacement, "", ucwords($this->value, $replacement));
        return $this;
    }

    /**
     * Set value (convented to string).
     * 
     * @param $value
     *
     * @throws \TypeError
     */
    public function setValue($value)
    {
        parent::setValue((string)$value);
    }

    /**
     * Make upper the first symbol of a string.
     *
     * @param string $enc
     *
     * @return Str
     */
    public function ucfirst($enc = 'utf-8')
    {
        $this->_value = mb_strtoupper(mb_substr($this->value, 0, 1, $enc), $enc).mb_substr($this->value, 1, mb_strlen($this->value, $enc), $enc);
        return $this;
    }

    /**
     * Selection of the word end for a numeral.
     *
     * For example,
     *  the word such us "день", has a next variants: "день", "дня", "дней";
     *  for any numeral the word can have different end.
     *
     * ```php
     *  $days    = 1;
     *  $strDay  = Str::wordEnd($days, ['день', 'дня', 'дней']);
     *  $strLeft = Str::wordEnd($days, ['Остался', 'Осталось']);
     *  echo "$strLeft $days $strDay";
     *  // Result: Остался 1 день
     *
     *  // OR //
     *
     *  $days    = 15;
     *  $strDay  = Str::wordEnd($days, ['день', 'дня', 'дней']);
     *  $strLeft = Str::wordEnd($days, ['Остался', 'Осталось']);
     *  echo "$strLeft $days $strDay";
     *  // Result: Осталось 15 дней
     * ```
     *
     * @param int   $numeral      The numeral (quantity)
     * @param array $alternative  The possible alternatives (2 or 3). The first element -- is for end of 1 (except 11, 111 etc)
     *                              the last alternative is default word. If there is three alternatives, then second
     *                              alternative is for 2-4 numeral (except 12-14, 112-114 etc).
     *
     * @return mixed
     */
    public static function wordEnd(int $numeral, array $alternative)
    {
        if (sizeof($alternative) < 2) {
            throw new \InvalidArgumentException("There should be 2 or 3 alternatives defined");
        }
        return ($numeral % 10 < 5 && $numeral % 10 > 1) && !($numeral % 100 > 11 && $numeral % 100 < 15) ? $alternative[1] :
            ($numeral % 10 == 1 && $numeral % 100 != 11 ? $alternative[0] : ($alternative[2] ?? $alternative[1]));
    }
}
