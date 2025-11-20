# AWS CloudWatch

Amazon CloudWatch is a monitoring and observability service for AWS.



### How does CloudWatch work?

CloudWatch has 4 main features:

1. Metrics
    - Numerical measurements over time.
    - Example: EC2 CPU Utilization = 80%
    - You can view charts in dashboards.
    - Standard metrics free, custom metrics cost metric per month

    Tip: metrics are not all instance, there are updated in seconds or minutes or hours etc.. depend on what its watching

    Tip: some metrics may require cloud watch agent to be installed in ec2 instance

2. Logs
    - Stores application logs, system logs, etc.
    - You can search and create metrics from logs.
    - Used heavily for debugging.

3. Alarms
    - Notifications when a metric crosses a threshold.

    - Example:
        - If CPU > 85% for 5 minutes → send email / SMS / trigger Lambda
        - If disk space is low → send alert

4. Events (EventBridge)
    - React to changes in your system.
    - Example:
        - When EC2 instance stops → trigger Lambda
        - When a user uploads object to S3 → send SNS notification


### Free Tier / What’s included

CloudWatch has a free tier (for new users or within each region) to help you start monitoring without huge costs.
- 10 custom metrics per month. 
- 5 GB log data ingestion per month. 
- 3 dashboards per month (in some regions). 
- 10 alarms per month. 