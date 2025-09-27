# Sudo 

- sudo command	Runs a single command as root Preserves most environment variables

- sudo -E command	same as above but forces environment preservation	✅ Best for GUI apps as root

- sudo su	Switches to root but keeps current environment variables mostly intact

- sudo su -	Switches to root but Resets almost all environment variables, including $DISPLAY, $HOME, etc.