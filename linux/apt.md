# APT (Advanced Package Tool)

It is: A package manager for Debian-based systems .. (Ubuntu, Debian, Linux Mint, Kali…)

It is a whole system for:
- Finding software
- Downloading it
- Verifying it
- Installing it
- Updating it
- Removing it safely

# Common apt commands
- `apt install pkg` ..install package
- `apt remove package-name` .. remove package while keeping configs in /etc
- `apt purge package-name` .. remove package and its configs
- `apt clean` .. When you install or upgrade packages These .deb files are kept even after the package is installed, so you can reinstall without re-downloading. It removes all cached .deb files
- `apt autoclean` .. removes only obsolete .deb files, Safer but frees less space.


### What Is a Package?
A package (.deb) contains:
- Program binaries
- Libraries
- Config files
- Metadata (dependencies, version)

Example:
- nginx_1.24.0_amd64.deb


### What Is a Repositories (Sources)?

APT downloads packages from repositories.

Configured in:

- /etc/apt/sources.list
- /etc/apt/sources.list.d/*.list

Example Entry in the sources.list file

```deb http://archive.ubuntu.com/ubuntu jammy main universe```

Meaning:

- deb → binary packages
- URL → repo location
- jammy → for ubuntu release
- main universe → components

Tip: APT uses `jammy` to download packages built specifically for that Ubuntu version.

| Ubuntu Version | Codename |
| -------------- | -------- |
| 20.04          | focal    |
| 22.04          | jammy    |
| 24.04          | noble    |

Tip: 
- main: Officially supported by Canonical, Security updates guaranteed such as bash,coreutils,systemd,apt
- universe: Community-maintained such as nginx, nodejs, redis, many developer tools

### Downloading repository metadata

when you run `sudo apt update`

apt will read the source files such as `deb http://archive.ubuntu.com/ubuntu jammy main universe`

then will go to `http://archive.ubuntu.com/ubuntu/dists/jammy/main/` and `http://archive.ubuntu.com/ubuntu/dists/jammy/universe/`

and download `packages.gz` and `Release` from binary-amd64/ and download into `/var/lib/apt/lists`

### Installing a package

when you run `sudo apt install nginx`

apt will read packages metadata inside `/var/lib/apt/lists` and download the package if it could find it

### Installting a package from separate repository

- `sudo apt install software-properties-common` This package provides the command add-apt-repository, which is not installed by default on minimal Ubuntu systems. Without it, you cannot easily add PPAs.

- `sudo add-apt-repository ppa:ondrej/php` this will add a source entry in /etc/apt/sources.list.d/ for the external source `deb https://ppa.launchpadcontent.net/ondrej/php/ubuntu/ jammy main`

- `sudo apt update` now packages meta data will be downloaded from ppa repo and add downloaded into `/var/lib/apt/lists`

- `apt list -a php*` see what are the available php packages that you can download if you want

Tip: ppa stands for personal package archive

Tip: you can add the source directly such as
- `sudo nano /etc/apt/sources.list.d/php-source.list`
- add line `deb https://ppa.launchpadcontent.net/ondrej/php/ubuntu/ jammy main`
- save, then `sudo apt update`


### Installing from a .deb file

alternatively to all this, you can download your .deb file manually

✅ `sudo apt install ./package-name.deb` shall install it and will install dependencies if found any

❌ `sudo dpkg -i package-name.deb` problem with this, it doesnt install dependencies if needed


### Upgrade command

`sudo apt upgrade` Updates all installed packages to the latest version available in your configured repositories.

`sudo apt full-upgrade` can remove or install new packages if needed to complete an upgrade.

If PHP 7.4 requires a new library that isn’t installed yet:
- apt upgrade → won’t upgrade (because it avoids installing new packages)
- apt full-upgrade → will upgrade, installing the new library.