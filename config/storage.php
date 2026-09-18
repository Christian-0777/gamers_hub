<?php

declare(strict_types=1);

require_once __DIR__ . '/env.php';
require_once __DIR__ . '/urls.php';

function localStorageConfig(): array
{
    return [dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads', appUrl('uploads')];
}

function localStorageFolder(string $folder): string
{
    $folder = trim(str_replace('\\', '/', $folder), '/');
    if (!in_array($folder, ['profile', 'cover'], true)) {
        throw new InvalidArgumentException('Unsupported upload folder.');
    }

    [$uploadRoot] = localStorageConfig();
    return $uploadRoot . DIRECTORY_SEPARATOR . $folder;
}

function uploadToLocalStorage(string $localPath, string $folder, string $filename, string $mimeType): array
{
    if (!is_file($localPath)) {
        throw new RuntimeException('The compressed image is missing.');
    }

    $filename = basename($filename);
    $targetDir = localStorageFolder($folder);
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        throw new RuntimeException('Unable to create the upload folder.');
    }

    $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
    if (!copy($localPath, $targetPath)) {
        throw new RuntimeException('Unable to save the uploaded image.');
    }

    [, $uploadsUrl] = localStorageConfig();
    $publicUrl = rtrim($uploadsUrl, '/') . '/' . rawurlencode($folder) . '/' . rawurlencode($filename);

    return [
        'path' => 'uploads/' . trim($folder, '/') . '/' . $filename,
        'url' => $publicUrl,
        'size_bytes' => filesize($targetPath) ?: 0,
    ];
}

function deleteFromLocalStorageUrl(?string $url): void
{
    if (!$url) {
        return;
    }

    $path = parse_url($url, PHP_URL_PATH);
    if (!is_string($path)) {
        return;
    }

    $path = '/' . ltrim(rawurldecode($path), '/');
    $uploadsMarker = '/uploads/';
    $uploadsPosition = strpos($path, $uploadsMarker);
    if ($uploadsPosition === false) {
        return;
    }

    $relativePath = substr($path, $uploadsPosition + strlen($uploadsMarker));
    [$uploadRoot] = localStorageConfig();
    $targetPath = realpath($uploadRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath));
    $rootPath = realpath($uploadRoot);
    if ($targetPath !== false && $rootPath !== false && str_starts_with($targetPath, $rootPath . DIRECTORY_SEPARATOR) && is_file($targetPath)) {
        @unlink($targetPath);
    }
}