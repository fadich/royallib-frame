<?php

namespace royal\db\mysql\query;


use royal\base\Object;

/**
 * Class BaseQueryHelper
 * @package royal\db\mysql\query
 *
 * Providing tools for building SQL-queries.
 *
 * Base abstract class, that can provide some static methods.
 * Basically, i think, there will be much of static methods, that build some parts of query (string).
 *
 * @author Fadi Ahmad
 */
abstract class BaseQueryHelper extends Object
{
    /**
     * Usually, there is no camelCase into SQL databases.
     * This method using for making some string (for example php-class's attribute's name)
     *    from camelCaseString to string, that uses delimiter between words.
     *
     * For example, "usingUnderscoreString" (using $replacement = '_$0') will be converted to "using_underscore_string".
     *
     * @param string $str
     *
     * @return string
     */
    public static function fromCamelCase(string $str) : string 
    {
        return (string)strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $str));
    }

    /**
     * Creating WHERE-params string from array or string.
     * For example:
     *      a) $conditions array can be set as ['param_1' => 'value_1', 'param_2 !' => 'value_2']. In this case, there will be
     *         conditions of selecting query such as "WHERE param_1=value_1 AND param_2 !=value_2";
     *      b) condition string can be set as "param_1 = 'val_1' OR param_2 > 30". In this case, there will be
     *         created conditions of selecting query such as "WHERE param_1 = 'val_1' OR param_2 > 30".
     *
     * In other words, conditions, that expressed as an array, permit to select data with exact match;
     * but string expressed conditions can be used to get data by any SQL conditions.
     *
     * @param array|string $conditions
     *
     * @return string
     */
    public static function where($conditions) : string
    {
        return is_array($conditions) ? self::arrayToParams($conditions, 'WHERE', 'AND', '=', true) : ($conditions ? "WHERE " . $conditions : " ");
    }

    /**
     * Creating ON-condition string from array.
     * For example:
     *      $conditions array can be set as ['param_1' => 'value_1', 'param_2 !' => 'value_2']. In this case, there will be
     *      conditions of joining such as "ON param_1=value_1 AND param_2 !=value_2".
     *
     * @param array|string $conditions
     *
     * @return string
     */
    public static function on($conditions) : string
    {
        return is_array($conditions) ? self::arrayToParams($conditions, 'ON', 'AND', '=') : ($conditions ? "ON {$conditions}" : " ");
    }

    /**
     * Creating ORDER BY string from an array or from a string.
     * For example:
     *      a) $order array can be set as ['column_1' => 'ASC', 'column_2' => 'DESC']; in this case, there will be
     *            ordering in selecting query such as "ORDER BY column_1 ASC, column_2 DESC";
     *      b) $order string is "column_1 ASC, column_2 DESC" in this case, there will be
     *            ordering in selecting query such as "ORDER BY column_1 ASC, column_2 DESC";
     *
     * @param array|string $orders
     *
     * @return string
     */
    public static function order($orders) : string
    {
        if (is_string($orders)) {
            return "ORDER BY {$orders}";
        }
        return self::arrayToParams($orders, 'ORDER BY', ', ', ' ');
    }

    /**
     * Creating LIMIT-param string from an array or from a string.
     * For example:
     *      a) $limit array can be set as [0,30]; in this case, there will be limit param in selecting query
     *            such as "LIMIT 0,30";
     *      b) if $limit array set as [30], in this case, there will be interpreted as "LIMIT 30";
     *      c) whenever $limit is a string such as "30, 50", there will be created string species "LIMIT 30, 50".
     *
     *
     * Limit array max size is 2.
     *
     * @param array|string $limit
     *
     * @return string
     */
    public static function limit($limit) : string
    {
        if (is_string($limit)) {
            return "LIMIT {$limit}";
        }
        if (sizeof($limit) > 2) {
            throw new \InvalidArgumentException("Limit array may contain no more, than 2 elements");
        }
        return !empty($limit) ? "LIMIT " . implode(",", $limit) : " ";
    }

    /**
     * A query part of selecting columns.
     * In case of $columns is a string - it is simply passed to the query, for example:
     *
     * ```php
     * echo BaseQueryHelper::columns("`column_1`, `columns_2`");
     * // The result string will be: "`column_1`, `columns_2`"
     * ```
     *
     * Whenever selecting columns array is empty, then selecting all (return "*"),
     *      else, array elements (that are strings names of necessary columns) separated by a comma.
     * In case of the is string a key of the array element, this means that value will an selecting column alias.
     * For example,
     *
     * ```php
     * echo BaseQueryHelper::columns([
     *      'column_1',
     *      'column_2',
     *      'something' => 'column_3',
     *      'foo'       => 'column_4',
     *      'column_5',
     * ]);
     * // The result string will be:
     * // "column_1, column_2, something AS column_3, foo AS column_4, column_5 "
     *
     * ```
     *
     * @param array|string $columns
     *
     * @return string
     */
    public static function columns($columns) : string
    {
        if (is_string($columns)) {
            return $columns;
        }
        if (empty($array)) {
            return " * ";
        }
        $columns = " "; $len = sizeof($array);
        foreach ($array as $key => $item) {
            if (is_string($key)) {
                $columns .= " {$key} AS ";
            }
            $columns .= " {$item}" . (--$len ? ", " : " ");
        }
        return $columns;
    }

    /**
     * Creating joins (sting, for query) with tables from $this->_join property.
     *
     * For example:
     *
     * ```php
     *  // property is array such as
     *  $this->_join =  [
     *                      'type'       => 'inner',
     *                      'table'      => 'table_2',
     *                      'conditions' => [
     *                          'table_1.col_1' => 'table_2.col_1',
     *                      ],
     *                  ]
     *
     *  // will be interpreted to string like:
     *
     *  echo $this->joins();
     *
     *  // output: "INNER JOIN table_2 ON table_1.col_1 = table_2.col_1";
     * ```
     *
     * @param array $joins array kile ['type' => $joinType, 'table' => $joinTableName, 'conditions' => [$cul_1 => $cil_2]]
     *
     * @return string
     */
    public static function joins(array $joins) : string
    {
        $res = " ";
        foreach ($joins as $join) {
            $res .= strtoupper($join['type']) . " JOIN {$join['table']} " . self::on($join['conditions']) . "\n";
        }
        return $res;
    }

    /**
     * Creating query-string (part of query) from some array params.
     * @see BaseQueryHelper::where()
     * @see BaseQueryHelper::on()
     * @see BaseQueryHelper::order()
     *
     * @param array  $array         the array of parameters
     *                                  such us ['param_1' => 'value_1', 'param_2 !' => 'value_2']
     * @param string $param         the param name (for example, "WHERE")
     * @param string $delimiter     the param delimiter ("AND", "," etc.)
     * @param string $separator     the separator between key and value ("=", " " etc.)
     * @param bool   $toStringValue flag, that means, need to add quotes (for value)
     *
     * @return string
     */
    protected static function arrayToParams(array $array, string $param, string $delimiter, string $separator, bool $toStringValue = false) : string
    {
        $params = " ";
        $quote = $toStringValue ? "\"" : '';
        if ($len = sizeof($array)) {
            $params = " {$param} ";
            foreach ($array as $column => $value) {
                $value = is_string($value) ? "{$quote}{$value}{$quote}" : $value;
                $params .= " {$column}{$separator}{$value} " . (--$len ? " {$delimiter} " : "");
            }
        }
        return $params;
    }
}
