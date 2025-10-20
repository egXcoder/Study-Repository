# Best Practices


Types: User, Post → PascalCase

Fields: id, createdAt → camelCase

Keep consistent verbs in mutations: createUser, updateUser, deleteUser.


- Extend, don’t redefine
    - Define a type once, then extend type in other modules to add more fields.
    - Prevents duplication and conflicts.

- Keep resolvers thin

- Resolvers should delegate logic to service or data layers, not contain business logic directly.

- Batch & cache with DataLoader
    - Without it, nested queries (user.posts.comments) can cause N+1 query problems.
    - DataLoader batches DB calls efficiently.