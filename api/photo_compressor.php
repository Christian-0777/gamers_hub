<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/urls.php';

function imageProcessorAvailable(): bool
{
    return class_exists('Imagick')
        || function_exists('imagecreatefromjpeg')
        || function_exists('imagecreatefrompng')
        || function_exists('imagecreatefromwebp')
        || function_exists('imagecreatefromgif');
}

function ensureUploadDirectory(string $relativePath): string
{
    $baseDir = __DIR__ . '/../' . ltrim($relativePath, '/');
    $directory = dirname($baseDir);

    if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
        throw new RuntimeException('Unable to create upload directory: ' . $directory);
    }

    return $baseDir;
}

function generateUploadFilename(string $prefix, string $extension): string
{
    $safeExtension = strtolower(ltrim($extension, '.'));
    return sprintf('%s_%s.%s', $prefix, bin2hex(random_bytes(8)), $safeExtension);
}

function normalizeImageSource(string $source)
{
    if (!imageProcessorAvailable()) {
        throw new RuntimeException('Image processing support is not available in this PHP installation. Enable the GD or Imagick extension.');
    }

    $imageInfo = @getimagesize($source);
    if ($imageInfo === false) {
        throw new InvalidArgumentException('The uploaded file is not a valid image.');
    }

    $mime = $imageInfo['mime'] ?? '';

    if (class_exists('Imagick')) {
        try {
            $image = new Imagick($source);
            return $image;
        } catch (Throwable $exception) {
            // Fall back to GD if available.
        }
    }

    switch ($mime) {
        case 'image/jpeg':
            if (!function_exists('imagecreatefromjpeg')) {
                throw new RuntimeException('The GD JPEG support is not enabled in this PHP environment.');
            }
            $image = @imagecreatefromjpeg($source);
            break;
        case 'image/png':
            if (!function_exists('imagecreatefrompng')) {
                throw new RuntimeException('The GD PNG support is not enabled in this PHP environment.');
            }
            $image = @imagecreatefrompng($source);
            break;
        case 'image/webp':
            if (!function_exists('imagecreatefromwebp')) {
                throw new RuntimeException('The GD WEBP support is not enabled in this PHP environment.');
            }
            $image = @imagecreatefromwebp($source);
            break;
        case 'image/gif':
            if (!function_exists('imagecreatefromgif')) {
                throw new RuntimeException('The GD GIF support is not enabled in this PHP environment.');
            }
            $image = @imagecreatefromgif($source);
            break;
        default:
            throw new InvalidArgumentException('Unsupported image type. Please upload a JPG, PNG, WEBP, or GIF image.');
    }

    if (!$image instanceof GdImage && !$image instanceof Imagick) {
        throw new RuntimeException('The image could not be processed.');
    }

    return $image;
}

function saveCompressedImage($image, string $targetPath, string $mimeType): void
{
    if ($image instanceof Imagick) {
        $format = match ($mimeType) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpeg',
        };

        $image->setImageFormat($format);
        $image->stripImage();
        $image->writeImage($targetPath);
        return;
    }

    $quality = 90;
    $saved = false;

    do {
        if ($mimeType === 'image/png') {
            if (!function_exists('imagepng')) {
                throw new RuntimeException('PNG saving is not available in this PHP environment.');
            }
            $saved = imagepng($image, $targetPath, max(0, min(9, 9 - floor((90 - $quality) / 10))));
        } elseif ($mimeType === 'image/webp') {
            if (!function_exists('imagewebp')) {
                throw new RuntimeException('WEBP saving is not available in this PHP environment.');
            }
            $saved = imagewebp($image, $targetPath, $quality);
        } elseif ($mimeType === 'image/gif') {
            if (!function_exists('imagegif')) {
                throw new RuntimeException('GIF saving is not available in this PHP environment.');
            }
            $saved = imagegif($image, $targetPath);
        } else {
            if (!function_exists('imagejpeg')) {
                throw new RuntimeException('JPEG saving is not available in this PHP environment.');
            }
            $saved = imagejpeg($image, $targetPath, $quality);
        }

        if ($saved) {
            break;
        }

        $quality -= 10;
    } while ($quality >= 10);

    if (!$saved) {
        throw new RuntimeException('The image could not be saved after compression.');
    }
}

function compressUploadedImage(array $file, string $storageDir, string $prefix): array
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }

    if (!is_uploaded_file($file['tmp_name'] ?? '')) {
        throw new RuntimeException('Uploaded image is invalid.');
    }

    $sourceSize = (int) ($file['size'] ?? 0);
    if ($sourceSize <= 0) {
        throw new RuntimeException('The uploaded image is empty.');
    }

    $fileExt = strtolower(pathinfo((string) ($file['name'] ?? 'image.jpg'), PATHINFO_EXTENSION));
    $mimeType = mime_content_type($file['tmp_name']);
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    if (!in_array($mimeType, $allowedMimeTypes, true)) {
        throw new InvalidArgumentException('Unsupported image type. Please upload a JPG, PNG, WEBP, or GIF image.');
    }

    $relativeDir = ltrim($storageDir, '/');
    $targetDir = __DIR__ . '/../' . $relativeDir;
    if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        throw new RuntimeException('Unable to create image storage folder.');
    }

    if ($sourceSize <= 5 * 1024 * 1024) {
        $targetPath = $targetDir . '/' . generateUploadFilename($prefix, $mimeType === 'image/png' ? 'png' : ($mimeType === 'image/webp' ? 'webp' : ($mimeType === 'image/gif' ? 'gif' : 'jpg')));
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            if (!copy($file['tmp_name'], $targetPath)) {
                throw new RuntimeException('Unable to save the uploaded image.');
            }
        }

        $normalizedTargetPath = str_replace('\\', '/', $targetPath);
        $rootPath = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: (__DIR__ . '/..'));
        $relativeUrl = preg_replace('#^' . preg_quote($rootPath, '#') . '/?#', '', $normalizedTargetPath, 1);
        $relativeUrl = ltrim((string) $relativeUrl, '/');

        return [
            'path' => $relativeUrl,
            'url' => appUrl($relativeUrl),
            'size_bytes' => filesize($targetPath) ?: $sourceSize,
        ];
    }

    $sourceImage = normalizeImageSource($file['tmp_name']);
    $outputExtension = $mimeType === 'image/png' ? 'png' : ($mimeType === 'image/webp' ? 'webp' : ($mimeType === 'image/gif' ? 'gif' : 'jpg'));
    $targetPath = $targetDir . '/' . generateUploadFilename($prefix, $outputExtension);

    $currentImage = $sourceImage;
    $currentPath = $targetPath;
    $quality = 90;
    $maxBytes = 5 * 1024 * 1024;
    $minBytes = 3 * 1024 * 1024;

    if ($currentImage instanceof Imagick) {
        $currentImage->setImageCompressionQuality($quality);
        $currentImage->stripImage();
        $sourceWidth = $currentImage->getImageWidth();
        $sourceHeight = $currentImage->getImageHeight();

        for ($attempt = 0; $attempt < 12; $attempt++) {
            $currentPath = $targetDir . '/' . generateUploadFilename($prefix, $outputExtension);
            $currentImage->setImageFormat($outputExtension);
            $currentImage->setImageCompressionQuality($quality);
            $currentImage->writeImage($currentPath);

            $fileSize = filesize($currentPath);
            if ($fileSize === false) {
                throw new RuntimeException('Could not determine the compressed image size.');
            }

            if ($fileSize <= $maxBytes && $fileSize >= $minBytes) {
                break;
            }

            if ($fileSize > $maxBytes) {
                $quality = max(20, $quality - 10);
                $scale = sqrt($maxBytes / $fileSize);
                $newWidth = max(1, (int) round($sourceWidth * $scale));
                $newHeight = max(1, (int) round($sourceHeight * $scale));
                $currentImage->resizeImage($newWidth, $newHeight, Imagick::FILTER_LANCZOS, 1);
                $sourceWidth = $newWidth;
                $sourceHeight = $newHeight;
                continue;
            }

            if ($fileSize < $minBytes && $quality < 95) {
                $quality = min(95, $quality + 5);
            }

            if ($fileSize < $minBytes && $quality >= 95) {
                break;
            }
        }
    } else {
        $saveCurrent = function ($image, string $path, string $mime) use (&$quality): void {
            if ($mime === 'image/png') {
                if (!function_exists('imagepng')) {
                    throw new RuntimeException('PNG saving is not available in this PHP environment.');
                }
                imagepng($image, $path, max(0, min(9, 9 - floor((90 - $quality) / 10))));
                return;
            }

            if ($mime === 'image/webp') {
                if (!function_exists('imagewebp')) {
                    throw new RuntimeException('WEBP saving is not available in this PHP environment.');
                }
                imagewebp($image, $path, $quality);
                return;
            }

            if ($mime === 'image/gif') {
                if (!function_exists('imagegif')) {
                    throw new RuntimeException('GIF saving is not available in this PHP environment.');
                }
                imagegif($image, $path);
                return;
            }

            if (!function_exists('imagejpeg')) {
                throw new RuntimeException('JPEG saving is not available in this PHP environment.');
            }
            imagejpeg($image, $path, $quality);
        };

        $resizeToTarget = function ($image, int $targetWidth, int $targetHeight) {
            $resized = imagecreatetruecolor($targetWidth, $targetHeight);
            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefill($resized, 0, 0, $transparent);
            imagealphablending($resized, true);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, imagesx($image), imagesy($image));
            return $resized;
        };

        $sourceWidth = imagesx($currentImage);
        $sourceHeight = imagesy($currentImage);

        for ($attempt = 0; $attempt < 12; $attempt++) {
            $currentPath = $targetDir . '/' . generateUploadFilename($prefix, $outputExtension);
            $saveCurrent($currentImage, $currentPath, $mimeType);

            $fileSize = filesize($currentPath);
            if ($fileSize === false) {
                throw new RuntimeException('Could not determine the compressed image size.');
            }

            if ($fileSize <= $maxBytes && $fileSize >= $minBytes) {
                break;
            }

            if ($fileSize > $maxBytes) {
                $quality = max(20, $quality - 10);
                $scale = sqrt($maxBytes / $fileSize);
                $newWidth = max(1, (int) round($sourceWidth * $scale));
                $newHeight = max(1, (int) round($sourceHeight * $scale));
                $currentImage = $resizeToTarget($currentImage, $newWidth, $newHeight);
                $sourceWidth = $newWidth;
                $sourceHeight = $newHeight;
                continue;
            }

            if ($fileSize < $minBytes && $quality < 95) {
                $quality = min(95, $quality + 5);
            }

            if ($fileSize < $minBytes && $quality >= 95) {
                break;
            }
        }
    }

    $finalPath = $currentPath;
    if (!file_exists($finalPath)) {
        throw new RuntimeException('Compressed image was not created.');
    }

    $finalSize = filesize($finalPath);
    if ($finalSize === false) {
        throw new RuntimeException('Unable to confirm compressed file size.');
    }

    if ($finalSize > $maxBytes) {
        throw new RuntimeException('The uploaded image could not be compressed below the 5MB limit.');
    }

    $rootPath = str_replace('\\', '/', realpath(__DIR__ . '/..') ?: (__DIR__ . '/..'));
    $normalizedFinalPath = str_replace('\\', '/', $finalPath);
    $relativeUrl = preg_replace('#^' . preg_quote($rootPath, '#') . '/?#', '', $normalizedFinalPath, 1);
    $relativeUrl = ltrim((string) $relativeUrl, '/');

    return [
        'path' => $relativeUrl,
        'url' => appUrl($relativeUrl),
        'size_bytes' => $finalSize,
    ];
}
