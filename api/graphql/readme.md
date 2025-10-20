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


### When to use Graphql?

it is there to solve the problem of The client downloads more data than it needs

| Feature               | REST API                                | GraphQL                                       |
|-----------------------|-----------------------------------------|-----------------------------------------------|
| Data Fetching         | Multiple endpoints, fixed responses     | Single endpoint, client-defined responses      |
| Over/Under-fetching   | Common problem                          | Solved by design                              |
| Caching               | Simple (HTTP caching)                   | Complex (requires custom setup)               |
| Learning Curve        | Lower (builds on HTTP)                  | Higher (new concepts, tooling)                |
| File Uploads          | Straightforward                         | Possible, but less standard                   |
| Real-time             | Typically requires WebSockets/SSE       | Native via Subscriptions                      |
| API Evolution         | Versioning endpoints (e.g., /v2/)       | Deprecating fields, additive changes          |
| Best For              | Simple, cacheable, resource-driven APIs | Complex systems, fast-moving frontends, mobile-heavy apps |


### Why Called Graph?

The “Graph” in GraphQL doesn’t mean you’ll always see a graph UI with nodes and edges like in Neo4j or visualization tools. Instead, it refers to the graph data model behind the API:

Nodes (Objects/Entities):
These are the types in your schema (e.g., User, Post, Comment).

Edges (Relationships):
The fields that connect one type to another (e.g., a User has many Posts, a Post has many Comments).

When you write a GraphQL query, you’re basically walking the graph of your data: starting from a root type (the Query type) and traversing along fields (edges) to reach connected objects (nodes).


### Is GraphQl Acts like a reverse proxy?

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

### Q: Type vs field

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