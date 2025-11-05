# Sharding

Split your data across multiple databases (shards)

- You create multiple databases in different servers all share same schema
- You define how to connect to the shards within config
- dynamically within your application choose which shard you should go to using `consistent hashing`

## Why Shard?
- Table grows huge (100M+ rows) → queries slow
- you have tried indexes and partitions but still its too much for a single server
- you need to start considering having multiple servers to scale
- you can split the data into //TODO:

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



Sharding is complex. Only do it when single-server partitioning + indexing cannot handle the workload.

For 100M rows, most cases don’t need sharding immediately; proper partitioning + indexes usually suffice.