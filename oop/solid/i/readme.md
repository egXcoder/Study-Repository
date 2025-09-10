# Interface Segregation Principle (ISP)

The Interface Segregation Principle (ISP) is about interfaces, not inheritance. inheritance goes for lsp 

If you have a big interface and end up with implementations that don’t use some of its methods, that’s a sign the interface needs to be split into smaller, more focused interfaces. This usually means the contract was designed incorrectly.  

`Clients should not be forced to depend on methods they don’t use.`

---

## ✨ Key Ideas
- Contracts should stay small and specific
- It’s better to have many small, focused interfaces than one big fat interface.  
- Classes should only implement the contracts that make sense for them.  

---

## Why Contracts Shouldn't be fat?

- small and focused is more effective

- Slows down evolution
If you add a method to a fat interface, suddenly all implementations break until they add that method.
With small contracts, you only affect the classes that actually care.

- Hurts readability
A fat interface with 10–20 methods is harder to understand than 3–4 focused ones.
Small, cohesive contracts make relationships clearer.

- Confuse Code
If an interface has methods that don’t apply to every implementation, classes will end up throwing exceptions, leaving methods empty, or adding dummy logic. Example: SmsNotification shouldn’t have to implement sendEmail().


---

## ✅ In Short
- ISP prevents fat interfaces.  
- Keep contracts small and focused.  
- Classes and services in Laravel should only implement what they actually use.  