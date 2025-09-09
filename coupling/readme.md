# Coupling 

is about how much one module/class depends on another module/class.

It measures the degree of dependency between software components.

Tight coupling → modules are highly dependent on each other.

Loose coupling → modules are independent or depend only on well-defined contracts/interfaces.


🔹 Types of Coupling (from worst to best)

- Content Coupling (worst 😱)
    One class directly modifies another class’s internal data (e.g., accessing private fields via hacks).
    Example: class A reaches inside class B and changes its private attributes.

- Common Coupling
    Multiple classes share global state/variables.
    Example: Both InvoiceService and TaxService modify a global Config.taxRate.

- External Coupling
    Two modules depend on external resources in the same way.
    Example: Both rely on a specific database schema structure.

- Control Coupling
    One module controls the logic of another by passing “what to do” flags.
    Example: processInvoice(invoice, true, false) → caller decides internals.

- Stamp/Data Coupling
    Modules pass whole objects when only part is needed.
    Example: Passing the whole Invoice object just to get invoice.id.

- Message/Data Coupling (best 🎉)
    Modules communicate only through well-defined parameters or interfaces.
    Example: taxCalculator.calculateTax(invoiceAmount)
    Here, InvoiceCalculator doesn’t care about how TaxCalculator works.


# 🔹 Example: Tight vs Loose Coupling
## Tight Coupling

```java
class InvoiceCalculator {
    private TaxCalculator taxCalculator = new TaxCalculator(); // direct dependency

    public double calculate(Invoice invoice) {
        return invoice.getAmount() + taxCalculator.calculateTax(invoice);
    }
}
```
InvoiceCalculator is tightly coupled to TaxCalculator.
If tax rules change → must modify InvoiceCalculator.


## Loose Coupling (via abstraction)

```java
interface TaxService {
    double calculateTax(double amount);
}

class InvoiceCalculator {
    private TaxService taxService; // depends on abstraction

    public InvoiceCalculator(TaxService taxService) {
        this.taxService = taxService;
    }

    public double calculate(Invoice invoice) {
        return invoice.getAmount() + taxService.calculateTax(invoice.getAmount());
    }
}
```

InvoiceCalculator doesn’t care which tax service implementation is used.
Could swap in FlatTaxService, ProgressiveTaxService, VatTaxService, etc.