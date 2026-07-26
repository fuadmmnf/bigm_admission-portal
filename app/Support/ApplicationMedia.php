<?php

namespace App\Support;

use App\Models\Application;
use Illuminate\Support\Facades\Storage;

class ApplicationMedia
{
    public static function hydrateCvMedia(Application $application): Application
    {
        $uploads = data_get($application->additional_info, 'uploads', []);

        $application->setAttribute(
            'photo_data_uri',
            self::fileToDataUri(data_get($uploads, 'applicant_photo'))
        );

        $application->setAttribute(
            'signature_data_uri',
            self::fileToDataUri(data_get($uploads, 'signature'))
        );

        return $application;
    }

    public static function fileToDataUri(?string $path): ?string
    {
        $normalized = self::normalizePublicPath($path);

        if ($normalized === null || ! Storage::disk('public')->exists($normalized)) {
            return null;
        }

        $contents = Storage::disk('public')->get($normalized);
        $extension = strtolower(pathinfo($normalized, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    public static function normalizePublicPath(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $normalized = ltrim((string) $path, '/');

        $publicStoragePrefix = trim((string) config('filesystems.disks.public.url', ''), '/').'/';
        if ($publicStoragePrefix !== '' && str_starts_with($normalized, $publicStoragePrefix)) {
            $normalized = substr($normalized, strlen($publicStoragePrefix));
        }

        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, 7);
        }

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, 8);
        }

        return $normalized;
    }
}

