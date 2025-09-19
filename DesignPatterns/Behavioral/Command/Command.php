<?php

//Idea: The Command is about encapsulating a request (an action you want to perform) as an object.


// Why would you do that, isnt it easier to run the method without having to wrap it into object
// what is the benefit you would get from wrapping a logic inside a method into an object?

// Benefits
// - Uniform handling of actions, Since every command has the same interface, you can treat them the same way (store, queue, log, undo, retry…).
// - Undo / Redo functionality
// - CLI wrapper (laravel commands and php artisan )
// - Queueing & Scheduling (laravel jobs and invoker is queue:work or schedule:run)
// - Macro commands (composition) Multiple commands can be bundled together to form a workflow.
// - Testing: Since commands are isolated and small, you can unit-test them independently of the system.


// ✅ In short:
// Think of Command when your action is something you might want to queue, undo, log, or standardize across different callers.


//Receiver: is the object that actually does the work. 
// - It has the real business logic. 
// - You can have multiple receivers in the Command pattern.

//Command: is the request as an object

//Invoker: is responsible for executing commands. 
// It doesn’t know what the command does internally, only that it has an execute() (and maybe undo()) method.
//The invoker can also store history (to allow undo/redo).

//Macro Command: is a command but within its execute method, it execute multiple commands 


// Imagine you’re building a file manager in PHP.
// You want to support commands like:
// Create a file
// Rename a file
// Delete a file
// Undo last operation



//Receiver
class FileSystemReceiver {
    public function createFile($path) {
        file_put_contents($path, ""); // create empty file
        echo "📄 File created: $path\n";
    }

    public function deleteFile($path) {
        if (file_exists($path)) {
            unlink($path);
            echo "🗑️ File deleted: $path\n";
        }
    }

    public function renameFile($oldPath, $newPath) {
        if (file_exists($oldPath)) {
            rename($oldPath, $newPath);
            echo "✏️ File renamed: $oldPath → $newPath\n";
        }
    }
}

interface Command {
    public function execute();
    public function undo();
}

class CreateFileCommand implements Command {
    private $fs;
    private $path;

    public function __construct(FileSystemReceiver $fs, $path) {
        $this->fs = $fs;
        $this->path = $path;
    }

    public function execute() {
        $this->fs->createFile($this->path);
    }

    public function undo() {
        $this->fs->deleteFile($this->path);
    }
}

class DeleteFileCommand implements Command {
    private $fs;
    private $path;

    public function __construct(FileSystemReceiver $fs, $path) {
        $this->fs = $fs;
        $this->path = $path;
    }

    public function execute() {
        $this->fs->deleteFile($this->path);
    }

    public function undo() {
        // We could restore from backup, but for simplicity just notify
        echo "⚠️ Cannot undo delete of $this->path\n";
    }
}

class RenameFileCommand implements Command {
    private $fs;
    private $oldPath;
    private $newPath;

    public function __construct(FileSystemReceiver $fs, $oldPath, $newPath) {
        $this->fs = $fs;
        $this->oldPath = $oldPath;
        $this->newPath = $newPath;
    }

    public function execute() {
        $this->fs->renameFile($this->oldPath, $this->newPath);
    }

    public function undo() {
        $this->fs->renameFile($this->newPath, $this->oldPath);
    }
}

//Invoker
class CommandManager {
    private $history = [];

    public function executeCommand(Command $command) {
        $command->execute();
        $this->history[] = $command;
    }

    public function undoLast() {
        $command = array_pop($this->history);
        if ($command) {
            $command->undo();
        } else {
            echo "⚠️ Nothing to undo\n";
        }
    }
}

//client code
$fs = new FileSystemReceiver();
$manager = new CommandManager();

$create = new CreateFileCommand($fs, "test.txt");
$rename = new RenameFileCommand($fs, "test.txt", "renamed.txt");
$delete = new DeleteFileCommand($fs, "renamed.txt");

$manager->executeCommand($create);   // 📄 File created: test.txt
$manager->executeCommand($rename);   // ✏️ File renamed: test.txt → renamed.txt
$manager->executeCommand($delete);   // 🗑️ File deleted: renamed.txt

$manager->undoLast();                // ⚠️ Cannot undo delete
$manager->undoLast();                // ✏️ File renamed: renamed.txt → test.txt
$manager->undoLast();                // 🗑️ File deleted: test.txt (undo of create)
