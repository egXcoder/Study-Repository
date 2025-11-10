# TCP

## 3 way handshake to init Connection:
- Client "SYN"
    - Seq = 26651213412
    - MSS = 1460 bytes
    - Rwnd = 64240

- Server "ACK and SYN"
    - ACK Number = 26651213413
    - Seq = 1234145520
    - MSS = 1452 bytes
    - Rwnd = 62136

- Client "ACK"
    - ACK Number = 1234145521
    - Rwnd = 132096

Tip: Sequence number is a number that sender and receiver uses to ack how much data receiver has correctly received

Tip: MSS is the maximum segment length it can fly between client and server in each trip. each trip carry one segment

Tip: rwnd is the receiver window, represents how much you can send me without waiting for ack, its in bytes


## MSS (Maximum Segment Size)
- every trip shall send only one segment which hold source port, destination port and part of the data
- MSS defines the largest amount of TCP segment can carry
- MSS is only negotiated during the TCP 3-way handshake and stays fixed for the duration of the connection.

### Q: if server is responding with 2Mb data to client, given that MMS = 1452 Bytes and effective window = 5KB?
- Server send 1452 segment 1
- Server send 1452 segment 2
- Server send 1452 segment 3
- Client Send ack
- Server send 1452 segment 1
- Server send 1452 segment 2
- Server send 1452 segment 3
- Client Send ack
- etc.. till all go through

## Flow Control (rwnd)

Purpose: Prevent the sender from overwhelming the receiver with too much data.

- the sender can send up to x bytes before it must wait for an ACK updating the window.
- so then we don't overwhelme receiver device by data
- TCP describe this x bytes by `Receive Window (rwnd)`.
- for example rwnd = 131,328 bytes .. means sender can send up to 128kb then it has to wait for receiver to say ack
- Example
    - Client Syn to Server
    - Server Ack , rwnd = 8k bytes
    - Client Send Data 4k bytes
    - Server Process Data then ack, rwnd = 8k bytes
    - Client Send Data 8k bytes
    - Server Process Data then ack, rwnd = 8k bytes
    - Client Send Data 8k bytes
    - Server ack, rwnd = 0
    - Client Periodically Send Tiny Probe, can i send more?
    - Server respond ack, rwnd = 2k bytes
    - Client Send 2k bytes
    - Server respond ack, rwnd = 8k bytes

Tip: Advertised rwnd is always included in ACK packets from the receiver.

Tip: sendable_bytes = min(rwnd, cwnd) .. cwnd is congestion window

Tip: sendable_bytes also called effective window

Tip: when sender send data, it auto determine how much data he should send using rwnd value from last receiver ack and his internal cwnd value = min(rwnd, cwnd)


## Congestion Control (cwnd)

Purpose: Prevent the network (hops) from being overwhelmed by too much traffic.

- TCP assumes packet loss = congestion, so it adjusts speed dynamically based on feedback.
- cwnd = congestion window (network capacity)
- Slow Start (when connection begins or after timeout)
    - Start with cwnd = 1 KB
    - Each ACK received → cwnd doubles (exponential growth).
- Congestion Avoidance
    - Once cwnd >= ssthresh (slow start threshold):
    - Growth switches from exponential → linear.
    - Increment cwnd slowly (e.g., 1 KB per RTT).
- Too Fast
    - if 3 duplicate ACKs (Fast Retransmit)
    - Halve cwnd (cwnd = cwnd / 2)
    - Continue Increasing cwnd Linear
- Loss Detected → Reduce Speed
    - If timeout → reduce ssthresh = cwnd / 2 then slow start again

Tip: cwnd doesnt show on wireshark, because its internal thing to the sender. sender dynamically evaluate it on his side and no need to send its value over the network to receiver



## 4 way handshake to close connection:
    1. "FIN"       Client → Server  : I’m done sending data.
    2. "ACK"       Server → Client  : I received your FIN." (But server might still send data)
    3. "FIN"       Server → Client  : Now I’m also done sending.
    4. "ACK"       Client → Server  : "Goodbye — confirmed." ✅ Connection closed


## Sending Order
- TCP guarantees in-order delivery to the application
- Data are splitted into Segments and they are numbered by sequence numbers 1,2,3,4
- Sender always send segments in order according to the byte stream
- Receiver may receive buffers out-of-order segments
- Receiver will reassemble them to get them back in order



//TODO: you say 4 way handshake, but i saw only two??