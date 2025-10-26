# Subscriptions

A subscription is a way for the server to push real-time updates to clients. no polling by client ..


it rely on websocket


### Schema

```graphql

type Subscription {
  userCreated: User
}

```

Example

```graphql
subscription {
  userCreated {
    id
    name
    email
  }
}
```

When a new user is added via a mutation, all clients subscribed to userCreated will automatically get data like:

```json

{
  "data": {
    "userCreated": {
      "id": "101",
      "name": "Ahmed",
      "email": "ahmed@example.com"
    }
  }
}


```


listen to subscription using apollo client

```javascript

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
      userCreated {
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