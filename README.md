# Laravel API Mail

[![Latest Version on Packagist](https://img.shields.io/packagist/v/anjan-talukdar/laravel-api-mail.svg?style=flat-square)](https://packagist.org/packages/anjan-talukdar/laravel-api-mail)
[![Total Downloads](https://img.shields.io/packagist/dt/anjan-talukdar/laravel-api-mail.svg?style=flat-square)](https://packagist.org/packages/anjan-talukdar/laravel-api-mail)
[![License](https://img.shields.io/packagist/l/anjan-talukdar/laravel-api-mail.svg?style=flat-square)](LICENSE.md)

A modern, extensible multi-provider HTTP Email API driver and notification package for **Laravel 10, 11, 12, and 13**. 

Designed to replace legacy SMTP with provider-native HTTP APIs, featuring out-of-the-box integration with the **Hostinger Mail API** (with automated mailbox `resourceId` lookup), fluent method-chained message composition, inline Markdown rendering, and a clean grayscale HTML email theme.

---

## Features

- ⚡ **Hostinger Mail API Integration**: Automatic fetching and caching of mailbox `resourceId` from Hostinger's `GET /api/v1/me` endpoint matched by sender (`from`) email address.
- 🔀 **Multi-Provider Architecture**: Pluggable driver manager (`EmailProviderManager`) allowing developers to easily switch providers via config or specify distinct providers per notification message.
- 🎨 **Fluent Message Builder (`ApiMailMessage`)**: Chainable syntax inspired by Laravel's `SimpleMessage` (`->subject()`, `->greeting()`, `->line()`, `->action()`, `->from()`, `->to()`, `->provider()`).
- 📝 **Inline Markdown Support**: Automatically compiles Markdown inline formatting (`**bold**`, `*italic*`, links) inside notification lines into clean HTML.
- 🖼️ **Custom Blade & Markdown Views**: Pass custom templates using `->view('emails.custom', $data)` or `->markdown('emails.custom', $data)` when needed.
- 🖤 **Grayscale HTML Theme**: Included out-of-the-box with a clean, minimal black-and-white responsive HTML template matching standard Laravel notification aesthetics.
- 🔔 **Notification Channel & Mailer Transport**: Works via dedicated `ApiMailChannel` (`toApiMail()`) or as a registered Symfony Mailer transport driver (`hostinger`).

---

## Requirements

- **PHP**: `^8.2 | ^8.3 | ^8.4`
- **Laravel**: `^10.0 | ^11.0 | ^12.0 | ^13.0`

---

## Installation

Install the package via Composer:

```bash
composer require anjan-talukdar/laravel-api-mail
```

*Note: The service provider and `ApiMail` facade will be automatically registered using Laravel Package Auto-Discovery.*

### Publishing Configuration

Publish the package configuration file to `config/api-mail.php`:

```bash
php artisan vendor:publish --tag=api-mail-config
```

---

## Environment Configuration

Add the following environment variables to your `.env` file:

```env
# Default Provider (e.g., hostinger)
API_MAIL_PROVIDER=hostinger

# Hostinger Mail API Credentials
HOSTINGER_MAIL_API_TOKEN=your_hostinger_bearer_token_here
HOSTINGER_MAIL_API_URL=https://api.mail.hostinger.com/api/v1
HOSTINGER_MAIL_CACHE_TTL=3600

# Global Sender Configuration
MAIL_FROM_ADDRESS="info@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## Usage Guide

### 1. Sending via Laravel Notifications

Use `ApiMailChannel` and return an `ApiMailMessage` in your notification's `toApiMail()` method:

```php
namespace App\Notifications;

use AnjanTalukdar\ApiMail\Channels\ApiMailChannel;
use AnjanTalukdar\ApiMail\Messages\ApiMailMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class AccessRequestStatusNotification extends Notification
{
    use Queueable;

    public function __construct(public $accessRequest) {}

    public function via(object $notifiable): array
    {
        return ['database', ApiMailChannel::class];
    }

    public function toApiMail(object $notifiable): ApiMailMessage
    {
        $statusText = ucfirst($this->accessRequest->status);
        $isApproved = $this->accessRequest->status === 'approved';

        return (new ApiMailMessage)
            ->provider('hostinger') // Optional: defaults to config('api-mail.default')
            ->from('info@yourdomain.com', 'Your App')
            ->to($notifiable->email)
            ->subject('Your Request to Join ' . $this->accessRequest->space->name . ' was ' . $statusText)
            ->greeting('Hello ' . ($notifiable->name ?? 'User') . ',')
            ->line("Your request to join the **{$this->accessRequest->space->name}** space has been **{$this->accessRequest->status}**.")
            ->action('Go to Dashboard', url('/app'))
            ->level($isApproved ? 'success' : 'error');
    }
}
```

---

### 2. Direct Dispatching via Facade

Send emails dynamically using the `ApiMail` facade:

```php
use AnjanTalukdar\ApiMail\Facades\ApiMail;
use AnjanTalukdar\ApiMail\Messages\ApiMailMessage;

$message = (new ApiMailMessage)
    ->from('info@yourdomain.com', 'Support Team')
    ->to('user@example.com')
    ->subject('Welcome to Our Platform')
    ->greeting('Hello John!')
    ->line('Thank you for joining our platform.')
    ->action('Get Started', 'https://yourdomain.com/app');

ApiMail::send($message);
```

---

### 3. Dynamic Multi-Provider Routing

You can route different notifications or messages to distinct email providers:

```php
// Send transactional email via Hostinger
$transactionalMsg = (new ApiMailMessage)
    ->provider('hostinger')
    ->to('user@example.com')
    ->subject('Invoice #1004');

// Send marketing broadcast via Resend (or other driver)
$marketingMsg = (new ApiMailMessage)
    ->provider('resend')
    ->to('user@example.com')
    ->subject('New Features Released!');
```

---

### 4. Custom Blade Views

Render custom Blade templates instead of the built-in HTML theme when required:

```php
return (new ApiMailMessage)
    ->to($notifiable->email)
    ->subject('Custom Card Notification')
    ->view('emails.custom-welcome', [
        'user' => $notifiable,
    ]);
```

---

## Hostinger Mailbox Auto-Resolution Architecture

Hostinger's Mail API requires sending emails via a specific mailbox `resourceId` endpoint:
`POST /api/v1/mailboxes/{resourceId}/send`

This package automates resource ID lookup seamlessly:
1. `HostingerMailboxResolver` issues a `GET /api/v1/me` request to retrieve account mailboxes.
2. It maps mailbox email addresses (e.g., `info@yourdomain.com` $\to$ `ACd58567a68c96e2b8c6624e02cbbd`).
3. Results are cached locally (default: 1 hour) so subsequent email sends incur zero extra API overhead.
4. If a sender address is newly created, it automatically invalidates cache and retries lookup seamlessly.

---

## Extending with Custom Drivers

Register custom API email drivers at runtime via the facade:

```php
use AnjanTalukdar\ApiMail\Facades\ApiMail;

ApiMail::extend('custom_driver', function ($app, $config) {
    return new CustomEmailProvider($config);
});
```

---

## Testing

Run tests using PHPUnit / Pest:

```bash
composer test
```

Or within a host Laravel application:

```bash
php artisan test --filter=ApiMailPackageTest
```

---

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

---

## Security Vulnerabilities

If you discover any security-related issues, please email `developer.anjantalukdar@gmail.com` instead of using the issue tracker.

---

## Credits

- [Anjan Talukdar](https://github.com/anjan-talukdar)
- [All Contributors](../../contributors)

---

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
