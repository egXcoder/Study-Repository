<?php


// Think of a File System:
// A File is a leaf (cannot contain anything).
// A Directory is a composite (can contain files and other directories).
// Both support operations like show().


// Component → defines the common interface.
// Leaf → represents a simple object (cannot have children).
// Composite → represents a container that can hold other components (leaf or composite).

//component
interface FileSystemComponent {
    public function show(int $indent = 0): void;
}

//leaf
class File implements FileSystemComponent {
    private string $name;

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function show(int $indent = 0): void {
        echo str_repeat("  ", $indent) . "- File: " . $this->name . "\n";
    }
}

//Composite
class Directory implements FileSystemComponent {
    private string $name;
    private array $children = [];

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function add(FileSystemComponent $component): void {
        $this->children[] = $component;
    }

    public function show(int $indent = 0): void {
        echo str_repeat("  ", $indent) . "+ Directory: " . $this->name . "\n";
        foreach ($this->children as $child) {
            $child->show($indent + 1);
        }
    }
}

// Client Code
// Build tree
$root = new Directory("root");
$root->add(new File("file1.txt"));
$root->add(new File("file2.txt"));

$subDir = new Directory("subdir");
$subDir->add(new File("file3.txt"));

$root->add($subDir);

// Show structure
$root->show();