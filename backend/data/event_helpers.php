<?php

if (!function_exists('asmara_nairobi_timezone')) {
    function asmara_nairobi_timezone() {
        return new DateTimeZone('Africa/Nairobi');
    }
}

if (!function_exists('asmara_event_date_string')) {
    /**
     * Backwards-compatible date accessor. Prefers end_date (the "event date"),
     * falls back to start_date, then to the legacy event_date field.
     */
    function asmara_event_date_string(array $event) {
        foreach (['end_date', 'start_date', 'event_date', 'date'] as $key) {
            if (!empty($event[$key])) {
                return trim((string) $event[$key]);
            }
        }
        return '';
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

if (!function_exists('asmara_event_start_date_object')) {
    function asmara_event_start_date_object(array $event): ?DateTimeImmutable {
        $value = trim((string)($event['start_date'] ?? ''));
        if ($value === '') {
            // Backwards compat — events without start_date fall back to event_date
            $value = trim((string)($event['event_date'] ?? ''));
            if ($value === '') return null;
        }
        $timezone = asmara_nairobi_timezone();
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value, $timezone);
        if ($date instanceof DateTimeImmutable) return $date->setTime(0, 0, 0);
        $ts = strtotime($value);
        if ($ts === false) return null;
        return (new DateTimeImmutable('@' . $ts))->setTimezone($timezone)->setTime(0, 0, 0);
    }
}

if (!function_exists('asmara_event_end_date_object')) {
    function asmara_event_end_date_object(array $event): ?DateTimeImmutable {
        $value = trim((string)($event['end_date'] ?? ''));
        if ($value === '') {
            return asmara_event_start_date_object($event);
        }
        $timezone = asmara_nairobi_timezone();
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $value, $timezone);
        if ($date instanceof DateTimeImmutable) return $date->setTime(0, 0, 0);
        $ts = strtotime($value);
        if ($ts === false) return null;
        return (new DateTimeImmutable('@' . $ts))->setTimezone($timezone)->setTime(0, 0, 0);
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
        // Exclude only explicitly inactive events
        $activeEvents = array_values(array_filter($events, function ($event) {
            return ($event['status'] ?? 'active') !== 'inactive';
        }));

        if (empty($activeEvents)) {
            return [];
        }

        // Split into upcoming and past/ongoing
        $upcoming = [];
        $past = [];
        $today = new DateTimeImmutable('today', asmara_nairobi_timezone());

        foreach ($activeEvents as $event) {
            $eventDate = asmara_event_date_object($event);
            if (!$eventDate || $eventDate >= $today) {
                $upcoming[] = $event;
            } else {
                $past[] = $event;
            }
        }

        // Sort upcoming ascending by date
        usort($upcoming, function ($a, $b) {
            $da = asmara_event_date_object($a);
            $db = asmara_event_date_object($b);
            if (!$da && !$db) return 0;
            if (!$da) return 1;
            if (!$db) return -1;
            return $da <=> $db;
        });

        // Sort past descending (most recent first)
        usort($past, function ($a, $b) {
            $da = asmara_event_date_object($a);
            $db = asmara_event_date_object($b);
            if (!$da && !$db) return 0;
            if (!$da) return 1;
            if (!$db) return -1;
            return $db <=> $da;
        });

        // Return upcoming first, followed by other active events so nothing disappears
        return array_merge($upcoming, $past);
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

if (!function_exists('asmara_event_date_range_label')) {
    /**
     * Smart date-range label:
     *   same day          → "February 14, 2026"
     *   same month/year   → "Feb 14 – 18, 2026"
     *   same year         → "Feb 28 – Mar 02, 2026"
     *   different years   → "Dec 30, 2025 – Jan 02, 2026"
     * Returns 'Date TBA' if no dates are set.
     */
    function asmara_event_date_range_label(array $event): string {
        $start = asmara_event_start_date_object($event);
        $end   = asmara_event_end_date_object($event);
        if (!$start && !$end) return 'Date TBA';

        $tz = asmara_nairobi_timezone();
        $now = new DateTimeImmutable('now', $tz);

        if ($start && !$end) {
            return $start->format('F j, Y');
        }
        if (!$start && $end) {
            return $end->format('F j, Y');
        }

        // Both set
        if ($start->format('Y-m-d') === $end->format('Y-m-d')) {
            return $start->format('F j, Y');
        }
        if ($start->format('Y-m') === $end->format('Y-m')) {
            return $start->format('M j') . ' – ' . $end->format('j, Y');
        }
        if ($start->format('Y') === $end->format('Y')) {
            return $start->format('M j') . ' – ' . $end->format('M j, Y');
        }
        return $start->format('M j, Y') . ' – ' . $end->format('M j, Y');
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
 * Normalise one stored gallery path to a public URL.
 */
if (!function_exists('asmara_normalise_event_path')) {
    function asmara_normalise_event_path(string $path): string {
        $path = trim($path);
        if ($path === '') return '';
        if (strpos($path, '/backend/uploads/events/') === 0 || strpos($path, '/frontend/images/') === 0) {
            return $path;
        }
        if (strpos($path, '../backend/uploads/events/') === 0) {
            return '/' . ltrim(substr($path, 3), '/');
        }
        if (strpos($path, 'backend/uploads/events/') === 0) {
            return '/' . ltrim($path, '/');
        }
        return $path[0] === '/' ? $path : '/' . ltrim($path, '/');
    }
}

/**
 * Return an array of public URLs for all gallery images of an event.
 * The featured / cover image is always first; the remaining gallery photos
 * follow (deduplicated). Falls back to the cover image when gallery is empty.
 */
if (!function_exists('asmara_event_gallery_urls')) {
    function asmara_event_gallery_urls(array $event): array {
        $gallery = $event['gallery'] ?? [];
        $cover   = asmara_event_image_url($event);

        // Build candidate gallery list from stored array
        $candidates = [];
        if (is_array($gallery)) {
            foreach ($gallery as $path) {
                $path = trim((string)$path);
                if ($path === '') continue;
                $candidates[] = $path;
            }
        }

        // If no cover set, promote the first gallery item as cover
        if ($cover === '' && !empty($candidates)) {
            $cover = $candidates[0];
            array_shift($candidates);
        }

        // Final ordered list starts with the cover (if any)
        $ordered = [];
        if ($cover !== '') $ordered[] = $cover;
        foreach ($candidates as $path) {
            $ordered[] = $path;
        }

        // De-duplicate while preserving order
        $seen = [];
        $urls = [];
        foreach ($ordered as $path) {
            $url = asmara_normalise_event_path($path);
            if ($url === '') continue;
            if (isset($seen[$url])) continue;
            $seen[$url] = true;
            $urls[] = $url;
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

        // Ensure destination folder exists
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0755, true);
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
