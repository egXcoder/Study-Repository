# Authorization Code

It allows a third-party app to get limited access to a user’s data without ever seeing their password.


`php artisan passport:client` add a row into oauth_clients for an authorization code client and state its redirect url. row is:
- client_id = 3
- client_secret = abc123secret
- redirect_uri = https://3rd-party-app.com/callback

Tip: you can define multiple redirect urls in database by using comma `https://app.example.com/callback,https://staging.example.com/callback`

## User At 3rd party Start Process

user at 3rd party click a button, which will send him to me

```bash
GET

https://me.com/oauth/authorize?
  client_id=4&
  redirect_uri=https://3rd-party-app.com/callback&
  response_type=code&
  scope=

```

The user will see a page asking if they authorize your app to access their data.

if approved, i will redirect him back to the redirect_uri with the authorization code

```bash

https://3rd-party-app/callback?code=AUTH_CODE

```

That AUTH_CODE is short-lived — it’s not your access token yet.

Now 3rd party will exchange authorization code for access token

```bash

POST https://me.com/oauth/token
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code
client_id=4
client_secret=abc123secret
redirect_uri=https://3rd-party-app/callback
code=AUTH_CODE

```

```json

{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1QiLCJh...",
  "refresh_token": "def456..."
}


```


🧠 When to use this flow
- You have a public web or mobile app acting on behalf of a user.
- You need to allow third-party applications to access your users’ data.
- You want secure login and token exchange without exposing passwords.