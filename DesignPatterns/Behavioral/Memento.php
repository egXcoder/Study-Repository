<?php

// you would use memnto when you want to store state at a point and restore it back if required
// - undo/redo functionality, or even restore to a specific state straight away
// - snapshots in business overflow
// - Stateful objects in simulations or games


// Scenario:
// A customer adds/removes items from a shopping cart.
// You want the ability to undo the last change (e.g., remove last added item, restore last removed item).

// Originator = ShoppingCart (the object whose state changes).
// Memento = snapshot of the cart items.
// Caretaker = CartHistory that keeps cart versions.


// Originator: Shopping Cart
class ShoppingCart {
    private $items = [];

    public function addItem(string $item): void {
        $this->items[] = $item;
    }

    public function removeItem(string $item): void {
        $this->items = array_filter($this->items, fn($i) => $i !== $item);
    }

    public function getItems(): array {
        return $this->items;
    }

    public function save(): CartMemento {
        return new CartMemento($this->items);
    }

    public function restore(CartMemento $memento): void {
        $this->items = $memento->getItems();
    }
}

// Memento: snapshot of the cart
class CartMemento {
    private $items;

    public function __construct(array $items) {
        // store immutable copy
        $this->items = $items;
    }

    public function getItems(): array {
        return $this->items;
    }
}



// Caretaker: manages history
class CartHistory {
    private $history = [];

    public function push(CartMemento $memento): void {
        $this->history[] = $memento;
    }

    public function pop(): ?CartMemento {
        return array_pop($this->history);
    }
}

//Q: why do i have to save in history manually, wont it once i create a cartmemnto it would add it to history automatically?
// The Memento pattern has a separation of roles on purpose:
// - Originator (Cart): only responsible for creating/restoring snapshots of its state.
// Caretaker (History): decides when to save.

// That means business logic decides when to save, not the object itself.
// For example:
// You might not want to save on every small change (e.g., every keystroke or item add).
// You might want to save only after checkout step, or after a user presses “Save Draft”.
// Automatic saving on every change would quickly bloat memory. 
// but how to use caretaker its up to you, it can be automatic if you think its good with you


//client code

$cart = new ShoppingCart();
$history = new CartHistory();

// Add first item
$cart->addItem("Laptop");
$history->push($cart->save());

$cart->addItem("Phone");
$history->push($cart->save());

$cart->addItem("Headphones");
echo "Cart now: " . implode(", ", $cart->getItems()) . "\n";

// Undo last add
$cart->restore($history->pop());
echo "Cart after undo: " . implode(", ", $cart->getItems()) . "\n";

// Undo again
$cart->restore($history->pop());
echo "Cart after second undo: " . implode(", ", $cart->getItems()) . "\n";