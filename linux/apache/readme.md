# Apache
A web server its responsibility is to listens for requests from browsers and forward these requests to handler like mod_php internally or php-fpm externally and respond to requests

- apache consist of multiple modules/config/sites, you can enable and disable between them

# apache core commands
- `apt install apache2` .. install on linux
- `apache2ctl -V` .. show apache version with its mpm (prefork or worker or event)
- `apache2ctl -M` .. list of enabled modules loaded successfully into memory

## modules
- `ls /etc/apache2/mods-available` ... list of available modules
- `ls /etc/apache2/mods-enabled` .. list of enabled modules (Loaded Modules)
- `a2enmod rewrite` .. enable module
- `a2dismod rewrite` .. disable module

## config
- `ls /etc/apache2/conf-available` ... list of available configuration
- `ls /etc/apache2/conf-enabled` .. list of enabled configuration
- `a2enconf php8.1-fpm` .. enable config
- `a2disconf php8.1-fpm` .. enable config

## sites
- `ls /etc/apache2/sites-available` ... list of available sites
- `ls /etc/apache2/sites-enabled` .. list of enabled sites
- `a2ensite your-site-ssl.conf` .. enable site
- `a2dissite your-site-ssl.conf` .. enable site


# Apache Service
- `systemctl enable apache2` enable service, so that it auto start on system restart 
- `systemctl enable apache2 --now` enable service and start it as well 
- `systemctl start apache2` start service 
- `systemctl restart apache2` restart service .. stop service completely and kill all processes, threads and build them again
- `systemctl reload apache2` reload service .. dont stop service, just reload its configurations.. 
    -- Current connections remain open and continue to work.
    -- New connections will use the updated configuration. 
    -- It does not re-load or re-apply loaded modules.
- `systemctl status apache2` see service status 


# Apache MPM Workers (Multi-Processing Modules Workers)

`apachectl -V` know current mpm

Apache has different MPMs that define how it handles concurrency:
- prefork → processes only, no threads (old, safe for non-thread-safe PHP).
- worker → multiple processes, each with multiple threads.
- event → like worker, but with smarter handling of idle keep-alive connections.

## Event MPM

### Architecture
- Even though you connect to Apache via one port (like 80 or 443), that doesn’t mean its the parent process which listens.
- Actually, the parent process never directly accepts or processes client traffic. It only creates the listening socket, forks children, and supervises.
- Parent forks N child processes
- All child processes inherit the same listening socket from the parent and waiting to accept connections in parallel
- All listening processes are technically "notified" for a new connection but only one of them can accept

### Configuration
- ServerLimit = 4
- ThreadsPerChild = 25
- AsyncRequestWorkerFactor = 2
- MaxRequestWorkers = 100


Result:
- 4 Processes
- 25 thread per process
- `2 × 25 = 50` Each process can track idle connections (keep-alive)
- 100 max concurrent active requests Apache can handle overall


Notice: every process in the 4 process, has by default one thread (listener thread) which listen for new connections, so actually each process has 26 thread



### Connecting
- A client connects (for example, a browser opens a TCP connection).
- The listener thread in one process accepts it.
- The connection is registered in the process event loop (`epoll` on Linux, `kqueue` on BSD, `select/poll` as fallback)
👉 At this stage, the connection exists but no worker thread is tied up yet.


### Handling Requests
- If the client sends an HTTP request, the event loop detects data ready.
- A worker thread is assigned to handle the request:
  - Parses headers
  - Passes request to modules (for example, `mod_proxy_fcgi` for PHP-FPM)
  - Generates the response
  - Sends the response back

After that:
- If the connection is closed → the worker thread is freed.
- If it’s keep-alive → the connection goes back into the event loop (idle), and the worker thread is released.

---

### Keep-Alive Magic (Why Event > Worker)
- Worker MPM: A worker thread stays stuck as long as the connection is open (even if idle).
- Event MPM: The worker thread is released after finishing the request. The event loop continues watching the idle connection.
  - If the client sends another request, a worker thread is reassigned.


# Apache PHP
by default apache doesnt auto install php, you have to install it.. so you have two ways to handle requests ..
 - mod_php which is php internally into apache
 - php-fpm (FastCGI Process Manager) ... It’s a separate service that runs PHP as an independent backend processor instead of embedding PHP directly into the web server. it works as one request / one process

notices:
- mod_php is simpler, but PHP-FPM is usually preferred today (better performance, works with Nginx, more flexible).
- mod_php works only with prefork. it doesnt work with worker or event
- php-fpm is 

Q: why mod_php works only with prefork?
because mod_php is non thread safe, as prefork doesnt require threads, so it works great with mod_php. but worker and event require threading so it need a version of php which is thread safe

Q: so why didnt apache create a module like mod_php_ts to work with worker and event instead of proxy to php-fpm?
Because making PHP truly thread-safe would require rewriting large parts of PHP itself and its extensions. It’s not just an Apache problem — it’s a deep architectural limitation of PHP and its ecosystem.

1. PHP Was Designed for Single-Threaded Execution
2. A Thread-Safe Build of PHP Used to Exist… and Failed
- Windows PHP binaries still have php-ts and php-nts builds.
- But even php-ts isn’t truly thread-safe, because most extensions are not.
- Worse, thread-safe builds are slower due to locking overhead.
So even when thread-safety was attempted, performance dropped and compatibility broke.
3. PHP-FPM Was a Cleaner, More Scalable Solution
✅ Keep PHP single-threaded
✅ Let Apache, Nginx, or Caddy handle concurrency
✅ Use FastCGI to connect them


## mod_php
- `apt install libapache2-mod-php` install mod-php and php itself (recent version)
- `a2enmod php*` enabled the * matches the installed version, e.g., php8.1
- `a2dismod mpm_event && a2enmod mpm_prefork && sudo systemctl restart apache2` you have to change worker to be prefork

### change mod_php version 
- running this won't work because php7.4 is not there on official repo, so it cant find it and will revert back to the recent one which is php8.1 `apt install libapache2-mod-php7.4`

- `add-apt-repository ppa:ondrej/php && apt update` you have to Add the PHP PPA (Ondřej Surý’s repo) which contains multiple PHP versions (7.4, 8.0, 8.1, 8.2, …).

- `apt install libapache2-mod-php7.4` install

- `ls /etc/apache2/mods-available/` make sure php7.4 module is added to apache available modules 

- `a2enmod php7.4 && systemctl restart apache2` now enable it 

- in same way, we can add another older version, example php5.6 
`apt install libapache2-mod-php5.6 && a2dismod php7.4 && a2enmod php5.6 && systemctl restart apache2`


## PHP-FPM (FastCGI Process Manager)
- `apt install php8.1-fpm` install by 
- `systemctl enable php8.1-fpm --now` enable it and start now
- `a2enmod proxy_fcgi setenvif mpm_event rewrite` enable required modules 
- `a2enconf php8.1-fpm` enable required configuration 
- to change php-fpm version `apt install php7.4-fpm && a2dismod php8.1-fpm && a2disconf php8.1-fpm && a2enmod php7.4-fpm && a2enmod php7.4-fpm`

Q: In FPM, is every request a new process?
yes, FPM keeps a pool of long-lived processes ready and each request go to a process. it does not fork a new process per request though (that would be too expensive).  

Q: Do I ever need to worry if PHP version is thread-safe (TS) or non-thread-safe (NTS)?

not really, it will be always NTS. unless its windows and using the winnt apache mpm then you may need the TS

If you are running PHP with PHP-FPM (the modern and most common setup with Apache `event/worker` MPM or Nginx), you dont need the thread-safe version. PHP-FPM uses multiple processes, not threads, so NTS is the standard and faster choice.  

Q: we have prefork is one request one process, and we have php-fpm is one request one process. so why would we make apache to be event and threading and all of that while at the end you will rely on fpm which is one request one process?

- Event is better than prefork in handling idle connections. in event, apache Keep-Alive idle connections in event loop queue, while in prefork idle connections stays as separate processes
- Event is better than prefork in allowing multiplexing hence http2. Event can allow multiplexing since connection live in one process, so every request is a thread within this process so multiplexing is possible. while in prefork every request is a new separate process so multiplexing is not possible
- Apache itself benefits massively from being Event/Threaded — because not all requests are PHP.


# Apache HTTPS
- `a2enmod ssl` enable ssl module
- `mkdir -p /etc/apache2/ssl`
- `openssl req -x509 -nodes -days 365 -newkey rsa:2048 -keyout /etc/apache2/ssl/apache-selfsigned.key -out /etc/apache2/ssl/apache-selfsigned.crt` .. generate self signed certificate
- `nano /etc/apache2/sites-available/your-site-ssl.conf` .. configure apache for https
"<VirtualHost *:443>
    ServerName example.com
    DocumentRoot /var/www/html

    SSLEngine on
    SSLCertificateFile /etc/apache2/ssl/apache-selfsigned.crt
    SSLCertificateKeyFile /etc/apache2/ssl/apache-selfsigned.key

    <Directory /var/www/html>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>"

- `a2ensite your-site-ssl.conf` .. enable apache for https


Q: if i don't put fqdn in self signed certificate is that an issue, or if i added another domain other than the one will be using?

With SSL certificates, the hostname matters a lot.
- If you don’t put an FQDN (use only localhost or leave it blank):
    - The certificate won’t match your actual domain.
    - Browsers will show a warning/error like “Certificate not valid for this site”.
    - You can still click Proceed (in dev/test), but it’s insecure for production.

- If you add another domain (say cert says mytest.com, but you use myreal.com):
    - Same issue: hostname mismatch → browser warning.
    - The browser only trusts a certificate if the domain in the address bar matches one of the names inside the certificate.

Q: can certificate have multiple domains?

yes, Certificates have a Common Name (CN) and/or Subject Alternative Names (SANs).
If your cert has: CN = example.com SAN = www.example.com, api.example.com, myapp.org
As long as they’re listed in the SAN field, the certificate will be valid for all of them.

Commercial CAs (like Let’s Encrypt, DigiCert, etc.) will only issue certs for domains you can prove you own (via DNS or HTTP challenge). So you can’t just add google.com to your SANs unless you control it


# Apache HTTP2
`a2enmod http2` .. enable http2 module
then within virtualhost directive add `Protocols h2 http/1.1`
h2 .. http2 over tls
h2c .. http2 without tls ... Browsers do not support h2c for security reasons.

Q: why http2 only allowed for event and worker but not prefork?
- Event can allow multiplexing since tcp connection live in one process and you can do threading.. so you can extract the streams for the connection and each stream goes to a thread in parallel

- Prefork cant do multiplexing since tcp connection live in one process but you can't do threading. so even if you extracted the streams you have to process them sequentially anyway, so prefork can't do multiplexing by design.

to understand the model:
- Event when you start http2 connection, tcp connection will live in a queue in a child process and a thread inside the child process can be assigned when request raised

- Prefork (theoritically) when you start http2 connection, tcp connection will live in a separate process and process keep idle till request is sent then the process will start working and response, then another request so process work again etc...



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

- `<VirtualHost *:443>` .. tells Apache: “Listen on all network interfaces (*) on port 443
- `<VirtualHost 127.0.0.1:443>` .. tells apache: listen on loopback network interface
- `ServerName example.com` .. This is the primary domain for this virtual host. When a browser requests https://example.com, Apache matches it to this block. if no host matches, then apache will fallback to the default host even if it doesnt match
- `DocumentRoot /var/www/html` .. If someone visits https://example.com/index.html, Apache will look for /var/www/html/index.html
- `Protocols h2 h2c http/1.1` .. use these protocols with ordered prioritiy
- `<Directory /var/www/html> ... </Directory>` .. A directory-specific configuration.
- `Options Indexes FollowSymLinks` .. 
    -- Indexes → if no index.html is found, Apache may show a file listing.
    -- FollowSymLinks → allows symbolic links to be followed.
- `Options -Indexes +FollowSymLinks`: stop file listing, please notice your site will still serve index.html or index.php if present
- `AllowOverride All`: Lets .htaccess files inside /var/www/html override Apache settings.
- `AllowOverride None`: stop .htaccess overriding, if it exist in directoy
- `Require all granted` .. Allows anyone (all clients) to access the files.
- `Require all denied` .. reply back with 403 Forbidden
