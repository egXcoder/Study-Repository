# Query

A query is ask for data


### Intro

- anonymous query (no name)
```graphql
query {
  user {
    id
    name
    email
  }
}
```

- named query

Naming is useful for debugging — error messages and logs will reference GetUser.
Required if you want to persist queries or use them in Apollo Client caching, because the name identifies the query.
```graphql
query GetUser{
  user {
    id
    name
    email
  }
}
```
- shorthand syntax — same as query { ... }
```graphql
{
  user {
    id
    name
    email
  }
}
```

Response 

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

### Multiple Fields And Arguments
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


### Aliases

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

### More than one operation

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

### Fragments

A fragment in GraphQL is a reusable piece of a query. and must always reference a type, not a field

🔹 Why use fragments?
- DRY (Don’t Repeat Yourself) .. Instead of repeating the same fields in multiple queries, define them once.
- Consistency  .. Ensures queries always fetch the same fields.
- Maintainability .. Update the fragment once → all queries using it automatically get the change.

```graphql

# Use it in a query
query GetUsers {
  users {
    ...UserFields
  }
}

# Use it in another query
query GetCurrentUser {
  currentUser {
    ...UserFields
  }
}

# Define a fragment
fragment UserFields on User {
  id
  name
  email
}

# Nesting Fragment
fragment PostFields on Post {
  id
  title
  author {
    ...UserFields
  }
}

```