# TCP

### 3 way handshake to init Connection:
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

Tip: Sequence number is a number that sender and receiver uses to ack how much data bytes receiver has correctly received

Tip: MSS is the maximum segment length it can fly between client and server in each trip. each trip carry one segment

Tip: rwnd is the receiver window, represents how much you can send me without waiting for ack, its in bytes


### MSS (Maximum Segment Size)
- every trip shall send only one segment which hold source port, destination port and part of the data
- MSS defines the largest amount of TCP segment can carry
- MSS is only negotiated during the TCP 3-way handshake and stays fixed for the duration of the connection.

### Typical flow like below
- Server send 1452 segment 1
- Server send 1452 segment 2
- Server send 1452 segment 3
- Client Send ack
- Server send 1452 segment 4
- Server send 1452 segment 5
- Server send 1452 segment 6
- Client Send ack
- etc.. till all go through

### Q: when does receiver send ack?

Typically receiver wait to receive few segments then ack .. Delayed ACK behavior is dynamic and depends on several factors

- If the sender is sending segments slowly, the receiver may wait up to the maximum delayed ACK timeout (often 200 ms) before sending an ACK
- If the sender is blasting segments quickly, the receiver send ack after 2 segments
- If sender moderately sending segments, receiver may ack after 4 or 5 or .. segments

Tip: Sender keeps sending data regardless receiver sent ack or not

### Q: when sender is forced to stop and wait for ack?

if sender exceeds min(rwnd, cwnd) . then he has to wait for receiver to ack
- rwnd: is number of bytes suggested by receiver device that this is maximum data you can send to fill my not-ack buffer memory
- cwnd: is number of bytes dynamically evaluated by sender that this is the maximum window i can send without disturbing the hops 

Tip: this number of bytes called effective window

#### Flow Control (rwnd)

Purpose: Prevent the sender from overwhelming the receiver with too much data.

- `Receive Window (rwnd)` is number of bytes suggested by receiver that this is the maximum window you can send me without wait for ack and it doesnt affect my device as typically segments not ack yet live in a buffer and we dont want to exceed it 
- for example rwnd = 131,328 bytes .. means sender can send up to 128kb then it has to wait for receiver to say ack
- Example
    - Server ack, rwnd = 8k bytes
    - Client Send Data 4k bytes
    - Server ack, rwnd = 8k bytes
    - Client Send Data 8k bytes
    - Server ack, rwnd = 0
    - Client Periodically Send Tiny Probe, can i send more?
    - Server respond ack, rwnd = 2k bytes
    - Client Send 2k bytes
    - Server respond ack, rwnd = 8k bytes
    - etc...

Tip: Advertised rwnd is always included in ACK packets from the receiver.

#### Congestion Control (cwnd)

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

Tip: Sometimes it can be 2-way close if the server has no more data to send at the time it receives the FIN

## Sending Order
- TCP guarantees in-order delivery to the application
- Data are splitted into Segments and they are numbered by sequence numbers 1,2,3,4
- Sender always send segments in order according to the byte stream
- Receiver may receive buffers out-of-order segments
- Receiver will reassemble them to get them back in order


## Chunking

Almost every protocol that sends variable-length data over the network has some form of logical chunking
- TLS → records (up to 16 KB)
- HTTP/2 → frames (usually 16 KB max)
- Kafka / MQTT → messages or packets

Wireshark Example:

283  TCP   Len=1452   [TCP PDU reassembled in 295]
284  TCP   Len=1452   [TCP PDU reassembled in 295]
...
295  TLSv1.3 Application Data


Here's what happens:

The sender has a tls message (e.g., 16 KB).

TCP splits it into MSS-sized segments (~1452 bytes each).

Wireshark sees the fragments and reassembles them into one logical TLS message (packet 295).

Tip: TCP doesn’t need to wait for all bytes of chunk 1 to be acknowledged before sending chunk 2.