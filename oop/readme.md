# OOP .. Object Oriented Programming

## Procedural vs OOP
```text
To build a cake
Procedural way is to do in sequence of steps
//Step 1
//Step 2 
// Step 3
Return cake;

OOP way:
Declare mixer
Declare oven
Declare pan
Use the three objects to build a cake
```

Procedural way is quicker and straight forward, but oop way is far better in the future because of code reusability, using the oop way i can build more intersting things using pan and mixer and oven. however if oop way is designed bad, it will cause alot of confusion and problems for the software


## [APIE](./apie/readme.md) 
its considered the foundation concepts in oop (Abstraction .. Polymorphism .. Inheritance .. Encapsulation)

## Interfaces
- Interfaces represent contract 
- interfaces must have methods as public. they can't have private/or protected methods


## SOLID
These are 5 guidelines for designing maintainable OOP systems:
- Single Responsibility.. A class should have only one reason to change [S](./solid/s/readme.md)
- Open/Closed Principle.. open for extension but closed for modification [O](./solid/o/readme.md)
- Liskov Substitution.. Subclasses can substitute their base classes without breaking the program. [L](./solid/l/readme.md)
- Interface Segregation.. Clients should not be forced to depend on methods they don’t use. [I](./solid/i/readme.md)
- Dependency Inversion.. High-level modules shouldn’t depend on low-level modules; both should depend on interfaces/contracts [D](./solid/d/readme.md)


## Cohesion vs Coupling
- [Cohesion](./cohesion/readme.md) → how focused a class is internally.
- [Coupling](./coupling/readme.md) → how much modules rely on each other externally.
- 👉 Good design aims for high cohesion (each class does one thing well) and low coupling (class don’t heavily depend on each other, they depend to contract/interface not concrete implementation or pass value as paramter instead of passing whole class).

Tip: high cohesion doesnt automatically lead to loose couple, they are two different dimensions and you should fight for both separately


## [Composition Over Inheritance](./composite%20over%20inheritance/readme.md)

oop encourage modularity. hence composition. unless inheritance is short then its fine but if to grow then have to refactor it to composition.


## [Identity And State](./identity%20and%20state/readme.md)
- identity is who the object is (unique reference, doesn’t change)
- state is what the object currently knows/contains (changes over time).
- Behavior often depends on state

## [Value Object](./identity%20and%20state/value_object.md)
- value object is defined by its value
- its used to have more control on meanining, rules and gurantees
- value object should be immutable to avoid bugs
 

## [Furps](./furps/readme.md)
classify software quality:
- F .. functionality
- U .. usability (user experience)
- R .. Reliability (doesnt break)
- P .. performance 
- S .. supportability


## [Domain Modeling](./domain%20modeling/readme.md)