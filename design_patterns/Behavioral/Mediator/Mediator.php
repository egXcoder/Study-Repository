<?php


//Mediator is the central hub which knows all colleagues
//Colleague is the individuals that need to communicate with each other but not directly


// Imagine a chatroom where multiple participants can send messages.
// Without a mediator: Each participant would need to know about every other participant → messy.
// With a mediator: participants send messages to the chatroom (mediator), which forwards them appropriately.



// Colleague
class ChatParticipant {
    protected $mediator;
    protected $name;

    public function __construct(ChatMediator $mediator, string $name) {
        $this->mediator = $mediator;
        $this->name = $name;
    }

    public function sendInChatRoom(string $message): void {
        echo "{$this->name} sends: {$message}\n";
        $this->mediator->sendMessage($message, $this);
    }

    public function receive(string $message, ChatParticipant $sender): void {
        echo "{$this->name} received from {$sender->name}: {$message}\n";
    }
}

// Mediator interface
interface ChatMediator {
    public function sendMessage(string $message, ChatParticipant $user): void;
    public function addParticipant(ChatParticipant $user): void;
}

// Concrete Mediator
class ChatRoom implements ChatMediator {
    private $users = [];

    public function addParticipant(ChatParticipant $user): void {
        $this->users[] = $user;
    }

    public function sendMessage(string $message, ChatParticipant $sender): void {
        foreach ($this->users as $user) {
            // Don’t send the message back to the sender
            if ($user !== $sender) {
                $user->receive($message, $sender);
            }
        }
    }
}




//client code

// Create mediator
$chatRoom = new ChatRoom();

// Create users
$user1 = new ChatParticipant($chatRoom, "Alice");
$user2 = new ChatParticipant($chatRoom, "Bob");
$user3 = new ChatParticipant($chatRoom, "Charlie");

// Add users to chatroom
$chatRoom->addParticipant($user1);
$chatRoom->addParticipant($user2);
$chatRoom->addParticipant($user3);

// Communication via mediator
$user1->sendInChatRoom("Hi everyone!");
$user2->sendInChatRoom("Hello Alice!");