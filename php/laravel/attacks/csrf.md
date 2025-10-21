# CSRF (Cross-Site Request Forgery)


It’s an attack that send a request to a website without user consent.

## Scenario:

- Let’s say you’re logged in to your bank’s website https://mybank.com .. notice: your browser stores a session cookie for mybank

- You then visit http://evil.com in another tab

- In Malicious Site `<img src="https://mybank.com/transfer?to=attacker&amount=1000" />` which Sends a Hidden Request .. 

- Browser automatically sends your request to mybank.com along with the session cookie

- If mybank.com’s transfer endpoint doesn’t verify authenticity, the request goes through. 💸 Money transferred without you ever clicking anything on mybank.com.

## It’s because:
- Browsers automatically include cookies for the target site with every request.
- The site trusts those cookies as proof of authentication.
- The attacker exploits that trust.


## How To Prevent

### CSRF Tokens

Every form or state-changing request with your application includes a random secret token.

```html
<form method="POST" action="/transfer">
  <input type="hidden" name="csrf_token" value="ABC123XYZ">
  <input type="text" name="amount">
  <button>Send</button>
</form>
```

The server checks: Does the token match what was stored in the session? If not → reject the request.

This prevents forged requests, since the attacker’s site can’t know the secret token.

CSRF Token is generated in start of each session as `_token=>asvzxbasseqwe123` and it regenerate whenever session regenerate .. when user first view there is a session and when he login session regenerate and when logout session regenerate etc..

Server send the token: 
- cookie `XSRF-TOKEN` so clients can use it 
- with php method `csrf_token()`

client send the token: 
- `_token` request parameter
- `X-CSRF-TOKEN` request header


### SameSite Cookies

Modern browsers support a cookie attribute called SameSite.

Set-Cookie: session=xyz; SameSite=Lax

This tells the browser: Don’t send this cookie with cross-site requests. This blocks CSRF attempts from external origins.

### Same Origin Policy and CORS

Same-Origin Policy (SOP): by default, browsers block JS from reading responses from different origins.

CORS: allows the server to say, “Yes, this other origin is allowed to read my data.”

Request `Origin: https://evil.com` ..  

Response `Access-Control-Allow-Origin: https://evil.com` or `Access-Control-Allow-Origin: *`

Tip: browser considers each subdomain a different origin.

partially solve it because CORS dont block requests completely.. only JS reading the response is blocked. So CSRF can still succeed even if your CORS policy is strict.


### Use POST (Not GET) for State-Changing Actions

then malicious site can't do this <a href="/transfer?to=attacker&amount=1000">Transfer</a>

partially solve it, because malicious site can send ajax request with post method


### Check the Origin or Referer Header

Check the Origin or Referer Header

When a browser send a request it include header Origin: https://yourbank.com or Referer: https://yourbank.com/account

Partially solve it, because malicious site still can forgery the origin header while he is sending his request

```php

$origin = $_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_REFERER'] ?? '';
if (!str_starts_with($origin, 'https://yourbank.com')) {
    http_response_code(403);
    exit('Invalid origin');
}

```