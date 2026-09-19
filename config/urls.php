<?php

declare(strict_types=1);

function appBasePath(): string
{
    $scriptPath = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = $scriptPath !== '' ? dirname($scriptPath) : '';

    $knownAppDirectories = ['api', 'auth_page', 'authenticated_pages', 'assets', 'config', 'db', 'includes', 'uploads', 'vendor'];
    $currentSegment = basename(rtrim($basePath, '/'));

    if ($basePath !== '' && $basePath !== '/' && in_array($currentSegment, $knownAppDirectories, true)) {
        $basePath = dirname($basePath);
    }

    if ($basePath === '/' || $basePath === '.' || $basePath === '') {
        return '';
    }

    return rtrim($basePath, '/');
}

function appUrl(string $path = ''): string
{
    $path = ltrim($path, '/');

    return appBasePath() . ($path === '' ? '/' : '/' . $path);
}

function absoluteAppUrl(string $path = ''): string
{
    $forwardedProtocol = strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
    $protocol = $forwardedProtocol !== '' ? explode(',', $forwardedProtocol)[0] : ((($_SERVER['HTTPS'] ?? '') !== '' && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
    $host = (string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost');

    return $protocol . '://' . $host . appUrl($path);
}
