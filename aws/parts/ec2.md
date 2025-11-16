# EC2 Elastic Compute Cloud

is an AWS service that provides virtual servers in the cloud.


You control:

- CPU && RAM
- Storage disks
- Security and networking
- When to start/stop/delete the machine
- Operating system (Linux / Windows)


### Building Blocks
| Component      | Meaning                                                          |
| -------------- | ---------------------------------------------------------------- |
| Instance       | The virtual machine (server)                                     |
| AMI            | The image used to create the OS (Ubuntu, Amazon Linux, Windows…) |
| Instance Type  | CPU & RAM configuration (e.g., t2.micro, m5.large)               |
| EBS Volume     | Disk attached to the instance                                    |
| SSH Key Pair   | SSH password alternative for login                               |
| Security Group | Firewall (controls inbound/outbound access)                      |
| Elastic IP     | Static public IP that doesn’t change                             |
| VPC            | Network interface                                                |


Tip: You can stop an instance anytime to reduce cost. When stopped: CPU/RAM billing stops but EBS storage continues.


### Families

| Family                                        | Optimized For                | Typical Use Cases                        |
| --------------------------------------------- | ---------------------------- | ---------------------------------------- |
| **General Purpose – A, T, M**                 | Balanced CPU + RAM           | Web apps, APIs, small databases          |
| **Compute Optimized – C**                     | High CPU power               | High-performance computing, online games |
| **Memory Optimized – R, X, Z, U**             | Very high RAM                | Large databases, in-memory cache         |
| **Storage Optimized – I, D, H**               | Very fast SSD / NVMe         | Big data, NoSQL, OLAP                    |
| **Accelerated Computing – P, G, Trn, Inf, F** | GPU / machine learning chips | AI/ML, GPU rendering, video, genomics    |
| **Mac – mac1, mac2**                          | Apple silicon                | iOS/macOS mobile app development         |


#### General Purpose:

| Family | Example Types    | Notes                             |
| ------ | ---------------- | --------------------------------- |
| **A**  | a1               | Cheapest (Arm CPU)                |
| **T**  | t2, t3, t3a, t4g | Burstable CPU — good for websites |
| **M**  | m5, m6i, m7g     | Always strong performance         |


Tip: m6 is newer version than m5, 90% newer version is better performance with same cost or lower cost.

Tip: 
- m6i is intel bundle
- m6a is amd bundle
- m6in is intel and network optimized
- m7g is graveton cpu while is a cpu invented by amazon and its arm based

Tip: Burstable Cpu means, normal case scenario cpu will be 2.1ghz and if heavy load it can go to 3.1ghz till it uses all credit.. while on low load there are credits figure is being increased and on heavy load this credit figure decreases

Tip: t3.large, t3.xlarge, t3.medium, t3.small. every variation represent combination of cpu and ram. these variances offer varying speed and costs 


### Pricing Models
| Pricing Model | When to use                                                        |
| ------------- | ------------------------------------------------------------------ |
| On-Demand     | Normal pay-as-you-go, flexible .. by the hours                     |
| Savings Plans | Long-term usage commitment (1–3 years) → cheaper                   |
| Spot          | Bid for unused servers → **up to 90% cheaper**, can be interrupted |

Tip: Spot model: aws will give your free server for your work, but aws can take it back anytime if they want


Tip: above pricing model is for the ec2 itself however there are other resources ec2 uses which is different in billing
- Compute (the server itself) -> as per pricing model above
- Storage — EBS Volumes -> billed GB/Month even if its unattached to ec2
- Network — Data Transfer
    -   | Direction                        | Cost                            |
        | -------------------------------- | ------------------------------- |
        | Data **in** to EC2 from internet | **Free**                        |
        | Data **within same region**      | Usually free or very cheap      |
        | Data **out to internet**         | **Charged** (main network cost) |
        | Data between AZs                 | Charged                         |
- Elastic IP -> Free as long as its running, but charged if it attached to stopped ec2 or detached from ec2


### Launch Templates

A Launch Template is a reusable configuration that contains the settings needed to launch an EC2 instance, such as:

| Setting         | Example        |
| --------------- | -------------- |
| AMI             | Ubuntu 22.04   |
| Instance Type   | t3.medium      |
| Storage         | GP3 50GB       |
| Security Groups | sg-web-01      |
| Key Pair        | prod-keypair   |
| Network         | VPC + Subnet   |
| User Data       | startup script |
| IAM Role        | EC2-role-app   |


Once you create the template, launching a new EC2 server becomes a one-click or automated process.


Tip: User Data is bash script that runs when the instance is being launched for first time used to prepare your ec2 for your usage like install specific webservers/ databases etc...

Tip: User Data runs as root by default. if you want user to run the command you can write `sudo -u ubuntu echo "hello"`