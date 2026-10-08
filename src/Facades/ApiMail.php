<?php

namespace AnjanTalukdar\ApiMail\Facades;

use AnjanTalukdar\ApiMail\Managers\EmailProviderManager;
use AnjanTalukdar\ApiMail\Messages\ApiMailMessage;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool send(ApiMailMessage $message)
 * @method static \AnjanTalukdar\ApiMail\Contracts\EmailProviderInterface driver(string|null $driver = null)
 * 
 * @see EmailProviderManager
 */
class ApiMail extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'api-mail';
    }
}
