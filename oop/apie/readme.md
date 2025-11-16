# APIE  .. Abstraction, Polymorphism, Inheritance, Encapsulation

## Abstraction: 

be generic as possible .. 

moving from the specific to the general, from the concrete to the abstract. It's finding the common, essential characteristics shared by a group of things and forming a broader category.

- Concrete Instance: Ahmed
- Abstraction Level 1: Web Developer (ignores your name, focuses on your profession)
- Abstraction Level 2: Developer (ignores your web-specific skills, focuses on general coding ability)
- Abstraction Level 3: Person (ignores your profession, focuses on human traits)
- Abstraction Level 4: Human (ignores your personal identity, focuses on the biological species)

we should choose abstraction level which make sense in our software..


## Polymorphism: 

many forms .. 

- one interface and many implementations.. (implemenation)
- one parent class and many subclasses which override the parent (inheritance)
- overloading method with different params (java)
- overriding methods by inhertiance


Notification interface, it can be: 
- EmailNotification
- SMSNotification
- SlackNotification.. 

all send notification in different forms.. you can swap notification way in realtime (flexiblilty), you can add extra notification forms to your code as you grow (scalablility) ..


## Inheritance: 

Reuse Code by creating child classes from parent class .. 

child has to respect parent contract (Liskov substition principle)


## Encapsulation:
Restricting direct access to properties and methods within classes. and only expose what make sense for the outside world ..

example, mobile phone has cpu/audio chips and camera sensors all of it is defined inside mobile phone class but defined as private and the only exposed some behaviours like requestCamera or startApplication .. but how that is done internally the outside world shouldnt able to know and he shouldnt be able to touch it

- always start declaration as private ... 
- make it protected if you think it maybe overrided .. 
- make it public only when you think outside world can use it

its always better to restrict access to the internal data and only allow exposure using getters if you really need outside world to read the data, typically outside world shouldnt change internal data of class, so i highly doubt necessaity of setters.