<?php

return [
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'api.openai.com/v1'),
        'organization' => env('OPENAI_ORGANIZATION'),
    ],
    'perplexity' => [
        'api_key' => env('PERPLEXITY_API_KEY'),
    ]
];
