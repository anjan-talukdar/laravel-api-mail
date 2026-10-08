<?php

namespace AnjanTalukdar\ApiMail\Managers;

use AnjanTalukdar\ApiMail\Contracts\EmailProviderInterface;
use AnjanTalukdar\ApiMail\Messages\ApiMailMessage;
use AnjanTalukdar\ApiMail\Providers\HostingerProvider;
use AnjanTalukdar\ApiMail\Services\HostingerMailboxResolver;
use Illuminate\Support\Manager;
use InvalidArgumentException;

class EmailProviderManager extends Manager
{
    /**
     * Get the default driver name.
     */
    public function getDefaultDriver(): string
    {
        return $this->config->get('api-mail.default', 'hostinger');
    }

    /**
     * Create Hostinger Mail API driver instance.
     */
    protected function createHostingerDriver(): EmailProviderInterface
    {
        $config = $this->config->get('api-mail.providers.hostinger', []);
        $resolver = $this->container->make(HostingerMailboxResolver::class);

        return new HostingerProvider($config, $resolver);
    }

    /**
     * Send an API mail message using the designated or default provider.
     */
    public function send(ApiMailMessage $message): bool
    {
        $driverName = $message->provider ?: $this->getDefaultDriver();
        $provider = $this->driver($driverName);

        if (!$provider instanceof EmailProviderInterface) {
            throw new InvalidArgumentException("API Mail driver [{$driverName}] does not implement EmailProviderInterface.");
        }

        return $provider->send($message);
    }
}
