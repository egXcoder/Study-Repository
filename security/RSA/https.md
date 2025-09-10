# HTTPS

HTTPS = HTTP over TLS/SSL.

TLS is the security protocol that gives you:

RSA is one of the key algorithms used in TLS — but not for encrypting all your web traffic. Instead, it’s mainly used to establish a shared secret key.

## TLS 1.3 
Client:
- generate private key a
- compute public key g^a mod p (classic DH)
-> [Request] Client Sends the public key to server 

Server:
- generates private key b
- Computes public key g^(b) mod p
-> [Response] Server Sends the public key back to the client.


Client computes: (server_public)^a = (g^(b) mod p) ^a = g^(ab) mod p
Server computes: (client_public)^b = (g^(a) mod p) ^b = g^(ab) mod p
Both sides arrive at the same secret g^(ab) mod p


To Add Entropy for uniqueness
- when client send his request, he send a random nonce.. server do the same when he respond
- on both end, tls specification provide HKDF (hash key deriving function) which takes (shared_key,client random,server random) and return the secret key which they are going to use for going forward encryption


please notice:
- (p and g) are prime numbers hardcoded in tls specifications. so client and server agree them from the start
- (1 RTT round trip time) is enough to have client and server can agree on the shared key 



## Q: it seems tls1.3 is generating private key and public key every session?

TLS 1.3 removed RSA key exchange completely.
Now all key exchange is (Elliptic Curve) Diffie–Hellman (DH/ECDH).
- Both browser and server generate fresh, random private values every handshake (ephemeral keys).
- They exchange the corresponding public values.
- They compute a shared secret using Diffie–Hellman math.
- From that secret, they derive session keys (with HKDF).
👉 This gives forward secrecy: even if the server’s private key is stolen tomorrow, past traffic can’t be decrypted.


## Q: so is RSA still used in tls1.3? .. since i don't see text being encrypted with private key and decrypted with public key?

RSA (or ECDSA) is still used for authentication, not for key exchange.
The server sends a certificate containing its public key. It then digitally signs some handshake data with its private key.
The browser verifies this signature with the public key from the certificate. This proves the server really owns the private key matching the certificate.

So:
- Asymmetric crypto (RSA/ECDSA) → only for identity/authentication.
- Diffie–Hellman → for actual key agreement.
- Symmetric crypto (AES/ChaCha20) → for bulk data encryption after handshake.


## Certificate:

Problem:
because someone in middle can act as the server and trick the client, there become a need to assure server responding is the correct server.

what we know
- server can hold private and public key

what we can do
- server can sign the data being sent to client with private key (CertificateVerify) and send the public key as well
- browser can use the public key to double check CertificateVerify to make sure all good

Problem
a man in the middle can have a private key and a public key and act as if he is the server

what we can do
- we need a certificate authority which will say example.com have public key of this
- the certificate authority will make sure you are the domain owner when you try to issue a certificate from them


The Certificate

- A long-lived data structure, usually X.509.
- Issued by a Certificate Authority (CA).
Contains:
    -- The server’s public key.
    -- Information like subject name, domain name, validity period, extensions.
    -- The CA’s digital signature over all of the above.
Purpose: prove that this public key belongs to the server’s domain name.


- so now, server can send the certificate with the response
- client will take the certificate 
    -- confirm ca is correct and listed on client list as trusted authority 
    -- confirm ca certificate signature is correct and do belong to ca
    -- confirm certificate declares domain name correct
    -- take the server public key from the certificate and use it to double check CertificateVerify to make sure all good