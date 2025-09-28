# HTTP

HTTP (HyperText Transfer Protocol) is the foundation of data communication on the web. It’s the protocol your browser and web servers use to request and deliver content like web pages, images, APIs, and more.


HTTP is typically built on top of TCP.


## HTTP/1.0

- One TCP Connection for One request
- Text-based .. 
- 50 Connections for Loading a website with 50 assets (CSS/JS/images)


## HTTP/1.1
- One TCP Connection can be used for multiple requests
- Uses persistent (Keep-Alive) by default
- Text-based ..

[One TCP connection] → Request #1 → Response #1 → Request #2 → Response #2 → ...
So multiple requests can be sent over a single TCP connection — but sequentially.

What About HTTP/1.1 Pipelining?
Client → Request A, Request B, Request C (without waiting for replies)
Server → Response A, Response B, Response C (in exact order)

But there are two problems:
Head-of-line Blocking ...	If Response A is slow, Responses B and C must wait — even if they’re ready
Inconsistent Server/Proxy Support .. Many servers and proxies disabled pipelining due to bugs and deadlocks

because of the above problems, browsers mostly disabled pipelining — it was unreliable. its not truely parallelism.


so what chrome done?
chrome creates by default 6 tcp connections per host and each connection can be used to send requests responses sequentially, it feels like parallism but limited because the max is 6 and also expensive because it creates 6 tcp connections instead of one


## HTTP/2

- Single TCP Connection Multiplexing (multiple requests in parallel)
- Binary protocol
- Headers Compression: HPACK compression
- True parallelism (multiplexing), compression, binary protocol, faster page loads

### Stream
A stream represents one request–response pair — but it is broken into many small chunks called frames.

Streams:
- Stream A: GET /logo.png
- Stream B: GET /style.css
- Stream C: GET /script.js


### Single TCP Connection

Client → [A1][B1][C1][A2][C2][B2][A3][B3][C3]...

Server → [A1][C1][B1][A2][B2][C2][C3][A3][B3]...
            ↑   ↑   ↑
         (Out of order allowed, no blocking)

Stream IDs:
- Stream A = A1 + A2 + A3 (frames)
- Stream B = B1 + B2 + B3 (frames)
- Stream C = C1 + C2 + C3 (frames)

The client reassembles frames by Stream ID to build the response Stream