<?php


//Idea: Wrap another object to control access to it
// - Protection Proxy: Restrict access based on roles, permissions etc..
// - Virtual Proxy: Lazy Initialization when creating object is expensive (memory, cpu, network) you dont want to create it till its really needed)
// - Remote Proxy: When object exists in another process, server, machine and you need a local stand-in to forward request
// - Caching Proxy: cache real object instead of recomputing
// - Smart Proxy: Logging, monitoring


//Subject: the original object
//Proxy: is the wrapper to control access, etc...

// Imagine a system that displays product images in an admin dashboard:
// Images are large and expensive to load from disk or remote storage.
// You don’t want to load them until they are actually displayed.
// Instead of directly loading the image, you use a proxy that loads it only when needed.


// Subject Interface
interface Image {
    public function display(): void;
}

// Real Subject (the heavy object)
class RealImage implements Image {
    private string $filename;

    public function __construct(string $filename) {
        $this->filename = $filename;
        $this->loadFromDisk();
    }

    private function loadFromDisk(): void {
        echo "Loading image from disk: {$this->filename}\n";
        // imagine this is a heavy operation
    }

    public function display(): void {
        echo "Displaying image: {$this->filename}\n";
    }
}

// Proxy
class ProxyImage implements Image {
    private string $filename;
    private RealImage $realImage = null;

    public function __construct(string $filename) {
        $this->filename = $filename;
    }

    public function display(): void {
        // Lazy initialization: load only when needed
        if ($this->realImage === null) {
            $this->realImage = new RealImage($this->filename);
        }
        $this->realImage->display();
    }
}

// Client Code
$image1 = new ProxyImage("product1.png");
$image2 = new ProxyImage("product2.png");

// Images not loaded yet
echo "Dashboard loaded.\n";

// Load on demand
$image1->display(); // loads then displays
$image1->display(); // already loaded, just displays
$image2->display(); // loads then displays


// Notice: if you have multiple proxies,and it make sense to chain them then you can use chain of responsbility design patterns
// and you can use laravel pipelines to assist you with that
//  Pipeline::send($request)
    // ->through([
    //     First::class,
    //     Second::class,
    //     Third::class,
    // ])
    // ->thenReturn();