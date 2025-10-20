# GraphQL

GraphQL is a query language for APIs (created by Facebook, now open-source).

It lets clients ask the server exactly for the data they need — nothing more, nothing less. No more overfetching (getting 50 fields but using 2).


## Key Ideas

Single endpoint GraphQL: /graphql it can do everything depend on the request body


Consume GraphQL
- Query → fetch data. (discussed in separate page)
- Mutation → modify data (like POST/PUT in REST) (discussed in separate page)
- Subscription → real-time updates (websockets) (discussed in separate page)

Build your graphql server (discussed in separate page)





Type vs field

User → refers to a GraphQL type (object type, interface, etc.) and its (Capitalized)

user → refers to a field on a type or on the root Query object and its (lowercase)

Tip: ID! .. means not nullable

```graphql

type User {          # GraphQL type → always capitalized by convention
  id: ID!
  name: String
  email: String
}

type Query {
  user(id: ID!): User   # user = field on Query, returns type User
}

```

### Q: what is the common framework language is used to expose graphql and which is common to consume graphql?

- Expose GraphQL (server-side implementation)
- Consume GraphQL (client-side implementation)


#### Exposing GraphQL (Server-side)

You need a server framework that defines the schema, resolvers, and executes queries.

The most common by language:

- JavaScript / TypeScript
    - Apollo Server → probably the most popular
    - GraphQL Yoga
    - Express-GraphQL (simple, minimal)

-Python
    - Graphene
    - Ariadne
    - Strawberry

-Java
    - graphql-java (basis for Spring Boot GraphQL)
    - Spring for GraphQL

-PHP
    - lighthouse-php for Laravel
    - graphql-php

- Ruby
    - graphql-ruby

- Go
    - gqlgen

Most common in industry today:
👉 Apollo Server (Node.js) and Spring Boot GraphQL (Java)


#### Consuming GraphQL (Client-side)

Clients send queries/mutations to the GraphQL server.

- JavaScript (frontend)
    - Apollo Client → most popular React integration
    - Relay (from Facebook, used in Facebook apps)
    - urql (lightweight alternative to Apollo)

- Mobile (Android/iOS)
    - Apollo Kotlin
    - (Android)
    - Apollo iOS
    - Relay also has mobile integrations

- Other languages (using HTTP requests directly):

- Python: requests or gql

- PHP: Guzzle

- Go: graphql-go

Most common client today:
👉 Apollo Client (React)