# Generator

A generator is a special kind of function in PHP that can yield values one by one — instead of returning all of them at once.

So instead of returning a full array (which consumes memory), a generator produces values lazily (on demand).



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