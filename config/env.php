<?php

date_default_timezone_set('UTC');

function env(string $key, ?string $default = null): ?string
{
	$value = getenv($key);

	if ($value !== false && $value !== '') {
		return $value;
	}

	static $fileValues;

	if ($fileValues === null) {
		$fileValues = [];
		$envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';

		if (is_readable($envFile)) {
			foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
				$line = trim($line);

				if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
					continue;
				}

				[$name, $setting] = explode('=', $line, 2);
				$fileValues[trim($name)] = trim($setting, " \t\n\r\0\x0B\"");
			}
		}
	}

	return $fileValues[$key] ?? $default;
}
