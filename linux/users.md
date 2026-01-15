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
    - `adduser ahmed`

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
# read = 4
# write = 2
# execute = 1
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

### G Bit:
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

### TODO::Switching Between Users
TODO:: to explain the laravel linux problem and how i solved it





Groups: 
- `groups` .. set my groups
- `groups www-data` .. see groups for user www-data
- `groupadd webdev` create a group
- `usermod -aG webdev ahmed` add group to user


File/Dir Owners:

- By default file/Dir when new file/dir is added .. logged user as owner and as group

- `sudo chown -R :webdev /var/www/html/laravel_project` .. change group


- `sudo chown -R ahmed:webdev /var/www/html/news_fetcher` .. change owner + group


- `sudo -u www-data touch /var/www/html/news_fetcher/storage/testfile` .. do something on behalf of other user