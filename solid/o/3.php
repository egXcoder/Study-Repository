<?php

// 3. Notifications (Email, SMS, Slack, …)

// Laravel itself applies OCP here.

// Instead of:
class ReportNotifier
{
    public function notify(User $user, string $message)
    {
        if ($user->channel === 'email') {
            Mail::to($user->email)->send(new GenericMail($message));
        } elseif ($user->channel === 'sms') {
            Sms::send($user->phone, $message);
        }
    }
}


// You can rely on Laravel Notifications:
// 👉 Tomorrow, you add WhatsAppChannel or PushNotificationChannel.
// You don’t modify ReportReadyNotification itself — Laravel just lets you extend channels.

class ReportReadyNotification extends Notification
{
    public function via($notifiable)
    {
        return ['mail', 'sms', 'slack']; // extendable
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Report Ready')
            ->line('Your report is ready to download.');
    }

    public function toSms($notifiable)
    {
        return "Your report is ready!";
    }

    public function toSlack($notifiable)
    {
        return (new SlackMessage)->content('Report is ready!');
    }
}