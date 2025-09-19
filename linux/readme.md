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


