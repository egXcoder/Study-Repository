# Apache
A web server its responsibility is to listens for requests from browsers and forward these requests to handler like mod_php internally or php-fpm externally and respond to requests

- apache consist of multiple modules, you can enable and disable between them

# apache core commands
- `apt install apache2` .. install on linux
- `ls /etc/apache2/mods-available` ... list of available modules
- `ls /etc/apache2/mods-enabled` .. list of enabled modules (Loaded Modules)
- `apache2ctl -M` .. list of enabled modules loaded successfully into memory
- `a2enmod rewrite` .. enable module
- `a2dismod rewrite` .. disable module

- `ls /etc/apache2/conf-available` ... list of available configuration
- `ls /etc/apache2/conf-enabled` .. list of enabled configuration
- `a2enconf php8.1-fpm` .. enable config
- `a2disconf php8.1-fpm` .. enable config


# Apache Service
- enable service, so that it auto start on system restart `systemctl enable apache2`
- start service `systemctl start apache2`
- restart service `systemctl restart apache2` .. stop service completely and kill all processes, threads and build them again
- reload service `systemctl reload apache2` .. dont stop service, just reload its configurations.. 
    -- Current connections remain open and continue to work.
    -- New connections will use the updated configuration. 
    -- It does not re-load or re-apply loaded modules.
- see service status `systemctl status apache2`

# Apache MPM Workers (Multi-Processing Modules Workers)

know current worker using `apachectl -V` ... it will show you the mpm worker that is running

Apache has different MPMs that define how it handles concurrency:
- prefork → processes only, no threads (old, safe for non-thread-safe PHP).
- worker → multiple processes, each with multiple threads.
- event → like worker, but with smarter handling of idle keep-alive connections.

## Event MPM

### 1. Startup
When Apache starts with **event MPM**, it:

- Spawns several processes (`ServerLimit`).
- In each process, it creates:
  - **1 listener thread** → waits for new connections
  - **N worker threads** (`ThreadsPerChild`) → process requests

Additional directives:

- `AsyncRequestWorkerFactor` → multiplier factor to determine how many idle connections each process can keep track of.
- `MaxRequestWorkers` → defines the total number of **active requests** across all processes.

**Example configuration:**

- ServerLimit = 4
- ThreadsPerChild = 25
- AsyncRequestWorkerFactor = 2
- MaxRequestWorkers = 100


**Result:**

- **1 parent process** (Apache itself)
- **4 processes × (1 listener + 25 workers) = 105 total threads**
- Each process can track `2 × 25 = 50` idle connections (keep-alive)
- Total active request limit = **100** (`MaxRequestWorkers`), typically it would match `4 × 25 = 100` by default, you can assign lower if you want to reduce concurrency

---

### 2. Accepting Connections
- A client connects (for example, a browser opens a TCP connection).
- The **listener thread** in one process accepts it.
- The connection is registered in an **event loop** (`epoll` on Linux, `kqueue` on BSD, `select/poll` as fallback).
👉 At this stage, the connection exists but **no worker thread is tied up yet**.

---

### 3. Handling Requests
- If the client sends an HTTP request, the event loop detects *data ready*.
- A **worker thread** is assigned to handle the request:
  - Parses headers
  - Passes request to modules (for example, `mod_proxy_fcgi` for PHP-FPM)
  - Generates the response
  - Sends the response back

After that:

- If the connection is **closed** → the worker thread is freed.
- If it’s **keep-alive** → the connection goes back into the event loop (idle), and the worker thread is released.

---

### 4. Keep-Alive Magic (Why Event > Worker)
- **Worker MPM:** A worker thread stays stuck as long as the connection is open (even if idle).
- **Event MPM:** The worker thread is released after finishing the request. The **event loop** continues watching the idle connection.
  - If the client sends another request, a worker thread is reassigned.


Q: is event loop and queue is in parent process or in the child process or both?

In Apache Event MPM, the event loop and the connection queue live in the child processes

Parent process
- Starts up and spawns the child processes.
- Does not handle requests or connections directly.
- Its job is mainly: monitoring children, restarting them if they die, reloading config, etc.

Child processes
- Each child process has:
    - One listener thread → accepts new connections from the socket (port 80/443).
    - One event loop (inside that listener thread) → tracks all active/idle connections for that child.
    - Worker threads → process active requests when the event loop says “data is ready.”

there isn’t one big global event loop in the parent — instead, each child has its own mini event loop + pool of worker threads.


# Apache PHP
by default apache doesnt auto install php, you have to install it.. so you have two ways to handle requests ..
 - using mod_php which is php internally into apache
 - using php-fpm (FastCGI Process Manager) 

notices:
- mod_php is simpler, but PHP-FPM is usually preferred today (better performance, works with Nginx, more flexible).
- mod_php, Apache can only use one PHP version at a time. PHP-FPM allows multiple versions.
- mod_php does not work with the event or worker MPM. You need Apache’s prefork MPM.


## mod_php
- install by `apt install libapache2-mod-php` this will automatically install apache mod-php + php itself (recent version)
- enabled by `a2enmod php*` the * matches the installed version, e.g., php8.1

- notice: you have to change worker to be prefork as mod_php require mpm-worker prefork always `sudo a2dismod mpm_event && sudo a2enmod mpm_prefork && sudo systemctl restart apache2`

### change mod_php version 
- running this won't work because php7.4 is not there on official repo, so it cant find it and will revert back to the recent one which is php8.1 `apt install libapache2-mod-php7.4`

- you have to Add the PHP PPA (Ondřej Surý’s repo) which contains multiple PHP versions (7.4, 8.0, 8.1, 8.2, …).
`add-apt-repository ppa:ondrej/php` then `apt update`

- now you can `apt install libapache2-mod-php7.4`

- make sure php7.4 module is added to apache available modules `ls /etc/apache2/mods-available/`

- now enable it `a2enmod php7.4 && systemctl restart apache2` 

- in same way, we can add another older version, example php5.6 
`apt install libapache2-mod-php5.6 && a2dismod php7.4 && a2enmod php5.6 && systemctl restart apache2`


## PHP-FPM (FastCGI Process Manager)
- install by `apt install php8.1-fpm`
- enable it `systemctl enable php8.1-fpm`, or enable and start now `systemctl enable php8.1-fpm --now`
- start it `systemctl start php8.2-fpm`
- enable required modules `a2enmod proxy_fcgi setenvif mpm_event rewrite`
- enable required configuration `a2enconf php8.1-fpm`

- to change php-fpm version `apt install php7.4-fpm && a2dismod php8.1-fpm && a2disconf php8.1-fpm && a2enmod php7.4-fpm && a2enmod php7.4-fpm`


## Apache Directives
- `<IfModule proxy_fcgi_module> .... </IfModule>` .. check if module is enabled then do what is inside
- `<IfModule !proxy_fcgi_module> .... </IfModule>` .. check if module is not enabled then do what is inside
- `<FilesMatch ".+\.ph(ar|p|tml)$"> ... </FilesMatch>` .. check if file Matches {file}.php, {file}.phtml, {file}.phar
- `Require all denied` .. reply back with 403 Forbidden
- `SetEnvIfNoCase ^Authorization$ "(.+)" HTTP_AUTHORIZATION=$1`
    -- "SetEnvIfNoCase": creates or modifies environment variables based on incoming HTTP headers. NoCase means it ignores header case (Authorization, authorization, AUTHORIZATION → all matched).
    -- "^Authorization$" : Regex pattern: matches the HTTP header named Authorization
    -- "(.+)": Captures the value of the header (whatever comes after Authorization:). Example: Authorization: Bearer abc123 → captured string = Bearer abc123.
    -- "HTTP_AUTHORIZATION=$1" : Stores the captured value in an environment variable called HTTP_AUTHORIZATION.
    -- so that $_SERVER['HTTP_AUTHORIZATION']
    -- Why? Apache, by default, strips or consumes the Authorization header during its own authentication phase (because it thinks it should handle Basic/Digest auth). Unless you explicitly tell Apache to forward it, PHP-FPM never sees it.

- `SetHandler "proxy:unix:/run/php/php8.1-fpm.sock|fcgi://localhost"`
    -- "SetHandler": Overrides how Apache should handle matching files/requests.
    -- "proxy:": Means “use Apache’s proxy system.” Specifically proxy_fcgi_module (FastCGI proxy).
    -- "unix:/run/php/php8.1-fpm.sock": Path to the PHP-FPM Unix domain socket. PHP-FPM listens here instead of a TCP port (like 127.0.0.1:9000). Unix sockets are faster and more secure (only local processes can use them).
SetHandler "proxy:unix:/run/php/php8.1-fpm.sock|fcgi://localhost"
    -- "|": The | separates the socket path from the backend “URL.”
    -- "fcgi://localhost": fcgi://localhost is just a label for Apache’s proxy system — it doesn’t actually use TCP here since we already gave it a Unix socket. Required syntax so Apache knows it’s talking to a FastCGI server.