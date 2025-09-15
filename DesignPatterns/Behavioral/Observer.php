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

    public function setStatus(string $status): void {
        $this->status = $status;
        $this->notifyObservers();
    }

    public function getStatus(): string {
        return $this->status;
    }

    private function notifyObservers(): void {
        foreach ($this->observers as $observer) {
            $observer->update($this);
        }
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