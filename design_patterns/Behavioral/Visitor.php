<?php

// - Strategy → one object, many interchangeable behaviors.
// - Visitor → many object types, one operation applied across them (extensible with more operations).

class Receipt implements Exportable {
    public function exportWith(Exporter $exporter): void {
        $exporter->handleReceipt($this);
    }
}

class CreditNote implements Exportable {
    public function exportWith(Exporter $exporter): void {
        $exporter->handleCreditNote($this);
    }
}

interface Exporter {
    public function handleInvoice(Invoice $invoice): void;
    public function handleReceipt(Receipt $receipt): void;
    public function handleCreditNote(CreditNote $creditNote): void;
}

class JsonExporter implements Exporter {
    public function handleInvoice(Invoice $invoice): void { /* ... */ }
    public function handleReceipt(Receipt $receipt): void { /* ... */ }
    public function handleCreditNote(CreditNote $creditNote): void { /* ... */ }
}

class CSVExporter implements Exporter {
    public function handleInvoice(Invoice $invoice): void { /* ... */ }
    public function handleReceipt(Receipt $receipt): void { /* ... */ }
    public function handleCreditNote(CreditNote $creditNote): void { /* ... */ }
}

// If your system frequently adds new report types (JSON, XML, CSV, Excel…), Visitor is worth it.
// If your system frequently adds new business objects (Invoice, Receipt, PurchaseOrder), then Visitor can feel bloated.