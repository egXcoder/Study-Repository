# Desktop Environment: GNOME, XFCE, KDE, MATE


`echo $XDG_CURRENT_DESKTOP` .. see which environment you are on


## XFCE

xfce may be lighter than gnome but its with cost.. the desktop look outdated and it doesnt have fany things or shortcuts to reach browsers easily.. gnome look more modern


### Installation
Switching from GNOME to XFCE on Ubuntu is pretty straightforward. You don’t “convert” GNOME into XFCE; instead, you install XFCE alongside GNOME and then choose it at login. Here’s how:

`sudo apt install xubuntu-desktop` .. install xfce


### Display Manager
During installation, it may ask you to select a display manager (like gdm3 or lightdm).
- gdm3 → default for GNOME
- lightdm → more lightweight, often recommended for XFCE

You can choose either, but lightdm is common for XFCE.


### Default to xfce

If you’re using GDM3 (Ubuntu’s default) .. GDM remembers the last session you picked at the login screen.

- Log out.
- On the login screen, click your username.
- Click the gear icon ⚙️ (bottom-right corner of the login box).
- Choose Ubuntu (or "Ubuntu on Wayland" / "GNOME" depending on what you installed).
- Enter your password and log in.

💡 From now on, GDM will default to Ubuntu until you change it again.



### Other parts 
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