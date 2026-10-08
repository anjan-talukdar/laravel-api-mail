<?php

namespace AnjanTalukdar\ApiMail\Providers;

use AnjanTalukdar\ApiMail\Contracts\EmailProviderInterface;
use AnjanTalukdar\ApiMail\Messages\ApiMailMessage;

abstract class AbstractProvider implements EmailProviderInterface
{
    public function __construct(protected array $config) {}

    /**
     * Get configured global or message specific 'from' address.
     */
    protected function resolveFromAddress(ApiMailMessage $message): string
    {
        return $message->fromEmail
            ?? $this->config['from']['address']
            ?? config('api-mail.from.address')
            ?? config('mail.from.address');
    }

    /**
     * Get configured global or message specific 'from' name.
     */
    protected function resolveFromName(ApiMailMessage $message): string
    {
        return $message->fromName
            ?? $this->config['from']['name']
            ?? config('api-mail.from.name')
            ?? config('mail.from.name');
    }
}
