# JournalCTL

is the tool you use on systemd-based systems (like Ubuntu) to read logs from the system journal.
reads logs from systemd’s journal, which is a binary log storage managed by the systemd-journald service.
stored in disk at /var/log/journal/
- `journalctl -u apache2` View logs for a specific service 


# GNOME, XFCE, KDE, MATE
These are desktop environments (DEs). = graphical user interface (GUI) on top of Linux.
It bundles:
- Window manager (controls how windows move, resize, minimize, etc.)
- Panel / taskbar / menus
- File manager
- System settings tools
- A consistent look & feel

linux main components:
- Kernel / Drivers → the engine & wiring (talks to hardware, GPU, etc.)
- Xorg (X server) → the steering system (knows how to “draw” on the screen and take input from keyboard/mouse)
- Desktop Environment (GNOME, XFCE, KDE, etc.) → the body & dashboard (menus, windows, panels, file manager, apps)

# Display Manager
When you start your computer:
1- The Display Manager is the first thing that appears.
2- It shows the login window where you enter your username and password.
3- After logging in, it starts your desktop environment (like GNOME, KDE, XFCE, etc.)

- GDM3 (GNOME Display Manager) .. It is the login screen software used in GNOME-based Linux distributions like Ubuntu, Debian, Pop!_OS, and others.

- LightDM ... login screen that loads XFCE / Older Ubuntu / Linux Mint

- SDDM ... login screen that loads KDE Plasma

Component	Purpose
GDM3	The login screen / session manager that loads GNOME
GNOME	Full Desktop Environment (your graphical workspace)



# XRDP
xRDP is an open-source Remote Desktop Protocol (RDP) server for Linux.

It allows you to connect to a Linux machine using Microsoft’s built-in Remote Desktop Client (mstsc.exe) or, in your case, Hyper-V Enhanced Session Mode


✅ In short:
Xorg = the Linux display engine
xorgxrdp = the glue that lets xRDP talk to Xorg
xRDP = the RDP server that Hyper-V Enhanced Session uses



# apt
- `apt install pkg` ..install package
- `apt remove package-name` .. remove package while keeping configs in /etc
- `apt purge package-name` .. remove package and its configs
- `apt clean` .. When you install or upgrade packages These .deb files are kept even after the package is installed, so you can reinstall without re-downloading. It removes all cached .deb files
- `apt autoclean` .. removes only obsolete .deb files, Safer but frees less space.


# incrase disk space
`lsblk` .. the disk is 100 GB but the root partition (sda2) is still 50 GB.

sda     8:0   0   100G  0 disk
└─sda2  8:2   0    50G  0 part / 

`parted /dev/sda` then after going there run `resizepart 2 100%` .. this will increase block size to take the remaining space

`resize2fs /dev/sda1` .. this will grow file system for the partition to take block size

Q: what if we didnt grow file system?
 - in this situation block will be bigger than file system, and you cant put data into the extra space unless file system grows

Q: what is difference between block and file system
 - you can think of it block is the box that you can put data into it, but you have to file system to match

Q: so what is the process to shrink partition?
 - its the opposite now, you have to shrink filesystem first, then shrink block

Q: what if i tried to shrink block directly?
 - this can cause irrecoverable corruption and permanent data loss as you will have file system thinking its still big while block is already shrinked, so file system will try to read and write on places that doesnt exist

Q: okay, but shouldnt linux stop that?
 - linux doesnt stop you, you have to know what you are doing

Q: if i shrink file system, would that may cause data loss?
 - well, it might yes..

Q: command to shrink?
 `resize2fs /dev/sda2 50G`
 `parted /dev/sda` then `resizepart 2 50GB`


# Socket
- A special file (not a regular text file) created by the kernel.
- When Apache connects to /run/php/php8.1-fpm.sock, the kernel opens a socket connection to PHP-FPM.
- That socket is bi-directional → data flows both ways (requests & responses).
- `ulimit -n`To know how many sockets can be opened in same time, its typically means how many file descriptors a process can have
- connecting by a socket, The kernel provides in-memory buffers for this socket. so that client and server can talk through memory
- Why sockets need a "file": 
    -- In Unix, “everything is a file” — sockets, pipes, devices, etc
    -- Access control → chmod on the socket file restricts who can connect.
    -- Namespace → Multiple services can each have their own sockets (/var/run/mysql.sock, /run/php/php8.1-fpm.sock, etc.).
    -- Lifecycle → The socket file disappears if the process is stopped.
    -- If sockets are also "files," the same API works. No need for a special API just for sockets.


Let’s say 3 users hit your site at the same time:
Apache accepts 3 requests.
For each, Apache opens a new connection to /run/php/php8.1-fpm.sock.
The kernel creates 3 separate connection streams (not all mixed into one).
kernel creates 3 file descriptor in Apache’s process like FD 42, like FD 51, like FD 70
PHP-FPM listens on the socket and accepts all 3.
PHP-FPM’s pool of workers takes them concurrently (one worker per request).
So even though it looks like “just a single file,” it can handle many simultaneous streams.



# tar
tar -xvzf file.tgz
tar -xvf file.tar

-x	Extract
-v	Verbose
-z	Gzip Compression	Indicates the archive is .gz / .tgz compressed
-f	File	Specifies the file name that follows

# systemctl
- systemctl is the manager for services (daemons) on Linux using systemd
    - `systemctl start nginx`
    - `systemctl stop nginx`
    - `systemctl reload nginx` .. reload service config
    - `systemctl enable nginx` .. activate service then it auto start when linux restart
    - `systemctl disable nginx` .. deactivate service
    - `systemctl enable nginx --now` .. activate service and start it
    - `systemctl status nginx` .. show servie status and logs
    - `systemctl list-units --type=service` .. list all services


- Difference Between systemctl and systemd
    - systemctl
        - A command-line tool used to interact with systemd
        - It’s the user interface to control and query systemd services and states.
        
    - systemd
        - The init system and service manager for Linux
        - It's the background process (daemon) that bootstraps the system, starts services, handles logging, networking, timers, etc.

# Processes

- `ps aux | grep apache2` know who is the user running a process


# Users

- `sudo groupadd webdev` add a group
- `sudo usermod -aG webdev ahmed` .. add user to group .. -a append G group
- `sudo usermod -aG webdev www-data` .. add user to group .. -a append G group
- `sudo chown -R ahmed:webdev /var/www/html/news_fetcher` .. change owner to be ahmed and group to be webdev. then ahmed and www-data both can reach the project files
- `sudo chmod -R 2775 /var/www/html/news_fetcher` .. set gid bit to directories, then any new file/directory created within directory would inherit group from directory .. gid bit = 2
- `sudo -u www-data touch /var/www/html/news_fetcher/storage/testfile` .. do something on behalf of other user



# Directories
- /opt is meant for optional or third-party applications that are not installed via package manager (APT, DNF, etc.) .. so when you install package manually, best practice is to move it opt directory