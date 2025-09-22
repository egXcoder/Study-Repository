<?php

//below is another implemenation of chain of responsibility using higher order function pipelines
//these pipelines handlers can be proxy or decorators, doesnt matter always we can call them handlers


class Pipeline
{
    protected $pipes = [];

    public function through(array $pipes)
    {
        $this->pipes = $pipes;
        return $this;
    }

    public function then(\Closure $destination)
    {
        $pipeline = array_reduce(array_reverse($this->pipes), function ($next, $pipe) {
                return function ($request) use ($next, $pipe) {
                    // If pipe is a closure, just call it
                    if ($pipe instanceof \Closure) {
                        return $pipe($request, $next);
                    }

                    // If pipe is an object, call its handle() method
                    if (is_object($pipe) && method_exists($pipe, 'handle')) {
                        return $pipe->handle($request, $next);
                    }

                    throw new \Exception("Invalid pipe type: must be Closure or object with handle().");
                };
            },
            $destination
        );

        return $pipeline;
    }
}

// Validation handler as a class
class UserRequiredValidation
{
    public function handle($request, $next)
    {
        if (empty($request['user'])) {
            echo "Validation failed: user missing" . PHP_EOL;
            return null; // stop chain
        }
        return $next($request);
    }
}

// Another validation handler
class ActionAllowedValidation
{
    protected $allowed = ['create', 'update'];

    public function handle($request, $next)
    {
        if (!in_array($request['action'], $this->allowed)) {
            echo "Validation failed: action not allowed" . PHP_EOL;
            return null;
        }
        return $next($request);
    }
}

$pipeline
    ->through([
        new UserRequiredValidation(),   // class handler
        new ActionAllowedValidation(),  // class handler

        // sanitizer as closure
        function ($request, $next) {
            $request['user'] = trim(strtolower($request['user']));
            return $next($request);
        },

        // logger as closure
        function ($request, $next) {
            echo "Logger: " . json_encode($request) . PHP_EOL;
            return $next($request);
        },
    ])
    ->then(
        function ($request) {
            echo "Final handler got request: " . json_encode($request) . PHP_EOL;
            return $request;
        }
    );

$pipeline(['user' => '  JOHNDOE  ','action' => 'create']); //execute A,B,C then final handler


//Q1 : why are we calling array_reverse?
// We use array_reduce to ensure pipes are called in the correct order, as function nesting is like stack
//so we put in the stack C,B,A ..which ends execution A,B,C
//
//see...
// $pipes = [A, B, C];
// $pipeline = fn($request) => C($request, fn($r) => B($r, fn($r) => A($r, $destination)));
// notice in terms of execution order, A($r,$destination) execute first, then B, then C which is the expected order 



//Q2: should we use oop way or closure way?
// Classic OOP way: Each handler is a class, and parent::handle($request) or $this->next->handle($request) passes the request along.
// Pros:
// - Very OO-friendly, each handler is a proper class.
// - Easier to unit test individual handlers.
// - Good if you want rich hierarchy, inheritance, or shared logic between handlers.

// Cons:
// - Slightly more verbose to set up the chain ($h1->setNext($h2)->setNext($h3)).
// - Less flexible if you just want a simple inline handler (closure) instead of a class.



// Callback / Closure way: Each “handler” is a function or closure, e.g., $next($request), which is exactly how Laravel middleware works.
// Pros:
// - Very flexible; you can define handlers inline.
// - Matches Laravel middleware and functional pipelines.
// - You can dynamically build a chain at runtime.

// Cons:
// - Harder to unit test in isolation, unless you wrap closures in classes.
// - Can become messy if you have complex branching logic or many closures.


// Which is better?

// Use classic parent::handle() if:
// - Handlers are complex, reusable classes.
// - You want OO structure and inheritance.

// Use callback/Closure style if:
// - You want dynamic, lightweight chains like middleware.
// - You are working in Laravel-style pipelines.



// Q3: Should every chain of handlers share a generic “request” type (like a Context or Request class)?
//   Or should each chain define its own abstract base class with the correct method signature (handle(MyRequest $request))?


// Option 1: Generic Request Object
class RequestContext {
    public array $data = [];
    public array $meta = [];
}

abstract class Handler {
    protected Handler $next = null;

    public function setNext(Handler $handler): Handler {
        $this->next = $handler;
        return $handler;
    }

    public function handle(RequestContext $request): RequestContext {
        if ($this->next) {
            return $this->next->handle($request);
        }
        return $request;
    }
}

// Pros:
// - Reusable — one Handler base class works for any kind of chain.
// - Flexible — handlers can attach arbitrary metadata ($request->meta['user'] = ...).
// - Useful when the request can vary or grow over time (e.g., pipelines, middleware).

// Cons:
// - Handlers don’t get compile-time safety.
// - You might need to “know” what keys are inside $request->data.
// - Could get messy without good documentation.


// Option 2: Domain-Specific Abstract Handler
abstract class PaymentHandler {
    protected PaymentHandler $next = null;

    public function setNext(PaymentHandler $handler): PaymentHandler {
        $this->next = $handler;
        return $handler;
    }

    abstract public function handle(PaymentRequest $request): PaymentResponse;
}


// Pros:
// - Strong typing — compiler/IDE can check you’re passing the right objects.
// - Handlers are self-documenting: PaymentHandler clearly belongs to payment chain.
// - Better if you have business-specific chains (auth, billing, validation, etc.).

// Cons:
// - You’ll repeat the Handler boilerplate for each domain (AuthHandler, PaymentHandler, etc.).
// - Less reusable — each chain is siloed.


// My rule of thumb:
// If I’m writing application-level logic (payments, approvals, workflow), I go with domain-specific handlers.
// If I’m writing infrastructure-level pipelines (logging, middleware, transformations), I use a generic Context or Request object.


//Q: does laravel have pipeline class that can be used out of the box?
//yes, laravel have Pipeline class and laravel itself use it heavily internally. you can use it to chain your handlers
//  Pipeline::send($request)
    // ->through([
    //     First::class,
    //     Second::class,
    //     Third::class,
    // ])
    // ->thenReturn();