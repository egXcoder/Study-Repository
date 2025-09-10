<?php
// ❌ Control Coupling Example (Bad)
class Invoice {
    public float $amount;

    public function __construct(float $amount) {
        $this->amount = $amount;
    }
}

class InvoiceService {
    public function processInvoice(Invoice $invoice, bool $applyDiscount, bool $applyTax): float {
        $total = $invoice->amount;

        if ($applyDiscount) {
            $total -= $total * 0.1;
        }

        if ($applyTax) {
            $total += $total * 0.15;
        }

        return $total;
    }
}

// Caller
$invoice = new Invoice(100);
$service = new InvoiceService();

// 👉 Caller has to know what true, false means → control coupling.
echo $service->processInvoice($invoice, true, false); // not clear what (true, false) means




// ✅ Fix 1: Separate Methods
class InvoiceService {
    public function calculateTotal(Invoice $invoice): float {
        return $invoice->amount;
    }

    public function calculateWithDiscount(Invoice $invoice): float {
        return $invoice->amount - ($invoice->amount * 0.1);
    }

    public function calculateWithTax(Invoice $invoice): float {
        return $invoice->amount + ($invoice->amount * 0.15);
    }
}

// Caller
$invoice = new Invoice(100);
$service = new InvoiceService();

echo $service->calculateWithDiscount($invoice); // ✅ much clearer



// ✅ Fix 2: Strategy Pattern, respect OCP as Fix1 doesnt
interface InvoiceCalculator {
    public function calculate(Invoice $invoice): float;
}

class DiscountCalculator implements InvoiceCalculator {
    public function calculate(Invoice $invoice): float {
        return $invoice->amount - ($invoice->amount * 0.1);
    }
}

class TaxCalculator implements InvoiceCalculator {
    public function calculate(Invoice $invoice): float {
        return $invoice->amount + ($invoice->amount * 0.15);
    }
}

class InvoiceService {
    public function process(Invoice $invoice, InvoiceCalculator $calculator): float {
        return $calculator->calculate($invoice);
    }
}

// Caller
$invoice = new Invoice(100);
$service = new InvoiceService();

echo $service->process($invoice, new DiscountCalculator()); // ✅ flexible