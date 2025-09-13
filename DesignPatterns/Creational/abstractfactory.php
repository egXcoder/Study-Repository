<?php

//factory here will be a class with many methods creating related objects 
// we can use FactoryMethod DP to write a class to return one of these factories 


//not necarrily class name will be factory its more of role rather than name, such as in laravel MysqlFactory is called MysqlDrive

interface DBFactory {
    public function createConnection(): Connection;
    public function createQueryBuilder(): QueryBuilder;
}


class MySQLFactory implements DBFactory {
    public function createConnection(): Connection {
        return new MySQLConnection();
    }

    public function createQueryBuilder(): QueryBuilder {
        return new MySQLQueryBuilder();
    }
}

class PostgresFactory implements DBFactory {
    public function createConnection(): Connection {
        return new PostgresConnection();
    }

    public function createQueryBuilder(): QueryBuilder {
        return new PostgresQueryBuilder();
    }
}

class DBFactoryProvider {
    public static function getFactory(string $db): DBFactory {
        return match ($db) {
            "mysql" => new MySQLFactory(),
            "postgres" => new PostgresFactory(),
            default => throw new Exception("Unknown DB type"),
        };
    }
}




// client
$factory = DBFactoryProvider::getFactory("mysql");
$connection = $factory->createConnection();
$connection->connect();

$queryBuilder = $factory->createQueryBuilder();
echo $queryBuilder->buildSelect("users") . "\n";



//in laravel
//DB is the database manager
DB::connection('mysql')->select(...);


namespace Illuminate\Database;
class DatabaseManager implements ConnectionResolverInterface{
 /**
     * Get a database connection instance.
     *
     * @param  string|null  $name
     * @return \Illuminate\Database\Connection
     */
    public function connection($name = null)
    {
        // ...
    }
}

namespace Illuminate\Database;
class MySqlConnection extends Connection
{
    /**
     * Get the default query grammar instance.
     *
     * @return \Illuminate\Database\Query\Grammars\MySqlGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        // ...
    }

    /*
     * Get a schema builder instance for the connection.
     *
     * @return \Illuminate\Database\Schema\MySqlBuilder
     */
    public function getSchemaBuilder()
    {
        // ...
    }

    /**
     * Get the default post processor instance.
     *
     * @return \Illuminate\Database\Query\Processors\MySqlProcessor
     */
    protected function getDefaultPostProcessor()
    {
        // ...
    }
}
