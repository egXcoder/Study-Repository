# Lib C


libc stands for C standard library. It’s a collection of standard functions for C programs, providing core functionality like:


- Input/output operations (e.g., printf, scanf, fopen)
- String handling (e.g., strcpy, strlen)
- Memory management (e.g., malloc, free)
- Math functions (e.g., sqrt, pow)
- Process control (e.g., fork, exit, system)


## Why Lib C?

Without libc, you’d have to write your own functions to handle even the simplest tasks like reading a file or printing to the screen. libc abstracts system calls and provides a portable, standardized interface.


## Lib C and System Calls

libc acts as a wrapper around system calls, making life easier for developers.

When your C program calls printf, it doesn’t call the kernel directly.

Instead, printf is implemented in libc. Internally, libc uses system calls to talk to the kernel.

printf("Hello\n")  →  libc implementation  →  write() system call  →  kernel writes to terminal


## System calls

are assembly instructions tell the cpu how to do something. and libc is a wrapper around it to provide better way for developers

```nasm

; Linux x86_64 example - write system call
mov rax, 1      ; sys_write system call number
mov rdi, 1      ; file descriptor (stdout)
mov rsi, msg    ; buffer
mov rdx, len    ; length
syscall         ; invoke system call

```
