# VPC (Virtual Private Network)

A VPC is your own private network inside a cloud provider (AWS, Azure, GCP, etc.).


- VPC is constructed in one region
- VPC must at least one subnet
- every subnet must be in one availability zone
- machines will be attached to a subnet


### Components
- VPC
- Subnets
- Route Tables
- Internet Gateway


### Flow
- create a vpc and give it a cidr
- create subnets for this vpc and give each a cidr
- create route table to tell subnets how they can route traffics (default route table make subnets visible to each other) 
- create internet gateway and attach it to vpc (its like adding a router to your vpc)
- for subnets to send traffic to public, make a route in subnet routes table to traffic 0.0.0.0 to igw


## VPC

- Each VPC need to have a CIDR to represent the structure of ips inside the network

- Recommended VPC CIDR
    - `10.0.0.0/16` 
    - `172.31.0.0/16` 
    - `192.168.0.0/20`

- Each VPC must have one or more subnets

## Subnets

Each Subnet need to have a CIDR to represent the structure of ips inside the subnet
- Recommended Subnet CIDR
    - `10.0.1.0/24`
    - `10.0.2.0/24`

- Each Subnet must be associated with a route table to tell subnet how it can route traffic

Tip: when you add a subnet, it auto attach subnet with the default route tables in vpc

Tip: for a subnet to access internet, you need to add a route in its route tables to say 0.0.0.0 traffic to igw

## Route Tables

A route table in AWS controls how traffic moves for a Subnet

#### Public Route Table:

| Destination | Target                              |
| ----------- | --------------------                |
| 10.0.0.0/16 | local (subnet can see its vpc cidr) |
| 0.0.0.0/0   | igw-12345 → Internet                |

Tip: 0.0.0.0/0 means all ip addresses

Tip: routes are matched in order

#### Private Route Table:

| Destination | Target                              |
| ----------- | ----------------------------------  |
| 10.0.1.0/24 | local (subnet can see only it self) |

Tip: any one want to reach internet 0.0.0.0/0 then send traffic to internet gateway which is like router


### Internet Gateway

is a VPC component that allow access to Internet, its like a router

Each VPC can have one Internet Gateway by creating igw and attach it to vpc

### NAT Gateway

A NAT Gateway is a vpc component that allows instances in a private subnet to access the internet for updates, downloads, etc.. while keeping them unreachable from the internet.

Tip: you still can request and response, but public world can't reach you

Tip: almost all vpc components is free except NAT gateway costs money 

### CIDR [Explained Here](./vpc/vpc_cidr.md)

### Bash [Explained Here](./vpc/vpc_bash.md)

### Firewall [Explained Here](./vpc/vpc_firewall.md)

### Subnetting

if we take a CIDR for example 10.0.0.0/16 and we want to create a subnet for it

there are multiple ways depending on how many bits you would reserve for subnets from host bits

- take one bit (then we can have two subnets)
    - 10.0.0.0/17
    - 10.0.128.0/17
- take two bits(then we can have 4 subnets)
    - 10.0.0.0/18
    - 10.0.64.0/18
    - 10.0.128.0/18
    - 10.0.192.0/18
- take three bits (then we can have 8 subnets)
- etc...

- for easier subnetting you can take 8 bits
    - 10.0.1.0/24
    - 10.0.2.0/24
    - 10.0.3.0/24
    - etc...
    - you can have 2^8 ~= 256 subnets
    - you can have ~= 256 hosts