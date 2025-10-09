# CLI Command




CLI commands are designed to accept inputs in various forms. In general, these inputs fall into: subcommands, arguments, options, and flags.

```<command> [arguments] [options/flags]```

## Argument Vs Option

### Arguments:
like method parameters .. required to have a value either by passing or by default value

Examples:
- `mv file1.txt /backup/`
- `ls /etc`

### Options + Flags

Optional to have 

Examples
```bash
    # Long Form
    php artisan migrate --database=sqlite 
    ls --color=auto
    rm --recursive #option with no value (called flag/switch)

    # Short Form
    rm -r
```

## Use Argument or Option

### Q: if i am designing cli command, when would i put my input as argument or option?

Rules of Thumb
- if your input is critical for command execution .. make it an argument.
- If yor input is optional .. use an option.
- If your input is just to enable/disable a feature, use a flag (no value needed).

### Example:

if you are designing a command which is news:pull and you have set of providers like ny, guardian,etc...

provider is critical for my command, it's not optional to have .. then it has to be argument

- `php artisan news:pull guardian` .. argument
- `php artisan news:pull` .. argument default to "All"
- `php artisan news:fetch guardian ny` .. argument with multiple values 


## Command Signature

- Argument (think of it like function arguments)

```php
protected $signature = 'mail:send {user}'; 
protected $signature = 'mail:send {user=foo}'; # default value
protected $signature = 'mail:send {user*}'; # accept one or many arguments
```

- Options (optional)

```php
protected $signature = 'mail:send {user} {--queue}';  # if passed then true else false (flag or switch)
protected $signature = 'mail:send {user} {--queue=}'; # if passed then value else null
protected $signature = 'mail:send {user} {--queue=*}'; # accept one or many values
protected $signature = 'mail:send {user} {--q|queue}';  # --queue or -q will work
```

- Argument + Options with comment
```php
protected $signature = 'mail:send
                        {user : The ID of the user}
                        {--queue : Whether the job should be queued}';
```