# Passport

Laravel Passport is Laravel’s OAuth2 authentication server implementation.


✅ Use Passport if:
- You’re building a public API for external clients.
- You need OAuth2 flows (e.g., Google-style “Authorize this app to access your data”).
- You want to use access tokens with scopes, expirations, and refresh tokens.


❌ Use Sanctum instead if:
- It’s a first-party app (your own frontend + your backend).
- You only need simple API tokens or session-based login.


Passport uses OAuth2 tokens under the hood, managed by the league/oauth2-server package.

It creates and manages several database tables for tokens:


- oauth_clients: Registered apps that can request tokens
- oauth_access_tokens: Stores issued access tokens
- oauth_refresh_tokens: Stores refresh tokens
- oauth_auth_codes: Used for authorization code flow
- oauth_personal_access_clients: Stores personal access token clients


## Install

- `composer require laravel/passport`
- `php artisan vendor:publish --tag=passport-migrations`
- `php artisan vendor:publish --tag=passport-config`
- `php artisan migrate`
- `php artisan passport:install`
    - Creates encryption keys for token signing (stored in storage/oauth-private.key and storage/oauth-public.key)
    - Inserts two clients into oauth_clients:
        - Password grant client
        - Personal access client

- Add HasApiTokens to your User model
```php

use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
}

```



## Core Concepts

- Client: An application that wants to access your API. Stored in oauth_clients.
- Access Token: A short-lived credential used to access protected routes. Stored in oauth_access_tokens.
- Refresh Token: Used to get a new access token when it expires. Stored in oauth_refresh_tokens
- Scope: Defines what the token is allowed to do (permissions).
- Grant Type: Defines how the client obtains the token (different auth flows).




## Authentication Methods (Grant Types)

- Personal Tokens [Explained here](./passport/personal_token.md)
- oauth2


## Grant Types

A grant type in OAuth2 defines how the client obtains an access token

- Password Grant: Legacy and discouraged [Explained here](./passport/password_grant.md)
- Client Credentials Grant: Internal Server-to-server [Explained here](./passport/client_credentials.md)
- Authorization Code Grant: The most secure and common flow — user logs in via browser, gets redirected to authorization server, which issues a short-lived code that your backend exchanges for an access token.
- Authorization Code with PKCE: Same as Authorization Code, but adds PKCE (Proof Key for Code Exchange) to prevent code interception. Recommended for native/mobile/public clients.
- Refresh Token Grant: Lets the client refresh an expired access token without re-authenticating the user. Usually accompanies authorization code flow.