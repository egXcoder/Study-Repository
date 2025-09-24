<?php

//idea: reduce memory usage when you have a large number of similar objects.
// Instead of creating a new object every time, Flyweight shares common, intrinsic data among multiple objects and only stores unique (extrinsic) data separately.


//FlyWeight class: The shared object that contains data that can be reused
//FlyWeight Factory: A factory responsible for creating and managing flyweight objects. then we instantiate flyweight only when we need


// Imagine you’re building an online store.
// Each product may have thousands of variants (size, color, etc.).
// But all variants share the same image file (say, the product logo or base photo).
// Instead of loading the image object for every variant, we reuse the image flyweight and only attach different extrinsic state (like size or position in the UI).



// No flyweight — every product variant loads its own image separately
class ProductVariant {
    private string $name;
    private string $color;
    private string $size;
    private string $imageFile;
    
    public function __construct(string $name, string $color, string $size, string $imageFile) {
        $this->name = $name;
        $this->color = $color;
        $this->size = $size;
        $this->imageFile = $imageFile;
        
    }
    
    //you can see imagefile is pretty expensive and redudant since its the same image file used in many variants
    //and currently its being read file disk many times unncessarily
    //so we should use flyweight pattern to encapsulate it and load it from disk if not already loaded
    public function render(): void {
        $this->readImageFromDisk();

        echo "Render image '{$this->imageFile}' for product '{$this->name}' "
        . "with color={$this->color}, size={$this->size}\n";
    }
    
    protected function readImageFromDisk(){
        // Imagine this is a heavy disk operation or memory object
        echo "Loading new image from disk: {$this->imageFile}\n";
    }
}

// Products with different variants
$variants = [
    ['name' => 'T-Shirt', 'color' => 'Red',   'size' => 'M',  'image' => 'tshirt.png'],
    ['name' => 'T-Shirt', 'color' => 'Blue',  'size' => 'L',  'image' => 'tshirt.png'],
    ['name' => 'T-Shirt', 'color' => 'Green', 'size' => 'S',  'image' => 'tshirt.png'],
    ['name' => 'Shoes',   'color' => 'Black', 'size' => '42', 'image' => 'shoes.png'],
    ['name' => 'Shoes',   'color' => 'White', 'size' => '41', 'image' => 'shoes.png'],
];

// Each product variant will load its own image, even if repeated
$objects = [];
foreach ($variants as $variant) {
    $objects[] = new ProductVariant(
        $variant['name'],
        $variant['color'],
        $variant['size'],
        $variant['image']
    );
}

// Render them
foreach ($objects as $obj) {
    $obj->render();
}


// Flyweight (shared product image)
class ProductImage {
    private string $file; // intrinsic state

    public function __construct(string $file) {
        $this->file = $file;
    }

    public function render(string $productName, string $color, string $size): void {
        $this->readImageFromDisk($this->file);

        echo "Render image '{$this->file}' for product '$productName' "
           . "with color=$color, size=$size\n";
    }

    protected function readImageFromDisk(){
        // Imagine this is a heavy disk operation or memory object
        echo "Loading new image from disk: {$this->file}\n";
    }
}

// Flyweight Factory
class ProductImageFactory {
    private array $images = [];

    public function getImage(string $file): ProductImage {
        if (!isset($this->images[$file])) {
            $this->images[$file] = new ProductImage($file);
        }
        return $this->images[$file];
    }
}


// Products with different variants
$variants = [
    ['name' => 'T-Shirt', 'color' => 'Red', 'size' => 'M', 'image' => 'tshirt.png'],
    ['name' => 'T-Shirt', 'color' => 'Blue', 'size' => 'L', 'image' => 'tshirt.png'],
    ['name' => 'T-Shirt', 'color' => 'Green', 'size' => 'S', 'image' => 'tshirt.png'],
    ['name' => 'Shoes',   'color' => 'Black', 'size' => '42', 'image' => 'shoes.png'],
    ['name' => 'Shoes',   'color' => 'White', 'size' => '41', 'image' => 'shoes.png'],
];

$factory = new ProductImageFactory();

//instead of loading file from disk multiple times, we will use the flyweight factory to reduce duplicate work
foreach ($variants as $variant) {
    $image = $factory->getImage($variant['image']); // shared (intrinsic)
    $image->render($variant['name'], $variant['color'], $variant['size']); // extrinsic
}