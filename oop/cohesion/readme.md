# Cohesion 

is about how strongly related and focused the class is

High cohesion → the class does one thing well, all its methods and data are closely related to that single purpose.

Low cohesion → the class or module is a “grab bag” of unrelated stuff, doing many different things at once.


1. High cohesion
All methods are about calculating invoice costs.
```java
class InvoiceCalculator {
    public double calculateTotal(Invoice invoice) { ... }
    public double applyDiscount(Invoice invoice, double discountRate) { ... }
    public double calculateTax(Invoice invoice) { ... }
}
```

2. Low cohesion
This class is doing many unrelated jobs: tax, emails, logging, parsing.
```php
class Utility {
    public double calculateTax(Invoice invoice) { ... }
    public void sendEmail(String recipient, String message) { ... }
    public void writeLog(String msg) { ... }
    public void parseJson(String json) { ... }
}
```


🔹 Why Cohesion Matters
- Readability → Developers can quickly understand what a class is supposed to do.
- Maintainability → Changes in one cohesive class won’t affect unrelated code.
- Reusability → A focused class is easier to reuse in other projects.
- Testability → Easier to unit test when a class has a single, clear purpose.


🔹 Relation to SRP (Single Responsibility Principle)
- SRP is basically a rule that says: “Each class should have one reason to change.”
- High cohesion is a result of following SRP.
👉 In other words: If you respect SRP, your modules will naturally have high cohesion.


✅ So, in short:
Cohesion is about “how well the pieces of a module belong together.”
We always aim for high cohesion (focused, single-purpose classes) and avoid low cohesion (scattered responsibilities).