# Sudo 

sudo = run a command as another user (usually root)

### Why sudo exists?
- Instead of: Logging in as root and Sharing root password
- Linux does: normal user → sudo → controlled root access
- Benefits: Least privilege, Auditing (/var/log/auth.log)


### NEVER edit sudoers directly

- ❌ DO NOT `nano /etc/sudoers`
- ✅ ALWAYS `sudo visudo` as it have syntax validation


### sudoers = firewall for root access

```bash

# allow deploy user to run any command as root such as `sudo cat /etc/passwd`
deploy ALL=(ALL) ALL

# allow deploy user to run any command as root without asking for password such as `sudo cat /etc/passwd`
deploy ALL=(ALL) NOPASSWD: ALL


# allow group developers to sudo any command as root
%developers ALL=(ALL) ALL

# allow ahmed only to `sudo systemctl restart apache` as root
# if ahmed `sudo systemctl stop apache` it will stop him
# Tip: full paths to binary is good practice here to prevent path hijacking
ahmed ALL=(ALL) /bin/systemctl restart apache2


# allow laravel user to run cache:clear and migrate with sudo
# when laravel do sudo here he will act as www-data
laravel ALL=(www-data) NOPASSWD: \
    /usr/bin/php /var/www/html/artisan cache:clear, \
    /usr/bin/php /var/www/html/artisan migrate
```

### Login as root

- `sudo su`	Switches to root but keeps current user environment variables
- `sudo su -` Switches to root and use root environment variables