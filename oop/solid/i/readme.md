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

- reduce surprises (if class says it follow this contract, it has to respect all parent rules)

- on dependencies, instead of a class depend on another class, it can only depend on the contract that it truely need which make the code loosely coupled (so classes and methods should depend on the things it truely need)


---

## ✅ In Short
- ISP prevents fat interfaces.  
- Keep contracts small and focused.  
- Classes and services in Laravel should only implement what they actually use.  