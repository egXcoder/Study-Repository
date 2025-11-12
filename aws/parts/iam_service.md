# IAM Service (Identity Access Management)

- Free Service
- manage users accessing your aws with their permissions
- IAM is a Global Service.It’s not tied to any AWS Region, your users and roles exist globally and can be used across all regions.
- its recommended root user which is the user registered in aws that you dont use it in regular basis and instead use IAM user 
- for root user to access console, he needs email + password + mfa (by email or by another method if configured)
- for iam user to access console, he needs 
    - username + password + account id + mfa(optional)
    - username + password + a link + mfa(optional) (recommended as easier)
- account settings allow you to change your password policy for your iam users like uppercase, lowercase, min characters etc...

### Account

aws account is the registered aws account and it can have one root user + multiple IAM users


### Resources In IAM:

1. Users
    Represent an individual person or application interact with aws api with access keys

    Each user can have:
    - A username
    - Password (for AWS Management Console)
    - Access keys (for API/CLI access)
    - Attached permissions (through policies or groups)

    📘 Example:
    arn:aws:iam::123456789012:user/Ahmed

2. Groups

    - A collection of IAM users that share common permissions.

    - Policies attached to a group automatically apply to all its users.

    📘 Example:
    arn:aws:iam::123456789012:group/Developers


3. Roles
    
    Roles typically is assigned to internal services to get permission to access other services without credentials

    Goal: An EC2 instance needs to read files from an S3 bucket.
    - You create a role (e.g., EC2S3ReadRole).
    - Attach a policy giving permission like:
    ```json
    {
        "Effect": "Allow",
        "Action": "s3:GetObject",
        "Resource": "arn:aws:s3:::mybucket/*"
    }
    ```
    - Attach this role to your EC2 instance.
    - EC2 automatically receives temporary credentials for that role.
    - Your app inside EC2 can now call aws s3 ls mybucket — without needing any stored keys.
    - ✅ No static credentials.
    - ✅ Automatic rotation.
    - ✅ Limited scope (only S3 read).
    

    📘 Example:
    arn:aws:iam::123456789012:role/S3AccessRole


4. Policies
    JSON documents that define permissions.

    Policies can be:
    - Define what actions can be performed.
    - AWS-provided or you can create a custom policy
    - you can edit policy by json or by visual editor with checkboxes

    📘 Example:
    ```json
    {
        "Effect": "Allow",
        "Action": "s3:*",
        "Resource": "*"
    }
    ```


5. Identity Providers (IdPs)

    Allow external users (from Google, Active Directory, etc.) to access AWS resources.

    Used for federated access via SAML or OpenID Connect.

    📘 Example:
    arn:aws:iam::123456789012:saml-provider/CompanyAD



Tip: Principle: is any entity that can take an action or make a request in AWS
- Root User
- IAM user
- IAM user accessing resources through api with access keys
- another service trying to do something on another service through roles

Tip: Any User/Service try do any action has to pass through IAM service first to authenticate + authorize the principle

Tip: root user doesnt pass on IAM as he is authorized for everything

<!-- 🚦 Best Practices (must know for exams/interviews)

Create individual IAM users (never share root account)

Use Groups to manage permissions

Grant least privilege (only what is necessary)

Enable MFA for all users

Use Roles instead of embedding access keys

Rotate credentials regularly

Use IAM Access Analyzer to review resource access -->