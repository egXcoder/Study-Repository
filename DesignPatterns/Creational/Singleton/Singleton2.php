<?php

//Q: How to handle multi threading with a singleton pattern?
// Eager Initialization (Thread-Safe by Default)
// Lazy Initialization (with Synchronization) Only create the instance when first needed, but wrap the creation in a lock.

//eager
class Singleton {
    private static Singleton $instance = new Singleton();

    private function __construct() {}

    public static function getInstance(): Singleton {
        return self::$instance;
    }
}

//Lazy
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

//Lazy in java
public class Singleton {
    private static volatile Singleton instance;

    private Singleton() {}

    public static Singleton getInstance() {
        if (instance == null) {  // first check
            synchronized (Singleton.class) {
                if (instance == null) {  // second check
                    instance = new Singleton();
                }
            }
        }
        return instance;
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


//Q: what is PHP-FPM?
// within event worker there are processes and each process can hold many requests, but apache is sending the request to PHP-FPM
// Since PHP-FPM uses processes, not threads, it can use Non-Thread Safe PHP (faster). 
// PHP-FPM itself is one request 

// PHP-FPM is standalone service that runs on your server, managing a pool of PHP worker processes.

// Why it exists
// Old way: mod_php — PHP runs inside Apache processes/threads.
// Tied to Apache’s process model (prefork, worker, event).
// Not flexible.

// Modern way: PHP-FPM — a daemon that only runs PHP code.
// Web server just acts as a proxy.
// More scalable, flexible, secure.


// How it works

// Apache (event MPM) or Nginx receives an HTTP request.
// If the request is for a .php file, Apache forwards it to PHP-FPM using FastCGI protocol.
// PHP-FPM:
// Picks an idle worker process from its pool.
// Runs the PHP script inside that process.
// Returns the output (HTML/JSON/etc.) back to Apache.
// Apache sends the result to the browser.