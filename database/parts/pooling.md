# Pooling


## MySQL/Postgres's job is:
➡️ Accept connections
➡️ Handle queries


## No of connection limitation
- Both MySQL and PostgreSQL have a parameter `max_connections`
- When connections reach max_connections, MySQL/Postgres does NOT queue incoming connections
- It immediately returns an error to the caller: ERROR 1040 (08004): Too many connections


## Problem:

in php applications, user make a request so a connection will open a database connection. for high traffic website, this is not going to work.. too many connections is going to be thrown alot. there is a repeated work for closing/reopen database connection everytime unncessary

## Solution is Pooling

- Pooling can be on application level, but it has to be on a long living process
    - since every php request is a separate process (php-fpm) .. there is no direct way to do pooling
    - laravel octane has a partial pooling. its model is about having +8 workers processes, each worker can handle multiple of php requests, so every worker can have one database connection.
    - other languages which can have one long living process, can do pooling natively such as nodejs, java jdbc, .net

- Pooling can be with middleware PHP -> DB Pooling -> DB
    - for Mysql .. ProxySQL
    - for Postgres .. PgBouncer

## Things to watch for

- one connection shouldnt be used by multiple clients in same time as it will lead to unexpected behavior. 