<?php

namespace App\Support;

use Framework\Http\Request;

/**
 * Simple photo upload helper
 */
class PhotoUpload
{
    private static ?string $lastError = null;

    /**
     * Upload and validate photo
     * @return string|null Returns the relative path (e.g. '/uploads/author/xxx.png') on success, or null on failure.
     */
    public static function handle(Request $request, string $uploadDir, string $filePrefix): ?string
    {
        self::$lastError = null;

        $file = $request->file('photo');

        if (!$file || !$file->isOk()) {
            self::$lastError = 'Žiadny súbor.';
            return null;
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            self::$lastError = 'Súbor je príliš veľký.';
            return null;
        }
        $info = $file->getType();
        if ($info !== 'image/png') {
            self::$lastError = 'Neplatný typ súboru; očakáva sa PNG.';
            return null;
        }

        $projectRoot = dirname(__DIR__, 2);
        $dir = $projectRoot . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $uploadDir;

        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                self::$lastError = 'Nepodarilo sa vytvoriť priečinok pre nahrávanie.';
                return null;
            }
        }

        $filename = uniqid($filePrefix . '_', true) . '.png';
        $dest = $dir . DIRECTORY_SEPARATOR . $filename;

        if (!$file->store($dest)) {
            self::$lastError = 'Nahratie zlyhalo.';
            return null;
        }

        return '/uploads/' . $uploadDir . '/' . $filename;
    }

    /**
     * Return the last error message from handle(), or null if the last call succeeded.
     */
    public static function lastError(): ?string
    {
        return self::$lastError;
    }
}
