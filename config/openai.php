<?php

declare(strict_types=1);

return [
    'api_key' => env('OPENAI_API_KEY'),
    'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'timeout' => (int) env('OPENAI_TIMEOUT', 30),
    'max_tokens' => (int) env('OPENAI_MAX_TOKENS', 800),
    'temperature' => (float) env('OPENAI_TEMPERATURE', 0.2),
    'models' => [
        'gpt-4o-mini' => 'GPT-4o mini — rápido e econômico',
        'gpt-4o' => 'GPT-4o — mais preciso',
        'gpt-4.1-mini' => 'GPT-4.1 mini',
        'gpt-4.1' => 'GPT-4.1',
    ],
];
