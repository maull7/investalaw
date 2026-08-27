<?php

return [
    'connect_timeout' => (int) env('AI_CONNECT_TIMEOUT', 10),
    'request_timeout' => (int) env('AI_REQUEST_TIMEOUT', 80),

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    ],

    'regulation' => [
        'max_context_characters' => (int) env('REGULATION_AI_MAX_CONTEXT_CHARACTERS', 60000),
    ],
];
