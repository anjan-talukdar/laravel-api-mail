<?php

namespace AnjanTalukdar\ApiMail\Providers;

use AnjanTalukdar\ApiMail\Messages\ApiMailMessage;
use AnjanTalukdar\ApiMail\Services\HostingerMailboxResolver;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class HostingerProvider extends AbstractProvider
{
    public function __construct(
        array $config,
        protected HostingerMailboxResolver $resolver
    ) {
        parent::__construct($config);
    }

    public function send(ApiMailMessage $message): bool
    {
        $apiToken = $this->config['token'] ?? '';
        $baseUrl = $this->config['base_url'] ?? 'https://api.mail.hostinger.com/api/v1';
        $cacheTtl = (int) ($this->config['cache_ttl'] ?? 3600);

        if (empty($apiToken)) {
            throw new RuntimeException("Hostinger Mail API token is not configured (HOSTINGER_MAIL_API_TOKEN).");
        }

        $fromAddress = $this->resolveFromAddress($message);
        $fromName = $this->resolveFromName($message);

        // Auto-resolve resourceId for the sender email address
        $resourceId = $this->resolver->resolveResourceId($fromAddress, $apiToken, $baseUrl, $cacheTtl);

        $toRecipients = $message->toRecipients;
        if (empty($toRecipients)) {
            throw new RuntimeException("ApiMailMessage requires at least one 'to' recipient.");
        }

        $replyTo = !empty($message->replyToRecipients) ? $message->replyToRecipients : [$fromAddress];

        $payload = [
            'displayName' => $fromName,
            'to' => array_values($toRecipients),
            'replyTo' => array_values($replyTo),
            'subject' => $message->subject ?? 'Notification',
            'text' => $message->renderPlainText(),
            'html' => $message->renderHtml(),
        ];

        $endpointUrl = rtrim($baseUrl, '/') . "/mailboxes/{$resourceId}/send";

        $response = Http::withToken($apiToken)
            ->acceptJson()
            ->post($endpointUrl, $payload);

        if ($response->failed()) {
            throw new RuntimeException("Hostinger Mail API send failed: [" . $response->status() . "] " . $response->body());
        }

        return true;
    }
}
