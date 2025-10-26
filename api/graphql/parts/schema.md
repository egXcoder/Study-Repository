# Schema

## Definitions:

### type : Explicit object like a User, Product, Order, etc.

```graphql

type User {
  id: ID!
  name: String!
  email: String!
}

type Post {
  id: ID!
  title: String!
  author: User!
}

```

### Root definitions: Query, Mutation, and Subscription .. These are special entry points into your API.

```graphql

type Query {
  users: [User!]!
  user(id: ID!): User
}

type Mutation {
  createUser(name: String!, email: String!): User!
  updateUser(id: ID!, name: String, email: String): User!
  deleteUser(id: ID!): Boolean!
}

type Subscription {
  userCreated: User!
}

```

### scalar — For custom primitive types

```graphql

scalar DateTime

```

### enum — For fixed value sets

```graphql

enum OrderStatus {
  PENDING
  SHIPPED
  DELIVERED
  CANCELLED
}

```


## Primitive Scalar types

Types:
- Int
- Float
- String
- Boolean
- ID .. unique identifier

Tip: ID! this sign of ! means not nullable
 
Usage:

```graphql

type Post {
  id: ID!
  title: String!
  content: String!
  likes: Int!
  views: Int!
  rating: Float
  isPublished: Boolean!
}


```