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

Tip: this backslash is required when you are splitting a command in multiple lines as bash read command line by line
- bad
    ```bash
    # bash is going to read `aws ec2 create-internet-gateway` as a command 
    #--region us-east-1 is another command . which gives syntax error
    
    igw_id=$(
        aws ec2 create-internet-gateway 
            --region us-east-1
            --query "InternetGateway.InternetGatewayId"  
            --output text
    )
    ```
- good
    ```bash
    # \ is interpreted by bash as line continuation
    igw_id=$(
        aws ec2 create-internet-gateway \
            --region us-east-1 \
            --query "InternetGateway.InternetGatewayId" \  
            --output text
    )
    ```


### Attach Intenet Gateway to VPC

```bash

attach_igw_to_vpc(){
    local attached_vpc_id=$(aws ec2 describe-internet-gateways \
        --region us-east-1 \
        --internet-gateway-ids $igw_id \
        --query "InternetGateways[*].Attachments[*].VpcId" \
        --output text
    )

    attached_vpc_id=$(echo $attached_vpc_id | awk '{print $1}')

    if [ "$attached_vpc_id" == "" ]; then
        attach_response=$(aws ec2 attach-internet-gateway \
            --region us-east-1 \
            --internet-gateway-id $igw_id \
            --vpc-id $vpc_id
        )

        if [ "$attach_response" == "" ]; then
            echo "Internet gateway is attached successfully"
        else
            echo "couldnt attach internet gateway"
        fi
    else
        echo "Internet gateway is already attached to $attached_vpc_id"
    fi
}

attach_igw_to_vpc

```

Tip: using aws cli you should always declare region explicitly then you dont get unexpected results

Tip: `echo "12 13 14 15" | awk '{print $2}'` will give 13 
Tip: `echo "12,13,14,15" | awk -F, '{print $2}'` will give 13 


### Create Public and private route tables

```bash

create_route_tables()
{
    local is_rtb_exist=$(aws ec2 describe-route-tables \
        --region us-east-1 \
        --filters Name=tag:Name,Values=$1-devops90-rtb \
        --query "RouteTables[*].RouteTableId" \
        --output text
    )

    is_rtb_exist=$(echo $is_rtb_exist | awk '{print $1}')

    if [ "$is_rtb_exist" == "" ]; then
        rtb_id=$(aws ec2 create-route-table \
            --region us-east-1 \
            --vpc-id $vpc_id \
            --tag-specifications ResourceType=route-table,Tags="[{Key=Name,Value=$1-devops90-rtb}]"  \
            --query "RouteTable.RouteTableId" \
            --output text
        )

        if [ "$rtb_id" == "" ]; then
            echo "couldnt create route table"
        else
            echo "route table is created with id $rtb_id"
        fi
    else
        rtb_id=$is_rtb_exist
        echo "$1 route table exist already"
    fi

    if [ "$1" == "public" ]; then
        is_success=$(aws ec2 create-route \
        --region us-east-1 \
        --route-table-id $rtb_id \
        --destination-cidr-block 0.0.0.0/0 \
        --gateway-id $igw_id \
        --query "Return" \
        --output text
        )

        if [ "$is_success" == "True" ]; then
            echo "$1 Route is created successfully against route table"
        else
            echo "Error While Creating the Route"
        fi
    fi
}

create_route_tables public
public_rtb_id=$rtb_id

create_route_tables private
private_rtb_id=$rtb_id

```
Tip: to compare strings
- `if [ "$name" == "Ahmed" ]; then`
- `if [ "$name" != "Ahmed" ]; then`
- `if [ "$name" == "" ]; then`

Tip: to compare numbers in if you can do 
- `if [ $score -eq 90 ]; then`
- `if [ $score -ne 90 ]; then`
- `if [ $score -gt 90 ]; then`
- `if [ $score -ge 90 ]; then`

Tip: to check if file exist
- `if [ -f "/etc/passwd" ]; then`
- `if [ -d "/etc" ]; then`
- `if [ -r "/etc/passwd" ]; then`
- `if [ -w "/etc/passwd" ]; then`
- `if [ -x "/etc/passwd" ]; then`

### Attach route tables to subnets

```bash

attach_route_table_to_subnet()
{
    local var=$(aws ec2 associate-route-table --region us-east-1 --route-table-id $1 --subnet-id $2 --query "AssociationId" --output text)
    if [ "$var" == "" ]; then
        echo "Coudn't attach route table $1 to subnet $2"
    else
        echo "route table $1 is attached to subnet $2"
    fi
}

attach_route_table_to_subnet $public_rtb_id $public_sub1_id
attach_route_table_to_subnet $public_rtb_id $public_sub2_id

attach_route_table_to_subnet $private_rtb_id $private_sub3_id
attach_route_table_to_subnet $private_rtb_id $private_sub4_id

```


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