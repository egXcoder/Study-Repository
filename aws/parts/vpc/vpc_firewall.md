# VPC Firewall

### Layers
1. Route53 (DNS)
2. Access Control List (Subnet)
3. Security Group (Machine)
4. local firewall (inside machine)

### Access Control List (Subnet)

- It acts as a `firewall for the subnet`.
- is a set of rules that control `inbound` and `outbound` traffic for subnets
- Stateless: If you allow inbound traffic on a port, you must also allow outbound traffic explicitly. Each request and response is evaluated separately.

Inbound Rules:
| Rule #    | Type    | Protocol | Port Range | Source/Destination | Action |
| --------- | ------- | -------- | ---------- | ------------------ | ------ |
| 100       | HTTP    | TCP      | 80         | 0.0.0.0/0          | Allow  |
| 110       | SSH     | TCP      | 22         | 192.168.1.0/24     | Allow  |
| 120       | All TCP | TCP      | 0-65535    | 0.0.0.0/0          | Deny   |
| *Default* | All     | All      | All        | All                | Deny   |

- Rules are evaluated in order from lowest to highest.
- First matching rule applies, after which evaluation stops.
- If no rule matches, the default rule applies.


### Security Group (Machine)

- It acts as a `firewall for compute such as EC2`.
- is a set of rules that control `inbound` and `outbound` traffic for EC2 instances (or other AWS resources that support SGs, like RDS, Lambda in VPC, etc.).
- Each instance must have at least one security group.

Inbound Rules:
| Type  | Protocol | Port Range | Source         |
| ----- | -------- | ---------- | -------------- |
| SSH   | TCP      | 22         | 192.168.1.0/24 |
| HTTP  | TCP      | 80         | 0.0.0.0/0      |
| HTTPS | TCP      | 443        | 0.0.0.0/0      |

Outbound Rules:
| Type        | Protocol | Port Range | Destination |
| ----------- | -------- | ---------- | ----------- |
| All Traffic | All      | All        | 0.0.0.0/0   |
