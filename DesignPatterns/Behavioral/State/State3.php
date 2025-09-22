<?php

//Finite State Machine: it is interested more in transitions and declare it

class StateMachine
{
    private string $state;
    private array $transitions;

    public function __construct(string $initialState, array $transitions)
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


class Order
{
    private int $id;
    private StateMachine $fsm;

    public function __construct(int $id)
    {
        $this->id = $id;

        $transitions = [
            'new' => [
                'pay' => 'paid', //action => new state
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
        ];

        $this->fsm = new StateMachine('new', $transitions);
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