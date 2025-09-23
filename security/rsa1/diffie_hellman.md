# (Diffie Hellman)
Diffie–Hellman (DH) is a method for two people to agree on a shared secret key over an insecure channel (like the internet), even if someone is listening.

The key point:
- You and I can exchange some numbers publicly.
- A perso in the middle can see them, but still cannot compute the final shared secret (because of math hardness).


Steps
2- Alice and Bob will agree on a public number x
1- Alice picks a private secret a and Bob picks a private secret .. both are never shared
3- Alice will compute public secret of x and a then send it to Bob ... so that Bob will have x,a,b
4- Bob will compute public secret of x and b then send it to Alice ... so that alice will have x,b,a
5- since alice and bob have the x,a,b they both can generate a symmetric key which they will use going forward to encrypt and decrypt messages