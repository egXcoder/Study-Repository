## Redis Data Structures

- String (99% used):  "name" => "Ahmed"
- List: its like java deque (used for queues) ... "tasks" => ["task1", "task2"]
- Hash Map: its like json but no nesting ... "user:1" => {name:"Ahmed",age:30}
- Set: unique ... "tags" => {"php","redis","cache"}
- Sorted Set with score for ordering ... "leaderboard" => {user1:100,user2:90}


## Commands
- Basics
    - `SET first_name ahmed`
    - `GET first_name` .. if doesnt exist will return nil
    - `EXISTS first_name`
    - `DEL first_name`

- Expiration
    - `SETEX first_name ahmed 10` .. set value with time-to-live as well 
    - `EXPIRE first_name 60` .. set time-to-live for key 60
    - `TTL first_name` .. check time-to-live for key .. if forever then it will return -1
    - `SET first_name "data" EX 300` ..  expires in 5 mins`

- Increment and decrement
    - `SET counter 1`
    - `INCR counter` 
    - `DECR counter` 
    - `INCRBY counter 5` 
    - `DECRBY counter 5` 

- List (useful for queue or stack you have)
    - `LPUSH tasks task1` .. add to left
    - `RPUSH tasks task1` .. add to right
    - `LPOP tasks task1` .. remove from left
    - `RPOP tasks task1` .. remove from right
    - `LRANGE tasks 0 -1`..  get all items
    - `GET tasks` .. will give error because GET only works with strings instead you can use lrange

- Sets (unique collections)
    - `SADD tags "php"` .. return 1
    - `SADD tags "redis"` .. return 1
    - `SADD tags "php"` ... return 0 as duplicate ignored
    - `SMEMBERS tags` ... get all
    - `SREM tags "php"` .. remove from set

- Sorted Sets (unique + ordered)
    - `ZADD leaderboard 100 "Alice"` .. score = 100
    - `ZADD leaderboard 250 "Bob"` ..
    - `ZADD leaderboard 175 "Charlie"`
    - `ZRANGE leaderboard 0 -1 WITHSCORES` ... get all ordered by score asc
    - `ZREVRANGE leaderboard 0 -1 WITHSCORES` ... get all ordered by score desc
    - `ZREM tags "php"` .. remove from set

- Hashes (like hashmap with key value pair)
    - `HSET first_user name "Ahmed" age 30` ... string first_user can be "users:1" .. its like a convention 
    - `HGET first_user name`
    - `HGETALL first_user`
    - `HEXISTS first_user name`
    - `HDEL first_user name`
    - no allowed nested hashes


- Various commands
    - `KEYS *` .. get all keys
    - `KEYS c*` .. get all keys start with c
    - `FLUSHALL` .. get rid of everything in redis