# Creational Design Patterns

They deal with object creation mechanisms — i.e., how objects are instantiated.

## Why do we need creational patterns?

- Sometimes object creation involves logic, not just new.
- they will help you to move construction logic to separate place

- Simple Factory: one centralized factory with static method decides which class to instantiate.
- Factory Method: many factories, and each subclass (factory) is responsibile to create one type of product
- Abstract Factory: decide which factory to instantiate from multiple available factories
- Builder: construction of a complex object
- Prototype: cloning an existing instance
- singleton: Ensures a class has only one instance and provides global access to it.