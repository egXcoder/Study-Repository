# Binaries

A binary is a file represents the final result of compiling source code (C, C++, Go, Rust, etc.). for example `ls` is a binary

### Where binaries located:

OS binaries + apt install binaries
- `/usr/sbin` .. meant to be used by root or sudo as it is system administration commands (ip,mount,reboot,shutdown,parted,mkfs) 
- `/usr/bin` .. other general binaries such as (ls,cp,mv,cat,bash,grep) 

if i would download my own portable tool such as aws
- `/usr/local/bin/`

symlinks in modern distributions:
- `/sbin` -> `/usr/sbin` .. symlink
- `/bin`  ->  `/usr/bin` .. symlink 

---

### How binaries works

it works because of `$PATH` environment variable

```bash
`echo $PATH` # /usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/snap/bin

# this means all binaries inside /usr/local/sbin and /usr/local/bin and .... you can call them directly from the shell and shell will look for the binary inside these paths
```

---

### Complete the picture

- you open shell
- which will load shell with user environment variable $PATH
- then you call for `aws` 
- shell will look in the path for this script, if find it it will execute it
- shell read aws file and execute it

Tip: to execute `aws` binary, you would have put it into `/usr/local/bin` and also give it execute permission 

---

### Shebang 

you may tell shell how to execute a file using shebang

script.sh
```bash
#!/bin/bash
echo "$(date) heartbeat" >> /var/log/heartbeat.log
```
now calling `./script.sh` would automatically do `/bin/bash script.sh`

without the shebang, it would have given 'cant execute binary file'

Tip: if you arent sure if bash is on bin or sbin or /usr/bin/local it doesnt matter . you can do shebang as below and this will auto locate your binary

```bash
#!/usr/bin/env bash
echo "$(date) heartbeat" >> /var/log/heartbeat.log
```