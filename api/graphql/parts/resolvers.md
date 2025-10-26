# Resolvers

Resolvers are functions which is graphql entire logic
- Query Resolver: how to fetch variant data
- Mutation Resolver: how to do mutations
- Subscription Resolver: how to subscribe to events


```js

const users = [];

const resolvers = {
  Query: {
    users: () => users,
    user: (_, args) => users.find(u => u.id === args.id),
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

Tip: this _ reference to parent and by convention its put as _ to mean ignore


## Computed Property

If a field’s value is not directly accessible (e.g. computed, nested, or external), such as user.posts has to be calculated. so we create a resolver for User.posts


```js

const users = [];

const resolvers = {
  Query: {
    users: () => users,
    user: (_, args) => users.find(u => u.id === args.id),
  },
  User: {
    posts: (parent) => posts.filter(p => p.authorId === parent.id),
  }
};


```

## Custom Scalars

if you want to add a custom scalar

```js

const { GraphQLScalarType, Kind } = require('graphql');

const DateTime = new GraphQLScalarType({
  name: 'DateTime',
  description: 'ISO-8601 compliant DateTime scalar',
  
  // When sending data to the client (serialization)
  serialize(value) {
    // Ensure it's a valid date and return as ISO string
    return new Date(value).toISOString();
  },
  
  // When receiving variables from the client
  parseValue(value) {
    return new Date(value); // Convert input string to Date
  },
  
  // When parsing hardcoded literals in GraphQL query
  parseLiteral(ast) {
    if (ast.kind === Kind.STRING) {
      return new Date(ast.value);
    }
    return null; // Invalid input
  }
});

const resolvers = {
  DateTime,
}

```