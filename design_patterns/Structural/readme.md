# Structural Design Patterns

They deal with how to assemble objects and classes into larger structures

## Why do we need structural patterns?



## List
- Adapter: allows two incompatible interfaces to work together by creating a "middleman" (the adapter).
- Bridge: Decouples abstraction from implementation, so they can evolve independently.
- Composite: lets you compose objects into tree structures and then work with these structures as if they were individual objects.
- Decorator: attaches additional responsibilities to an object dynamically
- Facade: provides a unified, simplified interface to a complex system.
- FlyWeight: when huge number of similar objects and you want to save memory by sharing common state instead of duplicating it.
- Proxy:  Controls access to the real object (e.g., lazy loading, security, remote access).


## Class Names
- Adapter: Adapter (the wrapper) + Adaptee (legacy itself) + Target (interface expected by client)
- Bridge .... Abstraction (client will use) + implementor (low level interface)
- Composite ... Leaf(file) + Composite (directory) + Component (filesystem)
- Decorator ... Decorator (the wrapper) + Component (original object to be extended)
- Facade .... Facade (simple interface) + SubSystem (complex classes)
- FlyWight... FlyWeight + FlyWeightFactory
- Proxy... Proxy (the wrapper) + Subject (original object)