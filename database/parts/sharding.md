# Sharding

Split your data across multiple databases (shards)

- You create multiple databases in different servers all share same schema
- application dynamically choose which shard you should go to using `consistent hashing`
- You define how to connect to the shards within config

## Why Shard?
- Table grows huge (100M+ rows) → queries slow
- you have tried indexes and partitions but still its too much for a single server
- you need to start considering having multiple servers to scale
- one of the ways to horizontal scale database is you can split the data into multiple servers
- each database server will contain group of data around an entity like a user or tenant
- so that you should avoid having cross-shard joins unless its dashboard or something

Tip: Sharding is last resort scaling, as its adding complexity in everything. try index first then partitions then caches then replicas

## How to shard
- Pick a shard key that is stable and evenly distributed for example authenticated user id
- Keep related data on the same shard such as user carts, orders, payments, etc..
- Avoid frequent cross-shard joins.



```php
// in config/database.php

'connections' => [
    'shard1' => [
        'driver' => 'mysql',
        'host' => env('DB_SHARD1_HOST'),
        'database' => env('DB_SHARD1_DATABASE'),
        'username' => env('DB_SHARD1_USERNAME'),
        'password' => env('DB_SHARD1_PASSWORD'),
    ],
    'shard2' => [
        'driver' => 'mysql',
        'host' => env('DB_SHARD2_HOST'),
        ...
    ],
    'shard3' => [
        'driver' => 'mysql',
        ...
    ],
],


```

```php
// app/services/ShardResolver.php

class ShardResolver
{
    public static function getConnectionForUser(int $userId): string
    {
        $connections = ['shard1', 'shard2', 'shard3'];

        return $connections[$userId % count($connections)];
    }
}

```

```php

class Cart extends Model
{
    protected $table = 'carts';

    public function getConnectionName()
    {
        return ShardResolver::getConnectionForUser(auth()->id());
    }
}

```

```php

Cart::where('id', 10)->first(); //will go the correct shard, relying on authenticated user

```


### Cons:
- More complexity on Application as its aware of shards 
- No way you can do transactions across shards
- Schema Change is hard as it have to migrate to all databases
- when you need cross-join shards data like dashboard, you have to query all the shards one by one


## Q: if i am in e-commerce .. i can group users data like carts + orders + payments in different shards and with laravel where i can dynamically choose connection in eloquent model. i see its straightforward really. so why sharding is painful?

The problem with sharding is not “choosing a connection.” The problem is everything after that.

- maintenance
    - if you have 5 shard, you must always make sure they are alive and none of them go down as each one is critical for particular group of users

- Rebalancing shards is hell
    - you have assumed you will have 5 shards, later on you grow and 5 is not enough and you need 2 more
    - to add another 2 shards
        - you have to go through the shards and move some users data to its new location after adding 2 more
        - stop writes for that user (freeze account temporarily)
        - copy data
        - verify data
        - switch routing
        - unfreeze
        - no global downtime
    - After rebalancing:
        - debugging requires knowing which shard user is on
        - logs from multiple DBs must be merged
        - backups now exist on different schedules

- Cross-user operations:
    - Many users add the same product to carts.
    - Inventory stock is shared as its a global resource. and we have to -ve the inventory stock
    - if we -ve the inventory then create the order in shard, what if shard went down or crashed.
    - if we create the order first in shard then -ve the inventory, what if another user bought it first
    - we have to reserve first the inventory quantity, try to create the order in shard then mark inventory level as done
    - which is more complexity over simple operation like ordering product

- Migrations become distributed
    - run migrations on 20 DBs .. one shard was offline → rollback?

