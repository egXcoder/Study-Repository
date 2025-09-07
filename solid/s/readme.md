# SRP – Single Responsibility Principle

A class should have only one reason to change.


When we say “a class should have one reason to change”, the trick is to recognize that "reasons to change" are not about the domain entity’s responsibilities (e.g., a user in real life can register, login, pay, order, etc.). Instead, they’re about axes of change in the software.

1. Think in Responsibilities, not Actions
A responsibility is not "all the things a user can do."
It’s about who will request changes to that class.

Example with User:
- Login behavior might change if the security team updates authentication policies.
- Payment behavior might change if the finance team changes billing logic.
- Ordering behavior might change if the sales team changes workflows.
👉 Already, that’s three different reasons to change. That’s why stuffing them all in one User class makes it fragile.


2. Look at Business Concerns vs. Technical Concerns

Axes of change often come from different concerns:
- Business rules: discounts, order approvals, invoice calculations.
- Technical rules: caching, database persistence, API calls.
- Cross-cutting rules: logging, validation, notifications.
If a class mixes multiple concerns, it has multiple axes of change.


Analogy
👤 Imagine a Real-Life User (a person): A person in real life can do many things:

- Log in to a website (enter credentials).
- Pay for something (use their credit card).
- Place an order.
- Cancel an order.
- Leave a review.

If you try to stuff all these responsibilities into the single User entity (class), it’s like saying: This person must carry all tools (laptop, wallet, shopping cart, cancellation forms, etc.) with them everywhere.

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

---

## Why SRP is Useful

### 1. Easier to Understand and explain to other developers
If a class does just one thing, you don’t need to read through 500 lines of mixed logic.  
You know exactly what it’s supposed to do.

**👉 Example in Laravel:**
- `UserFormatter` formats user data  
- `UserExporter` exports it to CSV  

If they were in one class, it would be harder to read and reason about.

---

### 2. Easier to Maintain
If a bug happens in how you format a user’s name, you only touch `UserFormatter`.  
You don’t risk breaking CSV exports.  
Each class has **isolated change impact**.

---

### 3. Easier to Test
Smaller classes/functions are simpler to unit test.  

- You can test just the formatting logic  
- You can test just the CSV export logic  
- No need to boot the whole application to test one thing  

---

### 4. Ability to reuse
When responsibilities are split, you can reuse pieces in different contexts.  

- `UserFormatter` can also be used in email templates, not just in export  
- If it were coupled with CSV exporting, you couldn’t reuse it easily  

---

### 5. Supports Teamwork
In a team, two developers can work in parallel:  

- One works on formatting rules  
- Another works on exporting  

No merge conflicts on the same file.

---

### 6. Helps with Scalability & Extensibility
If business needs change (e.g., export to Excel instead of CSV), you only add a new exporter without touching formatting logic.

---

## ✅ In Short
SRP makes your code **cleaner, safer to change, easier to test, and more reusable**.
