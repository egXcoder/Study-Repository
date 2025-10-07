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

- Event-driven and asynchronous: handles thousands of simultaneous connections with low memory footprint.

- Master-worker model: TODO:: to be reviewed

    - Master process: controls workers, reads config, handles signals.

    - Worker processes: handle actual connections and requests. Each worker can handle many clients asynchronously.

Q: TODO:: is nginx better than apache?


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

#### Examples:

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
        proxy_pass http://127.0.0.1:3000; #forward request to here
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr; # let backend know the original client IP
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
    }
}

```

notice: 
    - i am forcing connection = 'upgrade' but its not doing anything unless uprade header = 'websocket'..
    - proxy_cache_bypass $http_upgrade; bypass backend cache if this condition true .. its like dont cache backend requests for websockets


- TODO:: Acting as php-fpm with ability to cache backend requests