# CIDR

### What is CIDR Classless Inter-Domain Routing (CIDR)

It is a method for writing IP ranges without relying on classful addressing (Class A, B, C).

`192.168.1.0/24` .. Means: the first 24 bits identify the network, and the remaining 8 bits identify hosts.

`10.0.0.0/24` .. possible 256 ips for hosts

`10.0.0.0/16` .. there are possible hosts (65,536 IPs)


| CIDR | Subnet Mask     |
| ---- | --------------- |
| /24  | 255.255.255.0   |
| /16  | 255.255.0.0     |
| /20  | 255.255.240.0   |
| /30  | 255.255.255.252 |

with CIDR its efficient allocation

### Recommended VPC CIDR
- `10.0.0.0/16` 
- `172.31.0.0/16` 
- `192.168.0.0/20` 

### AWS CIDR
- VPC CIDR Block start from /16 to /28


# Previous Than CIDR

### IPV4
- ipv4 construct of 4 bytes = 32bit
- take form of byte.byte.byte.byte such as 192.168.1.1
- all possible values range from 00000000.00000000.00000000.00000000 to 11111111.11111111.11111111.11111111

Tip: group of 8 bits called octet, so you may hear octet whenever you refer to one of the bytes


### how to group devices in a network?
- you have to split IPV4 to Network and Host
- for example i will take first three bytes to be always like 192.168.1
- and i will leave the last byte that can represents host, so it can be from 0 to 255

### Classful Addressing
is an old IP addressing system that was used before CIDR
- Class A: 
    - byte(network).byte.byte.byte 
    - format: 0nnnnnnn.xxxxxx.xxxxxx.xxxxxx
    - possible network numbers = 7 bits = 2^7 = 128
    - possible hosts numbers = 24 bit = 2^24 ~= 16M
    - First Octet 1 – 126
- Class B: 
    - byte(network).byte(network).byte.byte 
    - format: 10nnnnnn.nnnnnnnn.xxxxxx.xxxxxx
    - possible network numbers = 15 bits ~= 16k
    - possible hosts numbers ~= 65k
    - First Octet 128 – 191
- Class C
    - byte(network).byte(network).byte(network).byte
    - format: 110nnnnn.nnnnnnnn.nnnnnnnn.xxxxxx
    - possible network numbers = 23 bits ~= 2M
    - possible hosts numbers ~= 8 bit ~= 254
    - First Octet 192 – 223
- Class D
    - Used for multicast
    - format: 1110nnnn.nnnnnnnn.nnnnnnnn.xxxxxx
    - First Octet 224 – 239
- Class E
    - Experimental
    - format: 11110nnn.nnnnnnnn.nnnnnnnn.xxxxxx
    - First Octet 240 – 255

Tip: IPs starting with 127.x.x.x are reserved for Loopback — not part of Class A.

#### It had major limitations:

IANA who was managing the ip ranges and selling them to companies faced a problem whenever selling ips to companies. each company should get a network so company would either use class A or B or C , If a company needed 500 IPs:
- Class C gives only 254 → too small
- Class B gives 65,534 → extremely wasteful
- Huge number of addresses were wasted.

so they had to come up with another standard called CIDR (Classless Inter-Domain Routing)
