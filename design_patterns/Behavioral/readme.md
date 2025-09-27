# Behavioral Design Patterns

define the ways in which objects collaborate and delegate responsibilities

## Why do we need behavioral patterns?

//TODO: Interpreter pattern??

## List
- Chain Of Responsibility: lets you pass a request along a chain of handlers
- Command: turns a request into an object.
- Iterator: provides a standard way to traverse a collection of objects without exposing its internal structure.
- Mediator: make colleagues not send messages to each other directly and instead rely on mediator
- Memento: save and restore its state
- Observer: subscription mechanism to notify multiple objects about events
- State: encapsulate state-based behavior and delegate behavior to its current state
- Strategy: define a family of algorithms, put each of them into a separate class, and make their objects interchangeable.
- Template Method: defines the skeleton (template) of an algorithm in a base class but lets subclasses override certain steps of the algorithm without changing its overall structure.
- Visitor: each visitor is guest expert who knows how have knowledge to do different operations for different classes


## Class Names
- Chain Of Responsbility ... Handler(Process the request or forward it to the next handler) 
- Command ... Command (Implements execute()) + Receiver (object that performs the actual action) + Invoker (Triggers the command)
- Iterator ... Iterator (Provides methods for sequential traversal) + Aggregate / Collection (collection that returns iterator)
- Mediator ... Mediator (central hub for communication) + Colleague (Objects communicate indirectly via the mediator)
- Observer ... Subject/Publisher (notify observers on change) + Observer/Subscriber/Listener (receive update from subject)
- State ... State (implementations for each state) + Context (object which delegate behavior to state classes)
- Strategy ... Strategy (family of algorithms) + Context (object which will use strategies)
- Template Method ...  Template (Abstract, overall algorithm, some steps filled, some steps blank) + Implemenation
- Visitor ... Visitor (guest with knowledge) + Element (object which will be visited)


