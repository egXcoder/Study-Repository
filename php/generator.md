# Generator

A generator is a special kind of function in PHP that can yield values one by one — instead of returning all of them at once.

So instead of returning a full array (which consumes memory), a generator produces values lazily (on demand).

You can think of a generator as: A function that can “pause” at each yield, and later “resume” from the same point — optionally receiving a value back with send().

## Theory

```php

function numbers()
{
    yield 1;
    yield 2;
    yield 3;
}

// calling function doesnt do anything just return generator object .. 
// A generator is lazy: it produces values only when you iterate over it (with foreach, next(), etc.).
// A generator is like a lightweight iterator.
$gen = numbers();


$gen->current(); //1

$gen->next();$gen->current(); //2

$gen->next();$gen->current(); //3

$gen->next();$gen->current(); //NULL

$gen->valid(); //false
```

```php
function numbers()
{
    yield 1;
    yield 2;
    yield 3;
}

$gen = numbers();

while ($gen->valid()) {
    echo "Key: " . $gen->key() . " | Value: " . $gen->current() . "\n";
    $gen->next();
}

```

```php
function numbers()
{
    yield 1;
    yield 2;
    yield 3;
}

foreach(numbers() as $num){
    echo $num;
}
```


## Real World Example

Imagine reading a 2 GB log file line by line:

```php

function readFileByLine($path) {
    $fd = fopen($path, 'r');
    while (!feof($fd)) {
        yield fgets($fd);
    }
    fclose($fh);
}

// uses almost no memory
foreach (readFileByLine('big.log') as $line) {
    echo $line;
}

```


## Sending Value Into Generator

Normally, you use a generator to get values out — each yield gives you something.

But with send(), you can also send a value into the generator, back to where it paused at yield.

```php

function greeter() {
    $name = yield "What's your name?";
    yield "Hello, $name!";
}

$gen = greeter();

echo $gen->current(), "\n"; // Step 1: prints "What's your name?"

echo $gen->send("Ahmed"), "\n"; // Step 2: send "Ahmed" back to generator


```

send() is rarely used in most PHP codebases:
- Most developers use generators for iteration — e.g. streaming large datasets: 99% of generator use in PHP looks like:
- PHP is not async-native
- send() makes logic harder to follow