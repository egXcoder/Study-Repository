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

        $this->state = $this->transitions[$this->state][$action];
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
        if(!$this->order->isOrderStateAllow('pay')){
            return ['error'=>'Cant Pay Order with state ' . $this->order->getState()];
        }

        // business logic lives here

        $this->order->applyOrderAction('pay');
        return ['success'=>'Paid successfully'];
    }

    public function ship(string $carrier)
    {
        if(!$this->order->isOrderStateAllow('ship')){
            return ['error'=>'Cant ship Order with state ' . $this->order->getState()];
        }

        // business logic lives here

        $this->order->applyOrderAction('ship');
        return ['success'=>'Shipped successfully'];
    }

    public function deliver()
    {
        if(!$this->order->isOrderStateAllow('deliver')){
            return ['error'=>'Cant deliver Order with state ' . $this->order->getState()];
        }

        // business logic lives here

        $this->order->applyOrderAction('deliver');
        return ['success'=>'Delivered successfully'];
    }

    public function refund()
    {
        if(!$this->order->isOrderStateAllow('refund')){
            return ['error'=>'Cant refund Order with state ' . $this->order->getState()];
        }

        // business logic lives here

        $this->order->applyOrderAction('refund');
        return ['success'=>'Refunded successfully'];
    }

    public function cancel()
    {
        if(!$this->order->isOrderStateAllow('cancel')){
            return ['error'=>'Cant cancel Order with state ' . $this->order->getState()];
        }
 
        // business logic lives here

        $this->order->applyOrderAction('cancel');
        return ['success'=>'Cancelled successfully'];
    }
}