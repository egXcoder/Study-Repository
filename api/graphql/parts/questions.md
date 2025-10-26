# Questions


## Why Called Graph?

The “Graph” in GraphQL doesn’t mean you’ll always see a graph UI with nodes and edges like in Neo4j or visualization tools. Instead, it refers to the graph data model behind the API:

Nodes (Objects/Entities): These are the types in your schema (e.g., User, Post, Comment).

Edges (Relationships): The fields that connect one type to another (e.g., a User has many Posts, a Post has many Comments).

When you write a GraphQL query, you’re basically walking the graph of your data: starting from a root type (the Query type) and traversing along fields (edges) to reach connected objects (nodes).


## Is GraphQl Acts like a reverse proxy?

Yes — GraphQL servers often act like a reverse proxy (or better: an “API gateway”)

Here’s how:

- Client defines exactly what it wants
    - The GraphQL query says: “I need a user, their posts, and the comments on those posts.”

- GraphQL server resolves it
    - The GraphQL layer doesn’t store the data itself (unless you want it to).
    - Instead, resolvers go fetch data from underlying sources:
        - A REST API endpoint
        - A database
        - A microservice
        - Even another GraphQL API
- GraphQL merges results into one response
    - The client gets exactly the shape it asked for, in a single round trip.



## Q: what is the common framework language is used to expose graphql and which is common to consume graphql?

- Expose GraphQL (server-side implementation)
- Consume GraphQL (client-side implementation)


### Exposing GraphQL (Server-side)

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


## Consuming GraphQL (Client-side)

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