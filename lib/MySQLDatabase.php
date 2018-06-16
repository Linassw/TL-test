<?php
require_once 'Interfaces/Database.php';

/**
 * Class MySQLDatabase
 *
 * The purpose of interface in this case is I really like having no dependencies for easier code maintenance
 * (it also makes it easier to write unit tests)
 * So no class is supposed to have any hardcoded object initiation aside from PHP native objs,
 * all other objects are passed via constructor and constructor expects a certain interface so we don't need to change
 * any code in other places if we decide to use i.e. a different Database wrapper, we only need to make sure the new
 * Database implements the same interface.
 *
 * And ofcourse Interfaces, mapper, database and other classes here are really inadequate for real life use,
 * they only meant to serve as an example of a general idea how I'd write the code
 */
class MySQLDatabase implements Database
{
//    private $host = 'localhost';
//    private $user = 'root';
//    private $pass = 'mysql';
//    private $dbName = 'teamliquid';

    private $host = '188.226.152.164';
    private $port = 33306;
    private $user = 'teamliquid';
    private $pass = 'teamliquidtest';
    private $dbName = 'teamliquid';

    private $handler;

    function __construct()
    {
        $this->handler = new PDO('mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbName, $this->user, $this->pass);
    }

    /**
     * @param string $query
     * @return array
     */
    public function getAll($query)
    {
        $entries = [];

        foreach ($this->handler->query($query, PDO::FETCH_NUM) as $row) {
            $entries[] = $row;
        }

        return $entries;
    }

    /**
     * @param string $query sql query
     * @param array|null $params
     * @return bool
     */
    public function preparedQuery($query, array $params = null)
    {
        $st = $this->handler->prepare($query);
        return $st->execute($params);
    }

    /**
     * Sets SQL mode to NO_AUTO_VALUE_ON_ZERO which allows 0 in auto_increment columns.
     *
     * It's not a recommended practice but since you have ID 0 in your CSV files I assume they have to be stored in a DB.
     * Or are they supposed to be invalid entries to test error handling?
     *
     * @return bool|PDOStatement
     */
    public function allowZeroInAutoIncrement()
    {
        return $this->handler->query("SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO'");
    }
}
