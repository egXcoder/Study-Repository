<?php

//Builder .. build complex object
//Directory .. use the builder to build pre-defined templates of the complex object

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