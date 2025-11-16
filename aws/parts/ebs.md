# EBS Elastic Block Storage

its to give you a volume of disk that can be attached to your ec2

it only exist as part of EC2, and doesnt exist on its own

most likely uses idea of SAN (storage area network) and raids

by default data exist in one disk and replicated in the same AZ. you can choose if you want to replicate it across multiple AZ but you will pay more

### ⚡ Performance Metrics — IOPS & Throughput
- IOPS:	Number of read/write operations per second
- Throughput: Amount of data transferred per second (MB/s)

for example:
- High IOPS: if you are doing random i/o to read small chunk of data here and there 
- High Throughput: if you are doing insert command and each insert has big text data

Tip:Usually Workloads focus on one more than the other., for example if we have 100 operation:
- small data .. then high iops and low throughput as each operation will finish fast
- big data .. then low iops and high throughput as each operation will take longer

### Types of EBS Volumes
| Volume Type | Disk Type | Best For                       | Key Metric                 |
| ----------- | --------- | ------------------------------ | -------------------------- |
| gp3         | SSD       | General workloads, 99% of apps | Balanced IOPS + throughput |
| gp2         | SSD       | Legacy workloads               | Size-based IOPS            |
| io2 / io1   | SSD       | Databases needing high IOPS    | Very high IOPS             |
| st1         | HDD       | Big data & streaming           | High throughput            |
| sc1         | HDD       | Archival                       | Cheap storage              |

Tip: gp3 is the recent version of general purpose volumes, in which by default iops = 3000 and throughput = 125 and you can provision it more while you create the volume but you will pay more

Tip: io2 is volume optimized for very high iops if you require more iops higher than the maximum of what gp3 can offer you which is 16k, so if you want iops more than 16k you would go for io2

Tip: i think you can leverage gp3 to ask for more iops and more throughput if you need rather than going to a different volume type

### Pricing

| Charge                 | Applies to                         |
| ---------------------- | ---------------------------------- |
| Storage (GB/month)     | Always                             |
| Provisioned IOPS       | Only for gp3 extra IOPS or io1/io2 |
| Provisioned Throughput | Only for gp3 extra throughput      |
| Snapshots              | S3 pricing                         |
| Data transfer          | Only cross-AZ or cross-region      |



### SAN (Storage Area Network)

A SAN is a dedicated high-performance storage network that provides block-level virtual disks to servers, using many physical disks working together for speed, availability, and flexibility.

- Without SAN: Each server has its own disks inside it → isolated storage.
- With SAN: All servers access storage centrally → shared storage pool.

#### How SAN Works Internally

Inside a SAN storage box:

- Many physical disks (HDD/SSD/NVMe)
- two power-supplier that if one fail the other can still power-up SAN
- Storage Controller manages caching, striping, replication, snapshots (its like tiny OS typically linux)
- controller has RAID configuration in which tell how to replicate and stripe data

when a server asks for a volume then SAN would
- Storage carved into LUNs (virtual disks)
- LUNs are exposed to servers over network
- To servers, a LUN looks like a normal hard disk.

#### Why SAN is fast:
Because SAN storage reads/writes across many disks in parallel.

#### What is RAID10?

RAID10 (also written RAID 1+0) combines Mirroring (RAID1) + Striping (RAID0). is one of raid configurations that combine performance + protection

- RAID1 (Mirroring): Makes duplicate copies of data for redundancy.
- RAID0 (Striping): Splits data across multiple disks for performance

#### How?
- Disk 1 ↔ Disk 2   (Pair 1 (replicates))
- Disk 3 ↔ Disk 4   (Pair 2 (replicates))
- server data is shared across pair 1 and pair 2
    - Write #1 → Pair 1
    - Write #2 → Pair 2
    - Write #3 → Pair 1
    - Write #4 → Pair 2

You can remove disk 2 if it fails, and server still work with no problem and this is the power of SAN high availability

Raid10 fails only if disk 1 and disk 2 both damaged in same time