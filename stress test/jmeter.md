# stress test

jmeter requires java run time

# Install

- Install Java (JMeter requires Java 8 or later):
`sudo apt update`
`sudo apt install default-jre`

Download the latest JMeter (from their website):
`wget https://dlcdn.apache.org//jmeter/binaries/apache-jmeter-5.6.3.tgz`


Extract:
`tar -xvzf apache-jmeter-5.6.3.tgz`


Move to /opt (optional but clean):
`sudo mv apache-jmeter-5.6.3 /opt/jmeter`


Run JMeter:
`/opt/jmeter/bin/jmeter`


(Optional) Create a Global Shortcut So you can just type jmeter from anywhere:

`echo 'export PATH=$PATH:/opt/jmeter/bin' >> ~/.bashrc`
`source ~/.bashrc`

Run JMeter:
`jmeter`


# Open

notice jmeter is a gui software and it rely on $DISPLAY environment variable, when you do sudo su -
it login as root and remove environment variables like $DISPLAY so jmeter won't open

to open jmeter you have to login as user normally on ubuntu, then open with `jmeter`

if you want to open jmeter as root, you can start it with `sudo jmeter` or `sudo -E jmeter`



# Usage

In the GUI, you will create a Test Plan. Minimal components for stress testing: Test Plan

- Thread Group → Number of users, ramp-up time, loop count
- HTTP Request → URL of the website you want to test
- Listeners → View results (optional in GUI, for debugging only)

2️⃣ Save Your Test Plan
Save as .jmx file:
stress_test_plan.jmx


## Thread Groups

Example Thread Group Settings
Setting	Example

- Number of Threads (Users)	100
- Ramp-Up Period (seconds)	10
- Loop Count	50

Ramp-Up: How long JMeter will stress

Loop Count: How many times each user repeats the requests

Optional: Add Timers

- Constant Timer → Add delay between requests
- Prevents overwhelming your server unrealistically



## Add HTTP Request in JMeter

In your Test Plan → Thread Group, right-click → Add → Sampler → HTTP Request

Configure it:

Field	Explanation	Example
- Server Name or IP	Domain or IP of your website	example.com
- Port Number	Port (usually 80 for HTTP, 443 for HTTPS)	443
- Protocol	http or https	https
- Path	URL path to request	/login
- Method	HTTP method	GET or POST
- Parameters / Body	Form fields or query parameters	username=abc&password=123
- Follow Redirects	Automatically follow 3xx redirects	✅ Usually yes
- Use KeepAlive	Keep connection open for multiple requests	✅ Improves realism
Example: GET Request

Server Name: example.com
Protocol: https
Path: /
Method: GET

This will simulate users hitting your homepage.

Optional Settings
- HTTP Header Manager	Add headers like Content-Type: application/json
- HTTP Cookie Manager	Handle cookies automatically, needed for login sessions
- HTTP Cache Manager	Simulate browser caching behavior

Multiple HTTP Requests
- You can add multiple HTTP Requests in one Thread Group.
- Each virtual user will execute them in order.
- You can also use Logic Controllers (like Loop Controller, If Controller) to vary behavior.


## View Results

Add a Listener:Right-click on Thread Group → Add → Listener

Choose one of the following depending on your need:
- View Results Tree	Shows every request & response. Great for debugging, but heavy for many users.
- Summary Report	Aggregate stats: min, max, average, throughput. Good for monitoring stress tests.
- Aggregate Report	Similar to Summary Report, shows averages and errors.
- Active Threads Over Time	Graph of how many virtual users are active during the test.
- Graph Results	Simple graph for response times over time.
- View Results in Table	Table view of request results.