<?php

if (!function_exists('asmara_nairobi_timezone')) {
    function asmara_nairobi_timezone() {
        return new DateTimeZone('Africa/Nairobi');
    }
}

if (!function_exists('asmara_event_date_string')) {
    function asmara_event_date_string(array $event) {
        $value = $event['event_date'] ?? ($event['date'] ?? '');
        return trim((string) $value);
    }
}

if (!function_exists('asmara_event_date_object')) {
    function asmara_event_date_object(array $event) {
        $dateString = asmara_event_date_string($event);
        if ($dateString === '') {
            return null;
        }

        $timezone = asmara_nairobi_timezone();
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $dateString, $timezone);
        if ($date instanceof DateTimeImmutable) {
            return $date->setTime(0, 0, 0);
        }

        $timestamp = strtotime($dateString);
        if ($timestamp === false) {
            return null;
        }

        return (new DateTimeImmutable('@' . $timestamp))->setTimezone($timezone)->setTime(0, 0, 0);
    }
}

if (!function_exists('asmara_event_is_upcoming')) {
    function asmara_event_is_upcoming(array $event, ?DateTimeInterface $today = null) {
        $eventDate = asmara_event_date_object($event);
        if (!$eventDate) {
            return true;
        }

        $today = $today ?: new DateTimeImmutable('today', asmara_nairobi_timezone());
        return $eventDate >= $today;
    }
}

if (!function_exists('asmara_filter_upcoming_events')) {
    function asmara_filter_upcoming_events(array $events) {
        $filtered = array_filter($events, function ($event) {
            return asmara_event_is_upcoming($event);
        });

        return array_values($filtered);
    }
}

if (!function_exists('asmara_event_date_label')) {
    function asmara_event_date_label(array $event) {
        $eventDate = asmara_event_date_object($event);
        if (!$eventDate) {
            return 'Date TBA';
        }

        return $eventDate->format('F j, Y');
    }
}

if (!function_exists('asmara_event_image_url')) {
    function asmara_event_image_url(array $event) {
        $url = trim((string)($event['image'] ?? ''));
        if ($url === '') {
            return '';
        }

        if (strpos($url, '/backend/uploads/events/') === 0 || strpos($url, '/frontend/images/') === 0) {
            return $url;
        }

        if (strpos($url, '../backend/uploads/events/') === 0) {
            return '/' . ltrim(substr($url, 3), '/');
        }

        if (strpos($url, 'backend/uploads/events/') === 0) {
            return '/' . ltrim($url, '/');
        }

        return $url[0] === '/' ? $url : '/' . ltrim($url, '/');
    }
}

/**
 * Return an array of public URLs for all gallery images of an event.
 * Falls back to the single cover image when no gallery array is present.
 */
if (!function_exists('asmara_event_gallery_urls')) {
    function asmara_event_gallery_urls(array $event): array {
        $gallery = $event['gallery'] ?? [];
        if (!is_array($gallery) || empty($gallery)) {
            $cover = asmara_event_image_url($event);
            return $cover !== '' ? [$cover] : [];
        }

        $urls = [];
        foreach ($gallery as $path) {
            $path = trim((string)$path);
            if ($path === '') continue;
            if (strpos($path, '/backend/uploads/events/') === 0 || strpos($path, '/frontend/images/') === 0) {
                $urls[] = $path;
            } elseif (strpos($path, '../backend/uploads/events/') === 0) {
                $urls[] = '/' . ltrim(substr($path, 3), '/');
            } elseif (strpos($path, 'backend/uploads/events/') === 0) {
                $urls[] = '/' . ltrim($path, '/');
            } else {
                $urls[] = $path[0] === '/' ? $path : '/' . ltrim($path, '/');
            }
        }
        return $urls;
    }
}

/**
 * Compress and save an uploaded image using PHP GD.
 * Converts to WebP (preferred) or JPEG at quality 82 — visually lossless, ~60% smaller file.
 * Proportionally resizes if image exceeds maxWidth / maxHeight.
 *
 * Returns public path /backend/uploads/events/event_xxx.webp|jpg, or '' on failure.
 */
if (!function_exists('asmara_compress_and_save_image')) {
    function asmara_compress_and_save_image(
        string $sourceTmpPath,
        string $uploadDir,
        int $quality = 82,
        int $maxWidth = 1920,
        int $maxHeight = 1080
    ): string {
        if (!function_exists('imagecreatefromstring')) {
            return ''; // GD not available — caller falls back to move_uploaded_file
        }

        $rawData = @file_get_contents($sourceTmpPath);
        if ($rawData === false) return '';

        $src = @imagecreatefromstring($rawData);
        if ($src === false) return '';

        $origW = imagesx($src);
        $origH = imagesy($src);

        // Proportional resize only if above threshold
        $newW = $origW;
        $newH = $origH;
        if ($origW > $maxWidth || $origH > $maxHeight) {
            $ratio = min($maxWidth / $origW, $maxHeight / $origH);
            $newW  = (int)round($origW * $ratio);
            $newH  = (int)round($origH * $ratio);
        }

        if ($newW !== $origW || $newH !== $origH) {
            $resized = imagecreatetruecolor($newW, $newH);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $src, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
            imagedestroy($src);
            $src = $resized;
        }

        // Prefer WebP — superior compression
        $useWebP  = function_exists('imagewebp');
        $ext      = $useWebP ? 'webp' : 'jpg';
        $basename = uniqid('event_') . '.' . $ext;
        $destPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $basename;

        $saved = false;
        if ($useWebP) {
            $saved = @imagewebp($src, $destPath, $quality);
        }

        if (!$saved) {
            // JPEG fallback — flatten transparency onto white
            $bg    = imagecreatetruecolor($newW, $newH);
            $white = imagecolorallocate($bg, 255, 255, 255);
            imagefill($bg, 0, 0, $white);
            imagecopy($bg, $src, 0, 0, 0, 0, $newW, $newH);
            $basename = uniqid('event_') . '.jpg';
            $destPath = rtrim($uploadDir, '/\\') . DIRECTORY_SEPARATOR . $basename;
            $saved    = @imagejpeg($bg, $destPath, $quality);
            imagedestroy($bg);
        }

        imagedestroy($src);

        if (!$saved || !file_exists($destPath)) return '';

        @chmod($destPath, 0644);
        return '/backend/uploads/events/' . basename($destPath);
    }
}
