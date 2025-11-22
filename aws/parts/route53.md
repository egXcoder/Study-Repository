# Route 53

53 is AWS’s DNS (Domain Name System) service.

called 53 because DNS uses port 53 on the network.







## Q: Step-by-step: What happens when you type example.com in a browser?

- You type the domain: The browser must find the IP address of this domain before it can connect to the server.
- Browser checks its DNS cache: If not → continue.
- Operating System DNS cache: If not → continue.
- Ask Local DNS Resolver (usually your ISP or router): If not cached → resolver starts a full DNS lookup.
    - Resolver contacts a Root DNS Server it will reply you with TLD you should ask
    - Resolver contacts the .com TLD (Top Level Domain) name servers
    - TLD stores your domain and NS Name Servers 
    - TLD Reply example.com is managed by one of 4 name servers .. pick one
        - ns-1942.awsdns-50.co.uk.
        - ns-99.awsdns-12.com.
        - ns-1442.awsdns-52.org.
        - ns-954.awsdns-55.net.
    - Resolver contacts one of the NS and if it fails it will contact another
    - this NS is essentially Route 53
    - Route 53 do its magic and return the ip address of the server if possible

Tip: Top Level Domain Server are servers for .com .net .org etc..

Tip: whenever you create a hosted zone in aws, it will auto give you globally unique 4 name servers which you can use to update your domain within domain registerer then TLD update their database to point your domain to route 53


## Route 53 magic

Route 53 NS is asked by the dns resolver: do you have ip address of example.com?

NS check the domain and reach your hosted zone then will act accordingly

Route 53 check the policies you have entered and reply with the record you have asked for 

### Health check
route 53 can use health checks to say if it can direct traffic to the resource or choose another one

you can create a new health check to monitor the health of a specified resource from a separate screen then attach it with A record in the hosted zone


### Policies
| Routing Policy | Route 53 response                                    |
| -------------  | ---------------------------------------------------- |
| Simple record  | returns single IP                                    |
| Weighted       | returns IP based on your weight (e.g., 80% A, 20% B) |
| Failover       | returns primary server unless health check fails     |
| Latency based  | returns server closest to the user’s location        |
| Geolocation    | returns server based on user’s country               |
| Multi-value    | returns multiple IPs                                 |


### Records
| Record    | What it does                              |
| --------- | ----------------------------------------- |
| A         | Map domain → IPv4 address                 |
| AAAA      | Map domain → IPv6 address                 |
| CNAME     | Alias one domain to another               |
| MX        | Mail exchange (for emails)                |
| TXT       | Text record (verification, SPF for email) |
| NS        | Name servers for domain                   |
| SRV       | Service record (VoIP, etc.)               |
| PTR       | Reverse DNS (IP → domain)                 |


TTL = 300 seconds → record cached for 5 minutes
TTL = 86400 seconds → record cached for 24 hours

### A record

A record and AAAA record can co-exist, so browser will get the two ip addresses v4 and v6. and its up to browser to pick, most likely it will try ipv6 and failover to ipv4

You can have multiple records within A records, and its up to client to choose which one it will pick

In practice, many domains only use one A record, though multiple A records is technically fine, but:
- Can make client behavior less predictable (random selection)
- Harder to do graceful failover unless you combine with health checks (like Route 53 multi-value answer routing)

### CNAME is Canonical Name

- if resolver asks NS what is ip address of example.com it will reply example.org
- resolver will ask what is example.org
- here resolver do two queries to reach the ip address

Tip: you can't have CNAME and A record in same time


### Ask NameServer do you have this domain


```bash
toor@DESKTOP-DLNJTCG:~$ dig any ahmedibrahim94.com @ns-99.awsdns-12.com

; <<>> DiG 9.18.28-0ubuntu0.22.04.1-Ubuntu <<>> any ahmedibrahim94.com @ns-99.awsdns-12.com
;; global options: +cmd
;; Got answer:
;; ->>HEADER<<- opcode: QUERY, status: NOERROR, id: 45180
;; flags: qr aa rd; QUERY: 1, ANSWER: 9, AUTHORITY: 0, ADDITIONAL: 1
;; WARNING: recursion requested but not available

;; OPT PSEUDOSECTION:
; EDNS: version: 0, flags:; udp: 4096
;; QUESTION SECTION:
;ahmedibrahim94.com.            IN      ANY

;; ANSWER SECTION:
ahmedibrahim94.com.     300     IN      A       192.168.1.1
ahmedibrahim94.com.     172800  IN      NS      ns-1442.awsdns-52.org.
ahmedibrahim94.com.     172800  IN      NS      ns-1942.awsdns-50.co.uk.
ahmedibrahim94.com.     172800  IN      NS      ns-954.awsdns-55.net.
ahmedibrahim94.com.     172800  IN      NS      ns-99.awsdns-12.com.
ahmedibrahim94.com.     900     IN      SOA     ns-1942.awsdns-50.co.uk. awsdns-hostmaster.amazon.com. 1 7200 900 1209600 86400
ahmedibrahim94.com.     300     IN      MX      10 mailserver\@gmail.com.
ahmedibrahim94.com.     300     IN      TXT     "text"
ahmedibrahim94.com.     300     IN      AAAA    2001:db8::8a2e:370:bab5

;; Query time: 60 msec
;; SERVER: 205.251.192.99#53(ns-99.awsdns-12.com) (TCP)
;; WHEN: Wed Nov 19 22:19:42 EET 2025
;; MSG SIZE  rcvd: 338
```


### Pricing Model

- Hosted Zones
    - first 25 hosted zones: $0.50 per zone per month
    - then $0.10 per zone per month

    Tip: Delete unused hosted zones (especially before end of month) to avoid monthly fee. If deleted within 12 hours of creation you won’t be charged for the zone fee.

- DNS Queries
    - $0.40 per million queries

    Tip: Alias records pointing to certain AWS services are free for the query itself.

- Health Checks
    - “basic” health checks $0.50 per health check per month
    - Optional features (HTTPS, string matching, fast intervals) may add ~$1.00–2.00 per month per health check.