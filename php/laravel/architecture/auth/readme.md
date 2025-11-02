# Auth


- Are you a user? [Authentication](./authentication/readme.md)

- if you are user, what permission you have?  [Authorization](./authorization.md)

- [Sanctum](./sanctum/readme.md)

- [Passport](./passport/readme.md)


## Summary of Authentication Methods

### Session Based

- User authenticates using credentials.
- Backend creates a session for the user.
- Every request includes a cookie that references the session.
- Sanctum


### API Token
- User authenticates using credentials.
- A random token is generated and stored in users.api_token.
- Client includes this token in each request for authentication.
- One token per user; valid indefinitely unless revoked.
- Laravel API guard


### Sanctum API Token
- User authenticates using credentials.
- Generates a personal access token stored in personal_access_tokens.token.
- Client includes this token in requests for authentication.
- Users can have multiple tokens.
- Tokens are valid indefinitely unless expiration rules are defined or revoked.
- Uses Laravel Sanctum.
- Sanctum

### JWT Token
- JWT holds inside it the necessary information to login like user_id
- token is signed, so you cant amend data inside the token or generate your own token
- Highly stateless by design. so you dont have to have session or storing api tokens in database
- JWT package

### Personal Token
- personal token acts exactly like your password
- anyone have your personal token can act as you straight away
- personal token should always be used by the user himself. 
- user shouldnt issue a personal token and give it to someone as its extermely risky
- Laravel Passport

### Oauth2
- OAuth 2.0 is a protocol that allows an application to access resources like APIs
- Its always on behalf of a user except client credentials grant type
- Laravel Passport

#### Password Credentials
- legacy and discouraged
- exchange email and password to access token
- not recommended because 3rd party app will need to have your username and password to authenticate

#### Client Credentials
- backend to integrate with your backend without user involvement
- 3rd party will have client id / client secret .. and he can use these two things to get access token directly without user involvement

#### Authorization code
- 3rd party to integrate with your backend on behalf of a user
- user will need to consent he approve 3rd party to retrieve and do some functionality on his behalf
- 3rd party need client id / client secret then ask the user may you approve me to operate on your behalf

#### Authorization code with pkce
- Same as authorization code
- exist to fix a problem that client secret can be exposed if its embeded in js application or java application
- so for such applications where client secret can be exposed, use pkce to add security