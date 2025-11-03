### Images Commands
- you can search for available images on docker website https://hub.docker.com/
- `docker images` ... list downloaded images .. inc dangling images
- `docker pull elasticsearch:8.19.4` ... pull image from docker website to your os
- `docker rmi <image_id>` ... remove specific image

### Manage Containers Containers
- `docker ps` .. list running containers
- `docker ps -a` .. list all containers including stopped
- `docker start <container_name_or_id>` ... start a stopped container
- `docker stop <container_name_or_id>` ... stop a stopped container
- `docker kill <container_name_or_id>` .. kill and no graceful shutdown
- `docker rm <container_name_or_id>` .. remove container
- `docker restart <container_name_or_id>` ... restart a stopped container
- `docker stop $(docker ps -q)` .. stop all containers

### Running Container
- `docker run ubuntu` .. 
    - run docker CMD and attach current terminal with that CMD command
    - by default docker assumes you dont want to interact with container, so it closes stdin so by default
    - if CMD command is server like apache, then your terminal will show you apache server started and waiting for listening
    - if CMD command is /bin/bash, then docker will run /bin/bash and since it STDIN is blocked by default, then it exit immediately

- `docker run -i ubuntu` .. (i interactive) 
    - run container CMD and allow stdin into docker CMD
    - if CMD command is /bin/bash, then docker won't close immediately and rather will wait for your input

- `docker run -t ubuntu` .. (t terminal) 
    - run container CMD and give back proper shell look and feel
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



### Executing Command in Container
- `docker exec -it redis redis-cli`
    - run a new command inside a running container.

- `docker exec -u root -it myapp bash`
    - run command inside a running container as root

- `docker exec myapp cat /etc/nginx/nginx.conf`
    - run one command to check configuration inside a container





## Cleaning
- `docker system df` .. see what is taking space with docker
- `docker system prune` .. remove stopped containers + dangling images + unused cache + unused network cards
- `docker container prune` .. stopped containers
- `docker image prune` .. dangling images
- `docker volume prune` .. unused volumes

Tip: `docker {whatever} prune -a` .. is more force on cleaning

Tip: Avoid pulling latest images → every pull may download new layers unnecessarily.