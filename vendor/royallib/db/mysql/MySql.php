<?php

namespace royal\db\mysql;


use royal\db\BaseConnection;
use royal\db\mysql\exception\MySqlConnectionError;

/**
 * Class MySql
 * @package royal\db\mysql
 *
 * @property \mysqli $connector
 */
class MySql extends BaseConnection
{
    /** @var \mysqli $_sql */
    protected $_sql;

    public function __construct($host = null, $username = null, $dbName = null, $password = null)
    {
        $this->_host     = $host;
        $this->_username = $username;
        $this->_dbName   = $dbName;
        $this->_password = $password;
        try {
            $this->connect();
        } catch (\mysqli_sql_exception $e) {
            $this->_errors[] = $e;
            throw $e;
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    public function connect()
    {
        if ($this->hasErrors()) {
            throw new MySqlConnectionError('Error connection to ' . $this->_username . '@' . $this->_host);
        }
        mysqli_report(MYSQLI_REPORT_STRICT);
        $this->_sql = new \mysqli($this->_host, $this->_username, $this->_password, $this->_dbName);
        if ($this->_sql->connect_error) {
            $this->_errors[]   = 'Error connection to ' . $this->_username . '@' . $this->_host;
            throw new MySqlConnectionError('Error connection to ' . $this->_username . '@' . $this->_host);
        }
        return $this;
    }

    public function close()
    {
        $this->_sql->close();
    }

    /**
     * @return \mysqli
     * @see MySql::$connector
     */
    protected function getConnector()
    {
        return $this->_sql;
    }
}
