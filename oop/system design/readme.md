# System Design


## Low Level Design
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






## High Level Design vs Low Level Design

| Aspect                | **High-Level Design (HLD)**                                                                          | **Low-Level Design (LLD)**                                                                                                                                      |
| --------------------- | ---------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Purpose**           | Describe the system architecture and how major components interact                                   | Describe internal implementation details of each component                                                                                                      |
| **Focus**             | *What* the system will contain                                                                       | *How* each part will be built                                                                                                                                   |
| **Audience**          | Architects, tech leads, stakeholders                                                                 | Developers who implement the code                                                                                                                               |
| **Abstraction level** | Broad, conceptual                                                                                    | Detailed, technical                                                                                                                                             |
| **Details include**   | Modules, services, API boundaries, data flow, external integrations                                  | Classes, functions, database tables, algorithms, data structures                                                                                                |
| **Output format**     | Architecture diagrams, component diagrams, technology choices                                        | Class diagrams, sequence diagrams, DB schema, pseudo-code                                                                                                       |
| **Example**           | “We will build a payment service that communicates with Adyen API and stores transactions in MySQL.” | “PaymentService class will have `authorizeCard()`, `capturePayment()`, and `refund()` methods. Table `payments` will have fields: id, user_id, amount, status…” |
| **Scope**             | Entire system                                                                                        | Individual components                                                                                                                                           |
| **Timing in project** | During system design / planning                                                                      | Before actual coding begins                                                                                                                                     |
| **Changes frequency** | Rare — architecture is stable                                                                        | Frequent — evolves during development                                                                                                                           |
| **Dependencies**      | Third-party services, load balancers, queues, microservices                                          | Internal libraries, helper classes, SQL queries                                                                                                                 |
| **Security focus**    | Security model, data privacy, authentication strategy                                                | Input validation, encryption routines, hashing algorithms                                                                                                       |
| **Testing impact**    | Guides integration testing & performance testing                                                     | Guides unit testing                                                                                                                                             |

