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

    /*
    |--------------------------------------------------------------------------
    | Memory Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the memory system for agents. Agents can store and retrieve
    | key-value data using different strategies (session, database, etc.).
    | Memory instances are shared across agents in coordinator executions.
    |
    */
    'memory' => [
        /*
        |--------------------------------------------------------------------------
        | Default Memory Strategy
        |--------------------------------------------------------------------------
        |
        | This is the default memory strategy used when creating memory instances.
        | Supported: "session", "database", "null"
        |
        */
        'default' => env('CHATBOT_MEMORY_DRIVER', 'session'),

        /*
        |--------------------------------------------------------------------------
        | Default TTL (Time To Live)
        |--------------------------------------------------------------------------
        |
        | Default time-to-live in seconds for memory entries when no TTL is specified.
        | Set to 0 for no expiration. Default is 1 hour (3600 seconds).
        |
        */
        'default_ttl' => (int) env('CHATBOT_MEMORY_TTL', 3600),

        /*
        |--------------------------------------------------------------------------
        | Memory Strategy Configurations
        |--------------------------------------------------------------------------
        |
        | Configure each memory strategy with its specific settings.
        | Each strategy can be enabled/disabled and customized as needed.
        |
        */
        'strategies' => [
            /*
            |--------------------------------------------------------------------------
            | Session Memory Strategy
            |--------------------------------------------------------------------------
            |
            | Stores memory data in the Laravel session. Fast and request-scoped,
            | but data is lost when the session expires or application restarts.
            |
            */
            'session' => [
                'enabled' => (bool) env('CHATBOT_MEMORY_SESSION_ENABLED', true),
                'prefix' => env('CHATBOT_MEMORY_SESSION_PREFIX', 'agent_memory'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Database Memory Strategy
            |--------------------------------------------------------------------------
            |
            | Stores memory data in the database. Persistent across requests and
            | application restarts, but requires database queries for operations.
            |
            */
            'database' => [
                'enabled' => (bool) env('CHATBOT_MEMORY_DATABASE_ENABLED', true),
                'connection' => env('CHATBOT_MEMORY_DATABASE_CONNECTION', null),
                'table' => env('CHATBOT_MEMORY_DATABASE_TABLE', 'agent_memory'),
                'cleanup_probability' => (int) env('CHATBOT_MEMORY_DATABASE_CLEANUP_PROBABILITY', 100),
            ],

            /*
            |--------------------------------------------------------------------------
            | Null Memory Strategy
            |--------------------------------------------------------------------------
            |
            | No-op memory strategy for testing or when memory is not needed.
            | All operations succeed but no data is actually stored or retrieved.
            |
            */
            'null' => [
                'enabled' => (bool) env('CHATBOT_MEMORY_NULL_ENABLED', false),
            ],
        ],
    ],
];
