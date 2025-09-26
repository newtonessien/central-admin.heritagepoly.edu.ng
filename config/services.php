<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

//     'admissions' => [
//     'url' => env('ADMISSIONS_API_URL'),
//     'token' => env('ADMISSIONS_API_TOKEN'),
//   ],

'admissions' => [
    'url' => env('APP_ENV') === 'local'
        ? 'https://admissions.heritagepoly.edu.ng.test/api/v1' // local Herd
        : env('ADMISSIONS_API_URL'),                        // production
    'token' => env('ADMISSIONS_API_TOKEN'),
],


'student_portal' => [
    'url' => env('APP_ENV') === 'local'
        ? 'https://students.heritagepoly.edu.ng.test/api/v1' // local Herd
        : env('STUDENT_PORTAL_URL'),                             // production
    'token' => env('STUDENT_PORTAL_TOKEN'),
],

// 'student_portal_central' => [
//     'url' => env('APP_ENV') === 'local'
//         ? 'https://students.heritagepoly.edu.ng.test/api/v1'
//         : env('CENTRAL_STUDENT_API_URL'),
//     'token' => env('CENTRAL_STUDENT_API_TOKEN'),
// ],

// 'student_portal_central' => [
//     'url'   => env('CENTRAL_STUDENT_API_URL'),
//     'token' => env('CENTRAL_STUDENT_API_TOKEN'),
// ],


// Admissions Portal to be used and remove admissions above
'admissions_portal' => [
    'base_url' => env('ADMISSIONS_PORTAL_BASE_URL'),
    'token'    => env('ADMISSIONS_PORTAL_TOKEN'),
],

    'excluded_application_types' => array_filter(
        explode(',', env('EXCLUDED_APPLICATION_TYPES', ''))
    ),



];
