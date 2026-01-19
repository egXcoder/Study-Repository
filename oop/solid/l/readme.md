# Liskov Substitution Principle (LSP)

subclasses must respect parent contract


## 🎯 Why LSP?
To reduce surprises


```php
// By reading this, any reasonable caller assumes: “If I give you a string, you will store it.”
abstract class Storage{
    abstract public function write(string $path, string $content): void;
}

// LocalStorage implements write correctly
class LocalStorage extends Storage{
    public function write(string $path, string $content): void{
        file_put_contents($path, $content);
    }
}

//violates LSP
class FtpStorage extends Storage{
    public function write(string $path, string $content): void{
        if (strlen($content) > 1024) {
            throw new Exception("FTP storage supports max 1KB only");
        }

        // upload to FTP server...
        echo "Uploaded to FTP: $path\n";
    }
}

//ahh it worked
saveFile(new LocalStorage()); // ✅ Works

//❌suprise, that didnt work.. how would i have known there is a validation hidden far away here, arghhh this code is bad
saveFile(new FtpStorage());
```

### How to fix it??

- Method 1: amend contract to say write function may throw exception then called would expect exception maybe triggered

```php
// By reading this, any reasonable caller assumes: “If I give you a string, you may store it or may give exception”
abstract class Storage{
    /**
     * @throws Exception
     */
    abstract public function write(string $path, string $content): void;
}

//so now when business call it, business would know this call may throw exception and you should handle it .. so no surprises
try {
    $storage->write('report.txt', $content);
} catch (Exception $e) {
    // fallback, retry, log, or switch storage
}
```

- Method 2: move validation from FtpStorage to business logic

```php
//business logic
protected function saveFile($path,$content){
    if (strlen($content) > 1024) {
        throw new Exception("FTP storage supports max 1KB only");
    }
    
    //FTP storage follow the parent contract that it will store any content
    //FTP storage doesnt have that validation inside
    $storage = new FtpStorage();
    $storage->write('/tmp/test.file',$content);
}
```