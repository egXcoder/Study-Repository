<?php

interface NotificationChannel
{
    public function send(string $message, User $user): void;
}

class EmailChannel implements NotificationChannel
{
    public function send(string $message, User $user): void
    {
        Mail::to($user->email)->send(new GenericNotification($message));
    }
}


// Now imagine we implement SmsChannel but make it throw an exception if the user has no phone:
// 🚨 That breaks LSP — because consumers expect send() to always deliver a message. Throwing for a missing phone changes the contract.
class SmsChannel implements NotificationChannel
{
    public function send(string $message, User $user): void
    {
        if (!$user->phone) {
            throw new Exception("User has no phone number!");
        }
    }
}



// ✅ A better approach:
// You’d ensure your contract allows for “optional delivery”, maybe by returning a boolean instead of void:
// Now both EmailChannel and SmsChannel can be safely substituted — the contract is respected.
interface NotificationChannel
{
    public function send(string $message, User $user): bool; // success/failure
}


