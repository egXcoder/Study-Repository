# Systemctl


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

# systemd
System daemon (systemd) is the first process (pid=1) run by linux kernel which starts all other services and daemons

- Difference Between systemctl and systemd
    - systemctl
        - A command-line tool used to interact with systemd
        - It’s the user interface to control and query systemd services and states.
        
    - systemd
        - The init system and service manager for Linux
        - It's the background process (daemon) that bootstraps the system, starts services, handles logging, networking, timers, etc.

### Systemd

```text
boot
 ↓
systemd
 ↓
read unit files (*.service)
 ↓
start required services
 ↓
keep them running
```

### systemd Load Unit Files

Systemd read units from multiple paths, with priority:

```text
/etc/systemd/system     (highest priority) .. For system administrator customizations, overrides, and new units.
/lib/systemd/system     (lowest priority) .. unit files installed by the OS or packages .. You usually don’t touch it.
/run/systemd/system .. Temporary units created at runtime. (disappear on reboot)
```

Tip: Anything in /etc overrides /lib.

Tip: you can use /etc/systemd/system for
- custom service you write `/etc/systemd/system/myapp.service`
- override for a package service `/etc/systemd/system/sshd.service.d/override.conf`


For each unit, systemd notes:
- Name and type (.service, .target, .socket, etc.)
- Dependencies (After=, Requires=, Wants=, Before=)
- Install info (WantedBy=... or RequiredBy=...)

This constructs a graph:
- Nodes = units
- Edges = dependencies / ordering rules

till the end of this step, systemd doesnt start anything. it just built the graph in memory

---

### cron.service example

when you enable the service via `systemctl enable cron` it will add a symlink into `/etc/systemd/system/multi-user.target.wants/cron.service -> /lib/systemd/system/cron.service`.. so that when system boots up and reach target of multi-user.target it will go and read directory `/etc/systemd/system/multi-user.target.wants` and start services inside considering their dependencies


```ini
#start cron after these two targets are started:
#remote-fs.target → all remote filesystems are mounted
#nss-user-lookup.target → user/group name lookup is ready
[Unit]
Description=Regular background program processing daemon
Documentation=man:cron(8)
After=remote-fs.target nss-user-lookup.target



[Service]
EnvironmentFile=-/etc/default/cron #Loads environment variables from /etc/default/cron if it exists
ExecStart=/usr/sbin/cron -f -P $EXTRA_OPTS # Command to run on start
IgnoreSIGPIPE=false
KillMode=process
Restart=on-failure # If crond exits with a failure code, systemd automatically restarts it


#this is the part which instruct on enabling service it would add it to multi-user.target directory
[Install]
WantedBy=multi-user.target
```

### Various Targets

targets are checkpoints that happens during boot up most likely, systemd runs all units attached with the targets when target is reached

#### Very early / boot-critical targets

| Target             | Purpose                                                                                             |
| ------------------ | --------------------------------------------------------------------------------------------------- |
| `basic.target`     | All essential system initialization done. This is where most core services start.                   |
| `sysinit.target`   | Core system initialization (mounts, swap, udev, local-fs.target). Usually required by basic.target. |
| `local-fs.target`  | All local filesystems are mounted.                                                                  |
| `remote-fs.target` | All remote/network filesystems (NFS, SMB) are mounted.                                              |
| `sockets.target`   | All socket units are activated (for socket-activated services).                                     |
| `paths.target`     | All path units are activated (path-watching services).                                              |
| `timers.target`    | All timer units are activated (cron-like timers).                                                   |


#### Mid-Stage

| Target                  | Purpose                                                                                |
| ----------------------- | ---------------------------------------------------------------------------            |
| `network.target`        | Basic networking is up                                                                 |
| `network-online.target` | Network fully online for dependent services                                            |
| `multi-user.target`     | Multi-user can log in text mode; typical services like cron, ssh, databases start here |


#### Late-Stage
| Target                 | Purpose                                                                                  |
| ---------------------- | ---------------------------------------------------------------------------------------- |
| `graphical.target` | graphical desktop login. Wants multi-user.target.                                        |
| `default.target`   | Symlink to the default system target (usually `graphical.target` or `multi-user.target`) |



#### Special Purpose Targets

| Target             | Purpose                                                  |
| ------------------ | -------------------------------------------------------- |
| `reboot.target`    | Reboot the system.                                       |
| `poweroff.target`  | Power off the system.                                    |
| `halt.target`      | Halt the system without rebooting.                       |
| `rescue.target`    | Single-user rescue mode (like runlevel 1).               |
| `emergency.target` | Emergency mode — only essential services, minimal shell. |



### Override cron.service

add override to the service at `/etc/systemd/system/cron.service.d/override.conf` .. Anything you put here replaces or adds to the original unit.

```text
[Service]
ExecStart=
ExecStart=/usr/sbin/cron -f -P "-L 5"
Restart=always
RestartSec=10
```

then reload `sudo systemctl daemon-reload` and `sudo systemctl restart cron.service`


Tip: `sudo systemctl edit cron.service` will do exact thing instead of creating file manually


### Units Types

| Unit Type | File Extension | What it Represents                                    | Example                                 |
| --------- | -------------- | ---------------------------------------               | ------------------------                |
| Service   | `.service`     | Background process / daemon                           | `ssh.service`, `nginx.service`          |
| Socket    | `.socket`      | A socket that can trigger a service on activity       | `cups.socket`                           |
| Target    | `.target`      | A group of units (like old runlevels)                 | `multi-user.target`, `graphical.target` |
| Timer     | `.timer`       | Time-based activation for services (cron replacement) | `apt-daily.timer`                       |
| Mount     | `.mount`       | A filesystem mount point                              | `home.mount`                            |
| Automount | `.automount`   | Automount point                                       | `home.automount`                        |
| Swap      | `.swap`        | Swap device                                           | `swapfile.swap`                         |
| Path      | `.path`        | Trigger service on file/directory events              | `cups.path`                             |
| Device    | `.device`      | Hardware device                                       | `dev-sda.device`                        |
| Slice     | `.slice`       | Resource grouping for cgroups                         | `system.slice`                          |
| Scope     | `.scope`       | External processes managed by systemd                 | `user-session.scope`                    |



### .timer example

- Create the script
    - `sudo nano /usr/local/bin/heartbeat.sh`
        ```bash
            #!/bin/bash
            echo "$(date) heartbeat" >> /var/log/heartbeat.log
        ```
    - `sudo chmod +x /usr/local/bin/heartbeat.sh`

- Create the service unit
    - `sudo nano /etc/systemd/system/heartbeat.service`
        ```bash
            [Unit]
            Description=Heartbeat Service

            [Service]
            Type=oneshot
            ExecStart=/usr/local/bin/heartbeat.sh
        ```

    Tip : Type=oneshot → runs once and exits (the timer will schedule repeats)

- Create the timer unit
    - `sudo nano /etc/systemd/system/heartbeat.timer`
        ```bash
            [Unit]
            Description=Run Heartbeat script every 10 seconds

            
            [Timer]
            OnBootSec=5 # Wait 5 seconds after boot to run the service for first time
            OnUnitActiveSec=10 # Run the service 10 seconds after the last run

            #Starts with timers.target
            #systemctl enable heartbeat.timer
            [Install]
            WantedBy=timers.target
        ```

- Reload systemd and start timer
    - `sudo systemctl daemon-reload`
    - `sudo systemctl enable heartbeat.timer` .. enable alone is not enough as enable only creates the symlink so that the timer will start next time the system boots
    - `sudo systemctl start heartbeat.timer`


Tip: timer is doing almost same as cron but in a different format.. cron is simple, timer is there if cron fails you as timer supports second-level, logging with journalctl and more control if you need it


### .socket example

- create the service
    - `sudo nano /etc/systemd/system/echo.service`
    ```ini
    [Unit]
    Description=Simple Echo Service
    Requires=echo.socket
    After=network.target

    [Service]
    ExecStart=/bin/nc -l 127.0.0.1 9000
    StandardInput=socket
    StandardOutput=socket
    Restart=always
    ```

- create the socket unit
    - `sudo nano /etc/systemd/system/echo.socket`
    ```ini
    [Unit]
    Description=Echo Service Socket

    [Socket]
    ListenStream=127.0.0.1:9000
    Accept=yes

    [Install]
    WantedBy=sockets.target
    ```

- enable and start the socket
    - `sudo systemctl daemon-reload`
    - `sudo systemctl enable --now echo.socket`

Tip: .socket dameon will open a socket and when there is a connection comes it will run the .service


### .service example
- create script
    - `sudo nano /usr/local/bin/myapp-script.sh`
    ```bash
    #!/bin/bash

    echo "Custom service started at $(date)"

    # Example: keep running and do something every 10 seconds
    while true; do
        echo "Hello from custom service at $(date)"
        sleep 10
    done

    ```
    - `sudo chmod +x /usr/local/bin/myapp-script.sh`

- create service
    `sudo nano /etc/systemd/system/myapp.service`
    ```ini
    [Unit]
    Description=My Custom Service
    After=network.target

    [Service]
    # Type=simple: service runs as long as ExecStart runs
    Type=simple

    # The command to run (replace with your script or executable)
    ExecStart=/usr/local/bin/myapp-script.sh

    # Restart automatically if the service fails
    Restart=on-failure

    # Wait 5 seconds before restarting
    RestartSec=5

    # Optional: run as specific user instead of root
    #User=myuser
    #Group=myuser

    # Optional: working directory
    #WorkingDirectory=/home/myuser

    # Optional: environment variables
    Environment="ENV_VAR1=value1" "ENV_VAR2=value2"

    [Install]
    # Make this service start on boot
    WantedBy=multi-user.target

    ```

- enable and start the socket
    - `sudo systemctl daemon-reload`
    - `sudo systemctl enable --now echo.socket`