# How to Expose a GraphQL API

### Define the Schema (TypeDef)

elements inside type Query .. are called fields and they are the available fields can be queried

separate type are separate objects like type User

ID! this sign of ! means not nullable

Scaler Types
- Int
- Float
- String
- Boolean
- ID .. unique identifier

Tip: you can define your custom scalar types like Date

```graphql

scalar Date

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


### Implement Resolvers

Resolvers are functions that run when someone executes a query/mutation/subscription.


```js

const users = [];

const resolvers = {
  Query: {
    users: () => users,
    user: (_, args) => users.find(u => u.id === args.id),
  },
  User: {
    posts: (parent) => posts.filter(p => p.authorId === parent.id),
  },
  Post: {
    author: (parent) => users.find(u => u.id === parent.authorId),
  },
  Mutation: {
    createUser: (_, { name, email }) => {
      const user = { id: String(users.length + 1), name, email };
      users.push(user);
      pubsub.publish("USER_CREATED", { userCreated: user });
      return user;
    },
    updateUser: (_, { id, name, email }) => {
      const user = users.find(u => u.id === id);
      if (!user) throw new Error("User not found");
      if (name) user.name = name;
      if (email) user.email = email;
      return user;
    },
    deleteUser: (_, { id }) => {
      const index = users.findIndex(u => u.id === id);
      if (index === -1) return false;
      users.splice(index, 1);
      return true;
    },
  },
  Subscription: {
    userCreated: {
      subscribe: (_, __, { pubsub }) => pubsub.asyncIterator(["USER_CREATED"]),
    },
  },
};


```