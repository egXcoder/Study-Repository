<?php


// Suppose you want a flexible logging system:
// Base logger → logs to file.

// Decorators can add extra behaviors dynamically:
// Timestamp before the message.
// Convert message to JSON.
// Send critical logs by email, etc.


interface Logger {
    public function log(string $message): void;
}

class FileLogger implements Logger {
    public function log(string $message): void {
        echo "Writing to file: $message\n";
    }
}

abstract class LoggerDecorator implements Logger {
    protected Logger $logger;

    public function __construct(Logger $logger) {
        $this->logger = $logger;
    }

    public function log(string $message): void {
        $this->logger->log($message);
    }
}

class TimestampLogger extends LoggerDecorator {
    public function log(string $message): void {
        $date = date("Y-m-d H:i:s");
        parent::log("[$date] $message");
    }
}

class JsonLogger extends LoggerDecorator {
    public function log(string $message): void {
        $jsonMessage = json_encode(["message" => $message]);
        parent::log($jsonMessage);
    }
}

//Client Code

// Base logger
$logger = new FileLogger();

// Add timestamp decorator
$logger = new TimestampLogger($logger);

// Add JSON decorator
$logger = new JsonLogger($logger);

// Use it
$logger->log("User logged in.");


// Writing to file: {"message":"[2025-09-14 15:22:10] User logged in."}