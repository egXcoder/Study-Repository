# SRP – Single Responsibility Principle

> **A class should have only one reason to change.**

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
