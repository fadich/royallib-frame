<?php


namespace royal\db\mysql\query;


use royal\base\Interact;
use royal\db\mysql\MySql;
use royal\type\Matrix;

/**
 * Class BaseQueryBuilder
 * @package royal\db\mysql\query
 *
 * @property \mysqli $connection
 */
abstract class BaseQueryBuilder extends Interact
{
    protected $_table;
    protected $_columns;
    protected $_where;
    protected $_order;
    protected $_limit;
    protected $_join = [];

    /** @var MySql $_connection */
    protected $_connection;

    public function __construct(MySql $sql)
    {
        $this->_connection = $sql->connect();
    }

    /**
     * @return \mysqli
     */
    protected function getConnection() : \mysqli
    {
        return $this->_connection->connector;
    }

    public function select($columns)
    {
        $this->_columns = QueryHelper::columns($columns);
        return $this;
    }

    public function from(string $from)
    {
        $this->_table = "{$from}";
        return $this;
    }

    /**
     * Entry join-data in array.
     * Will be created as:
     *      INNER JOIN $table [ON $conditions].
     *
     * @param string       $table         Join table name
     * @param array|string $conditions    Join conditions
     *
     * @return static
     */
    public function innerJoin(string $table, $conditions = [])
    {
        $this->_join[] = ['type' => 'INNER', 'table' => $table, 'conditions' => $conditions];
        return $this;
    }

    /**
     * Entry join-data in array.
     * Creating query's LEFT JOIN.
     * For example:
     *      LEFT JOIN $table [ON $conditions].
     *
     * @param string       $table         Join table name
     * @param array|string $conditions    Join conditions
     *
     * @return static
     */
    public function leftJoin(string $table, $conditions = [])
    {
        $this->_join[] = ['type' => 'LEFT', 'table' => $table, 'conditions' => $conditions];
        return $this;
    }

    public function where($conditions)
    {
        $this->_where = QueryHelper::where($conditions);
        return $this;
    }

    public function orderBy($order)
    {
        $this->_order = QueryHelper::order($order);
        return $this;
    }

    public function limit($limit)
    {
        $this->_limit = QueryHelper::limit($limit);
        return $this;
    }

    /**
     * Counting the number of pages of the table.
     * For example:
     *    using with conditions 'item_id < 110' -- returns the page number on which the element with item_id 110;
     *
     * @param int          $pageSize   the number of the elements on page
     * @param string|array $conditions selecting conditions (WHERE-params)
     *
     * @return int
     */
    public function countPages(int $pageSize, $conditions = []) : int
    {
        $where  = QueryHelper::where($conditions);
        $joins  = QueryHelper::joins($this->_join);
        $number = (int)$this->_connection->createCommand("SELECT FLOOR(COUNT(*) / {$pageSize}) AS `pages` FROM {$this->_name} {$joins} {$where}")->queryScalar();
        return $number ? $number : 1;
    }

    public function fetchAll()
    {
        $fetch = $this->fetch();
        return !$fetch ? $fetch : $fetch->fetch_all(MYSQLI_ASSOC);
    }

    public function fetchRow()
    {
        $fetch = $this->fetch();
        return !$fetch ? $fetch : $fetch->fetch_assoc();
    }

    public function fetchColumn($column)
    {
        $fetch = $this->fetchAll();
        return $fetch ? array_column($fetch, $column) : false;
    }

    public function fetchMap($from, $to)
    {
        $fetch = $this->fetchAll();
        return $fetch ? (new Matrix($fetch))->map($from, $to)->value : false;
    }

    public function fetchSingle()
    {
        $fetch = (!$fetch = $this->fetch()) ? $fetch : $fetch->fetch_row();
        return $fetch ? $fetch[0] : false;
    }

    public function fetchFields()
    {
        $fetch = $this->fetch();
        return !$fetch ? $fetch : $fetch->fetch_fields();
    }

    /**
     * @return \mysqli_result|boolean
     */
    protected function fetch()
    {
        return $this->connection->query($this->build());
    }

    protected function build()
    {
        return "SELECT 
                  {$this->_columns} 
                FROM {$this->_table}" .
                QueryHelper::joins($this->_join) . "
                {$this->_where}
                {$this->_order}
                {$this->_limit}";
    }
}
