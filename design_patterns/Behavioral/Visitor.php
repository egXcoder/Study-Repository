<?php


//Element: is the object which is going to be visited
//Visitor: is guest expert who have knowledge how to do a specific operation for all elements family

// - Each element doesn’t know what operations visitors will perform.
// - Adding a new operation → adding a new visitor without touching document classes. (respect OCP)
// - Adding a new document type → implement DocumentElement and update visitors. (bad)

//Visitor shines when you have Stable Object Structure (Elements) and often want to add new operations on them, Visitor is ideal.

// Q: i can put the operation directly into the classes instead of visitor?
// If operations are few → maybe just add methods to the element (simpler). but when operations are many and frequently added then visitor

// Q:i may create a visitor and implmenet two methods while others throw exception which violates LSP?
//Visitor works best when each visitor implement all element types, or you provide default behaviors in a base visitor class.


interface DocumentElement {
    public function accept(DocumentVisitor $visitor);
}


class Invoice implements DocumentElement {
    public $id;
    public $amount;

    public function __construct($id, $amount) {
        $this->id = $id;
        $this->amount = $amount;
    }

    public function accept(DocumentVisitor $visitor) {
        $visitor->visitInvoice($this);
    }
}

class SalesOrder implements DocumentElement {
    public $id;
    public $customer;

    public function __construct($id, $customer) {
        $this->id = $id;
        $this->customer = $customer;
    }

    public function accept(DocumentVisitor $visitor) {
        $visitor->visitSalesOrder($this);
    }
}


class DeliveryNote implements DocumentElement {
    public $id;
    public $items;

    public function __construct($id, $items) {
        $this->id = $id;
        $this->items = $items;
    }

    public function accept(DocumentVisitor $visitor) {
        $visitor->visitDeliveryNote($this);
    }
}


interface DocumentVisitor {
    public function visitInvoice(Invoice $invoice);
    public function visitSalesOrder(SalesOrder $salesOrder);
    public function visitDeliveryNote(DeliveryNote $deliveryNote);
}


class Exporter implements DocumentVisitor {
    public function visitInvoice(Invoice $invoice) {
        echo "Exporting Invoice #{$invoice->id} with amount {$invoice->amount} to XML\n";
    }

    public function visitSalesOrder(SalesOrder $order) {
        echo "Exporting Sales Order #{$order->id} for customer {$order->customer} to XML\n";
    }

    public function visitDeliveryNote(DeliveryNote $note) {
        echo "Exporting Delivery Note #{$note->id} with items: " . implode(", ", $note->items) . " to XML\n";
    }
}


class Auditor implements DocumentVisitor {
    public function visitInvoice(Invoice $invoice) {
        echo "Auditing Invoice #{$invoice->id}: Amount {$invoice->amount}\n";
    }

    public function visitSalesOrder(SalesOrder $order) {
        echo "Auditing Sales Order #{$order->id}: Customer {$order->customer}\n";
    }

    public function visitDeliveryNote(DeliveryNote $note) {
        echo "Auditing Delivery Note #{$note->id}: Items " . implode(", ", $note->items) . "\n";
    }
}


//client code
$documents = [
    new Invoice(1, 100),
    new SalesOrder(10, "Alice"),
    new DeliveryNote(100, ["Item1", "Item2"])
];

$exporter = new Exporter();
$auditor = new Auditor();

foreach ($documents as $doc) {
    $doc->accept($exporter);
    $doc->accept($auditor);
}




//Q: i don't know what are the operations that are available for elements because visitors are scattered all over?
// you can do a factory for available visitors so that you know what are the available visitors instead of having them scattered

class DocumentVisitorFactory {
    public static function getVisitor($operation){
        switch ($operation) {
            case "export":
                return new Exporter;
            case "audit":
                return new Auditor;
        }
    }
}