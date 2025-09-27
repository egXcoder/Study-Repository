# Creational Design Patterns

They deal with object creation mechanisms — i.e., how objects are instantiated.

## Why do we need creational patterns?

- Sometimes object creation involves logic, not just new.
- they will help you to move construction logic to separate place

## List
- Simple Factory: one factory responsible to create a product from available products
- Factory Method: many factories, and each factory is responsibile to create one type of product
- Abstract Factory: many factories, and each factory is responsibile to create family of similar products
- Builder: construction of a complex object
- Prototype: cloning an existing instance
- singleton: Ensures a class has only one instance and provides global access to it.


## Class Names
- Simple Factory  ... Factory + Product
- Factory Method  ... Factory + Product
- Abstract Factory .. Factory + Product
- Builder ... Builder (declare steps to build the product) + Director (have templates of complex objects can be built by builder)
- Prototype ... Prototype (cloneable object) + prototype registry (Stores and retrieves prototype instances and clone them on fly while retrive)
- Singleton ... Singleton