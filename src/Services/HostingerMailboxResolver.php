<?php

namespace AnjanTalukdar\ApiMail\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class HostingerMailboxResolver
{
    /**
     * Resolve the Hostinger resourceId for a given sender email address.
     *
     * @param string $fromEmail
     * @param string $apiToken
     * @param string $baseUrl
     * @param int $cacheTtl
     * @return string
     */
    public function resolveResourceId(
        string $fromEmail,
        string $apiToken,
        string $baseUrl = 'https://api.mail.hostinger.com/api/v1',
        int $cacheTtl = 3600
    ): string {
        $fromEmailLower = strtolower(trim($fromEmail));
        $cacheKey = 'hostinger_mailboxes_' . md5($apiToken);

        $mailboxes = Cache::remember($cacheKey, $cacheTtl, function () use ($apiToken, $baseUrl) {
            $response = Http::withToken($apiToken)
                ->acceptJson()
                ->get(rtrim($baseUrl, '/') . '/me');

            if ($response->failed()) {
                throw new RuntimeException("Hostinger API GET /v1/me failed with status " . $response->status() . ": " . $response->body());
            }

            $data = $response->json('data.mailboxes', []);
            $map = [];

            foreach ($data as $box) {
                if (isset($box['address'], $box['resourceId'])) {
                    $map[strtolower(trim($box['address']))] = $box['resourceId'];
                }
            }

            return $map;
        });

        if (isset($mailboxes[$fromEmailLower])) {
            return $mailboxes[$fromEmailLower];
        }

        // If exact match not found, clear cache and retry once in case a mailbox was newly added
        Cache::forget($cacheKey);

        $response = Http::withToken($apiToken)
            ->acceptJson()
            ->get(rtrim($baseUrl, '/') . '/me');

        if ($response->successful()) {
            $data = $response->json('data.mailboxes', []);
            foreach ($data as $box) {
                if (isset($box['address'], $box['resourceId']) && strtolower(trim($box['address'])) === $fromEmailLower) {
                    return $box['resourceId'];
                }
            }
        }

        // Fallback: If only one mailbox exists, use that, otherwise throw exception
        if (!empty($mailboxes)) {
            $firstResourceId = reset($mailboxes);
            return $firstResourceId;
        }

        throw new RuntimeException("No Hostinger mailbox resource ID found for sender email [{$fromEmail}].");
    }
}
