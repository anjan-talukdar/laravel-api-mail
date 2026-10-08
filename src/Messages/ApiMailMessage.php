<?php

namespace AnjanTalukdar\ApiMail\Messages;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Notifications\Action;

class ApiMailMessage
{
    public ?string $provider = null;
    public ?string $level = 'info';
    public ?string $subject = null;
    public ?string $greeting = null;
    public ?string $salutation = null;
    public array $introLines = [];
    public array $outroLines = [];
    public ?string $actionText = null;
    public ?string $actionUrl = null;

    public ?string $fromEmail = null;
    public ?string $fromName = null;

    public array $toRecipients = [];
    public array $replyToRecipients = [];
    public array $ccRecipients = [];
    public array $bccRecipients = [];

    public ?string $customView = null;
    public bool $isMarkdown = false;
    public array $viewData = [];

    /**
     * Set the API mail provider dynamically for this message.
     */
    public function provider(string $provider): static
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * Indicate that the notification gives information about a successful operation.
     */
    public function success(): static
    {
        $this->level = 'success';
        return $this;
    }

    /**
     * Indicate that the notification gives information about an error.
     */
    public function error(): static
    {
        $this->level = 'error';
        return $this;
    }

    /**
     * Set the level of the notification.
     */
    public function level(string $level): static
    {
        $this->level = $level;
        return $this;
    }

    /**
     * Set the sender email address and name.
     */
    public function from(string $address, ?string $name = null): static
    {
        $this->fromEmail = $address;
        $this->fromName = $name;
        return $this;
    }

    /**
     * Set the recipient(s) of the notification.
     */
    public function to(string|array $address): static
    {
        $this->toRecipients = is_array($address) ? $address : [$address];
        return $this;
    }

    /**
     * Set the reply-to address.
     */
    public function replyTo(string|array $address): static
    {
        $this->replyToRecipients = is_array($address) ? $address : [$address];
        return $this;
    }

    /**
     * Set the CC address.
     */
    public function cc(string|array $address): static
    {
        $this->ccRecipients = is_array($address) ? $address : [$address];
        return $this;
    }

    /**
     * Set the BCC address.
     */
    public function bcc(string|array $address): static
    {
        $this->bccRecipients = is_array($address) ? $address : [$address];
        return $this;
    }

    /**
     * Set the subject of the notification.
     */
    public function subject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set the greeting of the notification.
     */
    public function greeting(string $greeting): static
    {
        $this->greeting = $greeting;
        return $this;
    }

    /**
     * Set the salutation of the notification.
     */
    public function salutation(string $salutation): static
    {
        $this->salutation = $salutation;
        return $this;
    }

    /**
     * Add a line of text to the notification.
     */
    public function line(mixed $line): static
    {
        return $this->with($line);
    }

    /**
     * Add a line of text if the condition is true.
     */
    public function lineIf(bool $boolean, mixed $line): static
    {
        if ($boolean) {
            return $this->line($line);
        }
        return $this;
    }

    /**
     * Add lines of text to the notification.
     */
    public function lines(iterable $lines): static
    {
        foreach ($lines as $line) {
            $this->line($line);
        }
        return $this;
    }

    /**
     * Add a line of text or action.
     */
    public function with(mixed $line): static
    {
        if ($line instanceof Action) {
            $this->action($line->text, $line->url);
        } elseif (!$this->actionText) {
            $this->introLines[] = $this->formatLine($line);
        } else {
            $this->outroLines[] = $this->formatLine($line);
        }

        return $this;
    }

    /**
     * Configure the call-to-action button.
     */
    public function action(string $text, string $url): static
    {
        $this->actionText = $text;
        $this->actionUrl = $url;
        return $this;
    }

    /**
     * Set a custom Blade view template.
     */
    public function view(string $view, array $data = []): static
    {
        $this->customView = $view;
        $this->isMarkdown = false;
        $this->viewData = $data;
        return $this;
    }

    /**
     * Set a custom Markdown view template.
     */
    public function markdown(string $view, array $data = []): static
    {
        $this->customView = $view;
        $this->isMarkdown = true;
        $this->viewData = $data;
        return $this;
    }

    /**
     * Format the given line of text.
     */
    protected function formatLine(mixed $line): string|Htmlable
    {
        if ($line instanceof Htmlable) {
            return $line;
        }

        if (is_array($line)) {
            $line = implode(' ', array_map('trim', $line));
        }

        $formatted = trim(implode(' ', array_map('trim', preg_split('/\r\n|\r|\n/', (string) ($line ?? '')))));

        return \Illuminate\Support\Str::inlineMarkdown($formatted);
    }

    /**
     * Render HTML output.
     */
    public function renderHtml(): string
    {
        if ($this->customView) {
            return view($this->customView, array_merge(['message' => $this], $this->viewData))->render();
        }

        return view('api-mail::default-theme', ['message' => $this])->render();
    }

    /**
     * Render Plain Text output.
     */
    public function renderPlainText(): string
    {
        $lines = [];

        if ($this->subject) {
            $lines[] = "SUBJECT: " . $this->subject;
            $lines[] = "";
        }

        if ($this->greeting) {
            $lines[] = $this->greeting;
            $lines[] = "";
        }

        foreach ($this->introLines as $line) {
            $lines[] = strip_tags((string) $line);
        }

        if ($this->actionText && $this->actionUrl) {
            $lines[] = "";
            $lines[] = "{$this->actionText}: {$this->actionUrl}";
            $lines[] = "";
        }

        foreach ($this->outroLines as $line) {
            $lines[] = strip_tags((string) $line);
        }

        if ($this->salutation) {
            $lines[] = "";
            $lines[] = strip_tags((string) $this->salutation);
        }

        return implode("\n", $lines);
    }
}
