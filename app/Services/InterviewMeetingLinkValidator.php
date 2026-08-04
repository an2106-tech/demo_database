<?php

namespace App\Services;

class InterviewMeetingLinkValidator
{
    public function isValid(string $url): bool
    {
        $url = trim($url);

        if ($url === '' || mb_strlen($url) > 500 || ! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);

        if ($scheme !== 'https' || $host === '') {
            return false;
        }

        if ($host === 'meet.google.com' || str_ends_with($host, '.meet.google.com')) {
            return (bool) preg_match('#^/[a-z]{3,4}-[a-z]{4}-[a-z]{3}(?:/|$)#i', $path);
        }

        if ($host === 'zoom.us' || str_ends_with($host, '.zoom.us')) {
            return (bool) preg_match('#^/(?:j/\d+|wc/[^/]+/join)(?:/|$)#i', $path);
        }

        if ($host === 'teams.microsoft.com' || str_ends_with($host, '.teams.microsoft.com')) {
            return str_starts_with($path, '/l/meetup-join/');
        }

        return true;
    }
}
