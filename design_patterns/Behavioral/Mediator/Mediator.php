<?php


//idea: handles communication between objects, instead of them talking to each other directly

//Mediator: is the central hub which knows all colleagues
//Colleague: is the individuals that need to communicate with each other but not directly


// Imagine a chatroom where multiple participants can send messages.
// Without a mediator: Each participant would need to know about every other participant → messy.
// With a mediator: participants send messages to the chatroom (mediator), which forwards them appropriately.


// Colleague ... DTO (no behavior, just data)
class User {
    public function __construct(
        public string $name
    ) {}
}

// Mediator
class ChatRoom {
    private string $name;
    private array $users = [];

    public function __construct(string $name) {
        $this->name = $name;
    }

    public function addUser(User $user): void {
        $this->users[] = $user;
    }

    public function sendMessage(User $sender, string $message): void {
        echo "[{$sender->name}] sends: $message\n";
        foreach ($this->users as $user) {
            if ($user !== $sender) {
                $this->deliverMessage($user, $sender, $message);
            }
        }
    }

    private function deliverMessage(User $receiver, User $sender, string $message): void {
        echo "[{$receiver->name}] receives from {$sender->name}: $message\n";
    }
}

// Client code
$room = new ChatRoom("Sports");

$alice = new User("Alice");
$bob   = new User("Bob");
$carol = new User("Carol");

$room->addUser($alice);
$room->addUser($bob);
$room->addUser($carol);

$room->sendMessage($alice, "Hi everyone!");
$room->sendMessage($bob, "Hey Alice!");