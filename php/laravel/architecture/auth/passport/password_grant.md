### Password Grant

`legacy and discourage`

app exchange email and password for access token which require app must handle and store the user’s password — potential leaks. which breaks the core OAuth principle: “The client should never see the user’s credentials.”

The password grant is meant for highly trusted applications — like: Your first-party app (developed and owned by the same company as the API). Internal or legacy systems where OAuth2 redirect flows are impractical.


### Passport Usage

after installing passport there two rows added into oauth_clients one of them is for password grant client. row with id = 2 and it have secret column inside table as well 


### Client Requesting access token

client can be any app from anywhere, app need to provide username and password to exchange them for token

```bash

POST /oauth/token
Content-Type: application/json
{
  "grant_type": "password",
  "client_id": "2",
  "client_secret": "your-client-secret-as-defined-in-oauth_clients-table",
  "username": "john@example.com",
  "password": "secret",
  "scope": ""
}

```

```json

{
  "token_type": "Bearer",
  "expires_in": 3600,
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1...",
  "refresh_token": "def50200..."
}

```


### Refreshing Token

```bash

POST /oauth/token
Content-Type: application/json
{
  "grant_type": "refresh_token",
  "refresh_token": "def50200...",
  "client_id": "2",
  "client_secret": "your-client-secret-as-defined-in-oauth_clients-table",
}

```