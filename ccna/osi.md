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