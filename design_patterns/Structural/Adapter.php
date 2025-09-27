<?php


//Adapter: Converts Adaptee to Target by implementing Target and wrapping Adaptee
//Adaptee: The existing/legacy/incompatible class you want to reuse
//Target: The interface expected by the client


// Suppose your application expects to send notifications through a NotifierInterface
// but you want to reuse a legacy email sender class that has a different method signature.




class LegacyEmailSender {
    public function sendEmail(string $recipient, string $content) {
        echo "Sending EMAIL to $recipient: $content\n";
    }
}


//Expected Interface
interface NotifierInterface {
    public function send(string $to, string $message);
}

class EmailAdapter implements NotifierInterface {
    private $legacyEmailSender;

    public function __construct(LegacyEmailSender $legacyEmailSender) {
        $this->legacyEmailSender = $legacyEmailSender;
    }

    public function send(string $to, string $message) {
        // Translate the call to the legacy method
        $this->legacyEmailSender->sendEmail($to, $message);
    }
}