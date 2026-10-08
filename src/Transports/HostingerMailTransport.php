<?php

namespace AnjanTalukdar\ApiMail\Transports;

use AnjanTalukdar\ApiMail\Messages\ApiMailMessage;
use AnjanTalukdar\ApiMail\Providers\HostingerProvider;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

class HostingerMailTransport extends AbstractTransport
{
    public function __construct(protected HostingerProvider $provider)
    {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();

        if (!$email instanceof Email) {
            return;
        }

        $apiMessage = new ApiMailMessage();
        $apiMessage->subject($email->getSubject() ?? '');

        // From
        $fromAddresses = $email->getFrom();
        if (!empty($fromAddresses)) {
            /** @var Address $firstFrom */
            $firstFrom = $fromAddresses[0];
            $apiMessage->from($firstFrom->getAddress(), $firstFrom->getName() ?: null);
        }

        // To
        $toAddresses = array_map(fn (Address $addr) => $addr->getAddress(), $email->getTo());
        if (!empty($toAddresses)) {
            $apiMessage->to($toAddresses);
        }

        // Reply To
        $replyTo = array_map(fn (Address $addr) => $addr->getAddress(), $email->getReplyTo());
        if (!empty($replyTo)) {
            $apiMessage->replyTo($replyTo);
        }

        // Content
        $htmlBody = $email->getHtmlBody();
        $textBody = $email->getTextBody();

        if ($htmlBody) {
            $apiMessage->line(is_resource($htmlBody) ? stream_get_contents($htmlBody) : $htmlBody);
        } elseif ($textBody) {
            $apiMessage->line(is_resource($textBody) ? stream_get_contents($textBody) : $textBody);
        }

        $this->provider->send($apiMessage);
    }

    public function __toString(): string
    {
        return 'hostinger';
    }
}
