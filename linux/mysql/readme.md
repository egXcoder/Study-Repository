# mysql


- `apt install mysql-server` .. install
- `sudo mysql_secure_installation` configure mysql by setting passwords etc..
- `mysql -u root -p` by default root is allowed to login via auth_socket.. means you have to login in linux as root
- `sudo mysql -u root -p` or login as root by sudo
- if you want users to login to mysql with passwords. you may need to enable 


## users
- `SELECT User, Host, Plugin FROM mysql.user;` ... show list users with their auth plugins

    Auth Plugins:

    - auth plugins is like authentication policy which says this user can login or not. for example auth_socket checks for linux username, mysql_native_password hash password in sha1 and compare, caching sha2 secure hash with caching for faster login

    - if user is using a specific auth plugin. and the plugin is disabled. then this user can't login anymore unless plugin is back or user is changed to use different mechanism.

    - `FLUSH PRIVILEGES;` Reload all user accounts and permissions from the database so the changes take effect right now.

    - `SELECT PLUGIN_NAME, PLUGIN_STATUS FROM information_schema.plugins WHERE PLUGIN_TYPE = 'AUTHENTICATION';` .. to see available auth plugins

    - you can change root to be password instead of autologin `ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'Testpass1#';`

    - even if you forgot the root password, you can reset it to auth_socket by --skip-grant-tables then do query directly to update mysql.user where user=root to set plugin = auth_socket and authentication_string = ''

    - best practice though is to keep root auth_socket, and create another socket which is username and password for login

        - auth_socket: is a MySQL authentication plugin that lets users log in without a password, as long as their Linux system username matches their MySQL username.

            `CREATE USER 'ahmed'@'localhost' IDENTIFIED WITH auth_socket; FLUSH PRIVILEGES;`

            now if i am logged in linux as ahmed and i tried `mysql` it will let you in as ahmed directly

            `select current_user();` .. see user who is currently logged in

        - mysql_native_password: Classic username/password with SHA1 hashing .. Widely compatible with older apps and tools (Laravel, PHPMyAdmin, etc.)

            `CREATE USER 'native'@'localhost' IDENTIFIED WITH mysql_native_password BY 'Testpass1#'; FLUSH PRIVILEGES;`


        - caching_sha2_password: Default in MySQL 8+ (stronger security + caching for better performance) .. Recommended for new applications unless compatibility issues

            `CREATE USER 'sha2'@'localhost' IDENTIFIED WITH caching_sha2_password BY 'Testpass1#'; FLUSH PRIVILEGES;`

    


    

- User Privileges
    - `GRANT ALL PRIVILEGES ON *.* TO 'username'@'localhost';`
    - `GRANT SELECT, INSERT, UPDATE, DELETE ON mydb.* TO 'username'@'localhost';`
    - `FLUSH PRIVILEGES;` .. take the effect now by reload users and their permissions into memory




## phpmyadmin
- `apt install phpmyadmin` .. install phpmyadmin

- create phpmyadmin user and give him all privilleagues
```sql
CREATE USER 'phpmyadmin'@'localhost' IDENTIFIED BY 'StrongPassword123!';
GRANT ALL PRIVILEGES ON *.* TO 'phpmyadmin'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```