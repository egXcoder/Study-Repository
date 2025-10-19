# GraphQL

GraphQL is a query language for APIs (created by Facebook, now open-source).


It lets clients ask the server exactly for the data they need — nothing more, nothing less.


## Key Ideas

### Single endpoint

GraphQL: /graphql → send one query that says:


```graphql
{
  user(id: 123) {
    name
    posts {
      title
      comments {
        text
      }
    }
  }
}

```

### Client controls the shape of the data

No more overfetching (getting 50 fields but using 2).

### Strong typing

GraphQL has a schema that defines types, queries, and mutations.

```graphql

type User {
  id: ID!
  name: String!
  posts: [Post!]
}

```

### Operations

Query → fetch data.

Mutation → modify data (like POST/PUT in REST).

Subscription → real-time updates (websockets).


## Query

A query in GraphQL is the way clients ask for data (like SELECT in SQL, or GET in REST).

Tip: query keyword .. tells GraphQL this is a read operation. (You can omit it and still works)


Simple Example
```graphql
query {
  user {
    id
    name
    email
  }
}
```
```json
{
  "data": {
    "user": {
      "id": "1",
      "name": "Ahmed",
      "email": "ahmed@example.com"
    }
  }
}

```

Multiple Fields And Arguments
```graphql
{
  user(id: 1) {
    name
  }
  posts(limit: 5, orderBy: "date") {
    id
    title
    comments {
        id
        content
    }
  }
}

```
```json
{
  "data": {
    "user": {
      "name": "Alice Johnson"
    },
    "posts": [
      {
        "id": "201",
        "title": "GraphQL for Beginners",
        "comments": [
          { "id": "5001", "content": "This helped me a lot, thanks!" },
          { "id": "5002", "content": "Clear and simple explanation." }
        ]
      },
      {
        "id": "202",
        "title": "Advanced GraphQL Queries",
        "comments": [
          { "id": "5003", "content": "Love the fragments example!" }
        ]
      },
      {
        "id": "203",
        "title": "REST vs GraphQL",
        "comments": []
      },
      {
        "id": "204",
        "title": "Building APIs with GraphQL",
        "comments": [
          { "id": "5004", "content": "Exactly what I needed!" },
          { "id": "5005", "content": "Thanks for sharing." }
        ]
      },
      {
        "id": "205",
        "title": "GraphQL Best Practices",
        "comments": []
      }
    ]
  }
}
```


Aliases

```graphql

query{
  firstUser: user(id: 1) { name }
  secondUser: user(id: 2) { name }
}

```

```json
{
  "data": {
    "firstUser": { "name": "Ahmed" },
    "secondUser": { "name": "Sara" }
  }
}
```

Query with variables

```graphql

query GetPosts($limit: Int!) {
  posts(limit: $limit) {
    id
    title
  }
}

```

```javascript

fetch("https://mygraphqlapi.com/graphql", {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({
    query: `
      query GetPosts($limit: Int!) {
        posts(limit: $limit) {
          id
          title
        }
      }
    `,
    variables: { limit: 5 },
    operationName: "GetPosts"
  })
})
  .then(res => res.json())
  .then(data => console.log(data));

```

More than one operation

```graphql
query GetPosts($limit: Int!) {
  posts(limit: $limit) {
    id
    title
  }
}

query GetUser($userId: ID!) {
  user(id: $userId) {
    name
    email
  }
}

```
```javascript
fetch("https://mygraphqlapi.com/graphql", {
  method: "POST",
  headers: { "Content-Type": "application/json" },
  body: JSON.stringify({
    query: `
      query GetPosts($limit: Int!) {
            posts(limit: $limit) {
                id
                title
            }
        }

        query GetUser($userId: ID!) {
            user(id: $userId) {
                name
                email
            }
        }
    `,
    variables: { "userId": "123" },
    operationName: "GetUser"
  })
})
  .then(res => res.json())
  .then(data => console.log(data));

```

Fragments

fragments must always reference a type, not a field



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