# Liskov Substitution Principle (LSP)

Subclasses shouldn't change the expectations set by the parent.  

👉 If class **B** extends or implements **A**, you should be able to use **B** anywhere **A** is expected **without breaking things**.

---

## 🔧 In Practice
If you have a class `Car`, and a subclass `BMW`, you should be able to use a `BMW` object anywhere your code expects a `Car` object without crash or produce unexpected results set by the parent

---

## 🎯 Why LSP is Useful in Laravel Projects
- ✅ Ensures services or channels can be **swapped without bugs**  
- ✅ Makes subclasses usage reliable so that swapping between them shouldnt cause bugs  
- ✅ Prevents nasty runtime errors where you think a subclass “fits” but it doesn’t  
- ✅ Makes **dependency injection safe**  
- ✅ Improves **testability** — you can mock or swap implementations freely  
