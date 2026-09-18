<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/storage.php';

$database = db();
$migrations = [
    'profile' => __DIR__ . '/../uploads/profile',
    'cover' => __DIR__ . '/../uploads/cover',
];

foreach ($migrations as $folder => $directory) {
    foreach (glob($directory . '/*') ?: [] as $localPath) {
        if (!is_file($localPath)) {
            continue;
        }

        $filename = basename($localPath);
        if (!preg_match('/^' . preg_quote($folder, '/') . '_(\d+)_/i', $filename, $matches)) {
            fwrite(STDERR, "Skipping unrecognized file: {$filename}\n");
            continue;
        }

        $userId = (int) $matches[1];
        $mimeType = mime_content_type($localPath) ?: 'application/octet-stream';
        $result = uploadToLocalStorage($localPath, $folder, $filename, $mimeType);

        $column = $folder === 'profile' ? 'avatar_url' : 'cover_url';
        $statement = $database->prepare("UPDATE user_profiles SET {$column} = :url WHERE user_id = :user_id");
        $statement->execute(['url' => $result['url'], 'user_id' => $userId]);

        echo "Migrated {$filename} for user {$userId}\n";
    }
}