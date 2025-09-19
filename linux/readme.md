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
 - well block will be the space, but you cant put data into them as they are not used with file system

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