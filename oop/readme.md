# OOP .. Object Oriented Programming

## Procedural vs OOP

To build a cake
Procedural way is to do in sequence of steps
//Step 1
//Step 2 
// Step 3
Return cake;

Oop way:
Declare mixer
Declare oven
Declare pan
Use the three objects to build a cake


Procedural way is quicker and straight forward, but oop way is far better in the future because of code reusability, using the oop way i can build pie using pan and mixer and oven


## Cohesion vs Coupling

Cohesion → how focused a class is internally.
Coupling → how much modules rely on each other externally.
👉 Good design aims for high cohesion (each class does one thing well) and low coupling (class don’t heavily depend on each other, they depend to contract/interface not concrete implementation or pass value as paramter instead of passing whole class).

notice: high cohesion doesnt automatically lead to loose couple, they are two different dimensions and you should fight for both separately


## Interfaces

- Interfaces represent contract 
- interfaces must have methods as public. they can't have private/or protected methods