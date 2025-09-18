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


# Apache Service
- enable service, so that it auto start on system restart `systemctl enable apache2`
- start service `systemctl start apache2`
- restart service `systemctl restart apache2`

# Apache MPM Workers (Multi-Processing Modules Workers)

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