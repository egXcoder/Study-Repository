# Open–Closed Principle (OCP)

A class/module should be **open for extension** but **closed for modification**.

**Meaning:**  
When you want to add a new behavior, you **shouldn't edit existing, tested code** — instead, you **extend it**.

---

## 🚀 Why is it Useful?

- **Stability** → Once a class works and is tested, you don’t risk introducing bugs by modifying it every time a new requirement comes up.  
- **Extensibility** → You can add new features by adding new classes or extending behavior, not by hacking existing ones.  
- **Scalability** → Keeps the codebase cleaner as requirements grow, instead of one God-class with endless `if/else`.

---

## ✨ So, When Do You Modify a "Closed" Class?

You modify it for reasons that are **not about adding new features**:

- **Fix a bug** → e.g., if `Circle::getArea()` is wrong, you fix it.  
- **Refactor** → improve variable names or clean up the code without changing its behavior.  
- **Improve performance** → optimize an algorithm in a way that doesn’t change what it does.  

---

## ✅ In Short

OCP makes your codebase **future-proof**.  
Instead of editing code, you can **plug in new behaviors like Lego blocks**.
