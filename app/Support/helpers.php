<?php

if (! function_exists('semester_name')) {

    function semester_name(?int $semester): string
    {
        return match ($semester) {
            1 => 'First',
            2 => 'Second',
            3 => 'Full',
            default => '-',
        };
    }
}

if (! function_exists('student_portal_url')) {

    /**
     * Return the Student Portal base URL without the API suffix.
     */
    function student_portal_url(string $path = ''): string
    {
        $base = rtrim(config('services.student_portal.url'), '/');

        // Remove "/api/v1" only if it is the trailing segment.
        $base = preg_replace('#/api/v1$#', '', $base);

        return $base . '/' . ltrim($path, '/');
    }
}



function file_url($path, $category = 'students')
{
    if (!$path) return null;

    $path = ltrim($path, '/');

    return rtrim(env('FILES_BASE_URL'), '/')
        . "/uploads/{$category}/"
        . $path;
}
