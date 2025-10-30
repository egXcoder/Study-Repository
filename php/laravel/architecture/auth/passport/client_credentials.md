# Client Credentials

internal server-to-server communication inside your own infrastructure. strictly for first-party, trusted, or internal systems. not meant for 3rd party software

reason is its simplify access using only client id and secret, which is fine for internal communication

It’s perfect for:
- Background jobs
- Backend services
- APIs calling other APIs
- Cron jobs
- Microservices communication


## Initializing Client

`php artisan passport:client --client` add a row into oauth_clients for client credentials

```bash

Client ID: 3
Client secret: AbCdEfGh123

```

## Request Token

```bash

POST /oauth/token
Content-Type: application/x-www-form-urlencoded

grant_type=client_credentials
&client_id=3
&client_secret=AbCdEfGh123456

```

```json

{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1..."
}

```

Tip: Cannot use refresh tokens, each new token must be requested again.

Tip: Revokation, Delete the client or regenerate secret