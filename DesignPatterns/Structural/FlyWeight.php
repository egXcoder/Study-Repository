<?php


// Imagine you’re building an online store.
// Each product may have thousands of variants (size, color, etc.).
// But all variants share the same image file (say, the product logo or base photo).
// Instead of loading the image object for every variant, we reuse the image flyweight and only attach different extrinsic state (like size or position in the UI).


// Flyweight (shared product image)
class ProductImage {
    private string $file; // intrinsic state

    public function __construct(string $file) {
        $this->file = $file;
    }

    public function render(string $productName, string $color, string $size): void {
        echo "Render image '{$this->file}' for product '$productName' "
           . "with color=$color, size=$size\n";
    }
}

// Flyweight Factory
class ProductImageFactory {
    private array $images = [];

    public function getImage(string $file): ProductImage {
        if (!isset($this->images[$file])) {
            echo "Loading new image from disk: $file\n";
            $this->images[$file] = new ProductImage($file);
        }
        return $this->images[$file];
    }
}


// Client code

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