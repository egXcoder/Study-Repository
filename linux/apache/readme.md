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
Apache has different MPMs that define how it handles concurrency:
- prefork → processes only, no threads (old, safe for non-thread-safe PHP).
- worker → multiple processes, each with multiple threads.
- event → like worker, but with smarter handling of idle keep-alive connections.

Q:i think for event, its safer for fpm connection to be tcp rather than socket, but worker can be socket as apache will have multiple processes already? yes, you are correct
    -- Event MPM → TCP is often safer/more scalable at very high concurrency (less risk of contention, easier to tune backlog).
    -- Worker MPM → Unix socket works well (process-per-request model maps nicely, lower overhead).
    -- Prefork MPM → Unix socket is fine.

Q: so why event is more populate, i think worker is better?
at first glance worker MPM looks simpler and “better,” but event MPM became more popular and is now the default on most distros (Ubuntu, Debian, etc).


know current worker using `apachectl -V` ... it will show you the mpm worker that is running




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