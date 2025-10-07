# Nginx

is a high-performance web server that can also act as a reverse proxy, load balancer, and HTTP cache.

It’s widely used because it’s fast, lightweight, and efficient at handling many connections simultaneously.

## Core Roles of Nginx
- Web server: Serves static files like HTML, CSS, JS, images directly to users.

- Reverse proxy: Receives client requests and forwards them to backend servers (like PHP-FPM, Node.js, etc.)

- Load balancer: Distributes incoming requests across multiple backend servers for better performance and reliability

- HTTP cache: Can cache responses from backend servers to reduce load and speed up delivery

- TLS termination: Handles SSL/TLS connections so backend servers don’t need to manage encryption


## Nginx Architecture

Event-driven and asynchronous: handles thousands of simultaneous connections with low memory footprint.

🔹 Main Components
- Master process → Starts as root, reads configs, and spawns workers.
- Worker processes → Handle all actual client connections.

master process doesn’t handle client requests directly. Spawns worker processes and each listen on port

worker process is single-threaded — NGINX uses the OS’s epoll (Linux) or kqueue (BSD/macOS) system call. for that it can handle thousands of connections simultaneously. In Apache Event MPM every worker process have multiple ready threads so every connection would go to a thread

That allows it to:
- Watch many file descriptors (sockets) at once.
- React only when one of them is ready (readable/writable).
- Avoid idle waiting and thread context switching.
- reduce memory overhead of threading
- So one worker might handle 10,000+ concurrent connections without breaking a sweat.


## Install

`apt install nginx`
`systemctl enable nginx --now` activate and start nginx service
`systemctl status nginx` status of service

## Configure

`/etc/nginx/nginx.conf` .. main general configure

### Sites

Nginx has a concept similar to Apache’s a2ensite, but it’s not a built-in command; it’s just a convention on Debian/Ubuntu using sites-available and sites-enabled.

- Add Site
    - `nano /etc/nginx/sites-available/mysite.com` add a site into sites-available
    - `ln -s /etc/nginx/sites-available/mysite.com /etc/nginx/sites-enabled/` .. create symlink in site-enabled
    - `nginx -t` .. test config
    - `systemctl restart nginx`

- Remove Site
    - `rm /etc/nginx/sites-enabled/mysite.com`
    - `nginx -t` .. test
    - `systemctl restart nginx` .. restart

notice: ln -s .. paths should be explicit.. they cant be relative

#### Sites Examples:

- Acting as web server.. which means download static files..

    - This Model works with static web page .. 
    - every directory has index.html .. 
    - if no index.html exists it will give forbidden
    - this try_files.. is going to try to get css files etc.. if it can't find it will return 404



    ```nginx
    server {
        listen 80;
        server_name mysite.com www.mysite.com;

        root /var/www/mysite.com;
        index index.html index.htm;

        # try_files <file1> <file2> ... <fallback>;
        # Browser requests /about?title=info .. is there a file/directory called about or about/ .. then serve it directly .. if not then 404
        # Browser requests /css/style.css .. is there uri called css/style.css .. then serve it directly .. if not then 404
        location / {
            try_files $uri $uri/ =404;
        }
    }
    ```

- Acting as php-fpm web server

    ```nginx
    server {
        listen 9000;
        server_name mysite.com www.mysite.com;

        root /var/www/html/laravel/public;
        index index.php index.html index.htm;

        # Serve static files directly
        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        # PHP-FPM handling
        location ~ \.php$ {
            include snippets/fastcgi-php.conf;
            fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;  # adjust PHP version
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }

        # Deny access to .ht* files
        location ~ /\.ht {
            deny all;
        }

        # Optional: caching for static assets
        location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
            expires 30d;
            add_header Cache-Control "public";
        }

        # Optional: logs
        access_log /var/log/nginx/news_fetcher.access.log;
        error_log /var/log/nginx/news_fetcher.error.log;
    }

    ```

- Acting as reverse proxy

```nginx

server {
    listen 80;
    server_name mysite.com www.mysite.com;

    location / {
        proxy_pass https://127.0.0.1:3000; #forward request to here
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr; # let backend know the original client IP
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

```

notice: 
    - i am forcing connection = 'upgrade' but its not doing anything unless uprade header = 'websocket'..


- Acting as Load Balancer

```nginx


# Define backend group
upstream backend_cluster {
    least_conn; # if not there then round robin
    
    server 10.0.0.11 weight=3;   # more weight = more traffic

    # after 3 failed attempts, mark server as “unavailable” for 30 seconds, then auto retry
    server 10.0.0.12  max_fails=3 fail_timeout=30s; 
    
    # The backup server receives no traffic unless all primary servers are down.
    server 10.0.0.13 backup;   # only used if others fail
}

server {
    listen 80;
    server_name myapp.local;

    location / {
        proxy_pass http://backend_cluster;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        # Optional: keep connections alive
        proxy_http_version 1.1;
        proxy_set_header Connection "";
    }
}

```

ways to distribute requests:
- (default) Round robin  ... 
- (least_conn;) Least connections 
- (ip_hash;) IP hash


notice:
- If you use NGINX Plus (Commercial), you get true active health checks:
    - It periodically sends custom HTTP requests (e.g. /health) to backends.
    - Marks nodes “up” or “down” based on responses.
    - Reacts immediately (not only when traffic fails).


### Contexts Within Configuration

Nginx is entirely context-based. Every directive (line) in nginx.conf must live inside a context

- main: Top-level directives (affect entire Nginx process)
- events: Controls connection handling (worker connections, polling, etc.)
- http: Handles HTTP/HTTPS (Layer 7) requests
- stream: Handles TCP/UDP (Layer 4) proxying
- mail: Handles SMTP/IMAP/POP3 proxying (less common)

inside /etc/nginx/nginx.conf

```nginx

# -------------------------
# MAIN CONTEXT
# -------------------------
user  www-data;              # Which user Nginx worker processes run as
worker_processes  auto;      # Automatically determine number of workers
pid /var/run/nginx.pid;      # Location of PID file
error_log /var/log/nginx/error.log warn;  # Global error log
worker_rlimit_nofile 65535;  # Max open files per worker

# -------------------------
# EVENTS CONTEXT
# -------------------------
events {
    worker_connections 1024;  # Max connections per worker
}

# -------------------------
# HTTP CONTEXT
# -------------------------
http {
    include /etc/nginx/conf.d/*.conf;
    include /etc/nginx/sites-enabled/*;
}

```


notice: 
- configurations written in sites-enabled .. dont have to be surrounded http context, since its already surrounded in main config



### L7 and L4 Load Balancer

L7: This is the most common use case — proxying and balancing web requests.

✅ Features available:
- Routing based on path, host, headers, cookies, etc.
- SSL termination
- Caching, compression, rewriting, etc.

```nginx

http {
    upstream web_backend {
        server 10.0.0.2:8080;
        server 10.0.0.3:8080;
    }

    server {
        listen 80;
        server_name example.com;

        location / {
            proxy_pass http://web_backend;
            proxy_set_header Host $host;
            proxy_set_header X-Real-IP $remote_addr;
        }
    }
}

```

L4: TCP/UDP This uses the stream context — for raw TCP or UDP traffic such as HTTP, MySQL, SMTP, or custom protocols.

```nginx

stream {
    upstream db_backend {
        server 10.0.0.4:3306;
        server 10.0.0.5:3306;
    }

    server {
        listen 3306;
        proxy_pass db_backend;
    }
}

```


### Nginx or HAProxy for Load Balancing
if you are going for simple load balancing then nginx is good for the need.. 

if you are trying to do sophisticated load balancing then go for HAProxy which is built and designed for this



### (DOS ATTACK) Rate Limiting

```nginx
# in http {}

# define zone one with size = 10Mb (track ~ 160k unique ip)
# will track requests rate using key $binary_remote_addr which is client ip address
# Allows 10 requests per second per IP
limit_req_zone $binary_remote_addr zone=one:10m rate=10r/s;

server {
    listen 80;
    server_name example.com;

    location / {
        # allow 20 extra requests after exceeding the rate;
        # since its no delay then these 20 extra requests won't be delayed 
        # if exceeded then status 429 when rejected
        limit_req zone=one burst=20 nodelay;

        # allow 20 extra requests after exceeding the rate;
        # but it will release them gradually to comply with speed 10r/s
        # if exceeded then status 429 rejected
        limit_req zone=one burst=20;

        # after exceeding 10r/s it will be rejected immediately
        limit_req zone=one;

        proxy_pass http://backend;
    }
}

```


### Slow Loris Attack

Reduce timeouts so slow clients cannot hold connections forever.

If you don’t set those, slow clients can stay longer than desired — but they won’t block other clients.

✅ Result: NGINX is naturally resistant to Slowloris.

```nginx
# in http or server {}
client_body_timeout 10s;
client_header_timeout 10s;
send_timeout 10s;
keepalive_timeout 5s;     # reduce keep alive for idle connections
client_max_body_size 10m; # prevent huge uploads
large_client_header_buffers 4 8k;
```



### When To Use apache or nginx?

✅ Use NGINX if:
- You’re serving static files, APIs, or reverse proxying to app servers (Node, PHP-FPM, etc.)
- You want to handle many concurrent users with minimal RAM.
- You don’t need .htaccess or per-folder configuration.
- You want simple load balancing, caching, or SSL termination.


✅ Use Apache (Event MPM) if:
You need legacy .htaccess support (e.g., shared hosting, WordPress plugins).
You want mod_php or similar integration without php-fpm.
You already have deep Apache tooling or configurations.
You have moderate concurrency and want to keep flexibility.