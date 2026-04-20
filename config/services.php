<?php
// config/services.php

return [

    'mailgun'    => ['domain' => env('MAILGUN_DOMAIN'), 'secret' => env('MAILGUN_SECRET'), 'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net')],
    'postmark'   => ['token' => env('POSTMARK_TOKEN')],
    'ses'        => ['key' => env('AWS_ACCESS_KEY_ID'), 'secret' => env('AWS_SECRET_ACCESS_KEY'), 'region' => env('AWS_DEFAULT_REGION', 'us-east-1')],

    // ── OpenRouter (free AI API) ──────────────────────────────────────────
    'openrouter' => [
        'key'   => env('OPENROUTER_API_KEY', ''),
        'url'   => env('OPENROUTER_BASE_URL', 'https://openrouter.ai/api/v1'),
        'model' => env('AI_MODEL', 'deepseek/deepseek-r1:free'),
    ],

];