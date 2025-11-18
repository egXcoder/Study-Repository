# VPC Bash


### Creating VPC Script

```bash

#!/bin/bash

is_vpc_exist=$(aws ec2 describe-vpcs \
--region us-east-1 \
--filters Name=tag:Name,Values=devops90-vpc \
--query 'Vpcs[*].VpcId' \ # get this value from json response
--output text)

is_vpc_exist=$(echo $is_vpc_exist | awk '{print $1}') # handle if multiple vpcid returned then print the first vpcid

if [ "$is_vpc_exist" == "" ]; then
    vpc_id=$(aws ec2 create-vpc \
        --cidr-block 10.0.0.0/16 --region us-eastt-1 \
        --tag-specification ResourceType=vpc,Tags="[{Key=Name,Value=devops90-vpc}]" \
        --query 'Vpc.VpcId' \
        --output text) # used text rather than json to get vpc_id without double quotes "vpc-0a70afe70ad1de780"

    if [ "$vpc_id" == "" ]; then #bash like spacing except for variable assignment they it dont
        echo "Error On Creating VPC"
        exit 1
    fi

    echo "VPC Created $vpc_id"
else
    vpc_id=$is_vpc_exist
    echo "VPC Already Exists $vpc_id"
fi


echo $vpc_id

```


Tip: on assigning variables, bash dont like spacing 
- `vpc_id=$(command)` .. success
- `vpc_id = $(command)` .. fail

Tip: on if condition, bash want spacing
- success
    ```bash
    if [ "$varia" == "1" ]; then
        # do some bits
    fi
    ```
- error
    ```bash
    if[ "$varia" == "1" ]; then
        # do some bits
    fi
    ```
- error
    ```bash
    if ["$varia" == "1" ]; then
        # do some bits
    fi
    ```


### Creating subnets

```bash

# create subnets
create_subnet()
{
    local is_subnet_exist=$(aws ec2 describe-subnets \
        --region us-east-1 \
        --filters Name=tag:Name,Values=sub-$3-$1-devops90 \
        --query "Subnets[*].SubnetId" \
        --output text
    )

    is_subnet_exist=$(echo $is_subnet_exist | awk '{print $1}')

    if [ "$is_subnet_exist" == "" ]; then
        subnet_id=$(aws ec2 create-subnet \
        --vpc-id $vpc_id \
        --region us-east-1 \
        --availability-zone us-east-1$2 \
        --cidr-block 10.0.$1.0/24 \
        --tag-specifications ResourceType=subnet,Tags="[{Key=Name,Value=sub-$3-$1-devops90}]" \
        --query "Subnet.SubnetId" \
        --output text)

        if [ "$subnet_id" == "" ]; then
            echo "Error in create subnet $3-$1"
            exit 1
        fi

        echo "subnet $3-$1 is created successfully"
    else
        subnet_id=$is_subnet_exist
        echo "subnet $3-$1 already exists"
    fi
}    

create_subnet 1 a public
sub1_id=$subnet_id

create_subnet 2 b public
sub2_id=$subnet_id

create_subnet 3 a private
sub3_id=$subnet_id

create_subnet 4 b private
sub4_id=$subnet_id

```

Tip: variables declared in function is global scoped, unless you explicitly say `local var=$(command)`

Tip: Everything inside single quotes '' is taken literally. No variable expansion, no command substitution. Escape sequences like \n won’t work.
- `echo 'Hello $name'` .. show hello $name literally
- `echo "Hello $name"` .. show hello ahmed 


### Create Intenet Gateway

```bash

create_internet_gateway()
{
    local is_igw_exist=$(aws ec2 describe-internet-gateways \
        --region us-east-1 \
        --filters Name=tag:Name,Values=devops90-igw \
        --query "InternetGateways[*].InternetGatewayId" \
        --output text
    )

    is_igw_exist=$(echo $is_igw_exist | awk '{print $1}')

    if [ "$is_igw_exist" == "" ]; then
        igw_id=$(aws ec2 create-internet-gateway \
            --region us-east-1 \
            --tag-specifications ResourceType=internet-gateway,Tags="[{Key=Name,Value=devops90-igw}]" \
            --query "InternetGateway.InternetGatewayId" \
            --output text
        )

        if [ "$igw_id" == "" ]; then
            echo "Error in create internet gateway"
            exit 1
        fi

        echo "IGW created $igw_id"
    else
        igw_id=$is_igw_exist
        echo "Already exists $igw_id"
    fi
}

create_internet_gateway

```

Tip: TODO::to explain AWK

### Q: when i wrote the bash file in notepad++ and try to execute it in window, its giving strange errors?

The difference between CRLF and LF is how a new line is represented in a text file. Computers need a special character to know when a line of text ends and a new one begins. Different operating systems historically chose different conventions:

| OS                   | New Line Code | Name                        | Characters |
| -------------------- | ------------- | --------------------------- | ---------- |
| Windows              | CR + LF       | Carriage Return + Line Feed | `\r\n`     |
| Linux / Unix / macOS | LF            | Line Feed                   | `\n`       |


When you edit a script in Windows (CRLF) and try to run it in Linux (expects LF), Linux interprets the extra \r as a character, not whitespace.

So you get errors like: /bin/bash^M: bad interpreter

The ^M means the hidden CR (\r) character is causing trouble.

so to edit file, you have to make sure you are on LF (linux) rather than CRLF (window)