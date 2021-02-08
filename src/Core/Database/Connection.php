<?php

namespace App\Core\Database;

use PDO;

class Connection
{
    /**
     * The connection instance, because we only want one connection instance
     *
     * @var App\Core\Database\Connection
     */
    private static $instance = null;
    
    /**
     * The PHP PDO instance
     *
     * @var PDO
     */
    private static $pdo = null;

    /**
     * Prevent creating object with `new` keyword
     */
    private function __construct() {}

    /**
     * Prevent the object from being cloned
     *
     * @return void
     */
    private function __clone() {}

    /**
     * Connect to the database and return only single instance object
     *
     * @param string $driver
     * @param string $host
     * @param string $name
     * @param string $username
     * @param string $password
     * @return App\Core\Database\Connection
     */
    public static function make($driver, $host, $name, $username, $password)
    {
        if (!self::$pdo) {
	        try {
               $pdo = new PDO("{$driver}:host={$host};dbname={$name}", $username, $password);
               
               $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

               self::$pdo = $pdo;
               self::$instance = new self;

               return self::$instance;
			} catch (PDOException $e) { 
			   die('PDO CONNECTION ERROR: ' . $e->getMessage() . '<br/>');
			}
        }
    }

    /**
     * Get the PDO instance
     *
     * @return PDO
     */
    public function getPDO()
    {
        return self::$pdo;
    }
}

?>