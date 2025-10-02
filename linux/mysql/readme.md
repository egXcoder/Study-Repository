# mysql


- `apt install mysql-server` .. install
- `sudo mysql_secure_installation` configure mysql by setting passwords etc..
- `mysql -u root -p` by default root is allowed to login via auth_socket.. means you have to login in linux as root
- `sudo mysql -u root -p` or login as root by sudo
- if you want users to login to mysql with passwords. you may need to enable 


## users
`CREATE USER 'phpmyadmin'@'localhost' IDENTIFIED BY 'StrongPassword123!';` .. create user
`GRANT ALL PRIVILEGES ON *.* TO 'phpmyadmin'@'localhost';FLUSH PRIVILEGES;` .. give user full privileages



## phpmyadmin
- `apt install phpmyadmin` .. install phpmyadmin

- create phpmyadmin user and give him all privilleagues
```sql
CREATE USER 'phpmyadmin'@'localhost' IDENTIFIED BY 'StrongPassword123!';
GRANT SELECT, INSERT, UPDATE, DELETE ON phpmyadmin.* TO 'phpmyadmin'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```