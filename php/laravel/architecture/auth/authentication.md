# Authentication

Are you a user?


## Guard

Guard is the class responsible to say `are you a user`? laravel out of the box offer:
- Session Guard [Explained here](./guard/session.md)
- Token Guard (api_token column in users table) [Explained here](./guard/token.md)
- Custom Guard [Explained here](./guard/customguard.md)

Guards Config [Explained here](./guard/config.md)


## Sanctum and Passport

Laravel itself doesn’t ship many guard implementations beyond session and token, but it fully supports custom guards, either via your own implementation or via official packages


Sanctum [Explained here](./sanctum.md)
- Lightweight API authentication.
- Personal access tokens, SPA authentication, mobile apps.
- Uses a custom sanctum guard.
- Stateless or session-based.

Passport [Explained here](./passport.md)
- Full OAuth2 server implementation.
- For more complex scenarios: token scopes, clients, refresh tokens.
- Provides a passport guard.


## Summary

- Session ... out of box laravel web guard
- Token .. out of box laravel api guard .. users.api_token
- Sanctum .. sanctum guard .. api stateful using session (best for spa application)
- Sanctum .. sanctum guard .. api personal_tokens .. every user can have multiple tokens