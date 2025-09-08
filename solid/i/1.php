<?php

// ❌ Bad Example (fat interface)
interface NotificationInterface {
    public function sendEmail(string $to, string $message);
    public function sendSms(string $to, string $message);
    public function sendPush(string $to, string $message);
}

// Now if you implement SmsNotification, you’re forced to implement sendEmail() and sendPush(), even though they don’t make sense:
// This violates ISP → the class depends on methods it doesn’t use.
class SmsNotification implements NotificationInterface {
    public function sendEmail(string $to, string $message) {
        throw new \Exception("Not supported");
    }

    public function sendSms(string $to, string $message) {
        // SMS logic
    }

    public function sendPush(string $to, string $message) {
        throw new \Exception("Not supported");
    }
}



// ✅ Good Example (segregated interfaces)
interface EmailNotificationInterface {
    public function sendEmail(string $to, string $message);
}

interface SmsNotificationInterface {
    public function sendSms(string $to, string $message);
}

interface PushNotificationInterface {
    public function sendPush(string $to, string $message);
}


// Now each class only implements what it actually needs:
class SmsNotification implements SmsNotificationInterface {
    public function sendSms(string $to, string $message) {
        // Send SMS using Twilio or Nexmo
    }
}

class EmailNotification implements EmailNotificationInterface {
    public function sendEmail(string $to, string $message) {
        // Send email using Laravel Mail
        Mail::to($to)->send(new GenericMail($message));
    }
}