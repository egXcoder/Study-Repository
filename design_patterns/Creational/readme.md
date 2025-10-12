# Creational Design Patterns

Objects Instantiation have logic or expensive


## List
- Builder: construction of a complex object
- Simple Factory: create one product using one factory
- Factory Method: create one product from many factories, and each factory is responsibile to create one type of product
- Abstract Factory: create multiple products from many factories, and each factory is responsibile to create family of similar products
- Prototype: cloning an existing instance
- singleton: Ensures a class has only one instance and provides global access to it (Database Connection or Logging to file)


## Class Names
- Builder ... Builder (declare steps to build the product) + Product + Director (have templates of complex objects can be built by builder)
- Simple Factory  ... Factory + Product
- Factory Method  ... Factory + Product
- Abstract Factory .. Factory + Product
- Prototype ... Prototype (cloneable object) + prototype registry (Stores and retrieves prototype instances)
- Singleton ... Singleton