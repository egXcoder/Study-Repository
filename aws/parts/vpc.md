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
- create route table and assign it to relevant subnets
- create internet gateway and add a route to route table to direct traffic to igw


## VPC

- Each VPC need to have a CIDR to represent the structure of ips inside the network

- Recommended VPC CIDR
    - `10.0.0.0/16` 
    - `172.31.0.0/16` 
    - `192.168.0.0/20`

- Each VPC must have one or more subnets

## Subnets

- Each Subnet need to have a CIDR to represent the structure of ips inside the subnet
    - `10.0.1.0/24`
    - `10.0.2.0/24`

- Each Subnet must be associated with a route table.

Tip: when you add a subnet, it auto attach subnet with the default route tables in vpc

## Route Tables

A route table in AWS controls how traffic moves inside a Subnet and outside it.

#### Public Route Table:

| Destination | Target               |
| ----------- | -------------------- |
| 10.0.0.0/16 | local                |
| 0.0.0.0/0   | igw-12345 → Internet |


#### Private Route Table:

| Destination | Target                             |
| ----------- | ---------------------------------- |
| 10.0.0.0/16 | local                              |

Tip: any one trying to reach device from network 10.0.0.0/16 then send traffic locally in vpc

Tip: subnet with route table 10.0.0.0/16 this means, subnet can communicate with other subnets

Tip: any one want to reach internet 0.0.0.0/0 then send traffic to internet gateway which is like router


## Internet Gateway

is a VPC component that allows resources inside the VPC to access the Internet and be accessed from the Internet.

Each VPC can have one Internet Gateway by creating igw and attach it to vpc



### CIDR [Explained Here](./vpc_cidr.md)

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