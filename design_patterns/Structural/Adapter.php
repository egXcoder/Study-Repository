<?php


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