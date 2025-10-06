# Docker 

Docker is a tool that lets you package an application with everything it needs—code, libraries, dependencies, settings—inside a small isolated environment called a container.

## Applications:
- Saves time for onboarding developers
    - New developer joins the team? Instead of installing PHP, MySQL, Redis manually:
    - git clone project then docker-compose up
- microservices
    - You can run multiple containers on the same machine with very little overhead. Great for:
        - APIs
        - Microservices
        - Workers
        - Cron services

    - Isolated services (microservices)
        - Each service (e.g., frontend, backend, DB, Redis) runs in its own container.
        - No conflicts:
        - PHP in one container
        - Nginx in another
        - MySQL in another
        - If MySQL crashes, the rest still work.
    - Scalability
        - Scale containers up/down
        - Load balance
        - Restart on failure

- if you want to run multiple applications on same server
        
- if you want to try a tool without polluting your main system

- CI/CD .. TODO:: TO Be Reviewed after studying CI/CD 


## Key Concepts:

- Image: A template for your app. Example: PHP 8 + Composer + your code = one image.
- Container: A running instance of the image. You can start/stop/delete it anytime.
- every container has one entry point, one cmd command and this is the command which runs when container runs
    and your terminal is going to be attached with this command, if command is running server your terminal will end up with only logs and no input. if command is running redis-cli for example then your terminal will open redis-cli

- Dockerfile: A script that tells Docker how to build the image.
- docker-compose.yml: Used when you run multiple containers together (e.g., app + db + redis).

```dockerfile
FROM php:8.2-apache
COPY . /var/www/html
RUN docker-php-ext-install pdo_mysql
CMD bash -c "composer install && sleep 35 && php /var/www/html/artisan migrate & apache2-foreground"
```


## Install:
you can refer back to docker documentation website for that

## Images Commands
- you can search for available images on docker website https://hub.docker.com/
- `docker pull elasticsearch:8.19.4` ... pull image from docker website to your os
- `docker images` ... list downloaded images .. inc dangling images
- `docker image inspect ubuntu` .. read dockerfile of an image
- `docker rmi <image_id>` ... remove specific image
- `docker image prune` .. remove dangling images which have no tags and not used by containers (safe)
- `docker image prune -a` .. remove any image that dont have containers

dangling images:
```bash
# First build .. IMAGE_ID: abc123
docker build -t my-app:1.0 .

# Second build - creates NEW image, old one becomes dangling
docker build -t my-app:1.0 .

#------------------

docker pull nginx:latest
# Later, when a new nginx:latest is available: The old nginx:latest becomes <none>:<none> dangling
docker pull nginx:latest

#------------------------

$ docker images
REPOSITORY   TAG       IMAGE ID       CREATED         SIZE
my-app       latest    def45678       2 minutes ago   450MB
nginx        latest    1234abcd       3 days ago      187MB
<none>       <none>    abc12345       1 hour ago      450MB   ← DANGLING IMAGE
<none>       <none>    9876xyz        2 hours ago     320MB   ← DANGLING IMAGE
```


## CMD VS EntryPoint
- CMD
    - Provides default arguments/command for the container that can be overridden when you run the container.

    ```bash
    CMD ["executable","param1","param2"]   # exec form (preferred)
    CMD command param1 param2              # shell form

    docker run <image>          # runs CMD
    docker run <image> ls -l    # overrides CMD
    ```

- EntryPoint (Fixed)
    - Sets the main executable for the container. Arguments from docker run are passed to it.
    ```bash
    ENTRYPOINT ["executable","param1"]   # exec form (preferred)
    ENTRYPOINT command param1            # shell form

    docker run <image>               # runs ENTRYPOINT
    docker run <image> arg1 arg2     # passed as arguments to ENTRYPOINT
    ```

- CMD + EntryPoint:
    - ENTRYPOINT defines the main executable (the command that always runs).
    - CMD provides default arguments to that executable.

    ```bash
    ENTRYPOINT ["echo"]
    CMD ["Hello World"]

    docker run myimage # Output: Hello World
    docker run myimage "Hi there" # Output: Hi there  (CMD is overridden)

    # To Override Entry Point You have to be explicit
    docker run --entrypoint /bin/bash myimage "Hi there"
    ```

## Containers Commands

### Run (build) Containers
- `docker run ubuntu` .. 
    - run docker CMD and attach current terminal with that CMD command
    - by default docker assumes you dont want to interact with container, so it closes stdin so by default
    - if CMD command is server like apache, then your terminal will show you apache server started and waiting for listening
    - if CMD command is /bin/bash, then docker will run /bin/bash and since it STDIN is blocked by default, then it exit immediately

- `docker run -i ubuntu` .. (i interactive) 
    - run docker container CMD and allow stdin into docker CMD
    - if CMD command is /bin/bash, then docker won't close immediately and rather will wait for your input

- `docker run -t ubuntu` .. (t terminal) 
    - run docker container CMD and give back proper shell look and feel
    - if CMD command is /bin/bash, then docker will show you proper terminal instead of just input/output

- `docker run -it ubuntu` .. 
    - run docker CMD and allow STDIN and give proper terminal look and feel if possible

- `docker run -it ubuntu sh` ..
    - ability to override CMD command, so it would run sh instead
    - if CMD command is nginx server, and i wrote `docker run -it nginx bash` it will run container into bash and it wont run nginx server

- `docker run -d redis`
    - run container in detached mode .. like run it in background
    - super practical for server containers like apache, nginx, redis etc...

- `docker run -d --name mynginx -e MYSQL_ROOT_PASSWORD=my-secret-pw nginx` 
    - run a container with name and environment variable

- `docker run -d -p 8080:80 httpd`
    - run apache container in detached mode
    - expose port host:container .. so out machine gets port 8080 which is container 80

- `docker run -v <host_path>:<container_path> <image>`
    - v is for volume
    - mount directory from host to container
    - `docker run -d -p 8080:80 -v /home/user/mywebsite:/usr/local/apache2/htdocs/ httpd`


### List Containers
- `docker ps` .. list running containers
- `docker ps -a` .. list all containers including stopped
- `docker ps -q` .. list containers ids


### Start/Stop/Restart Containers
- `docker start <container_name_or_id>` ... start a stopped container
- `docker stop <container_name_or_id>` ... stop a stopped container
- `docker kill <container_name_or_id>` .. kill and no graceful shutdown
- `docker rm <container_name_or_id>` .. remove container
- `docker restart <container_name_or_id>` ... restart a stopped container
- `docker stop $(docker ps -q)` .. stop all containers


### Save Space
- Periodically run

```bash
# Remove stopped containers
docker container prune

# Remove dangling images
docker image prune

# Remove unused volumes
docker volume prune

# Remove everything not used
docker system prune -a
```

- Avoid latest → every pull may download new layers unnecessarily.




Q: is it better to pull image for latest or pull a specific tag?
using a specific tag is safer than latest. Here’s why: 

Points to the most recent build of that image

Pros:
- Always up-to-date

Cons:
- Can break your container unexpectedly
- New latest may introduce incompatible changes, new bugs, or updated dependencies
- Hard to reproduce exact environment later



## Dockerfile

- A Dockerfile is used to set instructions of how to build image.
- every image has only one CMD which will run when container runs..
- its better to split your application to containers that work together than to gather everything in one container
    - then you can upgrade or downgrade a specific component
    - if one container fails you can replace it
    - if all of services is in one image, it will be difficult to debug if something goes wrong or upgrade/downgrade if something goes wrong


```dockerfile

# 1. Base image
FROM ubuntu:22.04

# 2. Maintainer/author
LABEL maintainer="ahmed@example.com"

# Switch to root explicitly (optional if the base image is already root)
USER root

# 3. Environment variables
ENV APP_HOME=/app

# 4. Working directory .. directory you will be on while running commands
WORKDIR $APP_HOME

# 5. Copy files from host to container .. host current directory to specific location in docker
COPY ./ /var/www/html

# 6. Install dependencies
RUN apt-get update && apt-get install -y python3 python3-pip

# 7. Install Python packages
RUN pip3 install -r requirements.txt

# 8. Expose port (optional, for network access)
EXPOSE 5000

# 9. Command to run when container starts
CMD ["python3", "app.py"]

```

## Docker compose

run multi-container Docker applications using a single configuration file, typically called docker-compose.yml.

Instead of running multiple docker run commands manually, you can declare your entire application stack—containers, networks, volumes, ports, and environment variables—in one file and start everything with a single command.

- `docker-compose up` .. Build, (re)create, and start all services. Runs in foreground.
- `docker-compose up --build` .. ReBuild Image..then up containers
- `docker-compose down` .. Stop and remove containers, networks, and optionally volumes/images.
- `docker-compose stop` .. Stop running containers but keep them.
- `docker-compose start` .. Start existing stopped containers.
- `docker-compose restart` .. restart containers


```yaml

version: '3.9'  # Compose file version

services:
  web:
    build: ./app      # Build from Dockerfile in ./app
    ports:
      - "5000:5000"   # Map host port 5000 to container port 5000
    environment:
      - APP_ENV=development
    volumes:
      - ./app:/app    # Mount local folder for live code updates
    networks:
      - net
    depends_on:
      - db

  db:
    image: postgres:15
    environment:
      POSTGRES_USER: admin
      POSTGRES_PASSWORD: secret
      POSTGRES_DB: mydb
    volumes:
      - db_data:/var/lib/postgresql/data
    networks:
      - net

volumes:
  db_data:
    driver: local             # default, could use custom drivers
    driver_opts:
      o: bind                 # options for driver
      device: /path/on/host   # bind mount location

networks:
  net:
    driver: bridge

```