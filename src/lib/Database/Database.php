<?php


namespace pwgram\lib\Database;

use Exception;

//DB name

//Case MAMP users
define('USER', "root");
define('PASSWORD', "root");

//Case Vagrant users
//define('USER', "homestead");
//define('PASSWORD', "secret");

/**
 * Database
 */
class Database {

    /**
     * @var PDO
     */
    public $connection;

    /**
     * @var null
     */
    private static $instance = null;


    /**
     * @param $dbname
     * @return null|Database
     */
    public static function getInstance($dbname) {

        if (!self::$instance) {

            self::$instance = new self($dbname, USER, PASSWORD);
        }
        return self::$instance;
    }

    /**
     * Database constructor.
     * @param $dbname
     */
    private function __construct($dbname) {

        $this->connection = new \PDO(
            "mysql:host=localhost;dbname=$dbname;",
            USER,
            PASSWORD
        );
        $this->connection->setAttribute(
            \PDO::ATTR_DEFAULT_FETCH_MODE,
            \PDO::FETCH_ASSOC
        );
    }

    /**
     * Execute the given query
     * @param $query
     * @return PDOStatement
     */
    public function query($query) {

        return $this->connection->query($query);
    }

    /**
     * Prepare and execute the given query with the given parameters
     * @param $query
     * @param array $params
     * @return
     */
    public function preparedQuery($query, array $params) {

        $statement = $this->connection->prepare($query);
        $statement->execute($params);

        return $statement;
    }

    // Magic method clone is empty to prevent duplication of connection
    private function __clone() {

    }

    /**
     * This method starts a transaction, so each query executed after this method
     * will not be executed until the method @see commitTransaction is called.
     */
    public function initTransaction() {

        $this->connection->beginTransaction();
    }


    /**
     * Ends a transaction to the database. If any problem is found during the commit,
     * a rollback is executed.
     */
    public function commitTransaction() {

        try {
            $this->connection->commit();

        } catch (Exception $e) {

            $this->connection->rollBack();
            echo "Transaction error: ". $e->getMessage();
        }
    }



}