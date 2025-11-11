# Global Infrastructure

### Regions

A Region is a geographical area that contains multiple data centers at least 3

Regions Such:
- us-east-1 → North Virginia (USA)
- eu-west-1 → Ireland
- me-south-1 → Bahrain (nearest to Egypt & Middle East)

Every region has its own available services

Every region is isolated from others for fault isolation and data sovereignty.


### Availability Zone

Availability Zone is the data center

Inside each Region, AWS has at least 3 az

Example:
us-east-1 has AZs:
- us-east-1a
- us-east-1b
- us-east-1c
- us-east-1d

Each AZ has:
- Independent power
- Independent networking
- Independent cooling

AZs are located close to each other (few kms) and connected using high-speed fiber.

You deploy your infrastructure across multiple AZs to achieve high availability: DB in AZ-a and replica in AZ-b.


### Edge Locations (CDN / CloudFront)

An Edge Location is a small AWS datacenter that exists in hundreds of cities around the world. Its purpose is to deliver content to users with the lowest possible latency. mainly for static content as CDN
- Caching content (images, videos, static files)
- Reducing latency for users

Services using edge locations:
- Amazon CloudFront (CDN)
- AWS Global Accelerator
- Route53 DNS

### Local Zones

A Local Zone is an AWS data center placed closer to users in major cities, providing low-latency access to AWS compute and storage services.

Used when:
- there is a population of people in major city and many people within this city requires Ultra-low latency
- so we build them localzone near them to serve their need
- applications need Ultra-low latency (gaming, video editing, real-time apps)

AWS REGION (full infrastructure)
   ├── Availability Zone A
   ├── Availability Zone B
   ├── Availability Zone C
   └── (connected) LOCAL ZONE (mini datacenter in a distant city)

Example:
- Region: us-west-2 (Oregon)
- Local Zone: us-west-2-lax-1a (Los Angeles)
- Apps in LA can use EC2 / EBS inside the Local Zone, but still connect back to full services in Oregon (like S3, RDS).