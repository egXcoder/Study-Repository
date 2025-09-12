# Open–Closed Principle (OCP)

A class/module should be open for extension but closed for modification.

---

## Open For Extenstion  

When you want to add a new behavior, you shouldn't edit existing, tested code — instead, you extend it.

---

## Closed For Modification

You modify a closed class for reasons that are not about adding new behaviour:

- **Fix a bug** → e.g., if `Circle::getArea()` is wrong, you fix it.  
- **Refactor** → improve variable names or clean up the code without changing its behavior.  
- **Improve performance** → optimize an algorithm in a way that doesn’t change what it does. 
- **Evolve existing rules/content** → update details without changing the class’s purpose.  
  - Add a new field to `UserRequest` (it’s still validating requests).  
  - Change PDF text in `PDFExporter` (it’s still generating PDFs).  
  - Add a recipient in `WelcomeEmailer` (it’s still sending welcome emails).  
  - Add a new check to `AdminPolicy` (e.g., require 2FA).  
  - Add more logic in `UserRegisteredListener` (e.g., log an audit trail in addition to sending email).  

---

## 🚀 Why is it Useful?

- Once a class works and is tested, you don’t risk introducing bugs by modifying it every time a new requirement comes up.  (stability)
- You can add new features by adding new classes or extending behavior, not by hacking existing ones.  (extensibility)
- Keeps the codebase cleaner as requirements grow, instead of one God-class with endless `if/else`. (maintainability)

---

## ✅ In Short

OCP makes your codebase **future-proof**.  
Instead of editing code, you can **plug in new behaviors like Lego blocks**.
