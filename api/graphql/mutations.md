# Mutations

Mutations → used to change data (write/update/delete).

When you call a mutation, you can also ask for the fields of the changed object that you want back.

### Schema

```graphql

type User {
  id: ID!
  name: String!
  email: String!
}

type Mutation {
  createUser(name: String!, email: String!): User
  updateUser(id: ID!, name: String, email: String): User
  deleteUser(id: ID!): Boolean
}


```

### Create User

```graphql

mutation {
  createUser(name: "Ahmed", email: "ahmed@example.com") {
    id
    name
    email
  }

  updateUser(id: "123", name: "Ahmed Ibrahim") {
    id
    name
    email
  }

  deleteUser(id: "123")
}


```
```json

{
  "data": {
    "createUser": {
      "id": "101",
      "name": "Ahmed",
      "email": "ahmed@example.com"
    },
    "updateUser": {
      "id": "101",
      "name": "Ahmed Ibrahim",
      "email": "ahmed@example.com"
    },
    "deleteUser": true
  }
}

```

```javascript

fetch('/graphql', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    query: `
      mutation {
        createUser(name: "Ahmed", email: "ahmed@example.com") {
          id
          name
          email
        }

        updateUser(id: "123", name: "Ahmed Ibrahim") {
          id
          name
          email
        }

        deleteUser(id: "123")
      }
    `
  }),
})
.then(res => res.json())
.then(data => console.log('GraphQL response:', data))
.catch(err => console.error('Error:', err));



// or


fetch('/graphql', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    query: `
      mutation ($name: String!, $email: String!, $id: ID!, $newName: String!) {
        createUser(name: $name, email: $email) {
          id
          name
          email
        }

        updateUser(id: $id, name: $newName) {
          id
          name
          email
        }

        deleteUser(id: $id)
      }
    `,
    variables: {
      name: "Ahmed",
      email: "ahmed@example.com",
      id: "123",
      newName: "Ahmed Ibrahim"
    }
  }),
})
.then(res => res.json())
.then(data => console.log('GraphQL response:', data))
.catch(err => console.error('Error:', err));

```

### Named Mutations

```graphql

mutation CreateUserOp {
  createUser(name: "Ali", email: "ali@example.com") {
    id
    name
  }
}

mutation UpdateUserOp {
  updateUser(id: "101", name: "Ahmed Updated") {
    id
    name
  }
}


```

```javascript

fetch('/graphql', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    query: `
      mutation CreateUserOp {
        createUser(name: "Ali", email: "ali@example.com") {
          id
          name
        }
      }
      
      mutation UpdateUserOp {
        updateUser(id: "101", name: "Ahmed Updated") {
          id
          name
        }
      }
    `,
    operationName: "CreateUserOp"   // <-- choose which one to run
  })
})
.then(res => res.json())
.then(data => console.log(data));


```

### Error Happening

error is showing in response.. unless server crashes then it will return 500

```graphql

mutation {
  createUser(name: "Ali", email: "ali@example.com") {
    id
    name
  }

  updateUser(id: "9999", name: "Ahmed Ibrahim") {
    id
    name
  }

  deleteUser(id: "123")
}

```

```json

{
  "data": {
    "createUser": {
      "id": "101",
      "name": "Ali"
    },
    "updateUser": null,
    "deleteUser": true
  },
  "errors": [
    {
      "message": "User with id 9999 not found",
      "locations": [{ "line": 6, "column": 3 }],
      "path": ["updateUser"],
      "extensions": {
        "code": "USER_NOT_FOUND"
      }
    }
  ]
}

```