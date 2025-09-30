<?php

//idea: add dynamic behavior by Wrap object with a wrapper

//- Decorator: wrapper
//- Component: the original object to be extended


// decorator is useful when:
// - You need many combinations of features, and inheritance would cause a class explosion.
// Example: Notification system -> by (Email + SMS + Push Notification + Slack Message)
// Example: File save → Local Save + Cloud Sync + History Backup + Encryption.
// Example: Coffee → {With Milk, With Sugar, With Whipped Cream, With Milk+Sugar, ...}

// - if the original object (component) you didnt write and you cant amend it
// instead you would decorate it and rely on your decorator to be able to amend the behavior



//suppose you have a class of FileReader which read a file from disk

// [problem]
//What if I want to compress? Or encrypt? Or cache the content? or compress then encrypt .. or encrypt then cache .. or compress then cache
//if we create a class for every way,it will cause class explosion

// [answer]
//Decorator Pattern is perfect in this use case


interface Reader {
    public function read(): string;
}

class FileReader implements Reader {
    protected $file;

    public function __construct(string $file) {
        $this->file = $file;
    }

    public function read(): string {
        return file_get_contents($this->file);
    }
}

abstract class ReaderDecorator implements Reader {
    protected $reader;

    public function __construct(Reader $reader) {
        $this->reader = $reader;
    }

    public function read(): string {
        return $this->reader->read();
    }
}


class CompressionDecorator extends ReaderDecorator {
    public function read(): string {
        $data = parent::read();
        return gzcompress($data);
    }
}

class EncryptionDecorator extends ReaderDecorator {
    public function read(): string {
        $data = parent::read();
        return base64_encode($data); // fake encryption for demo
    }
}

//client code
$reader = new FileReader("data.txt");

// Wrap with compression
$compressed = new CompressionDecorator($reader);
echo $compressed->read();

// Wrap with encryption
$encrypted = new EncryptionDecorator($reader);
echo $encrypted->read();

// Stack multiple decorators
$secureReader = new EncryptionDecorator(
    new CompressionDecorator(
        new FileReader("data.txt")
    )
);

echo $secureReader->read();