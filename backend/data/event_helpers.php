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
