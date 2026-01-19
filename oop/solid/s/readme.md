# SRP – Single Responsibility Principle

A class should have only one reason to change.

The goal of OOP isn't to create a perfect simulation of the real world. (e.g., a user in real life can register, login, pay, order, etc.) you will end up with god objects

The goal is to create a model that effectively solves a problem within a specific context.

✅ SRP Version
Instead, in the real world, a user doesn’t do everything by themselves.
- They authenticate through a login system (Auth service).
- They pay through a payment processor (Stripe, PayPal, etc.).
- They order through an ordering system.
- They cancel through a cancellation workflow.
- The person (User) just initiates actions, but the specialized systems handle them.

So in code:
- User class = identity + profile (who the person is).
- AuthService = login/register.
- PaymentService = pay.
- OrderService = order/cancel.

## Class Types

| Class Type             | What It Should Do                          | Example Use                   |
| ---------------------- | ------------------------------------------ | ----------------------------- |
| **Controller**         | Accept request and return response         | `OrderController@store()`     |
| **Service**            | Contain business logic                     | `OrderService::placeOrder()`  |
| **Repository**         | Handle data storage and retrieval          | `OrderRepository::findById()` |
| **Model / Entity**     | Represent domain data and behavior         | `Order::addItem()`            |
| **DTO**                | Transfer structured data between layers    | `CreateOrderDTO`              |
| **Factory**            | Create complex objects                     | `OrderFactory::create()`      |
| **Builder**            | Build objects step by step                 | `OrderBuilder::build()`       |
| **Validator**          | Validate data correctness                  | `OrderValidator::validate()`  |
| **Notifier / Sender**  | Send notifications (email, SMS, etc.)      | `EmailNotifier::send()`       |
| **Middleware**         | Process request before/after handling      | `AuthMiddleware`              |
| **Adapter**            | Wrap external systems/APIs                 | `StripeAdapter`               |
| **Listener / Handler** | React to events                            | `OrderPlacedListener`         |
| **Command**            | Represent a single action/intent           | `PlaceOrderCommand`           |
| **Job / Worker**       | Run background or async tasks              | `SendInvoiceJob`              |
| **Policy**             | Handle authorization rules                 | `OrderPolicy::canEdit()`      |
| **Mapper**             | Convert objects between formats            | `OrderMapper::toDTO()`        |
| **Helper / Utility**   | Provide stateless helper functions         | `StringHelper::slugify()`     |


---

## Why SRP is Useful

- easier to Understand and explain to other developers. If a class does just one thing, you don’t need to read through 500 lines of mixed logic. You know exactly what it’s supposed to do.

    👉 Example in Laravel:
    - `UserFormatter` formats user data  
    - `UserExporter` exports it to CSV  


- If a bug happens in how you format a user’s name, you only touch `UserFormatter`.   You don’t risk breaking CSV exports. Each class has isolated change impact.


- Smaller classes/functions are simpler to unit test.  

- When responsibilities are split, you can reuse pieces in different contexts.  

- In a team, two developers can work in parallel: One works on formatting rules, Another works on exporting  

---

## ✅ In Short
SRP makes your code **cleaner, safer to change, easier to test, and more reusable**.
