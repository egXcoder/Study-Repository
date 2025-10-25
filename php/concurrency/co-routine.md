# CoRoutine

A coroutine can pause and resume later


Q: TODO:: what is the purpose of coroutines in general, when would it be helpful to be used.. is it mainly for i/o intensive tasks?


## When are coroutines useful

### I/O-intensive systems
- Network servers (HTTP, WebSocket, TCP)
- Database / API calls
- File I/O
- Any case where you spend most time waiting
That’s why frameworks like ReactPHP, Amp, and Swoole rely on coroutines (implemented via generators or fibers).

#### Without Coroutine

Each line blocks until the file is read — total time = sum of all 3.

```php

$data1 = file_get_contents('file1.txt');
$data2 = file_get_contents('file2.txt');
$data3 = file_get_contents('file3.txt');

```

#### With Coroutine

Now, while one coroutine is waiting on I/O, the event loop can run others.

```php

function readAsync($file) {
    // Start reading, then yield until data is ready
    yield async_read($file);
}

$tasks = [
    readAsync('file1.txt'),
    readAsync('file2.txt'),
    readAsync('file3.txt'),
];

eventLoop($tasks);

```



- A function never stops until the job is done.
- A coroutine can pause mid-task (“I’ll continue later”) so others can work in the meantime.



## coroutine vs async-await

A coroutine is a general concept in computer science — a function that can pause itself and resume later

You must manually call next() / send() to move on

```php

function greeter() {
    $name = yield "What’s your name?";
    echo "Hello, $name!\n";
}

$gen = greeter();
echo $gen->current();  // => asks question
$gen->send("Ahmed");   // => prints Hello, Ahmed!


```

async/await is a pattern built on top of coroutines or promises to make asynchronous code look synchronous.

await pauses the function until the Promise resolves .. The JS runtime resumes it automatically when ready .. That’s coroutine behavior — but with promises integrated and managed by the event loop automatically.

Automatically resumed by runtime

```js

async function greet() {
    const name = await askName();
    console.log("Hello, " + name);
}

```

In JS, async/await compiles to a generator function using yield internally:

```js

async function foo() {
    await bar();
}


// roughly compiles to:

function* foo() {
    yield bar();
}


// …and then wrapped in a scheduler that calls .next() when each Promise resolves.

```