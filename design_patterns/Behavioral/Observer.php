<?php

//Also Called .. Pub/Sub, Observer, Event Listeners

// two classes
// Subject (Publisher).. object being observed
// Observers (Subscribers).. Objects to be notified when the subject changes


// 👉 Suppose we have an Order.
// When an order is placed, multiple things should happen:
// Send email notification.
// Update inventory.
// Log analytics.
// Instead of hardcoding all that inside Order, we use Observer.


//Subject
class Order {
    private array $observers = [];
    private string $status;

    public function attach(OrderObserver $observer): void {
        $this->observers[] = $observer;
    }

    private function notifyObservers(): void {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
    }
    
    public function setStatus(string $status): void {
        $this->status = $status;
        $this->notifyObservers();
    }

    public function getStatus(): string {
        return $this->status;
    }

}

//Observers
interface OrderObserver {
    public function update(Order $order): void;
}

class EmailNotifier implements OrderObserver {
    public function update(Order $order): void {
        echo "📧 Email sent: Order is now {$order->getStatus()}\n";
    }
}

class InventoryUpdater implements OrderObserver {
    public function update(Order $order): void {
        echo "📦 Inventory updated because order is {$order->getStatus()}\n";
    }
}

class AnalyticsLogger implements OrderObserver {
    public function update(Order $order): void {
        echo "📊 Analytics logged: Order changed to {$order->getStatus()}\n";
    }
}

//client code
$order = new Order();

// Attach observers
$order->attach(new EmailNotifier());
$order->attach(new InventoryUpdater());
$order->attach(new AnalyticsLogger());

// Change status (all observers will be notified)
$order->setStatus("PLACED");
$order->setStatus("SHIPPED");



//please notice in the top example, Order is violating SRP as its doing multiple things, managing observers and logic of order as well
//so its better to separate concerns 

class OrderEventDispatcher {
    private array $observers = [];

    public function attach(OrderObserver $observer): void {
        $this->observers[] = $observer;
    }

    public function detach(OrderObserver $observer): void {
        $this->observers = array_filter(
            $this->observers,
            fn($obs) => $obs !== $observer
        );
    }

    public function notifyObservers(Order $order): void {
        foreach ($this->observers as $observer) {
            $observer->update($order);
        }
    }
}

// --- Order (domain model only) ---
class Order {
    private string $status;
    private OrderEventDispatcher $dispatcher;

    public function __construct(OrderEventDispatcher $dispatcher) {
        $this->subject = $dispatcher;
    }

    public function setStatus(string $status): void {
        $this->status = $status;
        $this->dispatcher->notifyObservers($this);
    }

    public function getStatus(): string {
        return $this->status;
    }
}

// --- Usage ---
$dispatcher = new OrderEventDispatcher();
$dispatcher->attach(new EmailNotifier());
$dispatcher->attach(new InventoryUpdater());
$dispatcher->attach(new AnalyticsLogger());

$order = new Order($dispatcher);
$order->setStatus("Processing");
$order->setStatus("Shipped");



//Q: If Order class directly depends on 10 different objects/services, is that a code smell?
// Yes, usually that is a smell — specifically a God object or too many responsibilities.
// Why?
// High coupling → Order becomes fragile because a change in any of the 10 services might force a change in Order.
// Hard to test → you’d need 10 mocks/stubs just to test Order.
// Violates SRP → the class likely has more than one reason to change.
// Poor cohesion → the class is doing “too much”.

// What’s the healthy range?
// A class having 2–4 collaborators is common and fine.
// If you reach 7+ dependencies, that’s usually a red flag 🚩 unless it’s just a DTO or orchestrator.


// What to do instead
// Introduce a Facade/Service: Group related services under one higher-level abstraction.
// Use Events / Dispatcher

// in laravel
// subject is event
// observers are the event listeners


// php artisan make:event OrderPlaced
// app/Events/OrderPlaced.php
class OrderPlaced {
    public $order;

    public function __construct($order) {
        $this->order = $order;
    }
}


// php artisan make:listener SendOrderEmail --event=OrderPlaced
// php artisan make:listener UpdateInventory --event=OrderPlaced

// app/Listeners/SendOrderEmail.php
class SendOrderEmail {
    public function handle(OrderPlaced $event) {
        echo "📧 Email sent for order {$event->order->id}\n";
    }
}

// app/Listeners/UpdateInventory.php
class UpdateInventory {
    public function handle(OrderPlaced $event) {
        echo "📦 Inventory updated for order {$event->order->id}\n";
    }
}

// Dispatch the Event (Subject triggers notification)
OrderPlaced::dispatch($order);

// Laravel automatically register Event Subscribers without manually mapping it