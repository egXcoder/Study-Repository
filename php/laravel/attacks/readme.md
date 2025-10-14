# Attacks


## Session Fixation Attack

This attack can happen if you dont regenerate session id after login

laravel breezer always regenerate session id after login

- Attacker gets a session ID (legit but not authenticated yet): Cookie: PHPSESSID=ABC123
- Attacker sends victim a link with that session ID embedded, or injects it into their browser somehow (e.g., through a URL, hidden field, or cookie poisoning): https://example.com/login?PHPSESSID=ABC123
- Victim logs in with their username & password, but they are still using session ABC123.
- Server marks session ABC123 as authenticated (because victim logged in).
- Attacker now uses ABC123 (which they already know) and is instantly logged in as the victim.


Q: how would attacker sends victim a link with session id?

- via xss .. attacker can do `document.cookie = "laravel_session=ABC123"`

- send url directly to victim https://example.com/index.php?PHPSESSID=abc123
    - In early PHP (and even up to PHP 5.x), 
    - there was a feature called transparent session ID propagation (aka session.use_trans_sid).    
    - If the browser didn’t accept cookies (like very old browsers, or cookies disabled), PHP would append the session id into every URL automatically so that the session could still be tracked. Example: https://example.com/index.php?PHPSESSID=abc123

- i can see its difficult for attackers to exploit it in modern sites, as send session id in url is not valid and if xss exist there are other harming it can done. rather than session fixation.. but even if its hard to exploit it on modern sites, its cheap fix to secure your website.. just by regenerate session id after login

