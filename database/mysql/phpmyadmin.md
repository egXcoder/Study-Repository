# phpmyadmin

- `apt install phpmyadmin` .. install phpmyadmin

- create phpmyadmin user and give him all privilleagues
```sql
CREATE USER 'phpmyadmin'@'localhost' IDENTIFIED BY 'StrongPassword123!';
GRANT ALL PRIVILEGES ON *.* TO 'phpmyadmin'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```