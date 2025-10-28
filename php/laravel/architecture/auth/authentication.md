# Authentication

Are you a user?


## Guard

Guard is the class responsible to say `are you a user`? laravel out of the box offer:
- Session Guard [Explained here](./guard/session.md)
- Token Guard (api_token column in users table) [Explained here](./guard/token.md)
- Custom Guard [Explained here](./guard/customguard.md)


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