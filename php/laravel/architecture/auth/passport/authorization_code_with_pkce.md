# Auth Code with PKCE (Proof Key for Code Exchange)

Problem with typical auth code is two things if combined then it would be catastrophy

- in javascript application or mobile application. you have to declare client secret in your software some where. and these application can be decompiled and client secret can be read easily

- after user click approve, server redirect back to the 3rd party software with authorization code in the header. so a man in middle attack can read the authorization code if he sniff.

if a developer can get his hand on client secret + authorization code.. he can issue access token for himself


So most mobile/SPAs skip the client_secret. and rely on PKCE


## Steps

### Client Generate a random code verifier in run time

```ini
code_verifier = "u7sG93mSdLs9zP8dE3sFj38ZsTzE28kYxSgKsd83"
```

### Client generate code challenge in run time
```ini
code_challenge = BASE64URL-ENCODE(SHA256(code_verifier))

code_challenge = "o8P6TqkXADxfs8Dy7Qy9_mD4qLgDTe6gKsiHv5yrA4Q"
```


### Client Start authorization

```bash

GET /oauth/authorize?
  client_id=your_client_id
  &redirect_uri=https://example.com/callback
  &response_type=code
  &code_challenge=o8P6TqkXADxfs8Dy7Qy9_mD4qLgDTe6gKsiHv5yrA4Q
  &code_challenge_method=S256

```

### After user authorizes, the server returns the code.

### Client Exchange code with access token

```bash

POST /oauth/token
{
  "grant_type": "authorization_code",
  "client_id": "4",
  "redirect_uri": "https://example.com/callback",
  "code": "AUTH_CODE",
  "code_verifier": "u7sG93mSdLs9zP8dE3sFj38ZsTzE28kYxSgKsd83"
}
```

### Server check

Server hashes the code_verifier, compares it with the saved code_challenge, and only issues the token if they match.

✅ This way, even if an attacker steals the authorization code, they cannot exchange it for a token — because they don’t have the code_verifier.


Tip: Passport doesn’t need explicit DB columns to store code challenge — it stores PKCE data inside the authorization code itself.