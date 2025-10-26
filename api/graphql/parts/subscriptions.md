# Subscriptions

A subscription is a way for the server to push real-time updates to clients. no polling by client ..

it rely on websocket

Graphql have their own in-memory pubsub that you can use to publish and listen to streams, however you can use different pubsub like redis or whatever


## Server

```js

// resolvers.js
const { PubSub } = require('graphql-subscriptions');

const users = [];

module.exports = (pubsub) => ({
  Mutation: {
    createUser: (_, { name, email }) => {
      const user = { id: String(users.length + 1), name, email };
      users.push(user);

      // Notify subscribers
      pubsub.publish('USER_CREATED', { userCreated: user });

      return user;
    },
  },
  Subscription: {
    userCreated: {
      // Every subscription listens on USER_CREATED topic
      subscribe: () => pubsub.asyncIterator(['USER_CREATED']),
    },
  },
});

```


```js

// server.js
const { createServer } = require('http');
const { ApolloServer } = require('apollo-server');
const { makeExecutableSchema } = require('@graphql-tools/schema');
const { WebSocketServer } = require('ws');
const { useServer } = require('graphql-ws/lib/use/ws');
const { PubSub } = require('graphql-subscriptions');

const typeDefs = require('./schema');
const createResolvers = require('./resolvers');

const pubsub = new PubSub();
const schema = makeExecutableSchema({
  typeDefs,
  resolvers: createResolvers(pubsub),
});

const server = new ApolloServer({ schema });
const httpServer = createServer();

async function start() {
  await server.start();
  server.applyMiddleware({ app: httpServer });

  // WebSocket server
  const wsServer = new WebSocketServer({
    server: httpServer,
    path: '/graphql',
  });

  useServer({ schema }, wsServer);

  httpServer.listen(4000, () => {
    console.log('🚀 GraphQL running at http://localhost:4000/graphql');
    console.log('💬 Subscriptions over ws://localhost:4000/graphql');
  });
}

start();

```


## Client

```js

import { WebSocketLink } from "@apollo/client/link/ws";
import { ApolloClient, InMemoryCache, gql } from "@apollo/client";

// connect via WebSocket
const wsLink = new WebSocketLink({
  uri: 'ws://localhost:4000/graphql',
  options: { reconnect: true }
});

const client = new ApolloClient({
  link: wsLink,
  cache: new InMemoryCache()
});

// subscribe
client.subscribe({
  query: gql`
    subscription {
      USER_CREATED {
        id
        name
        email
      }
    }
  `
}).subscribe({
  next(data) {
    console.log("New user created:", data);
  },
  error(err) { console.error("Subscription error:", err); }
});

```