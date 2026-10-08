<?php

namespace AnjanTalukdar\ApiMail\Contracts;

use AnjanTalukdar\ApiMail\Messages\ApiMailMessage;

interface EmailProviderInterface
{
    /**
     * Send the given API mail message.
     *
     * @param ApiMailMessage $message
     * @return bool
     */
    public function send(ApiMailMessage $message): bool;
}
