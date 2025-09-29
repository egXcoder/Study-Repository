<?php

//Idea: turns a request (action/operation) into a separate object

// Why would you do that?

// Benefits
// - Queueing & Scheduling (laravel jobs and invoker is queue:work or schedule:run)
// - Undo / Redo functionality

// Objects
// - Command: is the request as an object (OrderPlaced)
// - Receiver: object which do the actual work (OrderService)
// - Target: is the object which the command will operate on (Order)
// - Invoker: is responsible for executing commands. 
// - Macro Command: is a command but within its execute method, it execute multiple commands


//Typically Command is just a request and it delegate the actual work to receiver
// the useful of command is just that we can queue, we can schedule, we can undo/redo ..
// but command shouldnt need to worry about actual implemenations (it violates SRP)
class PlaceOrderCommand implements ShouldQueue {
    public function __construct(public int $orderId) {}

    public function handle(OrderService $service) {
        $service->placeOrder($this->orderId);
    }
}

// in small projects, we can put the implemenation inside command (Fat Command) . its not adviced to do that though
// as its violates SRP, also code now is not reusable if i want to place order from another place rather than queue
// notice: Command is the Receiver in same time, so no explicit receiver here..
class PlaceOrderJob implements ShouldQueue {
    public function __construct(public int $orderId) {}

    public function handle() {
        Order::create(['id' => $this->orderId]);
        Mail::to('customer@example.com')->send(new OrderPlacedMail($this->orderId));
        // etc...
    }
}


//Q: Is a CLI Wrapper (like a Laravel Artisan Command) considered Command Pattern?
// Short answer: No, a CLI command is not the classic Command Pattern — it’s more like a Facade or Adapter or controller to trigger application logic.




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


class MacroCommand implements Command {
    public function __construct(private array $commands) {}

    public function execute() {
        foreach ($this->commands as $command) $command->execute();
    }
    public function undo() {
        foreach (array_reverse($this->commands) as $command) $command->undo();
    }
}


// ✅ Usage Example
$macro = new MacroCommand([
    new CreateFileCommand(new FileSystemReceiver(), "tmp/test.txt"),
    new RenameFileCommand(new FileSystemReceiver(), "tmp/test.txt", "tmp/renamed.txt"),
]);

$manager = new CommandManager();
$manager->executeCommand($macro);
$manager->undoLast();