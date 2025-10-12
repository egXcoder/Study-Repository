# bash

## /.bashrc 

The .bashrc file is a shell configuration script that runs every time you open a new terminal session in Linux (for interactive, non-login shells). 

- its located inside your home directory

- whatever customization you have done in this file is user specific

Purpose	Examples
- Aliases	alias ll='ls -la'
- Environment Variables	export PATH=$PATH:/opt/jmeter/bin
- Prompt Customization	PS1="\u@\h:\w$ "
- Functions	mkcd() { mkdir $1 && cd $1; }
- Startup Commands	fortune or clear when opening terminal

Tip:
`source ~/.bashrc` you can reload your .bashrc without closing the terminal and open again 


### Environment variables setting

export PATH="$PATH:$HOME/.composer/vendor/bin"



## append to file


echo 'export PATH=$PATH:/opt/jmeter/bin' >> ~/.bashrc
