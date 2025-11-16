# Domain Modeling


1. State The Problem
2. Use Cases (Use case diagram)
3. Identify Core Entities (nouns) 
4. Model Relationships Between Entities  (ER Diagram for DB perspective)
5. Define Responsibilities (UML Class Diagram)
6. Define Events
7. Define System Flows Happy Paths, then edge cases (UML Sequence Diagrams / Activity Diagram)
8. Refactor to deal with happy paths and edge cases


Tip: Use Case Diagram = Which Actor Use Which Use Case


### Examples:
#### [Parking Lot Problem](./parking_lot/readme.md)

### Levels of Design

#### High-Level Design (HLD)
- Focuses on major components and their responsibilities.
- Example: Services, Repositories, Models, Events in your Parking Lot system.
- Goal: Understand how things interact, what classes exist, which patterns to use.
- You don’t need every detail here. Just enough to have a blueprint.


#### Low-Level Design (LLD)

- Focuses on class methods, relationships, and workflows.
- Example: TicketService calculates duration using PricingModel strategy, EntryService assigns spot, PaymentService handles multiple payment methods.
- Here you can think about edge cases, events, concurrency issues, and error handling.
- You don’t need to exhaustively cover every single edge case yet. Just the ones most likely or most critical.


#### Implementation

Start coding based on your HLD or LLD, and expect refactoring.

you dont have to go to every aspect and design all lld with all edge cases to start the project, however The more time you can allocate to design the better and effective your code will be.

practically, HLD and half of lld is sufficient for efficient coding