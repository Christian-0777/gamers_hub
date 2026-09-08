<?php

declare(strict_types=1);

function appBasePath(): string
{
    $scriptPath = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = dirname($scriptPath);

    if ($basePath === '/' || $basePath === '.') {
        return '';
    }

    return rtrim($basePath, '/');
}

function appUrl(string $path = ''): string
{
    $path = ltrim($path, '/');

    return appBasePath() . ($path === '' ? '/' : '/' . $path);
}
