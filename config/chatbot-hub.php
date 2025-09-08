<?php

return [
    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'api.openai.com/v1'),
        'organization' => env('OPENAI_ORGANIZATION'),
    ],
    'perplexity' => [
        'api_key' => env('PERPLEXITY_API_KEY'),
    ],
    'claude' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'base_url' => env('ANTHROPIC_BASE_URL'),
    ],
    'managers' => [
        'agent' => [
            'namespaces' => ['App\Plugins'],
        ],
        'agent_tool' => [
            'namespaces' => ['App\Plugins'],
        ],
        'agent_coordinator' => [
            'namespaces' => ['App\Plugins'],
        ],
    ],
];
