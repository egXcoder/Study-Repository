# Expose Modularization


## Q: my concern is this.. you have added all application logic into one object.. like all queries and all mutations into one giant resolver?


### Instead of big schema file, modularize them

```graphql
#graphql/schemas/user.graphql
type User {
  id: ID!
  name: String!
  email: String!
}

extend type Query {
  users: [User!]!
  user(id: ID!): User
}

extend type Mutation {
  createUser(name: String!, email: String!): User!
}


#graphql/schemas/post.graphql
type Post {
  id: ID!
  title: String!
  content: String!
  author: User!
}

extend type Query {
  posts: [Post!]!
  post(id: ID!): Post
}

extend type Mutation {
  createPost(title: String!, content: String!, authorId: ID!): Post!
}

# Add relationship field to User
extend type User {
  posts: [Post!]!
}

```


### Instead of one big resolver object, break them down into separate files:

```js
#graphql/resolvers/user.js

const users = [
  { id: "1", name: "Ahmed", email: "ahmed@example.com" },
  { id: "2", name: "Sara", email: "sara@example.com" },
];

module.exports = {
  Query: {
    users: () => users,
    user: (_, { id }) => users.find(u => u.id === id),
  },
  Mutation: {
    createUser: (_, { name, email }) => {
      const newUser = { id: String(users.length + 1), name, email };
      users.push(newUser);
      return newUser;
    },
  },
  User: {
    posts: (user, _, { posts }) => posts.filter(p => p.authorId === user.id),
  },
};


# graphql/resolvers/post.js
const posts = [
  { id: "1", title: "Hello", content: "World", authorId: "1" },
  { id: "2", title: "GraphQL", content: "Is awesome", authorId: "2" },
];

module.exports = {
  Query: {
    posts: () => posts,
    post: (_, { id }) => posts.find(p => p.id === id),
  },
  Mutation: {
    createPost: (_, { title, content, authorId }) => {
      const newPost = { id: String(posts.length + 1), title, content, authorId };
      posts.push(newPost);
      return newPost;
    },
  },
  Post: {
    author: (post, _, { users }) => users.find(u => u.id === post.authorId),
  },
};


```


Merging

```js

const { makeExecutableSchema } = require('@graphql-tools/schema');
const { loadFilesSync, mergeTypeDefs, mergeResolvers } = require('@graphql-tools/load-files');
const path = require('path');

// Load and merge all .graphql schemas
const typeDefs = mergeTypeDefs(loadFilesSync(path.join(__dirname, './schemas/**/*.graphql')));

// Load and merge all resolvers
const resolvers = mergeResolvers(loadFilesSync(path.join(__dirname, './resolvers/**/*.js')));

const schema = makeExecutableSchema({
  typeDefs,
  resolvers,
});

module.exports = schema;


```

Server

```js

const { ApolloServer } = require('@apollo/server');
const { startStandaloneServer } = require('@apollo/server/standalone');
const schema = require('./graphql');

const users = [];
const posts = [];

const server = new ApolloServer({
  schema,
});

startStandaloneServer(server, {
  context: async () => ({ users, posts }), // pass data sources to resolvers
  listen: { port: 4000 },
}).then(({ url }) => {
  console.log(`🚀 Server ready at ${url}`);
});

```


Tip:
- In schema Define types once, extend anywhere.
- The schema builder merges everything — order doesn’t matter.
- If you accidentally define the same type twice, GraphQL errors out.