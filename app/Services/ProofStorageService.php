<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

/**
 * Server-side proof storage. Cloudflare R2 is never public and never local
 * disk; the API hands out short-lived signed URLs (SEC-06, MON-07).
 */
final class ProofStorageService
{
    /**
     * @return array{path: string, url: string, method: string, headers: array<string, string>, expires_at: string}
     */
    public function createUpload(User $user): array
    {
        $path = sprintf('deposits/%d/%s', $user->id, (string) Str::uuid());
        $expiresAt = now()->addMinutes(10);

        try {
            /** @var array{url?: mixed, headers?: mixed} $signed */
            $signed = Storage::disk('r2')->temporaryUploadUrl($path, $expiresAt);
        } catch (Throwable $exception) {
            Log::warning('Deposit proof upload signing failed', ['user_id' => $user->id, 'error' => $exception->getMessage()]);

            throw new RuntimeException('Proof upload is temporarily unavailable.', 0, $exception);
        }

        $headers = [];
        foreach ((array) ($signed['headers'] ?? []) as $name => $value) {
            if (is_string($value)) {
                $headers[(string) $name] = $value;
            }
        }

        return [
            'path' => $path,
            'url' => is_string($signed['url'] ?? null) ? $signed['url'] : '',
            'method' => 'PUT',
            'headers' => $headers,
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    /**
     * A short-lived signed URL for an already-uploaded proof, or null when
     * signing is unavailable (the admin UI degrades without breaking).
     */
    public function signedViewUrl(string $path): ?string
    {
        try {
            return Storage::disk('r2')->temporaryUrl($path, now()->addMinutes(10));
        } catch (Throwable $exception) {
            Log::warning('Deposit proof view signing failed', ['path' => $path, 'error' => $exception->getMessage()]);

            return null;
        }
    }
}
