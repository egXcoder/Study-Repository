<?php


// Mediator also shines when you have many services like 10 services and each service may need to call 3 or 4 services
// which in turn may need to call 3 or 4 services
// this is going to become annoying and hard to follow. 
// mediator is for the rescue as it provide central hub of communication, so its much easier to see how services uses each other



// let’s imagine an e-commerce platform where multiple services have to talk to each other:
// PaymentService
// InventoryService
// ShippingService
// NotificationService
// LoyaltyPointsService
// AnalyticsService

// If they all talk directly, it quickly becomes a mess (Payment needs Inventory + Notification + Loyalty; Shipping needs Notification + Analytics, etc.).

interface Mediator {
    public function notify(object $sender, string $event, mixed $data = null): void;
}

class EcommerceMediator implements Mediator {
    private PaymentService $payment;
    private InventoryService $inventory;
    private ShippingService $shipping;
    private NotificationService $notification;
    private LoyaltyPointsService $loyalty;
    private AnalyticsService $analytics;

    public function __construct(
        PaymentService $payment,
        InventoryService $inventory,
        ShippingService $shipping,
        NotificationService $notification,
        LoyaltyPointsService $loyalty,
        AnalyticsService $analytics
    ) {
        $this->payment = $payment;
        $this->inventory = $inventory;
        $this->shipping = $shipping;
        $this->notification = $notification;
        $this->loyalty = $loyalty;
        $this->analytics = $analytics;

        // let services know their mediator
        $payment->setMediator($this);
        $inventory->setMediator($this);
        $shipping->setMediator($this);
        $notification->setMediator($this);
        $loyalty->setMediator($this);
        $analytics->setMediator($this);
    }

    public function notify(object $sender, string $event, mixed $data = null): void {
        switch ($event) {
            case "payment.success":
                $this->inventory->reserve($data['orderId']);
                $this->shipping->prepareShipment($data['orderId']);
                $this->loyalty->addPoints($data['userId'], $data['amount']);
                $this->notification->sendEmail($data['userId'], "Payment successful!");
                $this->analytics->track("PaymentCompleted", $data);
                break;

            case "payment.failed":
                $this->notification->sendEmail($data['userId'], "Payment failed!");
                $this->analytics->track("PaymentFailed", $data);
                break;

            case "shipping.dispatched":
                $this->notification->sendEmail($data['userId'], "Your order has been shipped!");
                $this->analytics->track("OrderShipped", $data);
                break;
        }
    }
}


// Services (Colleagues)
abstract class Service {
    protected Mediator $mediator = null;
    public function setMediator(Mediator $mediator): void {
        $this->mediator = $mediator;
    }
}

class PaymentService extends Service {
    public function pay(int $orderId, int $userId, float $amount): void {
        echo "💳 Processing payment of $$amount for order #$orderId\n";
        // pretend payment succeeded
        $this->mediator?->notify($this, "payment.success", [
            'orderId' => $orderId,
            'userId' => $userId,
            'amount' => $amount
        ]);
    }
}

class InventoryService extends Service {
    public function reserve(int $orderId): void {
        echo "📦 Reserving inventory for order #$orderId\n";
    }
}

class ShippingService extends Service {
    public function prepareShipment(int $orderId): void {
        echo "🚚 Preparing shipment for order #$orderId\n";
        $this->mediator?->notify($this, "shipping.dispatched", ['orderId' => $orderId, 'userId' => 42]);
    }
}

class NotificationService extends Service {
    public function sendEmail(int $userId, string $message): void {
        echo "📧 Email to user $userId: $message\n";
    }
}

class LoyaltyPointsService extends Service {
    public function addPoints(int $userId, float $amount): void {
        $points = floor($amount / 10);
        echo "⭐ Added $points loyalty points to user $userId\n";
    }
}

class AnalyticsService extends Service {
    public function track(string $event, array $data): void {
        echo "📊 Analytics: $event => " . json_encode($data) . "\n";
    }
}


// Q: cant we instead of mediator?, we can use events and listeners won't it be better or its just another way?

// Yes — you’re spot on. Using events and listeners (pub/sub) is another way to solve the same problem that the Mediator pattern addresses: reducing tight coupling between many services.
// Mediator
// - Centralizes the logic of "who should talk to whom".
// - The mediator knows all colleagues and orchestrates communication explicitly.
// - Colleagues just say: “Mediator, I paid”, and Mediator decides: “Okay, notify inventory, shipping, analytics, etc.”
// - Works well if you want centralized coordination and rules.


// Events & Listeners (Observer pattern)
// - More decentralized and flexible.
// - A service fires an event: “PaymentSuccess”.
// - Any number of listeners (Inventory, Shipping, Analytics, Notification, Loyalty) can subscribe and react.
// - The publisher doesn’t know who is listening.
// - 👉 Think of it like a radio broadcast: anyone tuned in to the channel hears it and reacts.


class PaymentService {
    public function pay(int $orderId, int $userId, float $amount): void {
        echo "💳 Processing payment...\n";
        // Fire an event
        Event::dispatch(new PaymentSuccess($orderId, $userId, $amount));
    }
}

class PaymentSuccess {
    public function __construct(
        public int $orderId,
        public int $userId,
        public float $amount
    ) {}
}

// Listeners
class ReserveInventory {
    public function handle(PaymentSuccess $event) {
        echo "📦 Reserving inventory for order {$event->orderId}\n";
    }
}

class SendConfirmationEmail {
    public function handle(PaymentSuccess $event) {
        echo "📧 Sending email to user {$event->userId}\n";
    }
}

class AddLoyaltyPoints {
    public function handle(PaymentSuccess $event) {
        $points = floor($event->amount / 10);
        echo "⭐ Added {$points} points for user {$event->userId}\n";
    }
}

// Now, PaymentService doesn’t know about Inventory, Email, Loyalty… it just fires PaymentSuccess.
// Listeners are free to attach/detach without touching PaymentService.


// Which to choose?
// Use Mediator if:
// You need explicit orchestration rules.
// The flow is complex and you want one place to see the logic.
// Example: “Only trigger shipping if inventory is reserved successfully, then notify loyalty after shipping confirmation.”

// Use Events/Listeners if:
// You want loose coupling and don’t care about the exact flow.
// Many services can freely listen or stop listening.
// Example: “Whenever a payment succeeds, whoever cares (analytics, loyalty, notifications) can react.”


// 👉 In modern frameworks (like Laravel, Symfony, Spring, etc.), events/listeners are often preferred because they are built-in, easy to extend, and less rigid.
// 👉 Mediator shines when the orchestration itself is complex and must be controlled (like workflows, transaction coordination, or chat rooms).




// Q:but isnt event is less reliable? because event may fail? and if the listeners is not in a specific sequence for example, it may send the notification while it it didnt reserve the stocks from inventory yet?

// You’ve nailed one of the biggest trade-offs between events and mediator/direct calls 👌

// Let’s break it down:

// 🔹 Problem with Events
// 1- Unreliable execution order (unless the system/framework enforces it).
// 2- If one listener fails, others may still run — or may not, depending on the framework.
// 3- Harder to reason about workflows: the flow is scattered across multiple listeners.

// 🔹 Mediator
// Centralized control.
// Can enforce the sequence of actions:
// Guarantees order and consistency.

// Inside mediator:

// $this->inventory->reserve($orderId);
// if ($reserved) {
//     $this->shipping->schedule($orderId);
//     $this->notification->send($orderId);
// } else {
//     $this->notification->sendFailure($orderId);
// }



// Events

// Looser coupling, but sequence is not guaranteed unless you explicitly define listener priorities (Laravel lets you set listener order, for example).
// Great when actions are independent (logging, analytics, sending marketing emails, updating dashboards).
// Risky when actions are dependent (inventory before shipping before notifying).

// 🔹 Practical rule of thumb
// - Use events when actions are independent and you don’t care about order. logging, analytics, “send coupon after signup”.
// - Use mediator (orchestrator) when actions are dependent and order matters.
// Example: e-commerce order workflow → reserve inventory → process shipping → send notification.

// 👉 You’re absolutely right: events can be less reliable if you expect them to enforce business-critical sequencing.
// That’s why in many systems we see both:
// Mediator/Service for core orchestration.
// Events fired at certain milestones for optional/parallel stuff.