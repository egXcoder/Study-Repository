# GraphQL

GraphQL is a query language for APIs (created by Facebook, now open-source).

## Key Ideas

Clients ask the server exactly for the data they need — nothing more, nothing less. 

No more overfetching (getting 50 fields but using 2).


## Usage

Single endpoint GraphQL: /graphql it can do everything depend on the request body

Building GrahpQl
- Schema (typeDef) [explained here](./parts/schema.md)
- Resolver [explained here](./parts/resolvers.md)
- GraphQL Server [explained here](./parts/server.md)


Use GraphQL
- Query → fetch data. [explained here](./parts/query.md)
- Mutation → modify data (like POST/PUT in REST) [explained here](./parts/mutations.md)
- Subscription → real-time updates (websockets) [explained here](./parts/subscriptions.md)

Build your graphql server (discussed in separate page)

## Questions

[explained here](./parts/questions.md)





## Q: Type vs field

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

