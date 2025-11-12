# CLI

### Install
- `curl "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -o "awscliv2.zip"`
- `unzip awscliv2.zip`
- `sudo ./aws/install`
- `aws --version`

### Configure

you can configure your default profile by `aws configure`

```bash
toor@DESKTOP-DLNJTCG:~$ aws configure
AWS Access Key ID [None]: AKIAT.........
AWS Secret Access Key [None]: XaCok7CD18y4L6............
Default region name [None]: eu-north-1
Default output format [None]: json
```

you can have more than one profile by amending file `vim ~/.aws/credentials`

```text
[default]
aws_access_key_id = AKIAT7J......
aws_secret_access_key = XaCok7CD18y4L....

[profile2]
aws_access_key_id = AKIAT7J......
aws_secret_access_key = XaCok7CD18y4L....

```


### Run Commands

now you can interact with aws through your cli

- `aws iam list-groups`

Tip: aws command typically is aws {service} {action}, you can always refer back to aws documenation for this