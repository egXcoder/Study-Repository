<?php

// its idea is to delegate work from Order to the OrderState, Order is called the context


// Imagine you’re building an Order system for e-commerce.
// An order can be in different states:
// New (just created)
// Paid
// Shipped
// Completed

// Each state dictates what actions are allowed.
// For example:
// You can pay a New order, but not a Shipped one.
// You can ship a Paid order, but not a New one.


//Applying the pattern can be overkill if a state machine has only a few states or rarely changes.

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