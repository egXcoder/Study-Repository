<?php

// its idea is to encapsulate state-based behavior and delegate behavior to its current state


//Tip: applying the pattern can be overkill if a state machine has only a few states or rarely changes.

interface OrderState {
    public function pay(Order $order);
    public function ship(Order $order);
    public function complete(Order $order);
}

class NewOrderState implements OrderState {
    public function pay(Order $order) {
        //pay logic is done here
        //...
        $order->setState(new PaidOrderState());
        return true;
    }

    public function ship(Order $order) {
        return false;
    }

    public function complete(Order $order) {
        return false;
    }
}

class PaidOrderState implements OrderState {
    public function pay(Order $order) {
        return false;
    }

    public function ship(Order $order) {
        //shipping logic is done here
        $order->setState(new ShippedOrderState());
        return true;
    }

    public function complete(Order $order) {
        return false;
    }
}

class ShippedOrderState implements OrderState {
    public function pay(Order $order) {
        return false;
    }

    public function ship(Order $order) {
        return false;
    }

    public function complete(Order $order) {
        //completion logic is done here
        $order->setState(new CompletedOrderState());
        return true;
    }
}

class CompletedOrderState implements OrderState {
    public function pay(Order $order) {
        return false;
    }

    public function ship(Order $order) {
        return false;
    }

    public function complete(Order $order) {
        return false;
    }
}


class Order {
    private OrderState $state;

    public function __construct() {
        $this->state = new NewOrderState(); // initial state
    }

    public function setState(OrderState $state) {
        $this->state = $state;
    }

    public function pay() {
        return $this->state->pay($this);
    }

    public function ship() {
        return $this->state->ship($this);
    }

    public function complete() {
        return $this->state->complete($this);
    }
}


// Q: but isnt these state classes violate lsp?
// This is actually a known tradeoff with State patterns. state patterns violate liskov substituion principle
// and since its known design pattern, so its expected for state class to acts differently which is totally fine