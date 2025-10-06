# Users


Groups: 

- `groups` .. set my groups
- `groups www-data` .. see groups for user www-data
- `groupadd webdev` create a group
- `usermod -aG webdev ahmed` add group to user


File/Dir Owners:

- By default file/Dir when new file/dir is added .. logged user as owner and as group

    - set gid bit to directories, then any new file/directory created within directory would inherit group from directory .. gid bit = 2
    - `sudo chmod -R 2775 /var/www/html/laravel_project` .. 


- `sudo chown -R :webdev /var/www/html/laravel_project` .. change group


- `sudo chown -R ahmed:webdev /var/www/html/news_fetcher` .. change owner + group


- `sudo -u www-data touch /var/www/html/news_fetcher/storage/testfile` .. do something on behalf of other user


UMASK:

- when user creates new file, the default permissions given is max_available_permission - umask
    - max available permission is for directory 0777 while files 0666
    - umask for normal user is 0002 .. then when user create file .. file take permission = 0666 - 0002 = 0664
    - umask for root user is 0022.. then file permission 0644
    - you can set `umask 0000` per process or per terminal ~/.bashrc or globally  