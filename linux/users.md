# Users

Linux is a multi-user operating system, which means users are a core security and isolation concept, not just logins.

Each user has:
- UID (User ID) → numeric identifier
- Primary GID (Group ID)
- Username (human-friendly label)

```bash
toor@DESKTOP-DLNJTCG:~$ id toor
uid=1000(toor) gid=1001(toor) groups=1001(toor),4(adm),20(dialout),24(cdrom),25(floppy),27(sudo)
```

---

### Types of Linux users

- root (superuser)
    - UID = 0
    - Full control over the system
    - Can bypass all permission checks
    - `whoami`   # root
    - ⚠️ Dangerous if misused


- System users
    - Used by services & daemons
    - Usually UID < 1000
    - Usually No login shell
    - Examples:
        - www-data → web server
        - mysql → database
        - postgres → PostgreSQL

    ```bash
    toor@DESKTOP-DLNJTCG:~$ grep postgres /etc/passwd
    postgres:x:116:126:PostgreSQL administrator,,,:/var/lib/postgresql:/bin/bash
    ```

- Regular users
    - Human users
    - UID ≥ 1000
    - Can log in
    - `useradd ahmed`

---

### Where User is stored

[-] `/etc/passwd` Users Meta Data

```bash
uuidd:x:106:112::/run/uuidd:/usr/sbin/nologin
tcpdump:x:107:113::/nonexistent:/usr/sbin/nologin
toor:x:1000:1001:,,,:/home/toor:/bin/bash
dnsmasq:x:108:65534:dnsmasq,,,:/var/lib/misc:/usr/sbin/nologin
haproxy:x:109:118::/var/lib/haproxy:/usr/sbin/nologin
rtkit:x:110:119:RealtimeKit,,,:/proc:/usr/sbin/nologin


# line format
username:x:UID:GID:comment:home:shell

# postgres user can be used to log in
postgres:x:116:126:PostgreSQL administrator,,,:/var/lib/postgresql:/bin/bash
# username=postgres
# uid=116
# gid=126
# comment=PostgreSQL administrator
# home = /var/lib/postgresql
# shell = /bin/bash


# apache user not used for login
www-data:x:33:33:www-data:/var/www:/usr/sbin/nologin
# shell = /usr/sbin/nologin 
```

[-] `/etc/shadow` Users Passwords

```bash
_apt:*:19683:0:99999:7:::
uuidd:*:19683:0:99999:7:::
tcpdump:*:19683:0:99999:7:::
toor:$y$j9T$u0lwEXeBwXZ6ste.wptRi.$mfoRJNmIuNklkS4lVxWv7xh111l/D9IuJOCvV5Yp4cA:19956:0:99999:7:::
dnsmasq:*:19967:0:99999:7:::
haproxy:!:20021:0:99999:7:::
rtkit:*:20398:0:99999:7:::
usbmux:*:20398:0:99999:7:::


# line format
username:password:last_password_change:min_days_before_password_need_changing:max_days_password_valid:warn_user

postgres:*:20463:0:99999:7:::
# postgres user
# password disabled
# last changed at 202463 (no of days after unix epoch) `date -d "1970-01-01 +202463 days"` = 29/04/2025
# min days before password need changing = 0
# max days password valid = 99999
# warn users before password expire with 7 days
```

[-] `/etc/group` User Groups

```bash
root:x:0:
daemon:x:1:
bin:x:2:
sys:x:3:
adm:x:4:syslog,toor
tty:x:5:
disk:x:6:
lp:x:7:
mail:x:8:
news:x:9:
uucp:x:10:
man:x:12:

# line format
group_name:password:GID:user_list

adm:x:4:syslog,toor
# group name = adm
# gid = 4
# users are syslog, toor
```
---


### Why users matter (security model)

```bash

root@DESKTOP-DLNJTCG:~# ls -l
total 324344
-rw-r--r-- 1 root root      1054 Oct 25  2024 conf.config
-rw-r--r-- 1 root root 303132488 Sep  4  2024 ls-lR
-rw-r--r-- 1 root root  28980856 Sep  4  2024 ls-lR.gz
drwx------ 3 root root      4096 Aug 21  2024 snap

# - is file while d is directoy
# rw- owner permission
# r-- group permission
# r-- others permission
# root owner
# root group


# Change Owner
chown postgres conf.config

# Change Group
chgrp docker conf.config

# Change Owner and Group
chown postgres:docker conf.config

# Recursive Change Owner and Group
chown -R postgres:docker /path/to/directory


# Change Permission 1
# read = 4 100
# write = 2 010
# execute = 1 001
chmod 666 conf.config # rw-rw-rw-
chmod 765 conf.config # rwx-rw-r-x

# Change Permission 2
chmod u=rwx conf.config # give permission rwx for owner
chmod u=rw conf.config # give permission rw for owner
chmod g=rw conf.config # give permission rw for group
chmod o=rw conf.config # give permission rw for others

# Change Permission 3
chmod u+w conf.config # give write access to owner
chmod g+w conf.config # give write access to group permission
chmod o+w conf.config # give write access to others
chmod o-x conf.config # take out execute permission from filename
```

---

### SGID:
By default, when user creates new file, the default group is the user primary group. however if gid bit is set to directory, then any new file/directory created within directory would inherit group from directory

```bash
# set gid bit which is this 2
chmod -R 2751 testdir

# change group
chgp docker testdir

# create file inside directory
root@DESKTOP-DLNJTCG:~/testdir# touch test
root@DESKTOP-DLNJTCG:~/testdir# ls -l
total 0
-rw-r--r-- 1 root docker 0 Jan 15 10:51 test


# tesdir drwxr-s--x this s in the group means gid bit is set
root@DESKTOP-DLNJTCG:~# ls -l
total 324348
-rw-r--rw- 1 root root        1054 Oct 25  2024 conf.config
-rw-r--r-- 1 root root           0 Jan 15 10:42 file
-rw-r--r-- 1 root root   303132488 Sep  4  2024 ls-lR
-rw-r--r-- 1 root root    28980856 Sep  4  2024 ls-lR.gz
drwx------ 3 root root        4096 Aug 21  2024 snap
drwxr-s--x 2 root docker      4096 Jan 15 10:51 testdir

# remove gid bit
chmod g-s testdir

# testdir back to normal
root@DESKTOP-DLNJTCG:~# ls -l
total 324348
-rw-r--rw- 1 root root        1054 Oct 25  2024 conf.config
-rw-r--r-- 1 root root           0 Jan 15 10:42 file
-rw-r--r-- 1 root root   303132488 Sep  4  2024 ls-lR
-rw-r--r-- 1 root root    28980856 Sep  4  2024 ls-lR.gz
drwx------ 3 root root        4096 Aug 21  2024 snap
drwxr-x--x 2 root docker      4096 Jan 15 10:51 testdir

# creating another file now inside testdir would make its default group to be root
root@DESKTOP-DLNJTCG:~# cd testdir
root@DESKTOP-DLNJTCG:~/testdir# touch test2
root@DESKTOP-DLNJTCG:~/testdir# ls -l
total 0
-rw-r--r-- 1 root docker 0 Jan 15 10:51 test
-rw-r--r-- 1 root root   0 Jan 15 10:55 test2
```

--- 

### UMASK:

when user creates new file, the default permission given is (max_available_permission - umask)
- max available permission for directory 0777 while files 0666
- umask for normal user is 0002 .. then when user create file .. file take permission = 0666 - 0002 = 0664
- umask for root user is 0022.. then default file permission 0644
- you can set `umask 0000` per process or per terminal ~/.bashrc or globally  

---

### Create User/Group

- `sudo useradd -m -s /bin/bash ahmed` .. add user with his home .. '-m' for home, '-s /bin/bash' for shell
- `sudo passwd ahmed` .. set password for user ahmed
- `cat /etc/passwd | awk -F: '{print $1}'` .. show list of users
- `sudo cat /etc/passwd | grep ahmed` .. show ahmed user
- `sudo userdel -r ahmed` .. delete user with his home .. '-r' is for remove home

<br>

- `sudo groupadd developers` .. add group
- `sudo usermod -aG developers ahmed` .. add user to group -aG is critical without it, existing groups are removed (append group)
- `cat /etc/group | awk -F: '{print $1}'` .. show list of groups
- `cat /etc/group | grep developers` .. show single group
- `sudo groupdel developers` .. delete group


Tip: `awk '{print $1}'` this gives first column as column 1 is separated by space
Tip: `awk -F: '{print $1}'` .. -F is for field separator and : means this is separator
Tip: To add/edit users/groups.. you need the sudo as normal user can't amend linux user system unless root or sudoer

---

### Switching User
- `sudo -u ahmed -i` .. login as another user
- `sudo -u ahmed whoami` .. run command as another user


### install Laravel Project

- Main idea is to install laravel project using a particular linux user 'ubuntu' for example 
- 'ubuntu' user will be used when you login to linux and also on cronjobs
- apache user 'www-data' and 'ubuntu' user will be on same group


- Add ubuntu user if not exists
    - `useradd -m ubuntu`

- Add group between ubuntu and www-data
    - `groupadd laravel_group`
    - `usermod -aG laravel_group ubuntu`
    - `usermod -aG laravel_group www-data`

- Add Project Directory
    - `sudo mkdir /var/www/html/myapp` .. this will create myapp folder as owner:group root:root
    - `sudo chown ubuntu:laravel_project myapp` .. change owner:group
    - `sudo chmod 755 myapp` .. owner can rwx , group can r-x, others can r-x

    Tip: execute permission to directory, means you can cd into it
    
    Tip: apache user or other services shouldn't have write permission to the typical project files like .env .. they may need permission to write into cache or storage which i am going to give its permission explicitly later on 

- Install Project
    - `sudo -u ubuntu -i` .. login as ubuntu user if not logged in already
    - `cd /var/www/myapp`
    - `git clone https://repo.git`
    - `composer install`
    - `sudo chown -R ubuntu:laravel_project /var/www/myapp` .. apply owner:group recursively to all files/directories within proj
    - `sudo chmod -R 755 /var/www/myapp` .. apply permission to all files/directories

- Handle Cache And Storage
    - `sudo chmod -R 2775 storage bootstrap/cache` .. add rwx for the group.. then apache user can write into cache and storage

    Tip: Critical: this number 2 is adding SGID bit flag into cache/storage directory. so that any new files/directories added inside them would inherit the group 

- Make sure Both Users UMask are 0002
    - Why?
        - if you didnt do this step, ubuntu user umask maybe 0022 so now if you run php artisan while using ubutnu, you may create a log or cache file with permission 0644
        - so now if apache user tries to write log or cache it will get permission denied because he only have read permission
    - What?
        - when any user creates file it takes default permission of (max available permission - umask)
        - max available permission for directory 0777 while files 0666
        - when umask is 0002 .. then new files take permission = 0666 - 0002 = 0664
    - How?
        - apache www-data user
            - check: `sudo -u www-data sh -c 'umask'` if this gives other than 0002
            - `sudo nano /etc/apache2/envvars`
            - then add umask 0002 then save file and restart `sudo systemctl restart apache2`

        - php-fpm user
            - check: `sudo -u php-fpm sh -c 'umask'` if this gives other than 0002
            - `sudo nano /etc/php/*/fpm/pool.d/www.conf`
            - then add php_admin_value[umask] = 0002 and restart `sudo systemctl restart php*-fpm`

        - ubuntu user which i use to login
            - check `sudo -u ubuntu sh -c 'umask'`
            - `sudo nano /home/laravel/.bashrc`
            - add umask 0002 then save then check again

    Tip: to check umask. you would think why not just `sudo -u ubuntu umask`.. this is invalid beause umask is not external binary.. its more of environment variable so you need to get it from shell so you need to run shell `sudo -u ubuntu sh -c 'umask'` -c is for command