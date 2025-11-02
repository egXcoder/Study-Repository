# Auth


## Authentication vs Authorization
- Authentication.. Are you a user? [Explained Here](./auth/authentication.md)
- Authorization.. if you are user, what permission you have? [Explained Here](../auth/authorization.md)


## Sanctum and Passport
- Sanctum [Explained here](./sanctum/readme.md)
- Passport [Explained here](./passport/readme.md)




## Summary:

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