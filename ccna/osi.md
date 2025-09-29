# OSI Model


## Easy Way to Remember the Layers

👉 All People Seem To Need Data Processing
- (A)pplication
- (P)resentation
- (S)ession
- (T)ransport
- (N)etwork
- (D)ata Link
- (P)hysical


## When you open google.com:
- Application (Layer 7): Browser sends HTTP request.
- Presentation (Layer 6): Data is encrypted with HTTPS (SSL/TLS).
- Session (Layer 5): Session is established between your browser and Google's server.
- Transport (Layer 4): TCP ensures reliable transmission. (Segment.TCP /Datagram.UDP)
- Network (Layer 3): IP routes the packets across the internet. (Packet)
- Data Link (Layer 2): Uses MAC addresses to communicate inside your local network. (Frames)
- Physical (Layer 1): Data is sent as electrical/optical signals via cable or Wi-Fi.


## Segment vs packets vs frames
Segment: Transport (Layer 4) .. data + port source + port destination
Packet: Network (Layer 3) .. encapsulate(data+addressing port) + ip address source and ip address destination
Frames: Data Link (Layer 2) .. encapsulate(packet) + mac address


## technologies
- Transport (L4) .. TCP
- Network (L3)   .. IPV4
- Data Link (L2) .. Ethernet


## Transport L4 Deep
- TCP Reliable if Segment lost then ask for it again
- UDP Fast but if Datagram lost then its fine, no problem (video/audio calls)

Segmentation in L4:
is to split data into segments and each segments will say what is the source and destination port

Sequencing in L4
is to order the segments like 1,2,3,4,....

### TCP

- 3 way handshake to init Connection:
    1. "SYN"        Client → Server : I want to start a connection. My initial sequence number is X.
    2. "SYN + ACK"  Server → Client : SYN+ACK: I acknowledge your request X+1. My initial sequence number is Y.
    3. "ACK"        Client → Server : ACK: I acknowledge your sequence number Y+1. Let’s begin.


- 4 way handshake to close connection:
    1. "FIN"       Client → Server  : I’m done sending data.
    2. "ACK"       Server → Client  : I received your FIN." (But server might still send data)
    3. "FIN"       Server → Client  : Now I’m also done sending.
    4. "ACK"       Client → Server  : "Goodbye — confirmed." ✅ Connection closed

- MSS (Maximum Segment Size)
    - is the maximum segment size can be received
    - say MSS = 1000 bytes .. i can receive segments up to 1000 bytes of data
    - MSS is only negotiated during the TCP 3-way handshake and stays fixed for the duration of the connection.

    - Q:why actual sent segments would vary in length?
        - appliacation data sent is < 1000 byte
        - sending last segment, the final chunk may not align perfectly with MSS.

    - Q: How Do Client and Server Know Each Other's MSS?
        - MSS Negotiation During Handshake
            - SYN, MSS = 1460 bytes client -> server 
            - SYN + ACK MSS = 1200 bytes .. server->client
            - Client will not send segments larger than 1200 (as server advertised)
            - Server will not send segments larger than 1460 (client’s advertised MSS)

- Flow Control
    - Flow control ensures the sender does not overwhelm the receiver. (protect receiver device)
    - Every device has limited buffer space to store incoming data. If the sender sends too much too quickly, the receiver's buffer could overflow, leading to data loss.
    - TCP prevents this by using a mechanism called the Receive Window (rwnd).
    - The receiver continuously tells the sender how much data it can still accept.
    - Originally, TCP’s Window Size field was only 16 bits, meaning: Maximum rwnd = 65,535 bytes (~64 KB)
    - Currently, inital rwnd can grow up to 1GB depend on initial negotiation on handshake
    - Example
        - Receiver buffer = 8000 bytes and Sender starts sending data
        - receiver says 8000 bytes free .. rwnd = 8000 .. Sender can send up to 8000 bytes
        - Sender sends 6000 bytes .. 2000 free .. rwnd = 2000 .. Sender slows down
        - Receiver processes 4000 bytes ... 6000 free .. rwnd = 6000 ... Sender speeds up again
        - Receiver full ... rwnd = 0 ... Sender Pause
        - Periodically, sender sends Window Probe packets (small packets) to check if the window has increased

- Congestion Control
    - Congestion control prevents the network (not the receiver) from being overwhelmed.
    - TCP assumes packet loss = congestion, so it adjusts speed dynamically based on feedback.
    - Effective Window = min(rwnd, cwnd)  .. cwnd (Congestion Window)
    - TCP Congestion Control Algorithms (Classic Version)
        - Slow Start (when connection begins or after timeout)
            - Start with cwnd = 1 MSS
            - Each ACK doubles cwnd (exponential growth)
        - Congestion Avoidance
            - Once cwnd crosses a threshold (ssthresh slow start threshold), growth becomes linear, not exponential.
        - Loss Detected → Reduce Speed
            - If timeout → set ssthresh = cwnd / 2 then cwnd = 1 MSS, restart Slow Start
            - If 3 duplicate ACKs → Fast Retransmit + Fast Recovery cwnd = cwnd/2 → Linear Recovery
    - Phases
        - Slow Start  .. Probe network capacity ... Exponential
        - Congestion Avoidance  .. Avoid overloading ... Linear
        - Fast Retransmit / Recovery  .. After segment loss ... Halve cwnd

- Effective Window
    - Sender Can Send Effective Window for example 1000 byte, then sender have to wait to hear for acknowledge
    - Example 1: Effective Window = 1000 bytes, MSS = 1000 bytes
        - Sender can send 1 full segment of 1000 bytes
        - After sending this segment → sender must wait for ACK (since 1000 bytes are now in flight = EW)
        - Once ACK arrives → sender can send the next 1000-byte segment
    - Example 2: Effective Window = 1000 bytes, MSS = 300 bytes
        - Sender can send multiple smaller segments as long as total in-flight ≤ 1000 bytes
        - Segments sent: 300 + 300 + 300 = 900 bytes (still < 1000) → OK
        - Can send another 100 bytes → now in-flight = 1000 bytes → must wait for ACK
        - As ACKs come back → window “frees up” → can send more data


- Sending Order
    - TCP guarantees in-order delivery to the application
    - Data are splitted into Segments and they are numbered by sequence numbers 1,2,3,4
    - Sender always send segments in order according to the byte stream
    - Receiver may receive buffers out-of-order segments
    - Receiver will reassemble them to get them back in order


- What actually happens
    - Sender slices data into segments (≤ MSS)
    - Sends segments sequentially until either:
        - all EW bytes are in flight
        - or there’s no more data to send
    - Segments may arrive at different times due to network conditions
    - receiver may get the segments in out of order and re-order them
    - TCP send ew in batches, each batch is segment really
    - if ew is 1000 bytes and all segments i have is 300 byte.. then for every ew i can only send 3 segments of 900 bytes and leave the rest 100 byte unused till next ack . when next ack EW increases Sender can now send more segments to use the freed-up space
    - suppose EW = 500 bytes and Segment size = 1000 bytes, TCP must split the segment, TCP dont like splitting segments unncessarily
