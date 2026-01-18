# Logs

Most Logs are stored in `/var/log/`


| Log file/folder     | Purpose                                        |
| ------------------- | ---------------------------------------------- |
| `/var/log/syslog`   | General system messages (Debian/Ubuntu)        |
| `/var/log/auth.log` | Authentication: logins, sudo usage, SSH logins |
| `/var/log/kern.log` | Kernel messages                                |
| `/var/log/dmesg`    | Boot-time kernel messages                      |
| `/var/log/apt/`     | Package manager activity (apt install/upgrade) |
| `/var/log/apache2/` | Apache web server logs                         |
| `/var/log/nginx/`   | Nginx logs                                     |
| `/var/log/mysql/`   | MySQL/MariaDB logs                             |
| `/var/log/boot.log` | Boot process logs                              |



### Read Logs
- Full Read
    - `cat syslog` .. if log file is small
    - `less syslog` .. read log file paginated if log file is big

- Filter
    - `grep "ssh" /var/log/auth.log` .. Finds all lines containing “ssh” in the authentication log
    - `awk '$1=="Jan" && $2==18 && $3 >= "08:00:00" && $3 <= "09:00:00"' /var/log/syslog` read syslog between Jan 18 08:00:00 and Jan 18 09:00:00


- read last lines
    - `tail -n 50 /var/log/syslog` .. read last 50 lines
    - `tail -f /var/log/syslog` .. follow new lines added and read last 10 lines at the end of log


### Journalctl
- `journalctl`          # show all systemd logs
- `journalctl -u ssh`   # show systemd logs for ssh service
- `journalctl -fu ssh`   # show systemd logs for ssh service and follow it for any line added