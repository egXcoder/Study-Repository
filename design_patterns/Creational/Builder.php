<?php

//Builder .. build complex object
//Product .. is the object being built
//Director .. use the builder to build pre-defined templates of the complex object

interface QueryBuilder {
    public function select($table, $columns);
    public function where($column, $value);
    public function limit($number);
    public function getQuery(): SQLQuery;
}


class MySQLQueryBuilder implements QueryBuilder {
    private $query;
    private $conditions = [];
    private $limit;

    public function select($table, $columns = ["*"]) {
        $cols = implode(", ", $columns);
        $this->query = "SELECT $cols FROM $table";
        return $this;
    }

    public function where($column, $value) {
        $this->conditions[] = "$column = '" . addslashes($value) . "'";
        return $this;
    }

    public function limit($number) {
        $this->limit = (int)$number;
        return $this;
    }

    public function getQuery(): SQLQuery {
        $sql = $this->query;
        if (!empty($this->conditions)) {
            $sql .= " WHERE " . implode(" AND ", $this->conditions);
        }
        if ($this->limit) {
            $sql .= " LIMIT " . $this->limit;
        }
        return new SQLQuery($sql);
    }
}


//Director use builder to build a predefined templates of the complex object
//Director ("Query Directors" can be called Repository)
class QueryDirector {
    private $builder;

    public function __construct(QueryBuilder $builder) {
        $this->builder = $builder;
    }

    public function buildGetActiveQuery(): SQLQuery {
        return $this->builder
            ->select("users", ["id", "name", "email"])
            ->where("status", "active")
            ->limit(10)
            ->getQuery();
    }

    public function buildGetActiveProducts(): SQLQuery {
        return $this->builder
            ->select("products", ["id", "name", "description"])
            ->where("status", "active")
            ->limit(10)
            ->getQuery();
    }
}

//Final Product
class SQLQuery {
    public $query;

    public function __construct($query) {
        $this->query = $query;
    }

    public function getQuery(): string {
        return $this->query;
    }
}



//Q: can builder can be the complex object?
// in a typical builder, goal is to differentiate instantiation from the logic .. so they should be separated


//Q: in builder there are required steps, how i can force client to do the required steps before he can build?

// i prefer second approach

// [1] you can enforce it by throwing exception on build method
// public User build() {
//      if (name == null){
//          throw new IllegalStateException("Name is required!");
//      }
//     return new User(name, age);
// }


// [2] you can also put it in constructor or factory pattern
// $user = User::make("Ahmed", 30)  // required
//             ->withRole("Admin")    // optional
//             ->withEmail("ahmed@example.com") // optional
//             ->build();


// class User {
//     private string $name;
//     private int $age;
//     private ?string $role = null;
//     private ?string $email = null;

//     // private constructor so clients can't use "new User"
//     private function __construct(string $name, int $age) {
//         $this->name = $name;
//         $this->age  = $age;
//     }

//     // ✅ Named factory method for required params
//     public static function make(string $name, int $age): User {
//         return new User($name, $age);
//     }

//     // ✅ Builder methods for optional params
//     public function withRole(string $role): self {
//         $this->role = $role;
//         return $this;
//     }

//     public function withEmail(string $email): self {
//         $this->email = $email;
//         return $this;
//     }

//     // ✅ Finalize
//     public function build(): self {
//         return $this;
//     }

//     // Just for demo
//     public function describe() {
//         return "{$this->name}, {$this->age} years old, role={$this->role}, email={$this->email}";
//     }
// }