<?php

// Q: so what is the problem with singelton in multi-threading environment?
// if thread A calls ::getInstance() .. this is going to create an instance in heap and will start to use it
// if thread B calls ::getInstance() in same time.. it will create another instance and it will override the static variable 
// with the new object, so you may have two different objects which destroy the core idea of singeleton 


//Q: How to handle multi threading with a singleton pattern?
// Eager Initialization (Thread-Safe by Default)
// Lazy Initialization (Thread-Safe only by locking)



//eager (thread safe by default)
class Singleton {
    private static Singleton $instance = new Singleton();

    private function __construct() {}

    public static function getInstance(): Singleton {
        return self::$instance;
    }
}

//Lazy (thread safe by locking)
class Singleton {
    private static Singleton $instance = null;

    private function __construct() {}

    public static function getInstance(): Singleton {
        if (self::$instance === null) {
            $f = fopen(__FILE__, 'r');
            // Lock to prevent race conditions
            flock($f, LOCK_EX); // acquire lock
            self::$instance = new Singleton();
            flock($f, LOCK_UN); // release lock
        }

        return self::$instance;
    }
}


//Q: is syncornize instantation solves all singelton problems?
 // locking getInstance Per thread doesnt solve all thread issues, what i mean other methods within singelton can still overlap
 // and to fix that, you have to protected all the methods against threading 

// Q: so what problem does locking getInstance solves
// if thread A calls ::getInstance() this is going to create an instance in heap and will start to use it
// if thread B in same time calls ::getInstance() it will create another instance and it will override the static variable 
// with the new object, so you may have two different objects which destroy the idea of core idea of singeleton 


// Q: in php, do we have to worry about locking instantiating singelton?
// not, php have two versions 
// - PHP Thread Safe (TS), which always make sure all calls are protected against threads all the time, you dont have to manually declare anything in php code
// - PHP Non-Thread Safe (NTS), which is faster since it doesnt keep checking locking for every call

// Apache and nginx mainly have two methods of receiving the requests
// - every request will go to a process which use one thread (like prefork worker), within this you can use PHP NTS version
// - there is big process and within this process there are multiple threads, each thread can serve a request (like event worker)
//   and here you need PHP TS to keep you safe

// if you are using event worker + PHP-FPM ... PHP-FPM is doing one process one request way, so you should be using NTS PHP Version

//If you used event worker + mod_php + PHP NTS (this is a danger zone) .. you shouldnt typically do that, as this will give you alot of problems every where