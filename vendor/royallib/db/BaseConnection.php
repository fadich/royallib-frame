<?php

namespace royal\db;

// TODO: describe \royal\db\BaseConnection

use royal\base\Interact;

/**
 * Class BaseConnection
 * @package royal\db
 *
 * @property string $host
 * @property string $username
 * @property string $database
 * @property string $password
 *
 * @author Fadi Ahmad
 */
abstract class BaseConnection extends Interact
{
    protected $_host;
    protected $_dbName;
    protected $_username;
    protected $_password;

    abstract function __construct($host = null, $username = null, $dbName = null, $password = null);

    abstract public function connect();

    abstract public function close();

    protected function getHost()
    {
        return $this->_host;
    }

    protected function getUsername()
    {
        return $this->_username;
    }

    protected function getDatabase()
    {
        return $this->_dbName;
    }

    protected function getPassword()
    {
        return $this->_password;
    }
}
