# Shared Responsibility Model

defines who is responsible for what in terms of security and operations.
- AWS is responsible for the security `of the cloud`
- You (customer) are responsible for security `in the cloud`


| AWS responsibility includes:                                |
| ----------------------------------------------------------- |
| Data center physical security (guards, cameras, biometrics) |
| Hardware (servers, storage devices, networking gear)        |
| Virtualization layer / hypervisors                          |
| Power, cooling, redundancy                                  |
| Global infrastructure (Regions, AZs, Edge locations)        |



| Customer responsibility depends on the service model:       |
| ----------------------------------------------------------- |
| OS configuration & patching (for EC2/IaaS)                  |
| Application code                                            |
| Data (encryption, backup, access control)                   |
| Network configuration (security groups, subnets, firewalls) |
| User access (IAM policies, MFA, password rotation)          |


#### Summary:
- AWS:    Physical security, hardware, infrastructure, virtualization
- You:    OS config, apps, data, IAM, network configs

#### Memory Trick
- AWS protects the cloud.
- You protect what you do in the cloud.