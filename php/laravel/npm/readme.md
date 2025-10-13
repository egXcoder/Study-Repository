# NPM

npm is node package manager to manage packaging installations/activation etc..

nodejs as runtime environment used for frontend tooling (like compiling CSS/JS with Vite, Mix, or Webpack).

## Installation


NVM = Node Version Manager

- A tool that lets you install and switch between different versions of Node.js on your machine.
- Useful because different projects often need different Node versions.
- `nvm install 20 && nvm list`


FNM = Fast Node Manager

- the new tool and its alternative to nvm
- It is written in Rust, which helps with speed, efficiency, low startup overhead
- `fnm install 20 && fnm list`



- you can have multiple nodejs versions within your linux and you can switch between them per terminal
    - `fnm list` .. list available versions on your machine
    - `fnm default 22` .. set default.. so whenver you open new terminal this will be the chosen version
    - `fnm use 20` .. it will change nodejs to be 20 on this terminal
    - within project file, create a file .nvmrc and put 20 (this will hint that you project to be version 20)
    - `fnm use` will read the version from .nvmrc file and use it per terminal
    
    Tip: when you change nodejs version.. npm version auto switch to compatible version .. in practice → installing Node gives you npm by default as nodejs bundled with npm, so most people treat them as a pair.

    