# Open–Closed Principle (OCP)

A class/module should be open for extension but closed for modification.

- Open For Extenstion: by adding new behavior, don't edit existing, tested code — instead, you extend it.

- Closed For Modification: you modify a closed class to (fix bug, refactor, optimize, evolve existing rule/content)

- Evolve existing rules/content → update details without changing the class’s purpose.  
  - Change PDF text in `PDFExporter` (it’s still generating PDFs).  
  - Add a cc in `WelcomeEmailer` (it’s still sending welcome emails).  
  - Add a new check to `AdminPolicy` (e.g., require 2FA).  
  - Add more logic in `UserRegisteredListener` (e.g., log an audit trail in addition to sending email).  
  - Add a new field to `UserRequest` (it’s still validating requests).  

---

## 🚀 Why is it Useful?

- Once a class works and is tested, you don’t risk introducing bugs by modifying it every time a new requirement comes up.  (stability)
- You can add new features by adding new classes or extending behavior, not by hacking existing ones.  (extensibility)
- Keeps the codebase cleaner as requirements grow, instead of one God-class with endless `if/else`. (maintainability)

---

## ✅ In Short

Instead of editing well tested code, you can plug in new behaviors