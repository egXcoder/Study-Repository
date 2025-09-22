<?php


//  Actually Below in not state design pattern anymore as for state design pattern. as idea of state pattern is to delegate work
//  to state classes, while Below you have 
//  - StateMachine which guard transitions between states thats all
//  - OrderService is the class where all the logic is defind confirm, ship, cancel, etc...

// in many real-world Laravel (or general business) applications, your OrderService style is better than a “pure textbook” State pattern.
//  -- Centralization: No hunting across a dozen tiny State classes.
//  -- Maintainability for your domain: If your order process has only 5–6 possible actions (pay, ship, deliver, refund, cancel) → a single service is cleaner.
//  -- Flexibility: Adding guard rules (isOrderStateAllow) is easier and more readable than scattering “throw new Exception”s across multiple state classes.

// When State pattern is worth it
//  -- If the number of states grows (10+).
//  -- If states are 3 -> 10 and each state has very different, complex behavior.
//  -- If you need pluggable behaviors (e.g., 3rd-party packages can add new states).
//  -- If your rules change a lot and you want to isolate them cleanly.
//  -- Otherwise, State pattern can feel like overengineering.

class StateMachine
{
    private string $state;
    private array $transitions;

    public function __construct(string $initialState,$transitions)
    {
        $this->state = $initialState;
        $this->transitions = $transitions;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function can(string $action): bool
    {
        return isset($this->transitions[$this->state][$action]);
    }

    public function apply(string $action): ?string
    {
        if (!$this->can($action)) {
            throw new Exception("Event '{$action}' not allowed from state '{$this->state}'");
        }

        $oldState = $this->state;

        $this->state = $this->transitions[$this->state][$action];

        EventDispatcher::dispatch('order.state_changed', [
            'from' => $oldState,
            'to' => $this->state,
            'action' => $action,
        ]);
        
        return $this->state;
    }
}

class SalesOrderStateMachine extends StateMachine
{
    public function __construct(string $initialState)
    {
        parent::__construct($initialState, [
            'new' => [
                'pay' => 'paid',
                'cancel' => 'cancelled',
            ],
            'paid' => [
                'ship' => 'shipped',
                'refund' => 'refunded',
            ],
            'shipped' => [
                'deliver' => 'completed',
            ],
            'cancelled' => [],
            'refunded' => [],
            'completed' => [],
        ]);
    }
}


class Order
{
    private int $id;
    private StateMachine $fsm;

    public function __construct(int $id)
    {
        $this->id = $id;
        $this->fsm = new SalesOrderStateMachine('new');
    }

    public function getState(){
        return $this->fsm->getState();
    }

    public function isOrderStateAllow($action){
        return $this->fsm->can($action);
    }

    public function applyOrderAction($action){
        return $this->fsm->apply($action);
    }
}

class OrderService{
    protected $order;
    
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function pay()
    {
        return $this->applyWithGuard('pay', function() {
            // business logic
        });
    }

    public function ship(string $carrier)
    {
        return $this->applyWithGuard('ship', function() {
            // business logic
        });
    }

    public function deliver()
    {
        return $this->applyWithGuard('deliver', function() {
            // business logic
        });
    }

    public function refund()
    {
        return $this->applyWithGuard('refund', function() {
            // business logic
        });
    }

    public function cancel()
    {
        return $this->applyWithGuard('cancel', function() {
            // business logic
        });
    }

    private function applyWithGuard(string $action, callable $logic) {
        if (!$this->order->isOrderStateAllow($action)) {
            return ['error' => "Cant {$action} Order with state " . $this->order->getState()];
        }

        $logic();
        
        $this->order->applyOrderAction($action);
        return ['success' => ucfirst($action) . " successfully"];
    }
}