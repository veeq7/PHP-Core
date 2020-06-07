<?php
    const DEBUG = 1;

    class Connection {
        protected $mysqliConnection;

        function __construct($ip, $login, $password) {
            $this->mysqliConnection = new mysqli($ip, $login, $password);

            if ($this->mysqliConnection->connect_errno) {
                echo("Connection Error: " . $this->mysqliConnection->connect_errno);
                exit();
            }
        }

        function getMysqliConnection() {
            return $this->mysqliConnection;
        }

        function query($query) {
            if (DEBUG) {
                echo("Query used: " . $query . "<br>");
            }
            return $this->mysqliConnection->query($query);
        }
    }

    class Database {
        protected $connection;
        protected $name;

        function __construct($connection, $name) {
            $this->connection = $connection;
            $this->name = $name;
        }
        
        function createDatabase() {
            $this->db_query("CREATE DATABASE", "");
            $this->useDatabase();
        }

        function deleteDatabase() {
            $this->db_query("DROP DATABASE", "");
        }

        function useDatabase() {
            $this->connection->getMysqliConnection()->select_db($this->name);
        }

        function selectMultiple($what, $tables, $args) {
            return $this->connection->getMysqliConnection()->query("SELECT " . $what . " FROM " . $tables . " " . $args);
        }

        function db_query($command, $args) {
            $query = $command . " " . $this->name . " " . $args . ";";
            return $this->connection->query($query);
        }

        function getConnection() {
            return $this->connection;
        }

        function getName() {
            return $this->name;
        }
    }

    class Table {
        protected $database;
        protected $name;

        function __construct($database, $name) {
            $this->columns = [];
            $this->database = $database;
            $this->name = $name;
        }

        function tb_query($command, $args) {
            $query = $command . " " . $this->name . " " . $args . ";";
            $this->database->useDatabase();
            return $this->database->getConnection()->query($query);
        }
        
        function createTable($columns) {
            $args = "(id int unsigned not null auto_increment, ";
            foreach ($columns as $name => $type) {
                $args .= "`" . $name . "` " . $type . ", ";
            }
            $args .= "primary key (id))";

            $this->tb_query("CREATE TABLE", $args);
        }
        
        function deleteTable() {
            $this->tb_query("DROP TABLE", "");
        }

        function insert($row) {
            $vars = "(";
            $values = "(";
            foreach ($row as $var => $value) {
                $vars .= "`" . $var . "`, ";
                $values .= "\"" . $value . "\", ";
            }
            $vars = substr($vars, 0, -2) . ")";
            $values = substr($values, 0, -2) . ")";

            $args = $vars . " VALUES " . $values;

            $this->tb_query("INSERT INTO", $args);
        }

        function insertAll($rows) {
            foreach($rows as $row) {
                $this->insert($row);
            }
        }

        function select($what, $args) {
            return $this->tb_query("SELECT " . $what . " FROM", $args);
        }
    }


?>