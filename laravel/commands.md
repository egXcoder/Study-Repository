# CLI Command



## General

CLI commands are designed to accept inputs in various forms. In general, these inputs fall into: subcommands, arguments, options, and flags.

```<command> <subcommand> [arguments] [options/flags]```

- Sub Commands:
    - When Command is multi functional, setting a subcommand would tell what is the required operation is
    - each Subcommands can have their own arguments, options, and flags.
    ``` bash
    git status       # show current repo status
    git push origin  # push commits to remote
    git log          # show commit history
    ```

- Arguments:
    - values passed to a command. (you can think of it like parameters of a method)
    - Order matters and can be required or optional

    Examples:
    ``` bash
        mv file1.txt /backup/ #argument (source file) #argument (destination directory) 
        ls /etc # /etc is optional argument if not set it will go to default
    ```

- Options + Flags
    - prefixed with -- (long form) or - (short form).

    ```bash
        php artisan migrate --database=sqlite # option with a value
        ls --color=auto # option with a value

        rm -r # option with no value (called flag)
        rm --recursive #option with no value (called flag)
    ```

Q: if i am designing cli command, when would i put my input as argument or option?

Rules of Thumb
- If your command cannot run without the value, make it an argument.
- If the value changes the behavior but is not strictly required, use an option.
- If the user just needs to enable/disable a feature, use a flag (no value needed).

Example:

if you are designing a command which is news:pull and you have set of providers like ny, guardian,etc...

- if command shouldnt be run without explicit provider then argumet `php artisan news:pull ny`
- if command can still run without explicit provider, for example default to all, then its option `php artisan news:pull`
- if command can support multiple providers in same time then use options `php artisan news:fetch --provider=bbc --provider=cnn`


# Laravel Command

- Argument (think of it like function arguments)

```php
protected $signature = 'mail:send {user}'; 
protected $signature = 'mail:send {user=foo}'; # default value
```

- Options (optional)

```php
protected $signature = 'mail:send {user} {--queue}';  # passed then true .. not passed then false (flag or switch)
protected $signature = 'mail:send {user} {--queue=}'; # passed then value .. not passed then null
protected $signature = 'mail:send {user} {--q|queue}';  # --queue or -q will work
```

- Argument + Options with comment
```php
protected $signature = 'mail:send
                        {user : The ID of the user}
                        {--queue : Whether the job should be queued}';
```