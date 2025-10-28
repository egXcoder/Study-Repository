# Cookie Attacks

Laravel’s cookie encryption mainly protects against three families of attacks

## Cookie Theft

An attacker manages to see your browser’s cookies (via XSS, proxy logging, or inspecting HTTP traffic on an unencrypted connection). 

If the cookies are not encrypted, they can directly read sensitive data like:

```json
{
  "user_id": 123,
  "remember_token": "xyz123",
  "roles": ["admin"]
}
```

Encryption effect:

Laravel’s EncryptCookies middleware turns that into an unreadable blob like: eyJpdiI6Ik5UemhOREUwTm1S... (random gibberish)

Even if the attacker steals it, without the APP_KEY they cannot decrypt or interpret it.

## Traffic Pattern Analysis

If the same plaintext always produced the same ciphertext, attacker can try to guess and decrypt the cipher 

Random IV effect: Random IVs make each ciphertext completely unique, even for identical data. There’s no pattern to analyze.


## Cookie Tampering

An attacker modifies their cookie and sends it back — for example:

```json
{
  "user_id": 2
}
```

changed to
```json
{
  "user_id": 1
}
```

Encryption + MAC effect:

Laravel doesn’t just encrypt cookies — it also attaches a MAC (Message Authentication Code). When Laravel decrypts a cookie:

It re-computes the MAC using the APP_KEY

If it doesn’t match → the cookie is rejected immediately


💡 So in essence:

Laravel’s encrypted cookies make attacks more expensive, more complex, and less rewarding.
They don’t make hacking impossible (nothing does), but they turn:

“Read a cookie → instant admin access”
into
“Steal the APP_KEY, bypass HTTPS, forge MAC → maybe get access”.

And that’s exactly what security is about — making life much harder for the attacker while keeping it easy for you.





## Replay Attacks

A malicious party captures a previously valid cookie and replays it to regain access.

### http only and secure

Always use HTTPS (Secure cookie). Without HTTPS an attacker can sniff cookies on the wire.

```php
// Set cookie flags in config/session.php:

'secure' => true, //https
'httponly' => true, //cant be read by js

```

### Shorten lifetime / rotate tokens

Reduce SESSION_LIFETIME to limit window of misuse.

### Regenerate on auth and sensitive actions 

it will make the cookie key to be invalid and will create a new cookie key

What happens if you regenerate every request?
- Imagine the browser doing multiple requests. if every request generate a session there will be much race condition between the requests that one of the request is saving while another one is moving data completely. this race condition will cause unexpected troubles in various places.

- Session storage bloat / cleanup issues


### Server-side session validation (fingerprinting)

```php
//on session creation
$fingerprint = hash('sha256', request()->ip() . '|' . request()->userAgent());
session(['fingerprint' => $fingerprint]);


//middleware
// app/Http/Middleware/VerifySessionFingerprint.php
public function handle($req, Closure $next) {
    $fp = session('fingerprint');
    if (!$fp) return $next($req); // or enforce login
    $current = hash('sha256', $req->ip().'|'.$req->userAgent());
    if (!hash_equals($fp, $current)) {
        // optional: log, invalidate session, force re-login
        auth()->logout();
        $req->session()->invalidate();
        return response()->json(['message'=>'Session invalid'], 401);
    }
    return $next($req);
}
```

### On each login, record device info (user agent, IP, timestamp). 

If a new session appears from a different country/device, force re-auth or send verification email / 2FA.

### Protect sensitive actions with mfa. 

Even if cookie is stolen, attacker still needs the 2nd factor for certain actions

### Logout-other-sessions / session management UI: 

Let users see and revoke active sessions/devices. You can implement APIs that delete sessions rows associated with a user.