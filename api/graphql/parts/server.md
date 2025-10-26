# GraphQL Server

## [1]. schema.js

```js

const { gql } = require('apollo-server');

const typeDefs = gql`
  scalar DateTime

  type User {
    id: ID!
    name: String!
    posts: [Post!]!
  }

  type Post {
    id: ID!
    title: String!
    content: String
    author: User!
    createdAt: DateTime!
  }

  type Query {
    users: [User!]!
    posts: [Post!]!
    user(id: ID!): User
  }

  type Mutation {
    createUser(name: String!): User!
    createPost(authorId: ID!, title: String!, content: String): Post!
  }
`;

module.exports = typeDefs;

```

Tip: this gql`` is helpful to find errors on schema syntax in compile time so you can quickly fix it


## [2] . resolvers.js

```js

const { GraphQLScalarType, Kind } = require('graphql');

// In-memory data store
const users = [];
const posts = [];

// Custom DateTime scalar
const DateTime = new GraphQLScalarType({
  name: 'DateTime',
  description: 'ISO-8601 DateTime scalar type',
  serialize(value) {
    return new Date(value).toISOString();
  },
  parseValue(value) {
    return new Date(value);
  },
  parseLiteral(ast) {
    if (ast.kind === Kind.STRING) return new Date(ast.value);
    return null;
  },
});

const resolvers = {
  DateTime,

  Query: {
    users: () => users,
    posts: () => posts,
    user: (_, { id }) => users.find(u => u.id === id),
  },

  Mutation: {
    createUser: (_, { name }) => {
      const user = { id: String(users.length + 1), name };
      users.push(user);
      return user;
    },
    createPost: (_, { authorId, title, content }) => {
      const post = {
        id: String(posts.length + 1),
        title,
        content,
        authorId,
        createdAt: new Date(),
      };
      posts.push(post);
      return post;
    },
  },

  User: {
    posts: (parent) => posts.filter(p => p.authorId === parent.id),
  },

  Post: {
    author: (parent) => users.find(u => u.id === parent.authorId),
  },
};

module.exports = resolvers;


```


## [3] server.js


```js

const { ApolloServer } = require('apollo-server');
const typeDefs = require('./schema');
const resolvers = require('./resolvers');

const server = new ApolloServer({ typeDefs, resolvers });

server.listen().then(({ url }) => {
  console.log(`🚀 Server ready at ${url}`);
});



```