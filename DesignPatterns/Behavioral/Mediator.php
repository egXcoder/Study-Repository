<?php


//Mediator is the central hub which knows all colleagues
//Colleague individual participants and not talk to each other directly


// Imagine a chatroom where multiple participants can send messages.
// Without a mediator: Each uparticpant would need to know about every other particpant → messy.
// With a mediator: particpants send messages to the chatroom (mediator), which forwards them appropriately.


//if we have a chat room contains 3 particpants, and particpant 1 want to send message
//so instead of $particpant1->sendMessage($message,particpant2) and $particpant1->sendMessage($message,particpant3)
//we would $particpant1->sendInChatRoom($message)
//there is a need of ChatRoom mediator which holds reference to all 3 particpants and makes one object to talk with the other objects


// Colleague
class ChatPartricipant {
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

    public function receive(string $message, ChatPartricipant $sender): void {
        echo "{$this->name} received from {$sender->name}: {$message}\n";
    }
}

// Mediator interface
interface ChatMediator {
    public function sendMessage(string $message, ChatPartricipant $user): void;
    public function addParticipant(ChatPartricipant $user): void;
}

// Concrete Mediator
class ChatRoom implements ChatMediator {
    private $users = [];

    public function addParticipant(ChatPartricipant $user): void {
        $this->users[] = $user;
    }

    public function sendMessage(string $message, ChatPartricipant $sender): void {
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
$user1 = new ChatPartricipant($chatRoom, "Alice");
$user2 = new ChatPartricipant($chatRoom, "Bob");
$user3 = new ChatPartricipant($chatRoom, "Charlie");

// Add users to chatroom
$chatRoom->addParticipant($user1);
$chatRoom->addParticipant($user2);
$chatRoom->addParticipant($user3);

// Communication via mediator
$user1->sendInChatRoom("Hi everyone!");
$user2->sendInChatRoom("Hello Alice!");