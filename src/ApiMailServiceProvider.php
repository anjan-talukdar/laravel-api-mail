<?php

namespace AnjanTalukdar\ApiMail;

use AnjanTalukdar\ApiMail\Channels\ApiMailChannel;
use AnjanTalukdar\ApiMail\Managers\EmailProviderManager;
use AnjanTalukdar\ApiMail\Providers\HostingerProvider;
use AnjanTalukdar\ApiMail\Services\HostingerMailboxResolver;
use AnjanTalukdar\ApiMail\Transports\HostingerMailTransport;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\ServiceProvider;

class ApiMailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/api-mail.php', 'api-mail');

        $this->app->singleton(HostingerMailboxResolver::class, function () {
            return new HostingerMailboxResolver();
        });

        $this->app->singleton('api-mail', function ($app) {
            return new EmailProviderManager($app);
        });

        $this->app->alias('api-mail', EmailProviderManager::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load views from package
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'api-mail');

        // Publish configuration and views
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/api-mail.php' => config_path('api-mail.php'),
            ], 'api-mail-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/api-mail'),
            ], 'api-mail-views');
        }

        // Register custom Laravel Mailer transport driver ('hostinger')
        Mail::extend('hostinger', function (array $config = []) {
            $fullConfig = array_merge(config('api-mail.providers.hostinger', []), $config);
            $resolver = $this->app->make(HostingerMailboxResolver::class);
            $provider = new HostingerProvider($fullConfig, $resolver);

            return new HostingerMailTransport($provider);
        });

        // Register custom Notification Channel ('api-mail')
        Notification::extend('api-mail', function ($app) {
            return $app->make(ApiMailChannel::class);
        });
    }
}
