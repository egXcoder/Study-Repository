# ELB Elastic Load Balancer

ELB is a fully managed load balancing service from AWS

it only exist as part of EC2, and doesnt exist on its own


Types:
1. ALB: Application Load Balancer
    - L7
    - Web Apps, MicroServices,APIs

2. NLB: Network Load Balancer
    - L4
    - high performance, low latency apps


### L4 vs L7 Load Balancers

L4 (tcp): just forwards the packets as it
- Pros:
    - its faster since no need to decrypt data and read it then forward it
    - One TCP Connection
- Cons:
    - no smart load balancing since it doesnt know the url path 
    - no caching

L7 (http): decrypt data and read it so it can do smart things:
- Pros:
    - forward requests to different servers depending on urls (microservices)
    - It offers caching based on URL within the http text
    - it can inspect requests coming and do firewalls on the received data
    - it can add extra headers to the http request before sending request to the backend server

- Cons:
    - slower as it needs to decrypt the data
    - if you will add another tls certificates between LB and servers it will be even slower
    - less secure, because if LB is compromised, whoever on it he can read the data of the coming requests to your system
    - two tcp connections

- TLS
    - LB validate and terminate TLS between client and LB
    - LB can talk with the servers in plain http (most common approach)
    - LB can talk with the servers with another TLS certificate, each server have a TLS certificate (more security but more configuration)


### Target Group

A Target Group is a logical group of backend targets that receive traffic from a Load Balancer.

| Feature                  | Purpose                                              |
| ------------------------ | ---------------------------------------------------- |
| Health checks            | Detect which targets are healthy                     |
| Load balancing algorithm | Decides to which target request goes                 |
| Deregistration delay     | Wait for in-flight requests before removing a target |
| Stickiness               | Pin users to the same target (optional)              |
| Port override            | Route to a different port on the target              |

Tip: Load balancing aglortithm is typically target group attributes and you can edit it after creation
- ALB (Application Load Balancer) by using protocol http/https:
    - Round Robin (Default)
    - Least Connection
    - Weighted Random
    - Option for stickness then it would stick a user to a server using cookie

- NLB (Network Load Balancer) by using protocol tcp:
    - Source Ip Hashing Only
    - Option for stickness then it would stick a user to a server using cookie

Tip: target group is like haproxy configuration
```haproxy
backend web_backend
    mode http
    balance roundrobin

    server app1 10.0.1.10:80 check
    server app2 10.0.1.11:80 check
    server app3 10.0.1.12:80 check
```

Tip: in AWS NLB is limited in algorithms and only supports ip hash because aws has designed NLB to be totally stateless and dont hold state in its memory and this would make L4 NLB is far effective and faster

Tip: i think the above configuration is more than sufficient for use cases, however if you want more control you may want to create your own haproxy load balancer server


### Listeners

Listeners listen to port and forward request to target group
- ALB (Application Load Balancer L7)
- NLB (Network Load Balaner L4)

Tip: listener type must match the target group L7,L7 or L4,L4

Tip: its like HAProxy
```haproxy

frontend https_in
    bind *:443 ssl crt /etc/haproxy/certs/mydomain.pem
    mode http

    option forwardfor     # add X-Forwarded-For header
    default_backend web_backend

frontend https_passthrough
    bind *:443
    mode tcp
    option tcplog
    tcp-request inspect-delay 5s
    tcp-request content accept if { req_ssl_hello_type 1 }
    use_backend web_https_backend
```

Tip: by default, aws create for you at least two load balancers in two multiple az