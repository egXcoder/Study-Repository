# The Math (simplified)

## 1. Choose two prime numbers:
p and q (big ones, like 1024-bit primes).

## 2. Compute their product:
n = p * q

## 3. Compute Euler’s totient:
φ(n) = φ(p*q) = (p−1)(q−1) ... when n is a product of two prime numbers

Euler's totient function, or the phi function (written as φ(n)), counts how many numbers less than 𝑛 are coprime with n
Two numbers are coprime if their greatest common divisor (gcd) is 1... In other words, they don’t share any prime factors.
8 and 15  ✅ coprime
8 and 12 ❌ not coprime .. because both share 2 as dividing factor

- φ(23) = 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22 all of these are co-prime

If p is a prime number p : φ(p) = p - 1. 
Example: φ(23) = (23-1) = 22 , because 1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22 all of these are co-prime

if n is a product of two prime numbers: φ(n) = φ(p*q) = (p - 1)(q - 1). 
Example: φ(15) = φ(3 * 5) = (3 - 1)(5 - 1) = 2 * 4 = 8.

If p is a normal number .. φ(p) has a formula but not needed in this scope 


## 4. Choose a public exponent 𝑒 Usually 65537 (a common choice, fast and secure).


## 5. Compute the private exponent d ≡ (1/e) * mod(φ(n))


🔒 Keys
Public key = (e,n)
Private Key = (d,n)


Example:
p = 11 , q = 13
n = p * q = 11 * 13 = 143
𝜑(𝑛)=(𝑝−1)(𝑞−1) = 10×12 = 120
Choose e=7 (public exponent, coprime with 120).
Compute 𝑑 with Extended Euclidean Algorithm = 103.


Public key (toy version):

-----BEGIN PUBLIC KEY-----
Modulus (n): 143
Public Exponent (e): 7
-----END PUBLIC KEY-----


Private key (toy version):

-----BEGIN PRIVATE KEY-----
Modulus (n): 143
Private Exponent (d): 103
-----END PRIVATE KEY-----


these numbers inside the keys are structured in a standard way called ANS1.DER then its base64 encoded which will give you the public and private key

-----BEGIN RSA PUBLIC KEY-----
MAcCAgCPAgEH
-----END RSA PUBLIC KEY-----

-----BEGIN RSA PRIVATE KEY-----
MBwCAQACAgCPAgEHAgFnAgELAgENAgEDAgEHAgEG
-----END RSA PRIVATE KEY-----