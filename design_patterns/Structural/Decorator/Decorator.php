<?php

//idea: add dynamic behavior by Wrap object with a wrapper

//Component: the original object to be extended
//Decorator: wrapper

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