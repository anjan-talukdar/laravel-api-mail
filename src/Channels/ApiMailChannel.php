<?php

namespace AnjanTalukdar\ApiMail\Channels;

use AnjanTalukdar\ApiMail\Managers\EmailProviderManager;
use AnjanTalukdar\ApiMail\Messages\ApiMailMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use RuntimeException;

class ApiMailChannel
{
    public function __construct(protected EmailProviderManager $manager) {}

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     * @return bool|null
     */
    public function send(mixed $notifiable, Notification $notification): ?bool
    {
        $message = null;

        if (method_exists($notification, 'toApiMail')) {
            $message = $notification->toApiMail($notifiable);
        } elseif (method_exists($notification, 'toMail')) {
            $mailMessage = $notification->toMail($notifiable);

            if ($mailMessage instanceof MailMessage) {
                $message = $this->convertMailMessageToApiMailMessage($mailMessage);
            } elseif ($mailMessage instanceof ApiMailMessage) {
                $message = $mailMessage;
            }
        }

        if (!$message instanceof ApiMailMessage) {
            return null;
        }

        // Auto-populate recipient email from notifiable if not explicitly set
        if (empty($message->toRecipients)) {
            $toEmail = $notifiable->routeNotificationFor('api-mail', $notification)
                ?? $notifiable->routeNotificationFor('mail', $notification)
                ?? $notifiable->email
                ?? null;

            if (empty($toEmail)) {
                throw new RuntimeException("Could not determine recipient email address for notification [" . get_class($notification) . "].");
            }

            $message->to($toEmail);
        }

        return $this->manager->send($message);
    }

    /**
     * Convert standard Laravel MailMessage into ApiMailMessage.
     */
    protected function convertMailMessageToApiMailMessage(MailMessage $mailMessage): ApiMailMessage
    {
        $apiMessage = new ApiMailMessage();

        if ($mailMessage->subject) {
            $apiMessage->subject($mailMessage->subject);
        }

        if ($mailMessage->greeting) {
            $apiMessage->greeting($mailMessage->greeting);
        }

        if ($mailMessage->salutation) {
            $apiMessage->salutation($mailMessage->salutation);
        }

        if (!empty($mailMessage->from)) {
            $apiMessage->from($mailMessage->from[0], $mailMessage->from[1] ?? null);
        }

        if (!empty($mailMessage->introLines)) {
            foreach ($mailMessage->introLines as $line) {
                $apiMessage->line($line);
            }
        }

        if ($mailMessage->actionText && $mailMessage->actionUrl) {
            $apiMessage->action($mailMessage->actionText, $mailMessage->actionUrl);
        }

        if (!empty($mailMessage->outroLines)) {
            foreach ($mailMessage->outroLines as $line) {
                $apiMessage->line($line);
            }
        }

        if ($mailMessage->level) {
            $apiMessage->level($mailMessage->level);
        }

        if ($mailMessage->view) {
            $apiMessage->view($mailMessage->view, $mailMessage->viewData);
        }

        if ($mailMessage->mailer) {
            $apiMessage->provider($mailMessage->mailer);
        }

        return $apiMessage;
    }
}
