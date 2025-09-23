<?php


//Singleton Pattern ensures that only one instance of a class exists in the entire application, and it provides a global access point to that instance.
// Single access point for resources like:
// Database connection (most common use case).
// Logger (one global logger instance).
// Cache handler (e.g., Redis, Memcached client).
// Saves memory & resources by avoiding multiple instances of the same heavy object.
// Useful when having multiple instances would cause conflicts (e.g., multiple DB connections).

class Database {
    private static $instance = null; // holds the single instance
    private \PDO $connection;

    // Make constructor private → prevents creating new objects via "new Database()"
    private function __construct() {
        $this->connection = new PDO("mysql:host=localhost;dbname=test", "root", "");
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Get the single instance (lazy initialization)
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // Provide global access to the DB connection
    public function getConnection(): PDO {
        return $this->connection;
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    private function __wakeup() {}
}

// ---- Usage ----

// First call creates the instance
$db1 = Database::getInstance();
$conn1 = $db1->getConnection();

// Second call reuses the same instance
$db2 = Database::getInstance();
$conn2 = $db2->getConnection();

// Check if both are same
var_dump($db1 === $db2); // true
var_dump($conn1 === $conn2); // true



