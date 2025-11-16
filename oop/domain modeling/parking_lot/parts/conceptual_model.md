# Design

### Extract Entities

Read Through the problem/requirements and try to find the Core Entities

Entities Are:
- Parking Lot
- Entry
- Exit
- Floor
- Spot
- SpotType (e.g., compact, large, handicapped, motorcycle)
- Customer
- Vehicle
- Parking Ticket
- Payment
- PaymentMethod (cash, card, coupon)
- PaidBy [Exit Panel or Human Agent]
- Pricing Model calculate based on hour, spot type, or vehicle type


### Model Relationships

rough diagram of boxes (entities) and arrows represent relationship and it can be very simple like uses or assigned etc..

- Parking Lot have multiple Entry and Multiple Exit
- Parking Lot have multiple Floor
- Floor have multiple Spot
- A Spot have one type
- A customer can have multiple vehicles
- Vehicle is assigned to a Spot while parked
- A Parking Ticket track Vehicle, Spot, Entry, Duration
- A Parking Ticket must have a payment
- A payment need to have a method and PaidBy
- Parking Ticket calculates amount using PricingModel


### Define Responsibilities
- ParkingLotService
    - Track Spots Availability
    - Get Available Spot
- EntryService
    - issue a parking ticket for a customer
    - Amend Spot on issued parking ticket if required 
- ExitService
    - Validate A ParkingTicket and calculate Duration
    - Pay A Parking Ticket for a customer
- CustomerRepository
    - Find A Customer Using His Phone Number
    - Create A new Customer 
- VehicleRepositoy
    - Find A vehicle With its pallet number
    - Create A vehicle For A customer
- Ticket Service
    - calaculate Duration For A ticket
    - calculate Price Using PricingModel
- PaymentService
    - pay With a Payment Method
- Payment Method
    - strategies for Cash,Card,Coupon
- PricingModel
    - strategies for pricing models

### Identify Events
- TicketIssued
- SpotUpdated
- TicketPaid
- VehicleExited
- PaymentFailed

### Define System Flows

#### Entry Flow
- Vehicle arrives at entrance.
- Gate asks ParkingLot for an available spot for this vehicle type.
- Assign spot and issue Ticket.
- Mark spot as occupied.


#### Exit Flow
- Vehicle arrives at exit.
- Validate Ticket and calculate parking duration.
- Calculate payment using Payment class and pricing rules.
- Process payment (cash, card, coupon).
- Release spot.


#### Edge Flows
- lot is full
    - Options: queue, redirect, allow parking in another spot type (if allowed).
- Lost ticket:
    - Customer cannot present a ticket → system should handle lost ticket fee.
- Ticket expiry / invalid:
    - Customer tries to use an old ticket after exit time window.
- Vehicle arrives and a ticket is issued, but now he wants to leave straight away within 5 minutes
    - if ticket duration is less than 10 minutes then free charge
- Issue on the system, two vehicles assigned to same spot
    - There should be ability to amend a spot on parking ticket after issue if required



### Design Patterns That Apply
- Strategy Pattern → For flexible pricing models.
- Factory Pattern → To create vehicles, spots, or tickets dynamically.
- Singleton → For ParkingLot instance if it’s global.
- Observer Pattern → To notify gates or displays when spots are filled or freed.
- Command Pattern → Could be used for Payment actions (process, refund).


### Extensibility & SOLID Principles
- Adding new vehicle types → just create a new Vehicle subclass.
- Adding new spot types → new ParkingSpot subclass.
- Adding new payment methods → implement PaymentMethod interface.
- Pricing rules can be changed without modifying core classes (Strategy).