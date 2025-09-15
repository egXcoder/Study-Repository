<?php

//Chain Of Responsibility also can be called
// - Pipeline
// - Interceptor Pattern
// - Filter Pattern
// - Middleware


// Let’s say we are building a login system where we check:
// If email is not empty.
// If password is not empty.
// If user exists in the database.
// Instead of putting all checks in one big method, we use CoR.


interface Handler {
    public function setNext(Handler $handler): Handler;
    public function handle(array $request): ?string;
}

abstract class AbstractHandler implements Handler {
    private Handler $nextHandler = null;

    public function setNext(Handler $handler): Handler {
        $this->nextHandler = $handler;
        return $handler;
    }

    public function handle(array $request): ?string {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($request);
        }
        return null;
    }
}

class CheckEmailHandler extends AbstractHandler {
    public function handle(array $request): ?string {
        if (empty($request['email'])) {
            return "Email is required!";
        }
        return parent::handle($request);
    }
}

class CheckPasswordHandler extends AbstractHandler {
    public function handle(array $request): ?string {
        if (empty($request['password'])) {
            return "Password is required!";
        }
        return parent::handle($request);
    }
}

class CheckUserExistsHandler extends AbstractHandler {
    private array $users = [
        "test@example.com" => "1234"
    ];

    public function handle(array $request): ?string {
        if (!isset($this->users[$request['email']])) {
            return "User not found!";
        }
        if ($this->users[$request['email']] !== $request['password']) {
            return "Invalid password!";
        }
        return "Login successful!";
    }
}


//client code

$emailHandler = new CheckEmailHandler();
$passwordHandler = new CheckPasswordHandler();
$userExistsHandler = new CheckUserExistsHandler();

// Chain them: email → password → userExists
$emailHandler->setNext($passwordHandler)->setNext($userExistsHandler);

// Example 1: Missing email
echo $emailHandler->handle(["password" => "1234"]);
// Output: Email is required!

// Example 2: Missing password
echo $emailHandler->handle(["email" => "test@example.com"]);
// Output: Password is required!

// Example 3: Wrong password
echo $emailHandler->handle(["email" => "test@example.com", "password" => "wrong"]);
// Output: Invalid password!

// Example 4: Success
echo $emailHandler->handle(["email" => "test@example.com", "password" => "1234"]);
// Output: Login successful!
